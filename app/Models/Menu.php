<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'target_head_count',
        'markup_percentage',
        'user_id',
    ];

    protected $casts = [
        'target_head_count' => 'integer',
        'markup_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the menu
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the segments for the menu
     */
    public function segments(): HasMany
    {
        return $this->hasMany(MenuSegment::class)->orderBy('sort_order');
    }

    /**
     * Calculate the total base cost of the menu, per guest.
     */
    public function getTotalCostAttribute(): float
    {
        $this->loadMissing(['segments.items.recipe']);

        return round((float) $this->segments->sum(fn ($segment) => $segment->total_cost), 2);
    }

    /**
     * The base cost for a single guest.
     */
    public function getCostPerGuestAttribute(): float
    {
        return $this->total_cost;
    }

    /**
     * Kept for existing consumers; cost per person is the same as cost per guest.
     */
    public function getCostPerPersonAttribute(): float
    {
        return $this->cost_per_guest;
    }

    /**
     * The base cost of the menu for every guest.
     */
    public function getTotalEventCostAttribute(): float
    {
        return round($this->cost_per_guest * max(0, (int) $this->target_head_count), 2);
    }

    /**
     * Get the count of segments
     */
    public function getSegmentsCountAttribute(): int
    {
        return $this->segments()->count();
    }

    /**
     * Calculate the selling price per guest with markup
     */
    public function getSellingPricePerPersonAttribute(): float
    {
        return round($this->cost_per_guest * (1 + ((float) $this->markup_percentage / 100)), 2);
    }

    /**
     * Alias with unambiguous naming.
     */
    public function getSellingPricePerGuestAttribute(): float
    {
        return $this->selling_price_per_person;
    }

    /**
     * Calculate the total selling price for all guests with markup
     */
    public function getTotalSellingPriceAttribute(): float
    {
        return round($this->selling_price_per_guest * max(0, (int) $this->target_head_count), 2);
    }

    /**
     * Calculate the markup amount per guest.
     */
    public function getMarkupAmountPerPersonAttribute(): float
    {
        return round($this->cost_per_guest * ((float) $this->markup_percentage / 100), 2);
    }

    /**
     * Alias with unambiguous naming.
     */
    public function getMarkupAmountPerGuestAttribute(): float
    {
        return $this->markup_amount_per_person;
    }

    /**
     * Calculate the total markup amount for all guests
     */
    public function getTotalMarkupAmountAttribute(): float
    {
        return round($this->markup_amount_per_guest * max(0, (int) $this->target_head_count), 2);
    }

    /**
     * Create default segments for a new menu
     */
    public function createDefaultSegments()
    {
        $defaultSegments = [
            ['name' => 'Starter', 'sort_order' => 1],
            ['name' => 'Main', 'sort_order' => 2],
            ['name' => 'Side', 'sort_order' => 3],
            ['name' => 'Dessert', 'sort_order' => 4],
        ];

        foreach ($defaultSegments as $segment) {
            $this->segments()->create($segment);
        }
    }
}
