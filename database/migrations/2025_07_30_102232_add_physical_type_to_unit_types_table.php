<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->string('physical_type')->nullable()->after('name')->comment('PHPUnitsOfMeasure class name');
        });

        // Insert or update common unit types with their PHPUnitsOfMeasure classes
        DB::table('unit_types')->updateOrInsert(
            ['name' => 'Mass'],
            [
                'physical_type' => 'Mass',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('unit_types')->updateOrInsert(
            ['name' => 'Volume'],
            [
                'physical_type' => 'Volume',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('unit_types')->updateOrInsert(
            ['name' => 'Length'],
            [
                'physical_type' => 'Length',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('unit_types')->updateOrInsert(
            ['name' => 'Temperature'],
            [
                'physical_type' => 'Temperature',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('unit_types')->updateOrInsert(
            ['name' => 'Quantity'],
            [
                'physical_type' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->dropColumn('physical_type');
        });
    }
};
