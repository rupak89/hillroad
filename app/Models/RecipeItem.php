<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    protected $fillable = [
        'recipe_id',
        'item_id',
        'unit',
        'quantity',
    ];

    /**
     * Get the recipe that owns this recipe item
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the item that belongs to this recipe item
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
