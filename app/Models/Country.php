<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'address_format',
        'postcode_required',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'postcode_required' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }
}
