<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the items associated with the brand.
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'default_brand_id');
    }
}
