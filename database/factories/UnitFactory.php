<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'ratio' => $this->faker->randomFloat(2, 0.1, 10),
            'unit_type_id' => UnitType::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
