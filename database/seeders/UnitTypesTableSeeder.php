<?php

namespace Database\Seeders;

use App\Enums\UnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('unit_types')->insert([
            [
                'label' => '1 Kilogram',
                'physical_type' => UnitType::Mass->value,
                'base_unit' => 'kilogram',
                'unit_name_plural' => 'kilograms',
                'unit_name_short' => 'kg',
                'unit_name_short_plural' => 'kgs',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => '1 Gram',
                'physical_type' => UnitType::Mass->value,
                'base_unit' => 'gram',
                'unit_name_plural' => 'grams',
                'unit_name_short' => 'gm',
                'unit_name_short_plural' => 'gms',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => '1 Pound',
                'physical_type' => UnitType::Mass->value,
                'base_unit' => 'pound',
                'unit_name_plural' => 'pounds',
                'unit_name_short' => 'lb',
                'unit_name_short_plural' => 'lbs',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => '1 Liter',
                'physical_type' => UnitType::Volume->value,
                'base_unit' => 'liter',
                'unit_name_plural' => 'liters',
                'unit_name_short' => 'L',
                'unit_name_short_plural' => 'Ls',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => '1 Milliliter',
                'physical_type' => UnitType::Volume->value,
                'base_unit' => 'milliliter',
                'unit_name_plural' => 'milliliters',
                'unit_name_short' => 'ml',
                'unit_name_short_plural' => 'mls',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);
    }
}
