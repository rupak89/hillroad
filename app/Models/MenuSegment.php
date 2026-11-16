<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function getTotalCostAttribute(): float
    {
        $this->loadMissing('items.recipe');

        return round((float) $this->items->sum(fn ($item) => $item->total_cost), 2);
    }

    /**
     * Get the count of items in this segment
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
}
