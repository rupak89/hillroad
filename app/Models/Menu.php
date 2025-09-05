<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Calculate the total cost of the menu (per guest)
     */
    public function getTotalCostAttribute()
    {
        // Avoid loading relationships if not already loaded to prevent memory issues
        if (!$this->relationLoaded('segments')) {
            return 0;
        }

        return $this->segments->sum(function ($segment) {
            return $segment->total_cost;
        });
    }

    /**
     * Calculate cost per person
     */
    public function getCostPerPersonAttribute()
    {
        if ($this->target_head_count <= 0) {
            return 0;
        }

        return $this->total_cost / $this->target_head_count;
    }

    /**
     * Get the count of segments
     */
    public function getSegmentsCountAttribute()
    {
        return $this->segments->count();
    }

    /**
     * Calculate the selling price per person with markup
     */
    public function getSellingPricePerPersonAttribute()
    {
        $baseCost = $this->total_cost;
        if ($this->markup_percentage > 0) {
            $markup = $baseCost * ($this->markup_percentage / 100);
            return $baseCost + $markup;
        }
        return $baseCost;
    }

    /**
     * Calculate the total selling price for all guests with markup
     */
    public function getTotalSellingPriceAttribute()
    {
        return $this->selling_price_per_person * $this->target_head_count;
    }

    /**
     * Calculate the markup amount per person
     */
    public function getMarkupAmountPerPersonAttribute()
    {
        if ($this->markup_percentage > 0) {
            return $this->total_cost * ($this->markup_percentage / 100);
        }
        return 0;
    }

    /**
     * Calculate the total markup amount for all guests
     */
    public function getTotalMarkupAmountAttribute()
    {
        return $this->markup_amount_per_person * $this->target_head_count;
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
