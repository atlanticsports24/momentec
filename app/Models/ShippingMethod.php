<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_enabled',
        'sort_order',
        'geo_zone_id',
        'cost',
        'free_shipping_min',
        'min_total',
        'max_total',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'cost' => 'decimal:4',
            'free_shipping_min' => 'decimal:4',
            'min_total' => 'decimal:4',
            'max_total' => 'decimal:4',
            'config' => 'array',
        ];
    }

    public function geoZone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class);
    }

    public function calculateCost(float $subtotal): float
    {
        if ($this->free_shipping_min !== null && $subtotal >= (float) $this->free_shipping_min) {
            return 0.0;
        }

        if ($this->code === 'free') {
            return 0.0;
        }

        return (float) $this->cost;
    }
}
