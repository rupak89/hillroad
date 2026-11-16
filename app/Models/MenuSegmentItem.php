<?php

namespace App\Models;

use App\Services\RecipeCostService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Calculate the total cost for this item (per guest).
     *
     * Menus are costed per portion, so use the recipe's cost per serving
     * rather than the cost of the entire recipe.
     */
    public function getTotalCostAttribute(): float
    {
        $this->loadMissing('recipe');

        $recipe = $this->recipe;
        if (! $recipe) {
            return 0;
        }

        try {
            $costData = app(RecipeCostService::class)->calculateRecipeCost($recipe);
            $costPerServing = $costData['cost_per_serving'] ?? 0;

            return round($costPerServing * (float) $this->quantity, 2);
        } catch (\Throwable $e) {
            Log::error('Error calculating menu item cost', [
                'item_id' => $this->id,
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get the cost of one serving of the recipe.
     */
    public function getCostPerUnitAttribute(): float
    {
        $this->loadMissing('recipe');

        if (! $this->recipe) {
            return 0;
        }

        try {
            $costData = app(RecipeCostService::class)->calculateRecipeCost($this->recipe);

            return (float) ($costData['cost_per_serving'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get formatted quantity
     */
    public function getFormattedQuantityAttribute(): string
    {
        return "{$this->quantity} per guest";
    }
}
