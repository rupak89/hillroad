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
        Schema::create('gst_settings', function (Blueprint $table) {
            $table->id();

            // New Zealand businesses charge GST at 15%. Registration is
            // optional for small businesses, so keep it configurable.
            $table->boolean('gst_registered')->default(true);
            $table->decimal('gst_rate', 5, 2)->default(15.00);

            // Whether prices entered against suppliers/items already include
            // GST. When true, item costs are converted back to GST-exclusive
            // amounts before recipe costing.
            $table->boolean('prices_include_gst')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_settings');
    }
};
