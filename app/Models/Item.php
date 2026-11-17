<?php

namespace App\Models;

use App\Services\GstService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'ordering_unit_id',
        'counting_unit_id',
        'default_supplier_id',
        'default_brand_id',
        'group_id',
        'latest_price',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function defaultBrand()
    {
        return $this->belongsTo(Brand::class, 'default_brand_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'default_brand_id');
    }

    public function orderingUnit()
    {
        return $this->belongsTo(Unit::class, 'ordering_unit_id');
    }

    public function countingUnit()
    {
        return $this->belongsTo(Unit::class, 'counting_unit_id');
    }

    /**
     * Calculate cost for a specific unit and quantity
     *
     * @param  float  $quantity
     * @return float
     */
    public function calculateCostForUnit(Unit $unit, $quantity = 1)
    {
        if (! $this->latest_price || ! $this->ordering_unit_id) {
            return 0;
        }

        $orderingUnit = $this->orderingUnit;

        if (! $orderingUnit) {
            return 0;
        }

        // Let conversion failures bubble up so callers can flag the recipe as
        // not costable instead of silently producing a cheaper result.
        $convertedQuantity = $unit->convertTo($quantity, $orderingUnit);

        // Always cost from the GST-exclusive price. When the setting says
        // entered prices already include GST, this back-calculates the net
        // amount so recipe and menu costs stay comparable.
        return $this->latest_price_excluding_gst * $convertedQuantity;
    }

    /**
     * The item's price with GST removed, if the price was entered inclusive.
     */
    public function getLatestPriceExcludingGstAttribute(): float
    {
        return app(GstService::class)->amountExcludingGst((float) ($this->latest_price ?? 0));
    }

    /**
     * The item's price with GST added when the entered price was exclusive.
     */
    public function getLatestPriceIncludingGstAttribute(): float
    {
        return app(GstService::class)->amountIncludingGst($this->latest_price_excluding_gst);
    }

    /**
     * Get cost per base unit (smallest unit)
     *
     * @return float
     */
    public function getCostPerBaseUnit()
    {
        if (! $this->latest_price || ! $this->ordering_unit_id) {
            return 0;
        }

        $orderingUnit = $this->orderingUnit;
        if (! $orderingUnit) {
            return 0;
        }

        $baseUnitQuantity = $orderingUnit->toBaseUnit(1);

        return $baseUnitQuantity > 0 ? $this->latest_price_excluding_gst / $baseUnitQuantity : 0;
    }

    /**
     * Get formatted cost per unit string
     *
     * @return string
     */
    public function getFormattedCostPerUnit()
    {
        if (! $this->latest_price || ! $this->ordering_unit_id) {
            return 'N/A';
        }

        $orderingUnit = $this->orderingUnit;
        if (! $orderingUnit) {
            return 'N/A';
        }

        return '$'.number_format($this->latest_price_excluding_gst, 2).' per '.$orderingUnit->name;
    }
}
