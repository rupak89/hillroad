<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();

            $table->string('label')->unique(); // 1 Kilo or 1 Pound or 1 Litre
            $table->string('physical_type'); //Weight or Volume, Count, Length class

            $table->string('base_unit'); //gram, kilogram, milliliter, liter, piece

            $table->string('unit_name_plural')->nullable(); //grams, kilograms, milliliters, liters, pieces
            $table->string('unit_name_short')->nullable(); //g, kg, ml, l, pcs
            $table->string('unit_name_short_plural')->nullable(); //gs, kgs, mls, ls, pcss

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_types');
    }
};



