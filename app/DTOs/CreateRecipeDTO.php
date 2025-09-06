<?php

namespace App\DTOs;

class CreateRecipeDTO extends BaseDTO
{
    public string $recipe_name;
    public ?string $instruction = null;
    public ?string $thumbnail = null;
    public array $ingredients = [];
    public array $sub_recipes = [];

    protected function rules(): array
    {
        return [
            'recipe_name' => 'required|string|max:255',
            'instruction' => 'nullable|string',
            'thumbnail' => 'nullable|string|max:500',
            'ingredients' => 'nullable|array',
            'ingredients.*.item_id' => 'required_with:ingredients.*|integer|exists:items,id',
            'ingredients.*.unit_id' => 'nullable|integer|exists:units,id',
            'ingredients.*.quantity' => 'required_with:ingredients.*|numeric|min:0.01',
            'sub_recipes' => 'nullable|array',
            'sub_recipes.*.child_recipe_id' => 'required_with:sub_recipes.*|integer|exists:recipes,id',
            'sub_recipes.*.quantity' => 'required_with:sub_recipes.*|numeric|min:0.01',
        ];
    }

    protected function fill(array $data): void
    {
        $this->recipe_name = $data['recipe_name'];
        $this->instruction = $data['instruction'] ?? null;
        $this->thumbnail = $data['thumbnail'] ?? null;
        $this->ingredients = $data['ingredients'] ?? [];
        $this->sub_recipes = $data['sub_recipes'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'recipe_name' => $this->recipe_name,
            'instruction' => $this->instruction,
            'thumbnail' => $this->thumbnail,
            'ingredients' => $this->ingredients,
            'sub_recipes' => $this->sub_recipes,
        ];
    }
}
