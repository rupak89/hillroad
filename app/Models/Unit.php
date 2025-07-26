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
}
