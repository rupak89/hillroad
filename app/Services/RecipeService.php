<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    /**
     * Get paginated list of recipes.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRecipes($perPage = 10)
    {
        return Recipe::orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new recipe.
     *
     * @param array $data
     * @return Recipe
     */
    public function createRecipe(array $data): Recipe
    {
        return DB::transaction(function () use ($data) {
            // Create the recipe
            $recipe = Recipe::create([
                'recipe_name' => $data['recipe_name'],
                'instruction' => $data['instruction'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
            ]);

            // Attach ingredients
            if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                foreach ($data['ingredients'] as $ingredient) {
                    if (!empty($ingredient['item_id']) && !empty($ingredient['quantity'])) {
                        $recipe->items()->attach($ingredient['item_id'], [
                            'unit_id' => $ingredient['unit_id'] ?? null,
                            'quantity' => $ingredient['quantity'],
                        ]);
                    }
                }
            }

            // Attach sub-recipes
            if (isset($data['sub_recipes']) && is_array($data['sub_recipes'])) {
                foreach ($data['sub_recipes'] as $subRecipe) {
                    if (!empty($subRecipe['child_recipe_id']) && !empty($subRecipe['quantity'])) {
                        $recipe->subRecipes()->attach($subRecipe['child_recipe_id'], [
                            'quantity' => $subRecipe['quantity'],
                        ]);
                    }
                }
            }

            return $recipe->load(['items', 'subRecipes']);
        });
    }

    /**
     * Get a single recipe by ID.
     *
     * @param int $id
     * @return Recipe|null
     */
    public function getRecipe(int $id): ?Recipe
    {
        return Recipe::with(['items', 'subRecipes'])->find($id);
    }

    /**
     * Update a recipe.
     *
     * @param int $id
     * @param array $data
     * @return Recipe|null
     */
    public function updateRecipe(int $id, array $data): ?Recipe
    {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            return null;
        }

        return DB::transaction(function () use ($recipe, $data) {
            // Update the recipe
            $recipe->update([
                'recipe_name' => $data['recipe_name'],
                'instruction' => $data['instruction'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
            ]);

            // Sync ingredients
            $ingredientData = [];
            if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                foreach ($data['ingredients'] as $ingredient) {
                    if (!empty($ingredient['item_id']) && !empty($ingredient['quantity'])) {
                        $ingredientData[$ingredient['item_id']] = [
                            'unit_id' => $ingredient['unit_id'] ?? null,
                            'quantity' => $ingredient['quantity'],
                        ];
                    }
                }
            }
            $recipe->items()->sync($ingredientData);

            // Sync sub-recipes
            $subRecipeData = [];
            if (isset($data['sub_recipes']) && is_array($data['sub_recipes'])) {
                foreach ($data['sub_recipes'] as $subRecipe) {
                    if (!empty($subRecipe['child_recipe_id']) && !empty($subRecipe['quantity'])) {
                        $subRecipeData[$subRecipe['child_recipe_id']] = [
                            'quantity' => $subRecipe['quantity'],
                        ];
                    }
                }
            }
            $recipe->subRecipes()->sync($subRecipeData);

            return $recipe->fresh(['items', 'subRecipes']);
        });
    }

    /**
     * Delete a recipe.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteRecipe(int $id): ?bool
    {
        $recipe = Recipe::find($id);
        if ($recipe) {
            return $recipe->delete();
        }
        return null;
    }

    /**
     * Get all recipes for dropdown (excluding the current recipe if provided)
     *
     * @param int|null $excludeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecipesForDropdown(?int $excludeId = null)
    {
        $query = Recipe::select('id', 'recipe_name')->orderBy('recipe_name');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }
}
