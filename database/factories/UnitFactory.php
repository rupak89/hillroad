<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            // units.name is unique, so do not use a small random word here.
            'name' => 'unit-'.Str::lower(Str::random(10)),
            'ratio' => $this->faker->randomFloat(2, 0.1, 10),
            'unit_type_id' => UnitType::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
