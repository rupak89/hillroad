<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecipeRequest;
use App\Services\RecipeCostService;
use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    public function __construct(
        private readonly RecipeService $recipeService,
        private readonly RecipeCostService $recipeCostService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $recipes = $this->recipeService->getRecipes((int) ($validated['per_page'] ?? 10));

        return response()->json([
            'message' => 'List of recipes',
            'recipes' => $recipes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecipeRequest $request): JsonResponse
    {
        try {
            $recipe = $this->recipeService->createRecipe($request->validated());

            return response()->json([
                'message' => 'Recipe created successfully',
                'recipe' => $recipe,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return $this->serverError('Error creating recipe', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'message' => 'Recipe details',
            'recipe' => $this->recipeService->getRecipe((int) $id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecipeRequest $request, string $id): JsonResponse
    {
        try {
            $recipe = $this->recipeService->updateRecipe((int) $id, $request->validated());

            return response()->json([
                'message' => 'Recipe updated successfully',
                'recipe' => $recipe,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return $this->serverError('Error updating recipe', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->recipeService->deleteRecipe((int) $id);

        return response()->json([
            'message' => 'Recipe deleted successfully',
        ]);
    }

    /**
     * Get dropdown data for recipe form.
     */
    public function getDropdownData(?string $excludeRecipeId = null): JsonResponse
    {
        $items = \App\Models\Item::select('id', 'item_name', 'counting_unit_id')
            ->orderBy('item_name')
            ->get();

        $recipes = $this->recipeService->getRecipesForDropdown(
            $excludeRecipeId !== null ? (int) $excludeRecipeId : null
        );

        return response()->json([
            'items' => $items,
            'recipes' => $recipes,
        ]);
    }

    /**
     * Calculate recipe cost.
     */
    public function calculateCost(string $id): JsonResponse
    {
        try {
            $recipe = $this->recipeService->getRecipe((int) $id);
            $costData = $this->recipeCostService->calculateRecipeCost($recipe);

            return response()->json([
                'success' => true,
                'cost_data' => $costData,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Error calculating recipe cost', $e);
        }
    }

    /**
     * Calculate cost for a number of servings.
     */
    public function calculateCostBasedOnQuantity(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'nullable|numeric|min:0.01|max:100000',
            'servings' => 'nullable|numeric|min:0.01|max:100000',
        ]);

        $quantity = (float) ($validated['quantity'] ?? $validated['servings'] ?? 1);

        try {
            $recipe = $this->recipeService->getRecipe((int) $id);
            $costData = $this->recipeCostService->calculateCostBasedOnQuantity($recipe, $quantity);

            return response()->json([
                'success' => true,
                'cost_data' => $costData,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->serverError('Error calculating recipe cost per serving', $e);
        }
    }

    /**
     * Get cost summaries for multiple recipes.
     */
    public function calculateMultipleCosts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipe_ids' => 'required|array|min:1|max:100',
            'recipe_ids.*' => 'integer|distinct',
        ]);

        try {
            $costData = $this->recipeCostService->calculateMultipleRecipesCost($validated['recipe_ids']);

            return response()->json([
                'success' => true,
                'cost_data' => $costData,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError('Error calculating multiple recipe costs', $e);
        }
    }

    /**
     * Log the failure and return a response that does not leak internals.
     */
    private function serverError(string $message, \Throwable $e): JsonResponse
    {
        report($e);

        return response()->json([
            'success' => false,
            'message' => $message,
        ], 500);
    }
}
