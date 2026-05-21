<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_enabled',
        'sort_order',
        'geo_zone_id',
        'min_total',
        'max_total',
        'success_order_status_id',
        'failed_order_status_id',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'min_total' => 'decimal:4',
            'max_total' => 'decimal:4',
            'config' => 'array',
        ];
    }

    public function geoZone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class);
    }

    public function successOrderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'success_order_status_id');
    }

    public function failedOrderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'failed_order_status_id');
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config ?? [], $key, $default);
    }
}
