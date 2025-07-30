<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecipeRequest;
use App\Services\RecipeService;
use App\Services\RecipeCostService;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function __construct(
        private RecipeService $recipeService,
        private RecipeCostService $recipeCostService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
        $recipes = $this->recipeService->getRecipes($perPage);

        return response()->json([
            'message' => 'List of recipes',
            'recipes' => $recipes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RecipeRequest $request)
    {
        $recipe = $this->recipeService->createRecipe($request->validated());

        return response()->json([
            'message' => 'Recipe created successfully',
            'recipe' => $recipe,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $recipe = $this->recipeService->getRecipe($id);

        if (!$recipe) {
            return response()->json([
                'message' => 'Recipe not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Recipe details',
            'recipe' => $recipe,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RecipeRequest $request, string $id)
    {
        $recipe = $this->recipeService->updateRecipe($id, $request->validated());

        if (!$recipe) {
            return response()->json([
                'message' => 'Recipe not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Recipe updated successfully',
            'recipe' => $recipe,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->recipeService->deleteRecipe($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Recipe deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Recipe not found or could not be deleted',
        ], 404);
    }

    /**
     * Get dropdown data for recipe form
     */
    public function getDropdownData(string $excludeRecipeId = null)
    {
        $items = \App\Models\Item::select('id', 'item_name', 'counting_unit_id')->orderBy('item_name')->get();
        $recipes = $this->recipeService->getRecipesForDropdown($excludeRecipeId);

        return response()->json([
            'items' => $items,
            'recipes' => $recipes,
        ]);
    }

    /**
     * Calculate recipe cost
     */
    public function calculateCost(string $id)
    {
        try {
            $recipe = $this->recipeService->getRecipe($id);

            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe not found',
                ], 404);
            }

            $costData = $this->recipeCostService->calculateRecipeCost($recipe);

            return response()->json([
                'success' => true,
                'cost_data' => $costData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating recipe cost',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate cost per quantity (servings/units)
     */
    public function calculateCostPerServing(string $id, Request $request)
    {
        try {
            $recipe = $this->recipeService->getRecipe($id);

            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe not found',
                ], 404);
            }

            $quantity = $request->input('quantity', $request->input('servings', 1)); // Accept both for backward compatibility

            if ($quantity <= 0) {
                return response()->json([
                    'message' => 'Quantity must be greater than 0',
                ], 400);
            }

            $costData = $this->recipeCostService->calculateCostPerServing($recipe, $quantity);

            return response()->json([
                'success' => true,
                'cost_data' => $costData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating recipe cost per serving',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cost summary for multiple recipes
     */
    public function calculateMultipleCosts(Request $request)
    {
        try {
            $recipeIds = $request->input('recipe_ids', []);

            if (empty($recipeIds)) {
                return response()->json([
                    'message' => 'No recipe IDs provided',
                ], 400);
            }

            $costData = $this->recipeCostService->calculateMultipleRecipesCost($recipeIds);

            return response()->json([
                'success' => true,
                'cost_data' => $costData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating multiple recipe costs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
