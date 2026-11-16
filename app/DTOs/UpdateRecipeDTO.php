<?php

namespace App\DTOs;

class UpdateRecipeDTO extends BaseDTO
{
    public ?string $recipe_name = null;

    public ?string $instruction = null;

    public ?string $thumbnail = null;

    public ?int $servings = null;

    public array $ingredients = [];

    public array $sub_recipes = [];

    /**
     * Track which optional fields were actually present in the request. Without
     * this an omitted `ingredients` key is indistinguishable from an explicit
     * empty array, which caused recipe updates to wipe existing data.
     */
    public bool $hasInstruction = false;

    public bool $hasThumbnail = false;

    public bool $hasIngredients = false;

    public bool $hasSubRecipes = false;

    protected function fill(array $data): void
    {
        $this->recipe_name = $data['recipe_name'] ?? $this->recipe_name;

        $this->hasInstruction = array_key_exists('instruction', $data);
        $this->instruction = $this->hasInstruction ? $data['instruction'] : null;

        $this->hasThumbnail = array_key_exists('thumbnail', $data);
        $this->thumbnail = $this->hasThumbnail ? $data['thumbnail'] : null;

        $this->servings = array_key_exists('servings', $data) ? (int) $data['servings'] : null;

        $this->hasIngredients = array_key_exists('ingredients', $data);
        $this->ingredients = $this->hasIngredients ? ($data['ingredients'] ?? []) : [];

        $this->hasSubRecipes = array_key_exists('sub_recipes', $data);
        $this->sub_recipes = $this->hasSubRecipes ? ($data['sub_recipes'] ?? []) : [];
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->recipe_name !== null) {
            $data['recipe_name'] = $this->recipe_name;
        }
        if ($this->hasInstruction) {
            $data['instruction'] = $this->instruction;
        }
        if ($this->hasThumbnail) {
            $data['thumbnail'] = $this->thumbnail;
        }
        if ($this->servings !== null) {
            $data['servings'] = $this->servings;
        }
        if ($this->hasIngredients) {
            $data['ingredients'] = $this->ingredients;
        }
        if ($this->hasSubRecipes) {
            $data['sub_recipes'] = $this->sub_recipes;
        }

        return $data;
    }
}
