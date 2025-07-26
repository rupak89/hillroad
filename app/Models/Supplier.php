<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'customer_number',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'post_code',
        'country',
        'website'
    ];

    /**
     * Get the items associated with the supplier.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
