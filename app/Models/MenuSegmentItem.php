<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\RecipeCostService;
use Illuminate\Support\Facades\Log;

class MenuSegmentItem extends Model
{
    protected $fillable = [
        'menu_segment_id',
        'recipe_id',
        'quantity',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the segment that owns the item
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(MenuSegment::class, 'menu_segment_id');
    }

    /**
     * Get the recipe for this item
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Calculate the total cost for this item (per guest)
     */
    public function getTotalCostAttribute()
    {
        // Avoid loading relationships if not already loaded to prevent memory issues
        if (!$this->relationLoaded('recipe')) {
            return 0;
        }

        $recipe = $this->recipe;
        if (!$recipe) return 0;

        try {
            // Use the RecipeCostService to calculate the actual recipe cost
            $recipeCostService = app(RecipeCostService::class);
            $costData = $recipeCostService->calculateRecipeCost($recipe);
            $recipeCost = $costData['total_cost'] ?? 0;

            // Always calculate per guest cost
            return $recipeCost * $this->quantity;
        } catch (\Exception $e) {
            Log::error('Error calculating menu item cost', [
                'item_id' => $this->id,
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get cost per unit
     */
    public function getCostPerUnitAttribute()
    {
        if (!$this->relationLoaded('recipe') || !$this->recipe) {
            return 0;
        }

        try {
            $recipeCostService = app(RecipeCostService::class);
            $costData = $recipeCostService->calculateRecipeCost($this->recipe);
            return $costData['total_cost'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get formatted quantity
     */
    public function getFormattedQuantityAttribute()
    {
        return "{$this->quantity} per guest";
    }
}
