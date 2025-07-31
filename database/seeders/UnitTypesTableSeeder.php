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
                'label' => 'Weight (1 kg)',
                'physical_type' => UnitType::Mass->value,
                'base_unit' => 'kilogram',
                'unit_name_plural' => 'kilograms',
                'unit_name_short' => 'kg',
                'unit_name_short_plural' => 'kgs',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => 'Weight (1 g)',
                'physical_type' => UnitType::Mass->value,
                'base_unit' => 'gram',
                'unit_name_plural' => 'grams',
                'unit_name_short' => 'gm',
                'unit_name_short_plural' => 'gms',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => 'Weight (1 lb)',
                'physical_type' => UnitType::Mass->value,
                'base_unit' => 'pound',
                'unit_name_plural' => 'pounds',
                'unit_name_short' => 'lb',
                'unit_name_short_plural' => 'lbs',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => 'Volume (1 L)',
                'physical_type' => UnitType::Volume->value,
                'base_unit' => 'liter',
                'unit_name_plural' => 'liters',
                'unit_name_short' => 'L',
                'unit_name_short_plural' => 'Ls',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => 'Volume(1 ml)',
                'physical_type' => UnitType::Volume->value,
                'base_unit' => 'milliliter',
                'unit_name_plural' => 'milliliters',
                'unit_name_short' => 'ml',
                'unit_name_short_plural' => 'mls',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'label' => 'Piece',
                'physical_type' => UnitType::Count->value,
                'base_unit' => 'piece',
                'unit_name_plural' => 'pieces',
                'unit_name_short' => 'pc',
                'unit_name_short_plural' => 'pcs',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);
    }
}
