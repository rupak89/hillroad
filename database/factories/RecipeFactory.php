<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition(): array
    {
        return [
            'recipe_name' => $this->faker->words(3, true),
            'instruction' => $this->faker->paragraph(),
            'thumbnail' => $this->faker->imageUrl(200, 200, 'food'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
