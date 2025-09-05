<?php

namespace App\Models;

use App\Services\RecipeCostService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class MenuSegment extends Model
{
    protected $fillable = [
        'menu_id',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the menu that owns the segment
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the items for the segment
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuSegmentItem::class)->orderBy('sort_order');
    }

    /**
     * Calculate the total cost of this segment (per guest)
     */
    public function getTotalCostAttribute()
    {
        // Avoid loading relationships if not already loaded to prevent memory issues
        if (!$this->relationLoaded('items')) {
            return 0;
        }

        $recipeCostService = app(RecipeCostService::class);

        return $this->items->sum(function ($item) use ($recipeCostService) {
            $recipe = $item->recipe;
            if (!$recipe) return 0;

            try {
                // Calculate recipe cost using the service
                $costData = $recipeCostService->calculateRecipeCost($recipe);
                $recipeCost = $costData['total_cost'] ?? 0;

                // Always calculate per guest cost
                return $recipeCost * $item->quantity;
            } catch (\Exception $e) {
                // If cost calculation fails, return 0
                Log::warning("Failed to calculate cost for recipe {$recipe->id} in menu segment {$this->id}: " . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get the count of items in this segment
     */
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }
}
