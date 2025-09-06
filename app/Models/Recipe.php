<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'recipe_name',
        'instruction',
        'servings',
        'thumbnail',
    ];

    /**
     * Get the items that belong to this recipe
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'recipe_items')
            ->withPivot('unit_id', 'quantity')
            ->withTimestamps();
    }

    /**
     * Get the sub-recipes that belong to this recipe
     */
    public function subRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_recipes', 'recipe_id', 'child_recipe_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get recipes where this recipe is used as a sub-recipe
     */
    public function parentRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_recipes', 'child_recipe_id', 'recipe_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
