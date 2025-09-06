<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use HasFactory;
    protected $fillable = [
        'label',
        'physical_type',
        'base_unit',
        'unit_name_plural',
        'unit_name_short',
        'unit_name_short_plural'
    ];

    /**
     * Get all units that belong to this unit type
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the PHPUnitsOfMeasure class for this unit type
     */
    public function getPhysicalQuantityInstance($value = 1, $unit = null)
    {
        if (!$this->physical_type) {
            throw new \Exception("No physical type defined for unit type: {$this->label}");
        }

        $className = $this->physical_type;

        if (!class_exists($className)) {
            throw new \Exception("Physical quantity class {$className} not found");
        }

        // Use the base_unit if no unit specified
        if (!$unit) {
            $unit = $this->base_unit ?: 'g'; // fallback
        }

        return new $className($value, $unit);
    }

    /**
     * Get the name attribute (alias for label)
     */
    public function getNameAttribute()
    {
        return $this->label;
    }
}
