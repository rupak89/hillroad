<?php

namespace Database\Factories;

use App\Enums\UnitType as UnitTypeEnum;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnitTypeFactory extends Factory
{
    protected $model = UnitType::class;

    public function definition(): array
    {
        $physicalType = $this->faker->randomElement([
            UnitTypeEnum::Mass->value,
            UnitTypeEnum::Volume->value,
            UnitTypeEnum::Length->value,
            UnitTypeEnum::Count->value,
        ]);

        return [
            'label' => 'type-'.Str::lower(Str::random(10)),
            // physical_type stores the FQCN of the quantity class.
            'physical_type' => $physicalType,
            'base_unit' => match ($physicalType) {
                UnitTypeEnum::Mass->value => 'kilogram',
                UnitTypeEnum::Volume->value => 'liter',
                UnitTypeEnum::Length->value => 'meter',
                default => 'piece',
            },
            'unit_name_plural' => $this->faker->word().'s',
            'unit_name_short' => $this->faker->lexify('??'),
            'unit_name_short_plural' => $this->faker->lexify('???'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
