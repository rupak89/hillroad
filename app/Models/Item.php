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
        return $this->belongsTo(group::class);
    }
}
