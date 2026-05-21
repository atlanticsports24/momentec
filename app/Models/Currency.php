<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'title',
        'code',
        'symbol_left',
        'symbol_right',
        'decimal_places',
        'value',
        'is_enabled',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'decimal_places' => 'integer',
            'value' => 'decimal:8',
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function format(float|string $amount): string
    {
        $formatted = number_format((float) $amount, $this->decimal_places);

        return ($this->symbol_left ?? '').$formatted.($this->symbol_right ?? '');
    }
}
