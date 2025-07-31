<?php

namespace App\Units;

use PhpUnitsOfMeasure\AbstractPhysicalQuantity;
use PhpUnitsOfMeasure\UnitOfMeasure;

class Count extends AbstractPhysicalQuantity
{
    protected static $unitDefinitions;

    protected static function initialize()
    {
        static::$unitDefinitions = [];

        // Register synonyms for "piece"
        static::addUnit(new UnitOfMeasure(
            'piece',
            function ($value) { return $value; },
            function ($value) { return $value; }
        ));

        static::addUnit(new UnitOfMeasure(
            'pieces',
            function ($value) { return $value; },
            function ($value) { return $value; }
        ));

        static::addUnit(new UnitOfMeasure(
            'pc',
            function ($value) { return $value; },
            function ($value) { return $value; }
        ));

        static::addUnit(new UnitOfMeasure(
            'pcs',
            function ($value) { return $value; },
            function ($value) { return $value; }
        ));
    }
}
