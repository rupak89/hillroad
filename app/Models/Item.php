<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
     * @param Unit $unit
     * @param float $quantity
     * @return float
     */
    public function calculateCostForUnit(Unit $unit, $quantity = 1)
    {
        if (!$this->latest_price || !$this->ordering_unit_id) {
            return 0;
        }

        $orderingUnit = $this->orderingUnit;

        if (!$orderingUnit) {
            return 0;
        }

        try {
            // Convert the quantity from the recipe unit to the ordering unit
            $convertedQuantity = $unit->convertTo($quantity, $orderingUnit);

            // Calculate cost based on latest price per ordering unit
            return $this->latest_price * $convertedQuantity;
        } catch (\Exception $e) {
            // Log the conversion error for debugging
            Log::warning("Unit conversion failed for item {$this->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get cost per base unit (smallest unit)
     *
     * @return float
     */
    public function getCostPerBaseUnit()
    {
        if (!$this->latest_price || !$this->ordering_unit_id) {
            return 0;
        }

        $orderingUnit = $this->orderingUnit;
        if (!$orderingUnit) {
            return 0;
        }

        $baseUnitQuantity = $orderingUnit->toBaseUnit(1);
        return $baseUnitQuantity > 0 ? $this->latest_price / $baseUnitQuantity : 0;
    }

    /**
     * Get formatted cost per unit string
     *
     * @return string
     */
    public function getFormattedCostPerUnit()
    {
        if (!$this->latest_price || !$this->ordering_unit_id) {
            return 'N/A';
        }

        $orderingUnit = $this->orderingUnit;
        if (!$orderingUnit) {
            return 'N/A';
        }

        return '$' . number_format($this->latest_price, 2) . ' per ' . $orderingUnit->name;
    }
}
