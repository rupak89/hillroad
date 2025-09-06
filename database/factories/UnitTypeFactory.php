<?php

namespace Database\Factories;

use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitTypeFactory extends Factory
{
    protected $model = UnitType::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'physical_type' => $this->faker->randomElement(['Mass', 'Volume', 'Length']),
            'base_unit' => $this->faker->word(),
            'unit_name_plural' => $this->faker->word() . 's',
            'unit_name_short' => $this->faker->lexify('??'),
            'unit_name_short_plural' => $this->faker->lexify('???'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
