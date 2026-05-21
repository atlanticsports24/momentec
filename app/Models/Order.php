<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'order_status_id',
        'payment_method_id',
        'shipping_method_id',
        'currency_id',
        'currency_code',
        'currency_value',
        'customer_email',
        'customer_firstname',
        'customer_lastname',
        'customer_telephone',
        'payment_company',
        'payment_firstname',
        'payment_lastname',
        'payment_address_1',
        'payment_address_2',
        'payment_city',
        'payment_postcode',
        'payment_country_id',
        'payment_zone_id',
        'shipping_firstname',
        'shipping_lastname',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_postcode',
        'shipping_country_id',
        'shipping_zone_id',
        'payment_method_code',
        'payment_method_name',
        'shipping_method_code',
        'shipping_method_name',
        'comment',
        'subtotal',
        'shipping_total',
        'tax_total',
        'total',
        'ip_address',
        'user_agent',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'currency_value' => 'decimal:8',
            'subtotal' => 'decimal:4',
            'shipping_total' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'total' => 'decimal:4',
            'paid_at' => 'datetime',
        ];
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function totals(): HasMany
    {
        return $this->hasMany(OrderTotal::class)->orderBy('sort_order');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class)->latest();
    }

    public function paymentCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'payment_country_id');
    }

    public function shippingCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'shipping_country_id');
    }
}
