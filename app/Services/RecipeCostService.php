<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RecipeCostService
{
    /**
     * Results already calculated during the current request/scope.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $costCache = [];

    /**
     * Recipes currently being calculated. Used to break circular references
     * left behind by bad data instead of recursing forever.
     *
     * @var array<int, bool>
     */
    private array $calculating = [];

    /**
     * Calculate the cost of a recipe.
     *
     * `total_cost` is the cost of the whole recipe as written. `cost_per_serving`
     * is what consumers should use when costing a single portion (menus do this).
     *
     * @return array<string, mixed>
     */
    public function calculateRecipeCost(Recipe $recipe): array
    {
        if (isset($this->costCache[$recipe->id])) {
            return $this->costCache[$recipe->id];
        }

        if (isset($this->calculating[$recipe->id])) {
            return $this->emptyResult($recipe, [
                "Circular recipe dependency detected while costing {$recipe->recipe_name}.",
            ]);
        }

        $this->calculating[$recipe->id] = true;

        try {
            $result = $this->doCalculateRecipeCost($recipe);
        } finally {
            unset($this->calculating[$recipe->id]);
        }

        return $this->costCache[$recipe->id] = $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function doCalculateRecipeCost(Recipe $recipe): array
    {
        $totalCost = 0.0;
        $itemCosts = [];
        $subRecipeCosts = [];
        $errors = [];

        $recipe->loadMissing([
            'items' => fn ($query) => $query->withPivot('unit_id', 'quantity'),
            'items.orderingUnit.unitType',
            'items.countingUnit.unitType',
            'subRecipes' => fn ($query) => $query->withPivot('quantity'),
        ]);

        $units = $this->loadUnitsForRecipe($recipe);

        foreach ($recipe->items as $item) {
            $pivotData = $item->pivot;
            $quantity = (float) $pivotData->quantity;
            $unitId = $pivotData->unit_id;
            $unit = $unitId ? $units->get((int) $unitId) : null;

            try {
                if ($unit && $quantity > 0 && (float) $item->latest_price > 0) {
                    $itemCost = round($item->calculateCostForUnit($unit, $quantity), 4);
                    $totalCost += $itemCost;

                    $itemCosts[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'quantity' => $quantity,
                        'unit' => $unit->name,
                        'unit_cost' => $item->latest_price ?? 0,
                        'ordering_unit' => $item->orderingUnit ? $item->orderingUnit->name : 'N/A',
                        'cost_per_unit' => $item->getFormattedCostPerUnit(),
                        'total_cost' => round($itemCost, 2),
                        'can_calculate' => true,
                    ];
                } else {
                    $error = match (true) {
                        ! $unit => "Unit not found for ingredient: {$item->item_name}",
                        $quantity <= 0 => "Invalid quantity for ingredient: {$item->item_name}",
                        default => "No price available for ingredient: {$item->item_name}",
                    };
                    $errors[] = $error;

                    $itemCosts[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'quantity' => $quantity,
                        'unit' => $unit ? $unit->name : 'N/A',
                        'unit_cost' => $item->latest_price ?? 0,
                        'ordering_unit' => $item->orderingUnit ? $item->orderingUnit->name : 'N/A',
                        'cost_per_unit' => $item->getFormattedCostPerUnit(),
                        'total_cost' => 0,
                        'can_calculate' => false,
                        'error' => $error,
                    ];
                }
            } catch (\Throwable $e) {
                $errors[] = "Error calculating cost for {$item->item_name}: {$e->getMessage()}";
                Log::error('Recipe cost calculation error', [
                    'recipe_id' => $recipe->id,
                    'item_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);

                $itemCosts[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $quantity,
                    'unit' => $unit ? $unit->name : 'N/A',
                    'unit_cost' => $item->latest_price ?? 0,
                    'ordering_unit' => $item->orderingUnit ? $item->orderingUnit->name : 'N/A',
                    'cost_per_unit' => $item->getFormattedCostPerUnit(),
                    'total_cost' => 0,
                    'can_calculate' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        foreach ($recipe->subRecipes as $subRecipe) {
            $quantity = (float) ($subRecipe->pivot->quantity ?? 1);

            try {
                $subRecipeCostData = $this->calculateRecipeCost($subRecipe);

                // A sub-recipe quantity is a number of batches, so use its
                // whole-recipe cost rather than its per-serving cost.
                $subRecipeCost = round($subRecipeCostData['total_cost'] * $quantity, 4);
                $totalCost += $subRecipeCost;

                $subRecipeCosts[] = [
                    'recipe_id' => $subRecipe->id,
                    'recipe_name' => $subRecipe->recipe_name,
                    'quantity' => $quantity,
                    'unit_cost' => round($subRecipeCostData['total_cost'], 2),
                    'total_cost' => round($subRecipeCost, 2),
                    'breakdown' => $subRecipeCostData,
                    'can_calculate' => $subRecipeCostData['can_calculate_full_cost'],
                ];

                if (! empty($subRecipeCostData['errors'])) {
                    $errors = array_merge($errors, $subRecipeCostData['errors']);
                }
            } catch (\Throwable $e) {
                $errors[] = "Error calculating cost for sub-recipe {$subRecipe->recipe_name}: {$e->getMessage()}";
                Log::error('Sub-recipe cost calculation error', [
                    'recipe_id' => $recipe->id,
                    'sub_recipe_id' => $subRecipe->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $servings = max(1, (int) ($recipe->servings ?: 1));
        $totalCost = round($totalCost, 2);
        $costPerServing = round($totalCost / $servings, 2);

        return [
            'recipe_id' => $recipe->id,
            'recipe_name' => $recipe->recipe_name,
            'servings' => $servings,
            'total_cost' => $totalCost,
            'cost_per_serving' => $costPerServing,
            'cost_per_serving_formatted' => $this->formatMoney($costPerServing),
            'total_cost_formatted' => $this->formatMoney($totalCost),
            'item_costs' => $itemCosts,
            'sub_recipe_costs' => $subRecipeCosts,
            'errors' => $errors,
            'can_calculate_full_cost' => empty($errors),
            'ingredients_count' => count($itemCosts),
            'sub_recipes_count' => count($subRecipeCosts),
        ];
    }

    /**
     * Load every pivot unit in one query and preload its unit type so that
     * Unit::convertTo() does not hit the database for each ingredient.
     *
     * @return Collection<int, Unit>
     */
    private function loadUnitsForRecipe(Recipe $recipe): Collection
    {
        $unitIds = $recipe->items
            ->pluck('pivot.unit_id')
            ->filter()
            ->unique()
            ->values();

        if ($unitIds->isEmpty()) {
            return collect();
        }

        return Unit::with('unitType')
            ->whereIn('id', $unitIds->all())
            ->get()
            ->keyBy('id');
    }

    /**
     * Calculate the cost of making N servings of a recipe.
     *
     * @return array<string, mixed>
     */
    public function calculateCostBasedOnQuantity(Recipe $recipe, float $servings = 1): array
    {
        $servings = max(0.01, $servings);
        $baseCostData = $this->calculateRecipeCost($recipe);
        $costPerServing = $baseCostData['cost_per_serving'];
        $totalCost = round($costPerServing * $servings, 2);

        return [
            'total_cost' => $totalCost,
            'servings' => $servings,
            'cost_per_serving' => $costPerServing,
            'cost_per_serving_formatted' => $this->formatMoney($costPerServing),
            'total_cost_formatted' => $this->formatMoney($totalCost),
            'breakdown' => $baseCostData,
        ];
    }

    /**
     * Get cost summaries for multiple recipes.
     *
     * @param  array<int, int|string>  $recipeIds
     * @return array<int, array<string, mixed>>
     */
    public function calculateMultipleRecipesCost(array $recipeIds): array
    {
        $recipeIds = array_values(array_unique(array_map('intval', $recipeIds)));

        if ($recipeIds === []) {
            return [];
        }

        $recipes = Recipe::with([
            'items' => fn ($query) => $query->withPivot('unit_id', 'quantity'),
            'items.orderingUnit.unitType',
            'items.countingUnit.unitType',
            'subRecipes' => fn ($query) => $query->withPivot('quantity'),
        ])->whereIn('id', $recipeIds)->get();

        return $recipes
            ->map(fn (Recipe $recipe) => $this->calculateRecipeCost($recipe))
            ->all();
    }

    /**
     * Forget cached cost data, optionally for a single recipe.
     */
    public function forget(?int $recipeId = null): void
    {
        if ($recipeId === null) {
            $this->costCache = [];
            $this->calculating = [];

            return;
        }

        unset($this->costCache[$recipeId], $this->calculating[$recipeId]);
    }

    /**
     * @param  array<int, string>  $errors
     * @return array<string, mixed>
     */
    private function emptyResult(Recipe $recipe, array $errors): array
    {
        $servings = max(1, (int) ($recipe->servings ?: 1));

        return [
            'recipe_id' => $recipe->id,
            'recipe_name' => $recipe->recipe_name,
            'servings' => $servings,
            'total_cost' => 0.0,
            'cost_per_serving' => 0.0,
            'cost_per_serving_formatted' => $this->formatMoney(0),
            'total_cost_formatted' => $this->formatMoney(0),
            'item_costs' => [],
            'sub_recipe_costs' => [],
            'errors' => $errors,
            'can_calculate_full_cost' => false,
            'ingredients_count' => 0,
            'sub_recipes_count' => 0,
        ];
    }

    private function formatMoney(float $amount): string
    {
        return '$'.number_format($amount, 2);
    }
}
