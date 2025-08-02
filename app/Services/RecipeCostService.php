<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;

class RecipeCostService
{
    /**
     * Calculate total cost of a recipe
     *
     * @param Recipe $recipe
     * @return array
     */
    public function calculateRecipeCost(Recipe $recipe)
    {
        $totalCost = 0;
        $itemCosts = [];
        $subRecipeCosts = [];
        $errors = [];

        // Calculate cost for ingredients
        foreach ($recipe->items as $item) {
            $pivotData = $item->pivot;
            $quantity = $pivotData->quantity;
            $unitId = $pivotData->unit_id;

            try {
                if ($unitId && $quantity > 0) {
                    $unit = Unit::find($unitId);
                    if ($unit && $item->latest_price > 0) {
                        $itemCost = $item->calculateCostForUnit($unit, $quantity);
                        $totalCost += $itemCost;

                        $itemCosts[] = [
                            'item_id' => $item->id,
                            'item_name' => $item->item_name,
                            'quantity' => $quantity,
                            'unit' => $unit->name,
                            'unit_cost' => $item->latest_price ?? 0,
                            'ordering_unit' => $item->orderingUnit ? $item->orderingUnit->name : 'N/A',
                            'cost_per_unit' => $item->getFormattedCostPerUnit(),
                            'total_cost' => $itemCost,
                            'can_calculate' => true
                        ];
                    } else {
                        $error = !$unit ? "Unit not found for ingredient: {$item->item_name}" : 
                                         "No price available for ingredient: {$item->item_name}";
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
                            'error' => $error
                        ];
                    }
                } else {
                    $error = !$unitId ? 'Missing unit' : 'Missing or invalid quantity';
                    $errors[] = "Invalid data for ingredient {$item->item_name}: {$error}";
                    
                    $itemCosts[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'quantity' => $quantity,
                        'unit' => 'N/A',
                        'unit_cost' => $item->latest_price ?? 0,
                        'ordering_unit' => $item->orderingUnit ? $item->orderingUnit->name : 'N/A',
                        'cost_per_unit' => $item->getFormattedCostPerUnit(),
                        'total_cost' => 0,
                        'can_calculate' => false,
                        'error' => $error
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = "Error calculating cost for {$item->item_name}: " . $e->getMessage();
                Log::error("Recipe cost calculation error", [
                    'recipe_id' => $recipe->id,
                    'item_id' => $item->id,
                    'error' => $e->getMessage()
                ]);
                
                $itemCosts[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $quantity,
                    'unit' => 'N/A',
                    'unit_cost' => $item->latest_price ?? 0,
                    'ordering_unit' => $item->orderingUnit ? $item->orderingUnit->name : 'N/A',
                    'cost_per_unit' => $item->getFormattedCostPerUnit(),
                    'total_cost' => 0,
                    'can_calculate' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Calculate cost for sub-recipes (recursive)
        foreach ($recipe->subRecipes as $subRecipe) {
            $pivotData = $subRecipe->pivot;
            $quantity = $pivotData->quantity ?? 1;

            try {
                $subRecipeCostData = $this->calculateRecipeCost($subRecipe);
                $subRecipeCost = $subRecipeCostData['total_cost'] * $quantity;
                $totalCost += $subRecipeCost;

                $subRecipeCosts[] = [
                    'recipe_id' => $subRecipe->id,
                    'recipe_name' => $subRecipe->recipe_name,
                    'quantity' => $quantity,
                    'unit_cost' => $subRecipeCostData['total_cost'],
                    'total_cost' => $subRecipeCost,
                    'breakdown' => $subRecipeCostData,
                    'can_calculate' => $subRecipeCostData['total_cost'] > 0
                ];
            } catch (\Exception $e) {
                $errors[] = "Error calculating cost for sub-recipe {$subRecipe->recipe_name}: " . $e->getMessage();
                Log::error("Sub-recipe cost calculation error", [
                    'recipe_id' => $recipe->id,
                    'sub_recipe_id' => $subRecipe->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'recipe_id' => $recipe->id,
            'recipe_name' => $recipe->recipe_name,
            'total_cost' => round($totalCost, 2),
            'item_costs' => $itemCosts,
            'sub_recipe_costs' => $subRecipeCosts,
            'errors' => $errors,
            'can_calculate_full_cost' => empty($errors),
            'ingredients_count' => count($itemCosts),
            'sub_recipes_count' => count($subRecipeCosts)
        ];
    }

    /**
     * Calculate cost per serving
     *
     * @param Recipe $recipe
     * @param int $servings
     * @return array
     */
    public function calculateCostPerServing(Recipe $recipe, $servings = 1)
    {
        $baseCostData = $this->calculateRecipeCost($recipe);
        $baseCost = $baseCostData['total_cost']; // Cost for 1 unit
        $totalCost = $baseCost * $servings; // Total cost for all units

        return [
            'total_cost' => $totalCost,
            'servings' => $servings,
            'cost_per_serving' => round($baseCost, 2), // Cost per individual unit
            'cost_per_serving_formatted' => '$' . number_format($baseCost, 2),
            'total_cost_formatted' => '$' . number_format($totalCost, 2),
            'breakdown' => $baseCostData
        ];
    }

    /**
     * Get cost summary for multiple recipes
     *
     * @param array $recipeIds
     * @return array
     */
    public function calculateMultipleRecipesCost(array $recipeIds)
    {
        $recipes = Recipe::with([
            'items.orderingUnit', 
            'items.countingUnit',
            'items' => function($query) {
                $query->withPivot('unit_id', 'quantity');
            },
            'subRecipes' => function($query) {
                $query->withPivot('quantity');
            }
        ])->whereIn('id', $recipeIds)->get();
        $results = [];

        foreach ($recipes as $recipe) {
            $results[] = $this->calculateRecipeCost($recipe);
        }

        return $results;
    }
}
