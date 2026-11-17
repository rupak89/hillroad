<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstSetting extends Model
{
    protected $fillable = [
        'gst_registered',
        'gst_rate',
        'prices_include_gst',
    ];

    protected $casts = [
        'gst_registered' => 'boolean',
        'gst_rate' => 'decimal:2',
        'prices_include_gst' => 'boolean',
    ];

    /**
     * GST settings are business-wide, so the app keeps a single row.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'gst_registered' => true,
            'gst_rate' => 15.00,
            'prices_include_gst' => false,
        ]);
    }
}
