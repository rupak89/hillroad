<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

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

        // Load unit types if not already loaded
        $this->load('unitType');
        $targetUnit->load('unitType');

        // For units of the same unit type, prefer ratio-based conversion
        if ($this->unit_type_id === $targetUnit->unit_type_id && $this->ratio && $targetUnit->ratio) {
            return ($quantity * $this->ratio) / $targetUnit->ratio;
        }

        // If different unit types, check if they have the same physical_type
        if ($this->unit_type_id !== $targetUnit->unit_type_id) {
            if (!$this->unitType->physical_type ||
                !$targetUnit->unitType->physical_type ||
                $this->unitType->physical_type !== $targetUnit->unitType->physical_type) {
                throw new \Exception("Cannot convert between different unit types: {$this->unitType->label} and {$targetUnit->unitType->label}");
            }
        }

        // Try PHPUnitsOfMeasure library for cross-unit-type conversions
        if ($this->unitType->physical_type && $this->unit_type_id !== $targetUnit->unit_type_id) {
            try {
                // Map unit names to PHPUnitsOfMeasure compatible names
                $sourceUnitName = $this->getPhysicalUnitName();
                $targetUnitName = $targetUnit->getPhysicalUnitName();

                // Convert quantity to actual physical amount first (accounting for ratios)
                $actualSourceAmount = $quantity * $this->ratio;

                $physicalQuantity = $this->unitType->getPhysicalQuantityInstance($actualSourceAmount, $sourceUnitName);
                $actualTargetAmount = $physicalQuantity->toUnit($targetUnitName);

                // Convert back to target unit's ratio
                return $actualTargetAmount / $targetUnit->ratio;
            } catch (\Exception $e) {
                // If PHPUnitsOfMeasure fails, try the target unit type
                try {
                    $sourceUnitName = $this->getPhysicalUnitName();
                    $targetUnitName = $targetUnit->getPhysicalUnitName();

                    // Convert quantity to actual physical amount first (accounting for ratios)
                    $actualSourceAmount = $quantity * $this->ratio;

                    $physicalQuantity = $targetUnit->unitType->getPhysicalQuantityInstance($actualSourceAmount, $sourceUnitName);
                    $actualTargetAmount = $physicalQuantity->toUnit($targetUnitName);

                    // Convert back to target unit's ratio
                    return $actualTargetAmount / $targetUnit->ratio;
                } catch (\Exception $e2) {
                    // Fall through to error
                }
            }
        }

        throw new \Exception("Cannot convert from {$this->name} to {$targetUnit->name}: no conversion method available");
    }

    /**
     * Get the PHPUnitsOfMeasure compatible unit name
     */
    protected function getPhysicalUnitName()
    {
        // Ensure unitType is loaded
        $this->load('unitType');

        // Use the base_unit from the unitType, which is already PHPUnitsOfMeasure compatible
        return $this->unitType->base_unit;
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
        // Same unit type can always convert
        if ($this->unit_type_id === $targetUnit->unit_type_id) {
            return true;
        }

        // Different unit types can convert if they have the same physical_type
        $this->load('unitType');
        $targetUnit->load('unitType');

        return $this->unitType->physical_type &&
               $targetUnit->unitType->physical_type &&
               $this->unitType->physical_type === $targetUnit->unitType->physical_type;
    }
}
