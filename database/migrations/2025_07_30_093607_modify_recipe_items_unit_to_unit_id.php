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
        Schema::table('recipe_items', function (Blueprint $table) {
            // Add unit_id column if it doesn't exist
            if (!Schema::hasColumn('recipe_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_items', function (Blueprint $table) {
            if (Schema::hasColumn('recipe_items', 'unit_id')) {
                $table->dropColumn('unit_id');
            }
        });
    }
};
