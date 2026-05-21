<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTotal extends Model
{
    protected $fillable = [
        'order_id',
        'code',
        'title',
        'value',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'sort_order' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
