<?php

namespace App\Enums;


enum UnitType: string
{
    case Mass = \PhpUnitsOfMeasure\PhysicalQuantity\Mass::class;
    case Length = \PhpUnitsOfMeasure\PhysicalQuantity\Length::class;
    case Volume = \PhpUnitsOfMeasure\PhysicalQuantity\Volume::class;
    case Temperature = \PhpUnitsOfMeasure\PhysicalQuantity\Temperature::class;
    case Time = \PhpUnitsOfMeasure\PhysicalQuantity\Time::class;

    public function getLabel(): string
    {
        return match ($this) {
            self::Mass => 'Weight',
            self::Length => 'Length',
            self::Volume => 'Volume',
            self::Temperature => 'Temperature',
            self::Time => 'Time',
        };
    }
}
