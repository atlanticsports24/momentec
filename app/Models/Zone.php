<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zone extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'code',
        'is_enabled',
        'tax_rate',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'tax_rate' => 'decimal:4',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
