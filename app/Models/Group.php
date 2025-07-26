<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the items associated with the group.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
