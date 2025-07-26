<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
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

    public function orderingUnit()
    {
        return $this->belongsTo(Unit::class, 'ordering_unit_id');
    }

    public function countingUnit()
    {
        return $this->belongsTo(Unit::class, 'counting_unit_id');
    }
}
