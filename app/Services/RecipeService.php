<?php

namespace App\Services;

use App\Models\Recipe;
use App\DTOs\CreateRecipeDTO;
use App\DTOs\UpdateRecipeDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RecipeService
{
    /**
     * Get paginated list of recipes.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRecipes(int $perPage = 10): LengthAwarePaginator
    {
        return Recipe::with([
                'items:id,item_name',
                'subRecipes:id,recipe_name'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new recipe.
     *
     * @param array $data
     * @return Recipe
     * @throws \Illuminate\Validation\ValidationException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function createRecipe(array $data): Recipe
    {
        try {
            $dto = CreateRecipeDTO::fromArray($data);

            return DB::transaction(function () use ($dto) {
                // Create the recipe
                $recipe = Recipe::create([
                    'recipe_name' => $dto->recipe_name,
                    'instruction' => $dto->instruction,
                    'thumbnail' => $dto->thumbnail,
                ]);

                // Check for cyclic dependencies in sub-recipes
                if (!empty($dto->sub_recipes)) {
                    $subRecipeIds = array_column($dto->sub_recipes, 'child_recipe_id');
                    if ($this->wouldCreateCycle($recipe->id, $subRecipeIds)) {
                        throw new \InvalidArgumentException('Cannot add sub-recipes: would create a cyclic dependency or self-reference');
                    }
                }

                // Attach ingredients in batch
                if (!empty($dto->ingredients)) {
                    $ingredientAttachData = [];
                    foreach ($dto->ingredients as $ingredient) {
                        if (!empty($ingredient['item_id']) && !empty($ingredient['quantity'])) {
                            $ingredientAttachData[$ingredient['item_id']] = [
                                'unit_id' => $ingredient['unit_id'] ?? null,
                                'quantity' => $ingredient['quantity'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    if (!empty($ingredientAttachData)) {
                        $recipe->items()->attach($ingredientAttachData);
                    }
                }

                // Attach sub-recipes in batch
                if (!empty($dto->sub_recipes)) {
                    $subRecipeAttachData = [];
                    foreach ($dto->sub_recipes as $subRecipe) {
                        if (!empty($subRecipe['child_recipe_id']) && !empty($subRecipe['quantity'])) {
                            $subRecipeAttachData[$subRecipe['child_recipe_id']] = [
                                'quantity' => $subRecipe['quantity'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    if (!empty($subRecipeAttachData)) {
                        $recipe->subRecipes()->attach($subRecipeAttachData);
                    }
                }

                return $recipe->load([
                    'items.orderingUnit',
                    'items.countingUnit',
                    'items.supplier',
                    'items.defaultBrand',
                    'items.group',
                    'subRecipes.items',
                    'subRecipes.subRecipes'
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error creating recipe: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        } finally {
            $this->clearRecipeCache();
        }
    }

    /**
     * Get a single recipe by ID.
     *
     * @param int $id
     * @return Recipe
     * @throws ModelNotFoundException
     */
    public function getRecipe(int $id): Recipe
    {
        try {
            return Recipe::with([
                'items.orderingUnit',
                'items.countingUnit',
                'items.supplier',
                'items.defaultBrand',
                'items.group',
                'subRecipes.items',
                'subRecipes.subRecipes'
            ])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error('Recipe not found: ' . $id);
            throw $e;
        }
    }

    /**
     * Update a recipe.
     *
     * @param int $id
     * @param array $data
     * @return Recipe
     * @throws \Illuminate\Validation\ValidationException
     * @throws ModelNotFoundException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function updateRecipe(int $id, array $data): Recipe
    {
        try {
            $dto = UpdateRecipeDTO::fromArray($data);

            return DB::transaction(function () use ($id, $dto) {
                $recipe = Recipe::findOrFail($id);

                // Check for cyclic dependencies in sub-recipes
                if (!empty($dto->sub_recipes)) {
                    $subRecipeIds = array_column($dto->sub_recipes, 'child_recipe_id');
                    if ($this->wouldCreateCycle($id, $subRecipeIds)) {
                        throw new \InvalidArgumentException('Cannot update sub-recipes: would create a cyclic dependency or self-reference');
                    }
                }

                // Update the recipe basic fields
                $updateData = [];
                if ($dto->recipe_name !== null) {
                    $updateData['recipe_name'] = $dto->recipe_name;
                }
                if ($dto->instruction !== null) {
                    $updateData['instruction'] = $dto->instruction;
                }
                if ($dto->thumbnail !== null) {
                    $updateData['thumbnail'] = $dto->thumbnail;
                }

                if (!empty($updateData)) {
                    $recipe->update($updateData);
                }

                // Sync ingredients
                $ingredientData = [];
                if (!empty($dto->ingredients)) {
                    foreach ($dto->ingredients as $ingredient) {
                        if (!empty($ingredient['item_id']) && !empty($ingredient['quantity'])) {
                            $ingredientData[$ingredient['item_id']] = [
                                'unit_id' => $ingredient['unit_id'] ?? null,
                                'quantity' => $ingredient['quantity'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
                $recipe->items()->sync($ingredientData);

                // Sync sub-recipes
                $subRecipeData = [];
                if (!empty($dto->sub_recipes)) {
                    foreach ($dto->sub_recipes as $subRecipe) {
                        if (!empty($subRecipe['child_recipe_id']) && !empty($subRecipe['quantity'])) {
                            $subRecipeData[$subRecipe['child_recipe_id']] = [
                                'quantity' => $subRecipe['quantity'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
                $recipe->subRecipes()->sync($subRecipeData);

                return $recipe->fresh([
                    'items.orderingUnit',
                    'items.countingUnit',
                    'items.supplier',
                    'items.defaultBrand',
                    'items.group',
                    'subRecipes.items',
                    'subRecipes.subRecipes'
                ]);
            });
        } catch (ModelNotFoundException $e) {
            Log::error('Recipe not found for update: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating recipe: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        } finally {
            $this->clearRecipeCache();
        }
    }

    /**
     * Delete a recipe.
     *
     * @param int $id
     * @return bool
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function deleteRecipe(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $recipe = Recipe::findOrFail($id);
                return $recipe->delete();
            });
        } catch (ModelNotFoundException $e) {
            Log::error('Recipe not found for deletion: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error deleting recipe: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Get all recipes for dropdown (excluding the current recipe if provided)
     *
     * @param int|null $excludeId
     * @return Collection
     */
    public function getRecipesForDropdown(?int $excludeId = null): Collection
    {
        $query = Recipe::select('id', 'recipe_name')->orderBy('recipe_name');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }

    /**
     * Check if adding sub-recipes would create a cycle or self-reference
     *
     * @param int $recipeId
     * @param array $subRecipeIds
     * @return bool
     */
    private function wouldCreateCycle(int $recipeId, array $subRecipeIds): bool
    {
        // Check for self-reference
        if (in_array($recipeId, $subRecipeIds)) {
            return true;
        }

        // Pre-load all recipes with their sub-recipes to avoid N+1
        $allRecipeIds = collect($subRecipeIds);
        $this->preloadRecipesForCycleCheck($allRecipeIds, $recipeId);

        // Check for cycles using depth-first search
        $visited = [];
        foreach ($subRecipeIds as $subRecipeId) {
            if ($this->hasCyclicDependency($subRecipeId, $recipeId, $visited)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pre-load all potentially needed recipes to avoid N+1 queries
     *
     * @param \Illuminate\Support\Collection $initialIds
     * @param int $targetRecipeId
     * @return void
     */
    private function preloadRecipesForCycleCheck($initialIds, int $targetRecipeId): void
    {
        $allIds = collect([$targetRecipeId])->merge($initialIds);
        $processedIds = collect();

        // Recursively gather all recipe IDs that might be needed
        while ($allIds->diff($processedIds)->isNotEmpty()) {
            $newIds = $allIds->diff($processedIds);
            $processedIds = $processedIds->merge($newIds);

            $recipes = Recipe::whereIn('id', $newIds->toArray())
                ->with('subRecipes:id')
                ->get(['id']);

            $subRecipeIds = $recipes->flatMap(function ($recipe) {
                return $recipe->subRecipes->pluck('id');
            });

            $allIds = $allIds->merge($subRecipeIds);

            // Prevent infinite loops - limit depth
            if ($processedIds->count() > 100) {
                break;
            }
        }

        // Now load all recipes with their sub-recipes in one query
        Recipe::whereIn('id', $processedIds->toArray())
            ->with('subRecipes:id')
            ->get()
            ->keyBy('id')
            ->each(function ($recipe) {
                // Cache the recipe in memory for the cycle check
                $this->recipeCache[$recipe->id] = $recipe;
            });
    }

    /**
     * Cache for pre-loaded recipes to avoid N+1 queries
     *
     * @var array
     */
    private array $recipeCache = [];

    /**
     * Recursively check if a sub-recipe has a cyclic dependency
     *
     * @param int $currentRecipeId
     * @param int $targetRecipeId
     * @param array $visited
     * @return bool
     */
    private function hasCyclicDependency(int $currentRecipeId, int $targetRecipeId, array &$visited): bool
    {
        // If we've already visited this recipe in this path, we have a cycle
        if (in_array($currentRecipeId, $visited)) {
            return true;
        }

        // If we've reached the target recipe, we have a cycle
        if ($currentRecipeId === $targetRecipeId) {
            return true;
        }

        // Mark this recipe as visited in the current path
        $visited[] = $currentRecipeId;

        // Use cached recipe to avoid N+1 query
        $recipe = $this->recipeCache[$currentRecipeId] ?? null;
        if ($recipe && $recipe->subRecipes) {
            foreach ($recipe->subRecipes as $subRecipe) {
                if ($this->hasCyclicDependency($subRecipe->id, $targetRecipeId, $visited)) {
                    return true;
                }
            }
        }

        // Remove this recipe from visited path (backtrack)
        array_pop($visited);

        return false;
    }

    /**
     * Public method to validate if sub-recipes would create cycles
     *
     * @param int $recipeId
     * @param array $subRecipeIds
     * @return bool
     */
    public function validateSubRecipes(int $recipeId, array $subRecipeIds): bool
    {
        $result = !$this->wouldCreateCycle($recipeId, $subRecipeIds);
        $this->clearRecipeCache();
        return $result;
    }

    /**
     * Clear the recipe cache to free memory
     *
     * @return void
     */
    private function clearRecipeCache(): void
    {
        $this->recipeCache = [];
    }
}
