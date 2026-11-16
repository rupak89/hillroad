<?php

namespace App\DTOs;

class CreateRecipeDTO extends BaseDTO
{
    public string $recipe_name;

    public ?string $instruction = null;

    public ?string $thumbnail = null;

    public int $servings = 1;

    public array $ingredients = [];

    public array $sub_recipes = [];

    protected function fill(array $data): void
    {
        if (! isset($data['recipe_name']) || trim((string) $data['recipe_name']) === '') {
            throw new \InvalidArgumentException('The recipe name is required.');
        }

        $this->recipe_name = $data['recipe_name'];
        $this->instruction = $data['instruction'] ?? null;
        $this->thumbnail = $data['thumbnail'] ?? null;
        $this->servings = (int) ($data['servings'] ?? 1);
        $this->ingredients = $data['ingredients'] ?? [];
        $this->sub_recipes = $data['sub_recipes'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'recipe_name' => $this->recipe_name,
            'instruction' => $this->instruction,
            'thumbnail' => $this->thumbnail,
            'servings' => $this->servings,
            'ingredients' => $this->ingredients,
            'sub_recipes' => $this->sub_recipes,
        ];
    }
}
