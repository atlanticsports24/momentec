<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    protected $fillable = [
        'name',
        'code',
        'color',
        'sort_order',
        'is_core',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_core' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
