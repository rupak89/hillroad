<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{

    protected $fillable = [
        'name',
        'ratio',
        'unit_type_id',
    ];
    protected $casts = [
        'ratio' => 'float',
    ];

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    /**
     * Convert quantity from this unit to a target unit
     *
     * @param float $quantity
     * @param Unit $targetUnit
     * @return float
     */
    public function convertTo($quantity, Unit $targetUnit)
    {
        // If same unit, no conversion needed
        if ($this->id === $targetUnit->id) {
            return $quantity;
        }

        // If different unit types, cannot convert
        if ($this->unit_type_id !== $targetUnit->unit_type_id) {
            throw new \Exception("Cannot convert between different unit types: {$this->unitType->name} and {$targetUnit->unitType->name}");
        }

        $unitType = $this->unitType;

        // Try PHPUnitsOfMeasure library first for standard conversions
        if ($unitType->physical_type) {
            try {
                $physicalQuantity = $unitType->getPhysicalQuantityInstance($quantity, $this->name);
                return $physicalQuantity->toUnit($targetUnit->name);
            } catch (\Exception $e) {
                // If PHPUnitsOfMeasure fails, fallback to ratio conversion
            }
        }

        // Fallback to ratio-based conversion
        if ($this->ratio && $targetUnit->ratio) {
            return ($quantity * $this->ratio) / $targetUnit->ratio;
        }

        throw new \Exception("Cannot convert from {$this->name} to {$targetUnit->name}: no conversion method available");
    }

    /**
     * Convert quantity to the base unit of this unit type
     *
     * @param float $quantity
     * @return float
     */
    public function toBaseUnit($quantity)
    {
        if ($this->ratio) {
            return $quantity * $this->ratio;
        }

        return $quantity;
    }

    /**
     * Check if this unit can be converted to another unit
     *
     * @param Unit $targetUnit
     * @return bool
     */
    public function canConvertTo(Unit $targetUnit)
    {
        return $this->unit_type_id === $targetUnit->unit_type_id;
    }
}
