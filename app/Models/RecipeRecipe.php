<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeRecipe extends Model
{
    protected $fillable = [
        'recipe_id',
        'child_recipe_id',
        'quantity',
    ];

    /**
     * Get the parent recipe
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the child recipe
     */
    public function childRecipe()
    {
        return $this->belongsTo(Recipe::class, 'child_recipe_id');
    }
}
