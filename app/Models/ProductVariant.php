<?php

namespace App\Models;

use App\Services\Catalog\CatalogImageStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'item_sku',
        'upc_code',
        'gtin',
        'msrp',
        'cost',
        'currency',
        'division',
        'item_description',
        'admin_variant_description_locked',
        'main_image_url',
        'other_image_url',
        'swatch_image_url',
        'size_chart_image_url',
        'variation_theme',
        'color',
        'size',
        'weight',
        'weight_unit',
        'volume',
        'volume_unit',
        'case_pack_qty',
        'color_hex_value',
        'status',
        'product_video_url',
        'ribbon',
        'country_of_origin',
    ];

    protected function casts(): array
    {
        return [
            'admin_variant_description_locked' => 'boolean',
            'msrp' => 'decimal:4',
            'cost' => 'decimal:4',
            'weight' => 'decimal:4',
            'volume' => 'decimal:4',
            'case_pack_qty' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'product_variant_id');
    }

    public function imageRecord(string $role): ?Image
    {
        return $this->images()
            ->where('role', $role)
            ->where('download_status', Image::STATUS_COMPLETED)
            ->whereNotNull('path')
            ->orderBy('sort_order')
            ->first();
    }

    public function imageRelativePath(string $role): ?string
    {
        $path = $this->imageRecord($role)?->path;

        if ($path !== null && $path !== '' && ! str_starts_with($path, 'http')) {
            return app(CatalogImageStorage::class)->normalizeRelativePath($path);
        }

        return null;
    }

    public function imageUrl(string $role): ?string
    {
        $record = $this->imageRecord($role);
        if ($record) {
            return $record->publicUrl();
        }

        $url = match ($role) {
            'main' => $this->main_image_url,
            'swatch' => $this->swatch_image_url,
            'other' => $this->other_image_url,
            'size_chart' => $this->size_chart_image_url,
            default => null,
        };

        if (filled($url) && str_starts_with($url, 'http')) {
            return $url;
        }

        return null;
    }

    public function mainImageRelativePath(): ?string
    {
        return $this->imageRelativePath('main');
    }

    public function mainImageUrl(): ?string
    {
        return $this->imageUrl('main');
    }
}
