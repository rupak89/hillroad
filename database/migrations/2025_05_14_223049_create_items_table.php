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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name')->unique();
            $table->foreignId('ordering_unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('counting_unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('default_supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->foreignId('default_brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->decimal('latest_price', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

