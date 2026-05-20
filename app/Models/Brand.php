<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'code',
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::saving(function (Brand $brand): void {
            if ($brand->slug === null || $brand->slug === '') {
                $brand->slug = Str::slug($brand->code) ?: 'brand-'.Str::lower(Str::random(8));
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
