<?php

namespace App\Models;

use App\Models\Image;
use App\Services\Catalog\CatalogImageStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'parent_sku',
        'brand_id',
        'name',
        'description',
        'admin_description_locked',
        'division',
        'currency',
        'variation_theme',
        'launch_date',
        'features',
        'min_msrp',
        'max_msrp',
        'min_cost',
        'max_cost',
        'default_main_image_path',
    ];

    protected function casts(): array
    {
        return [
            'admin_description_locked' => 'boolean',
            'launch_date' => 'date',
            'min_msrp' => 'decimal:4',
            'max_msrp' => 'decimal:4',
            'min_cost' => 'decimal:4',
            'max_cost' => 'decimal:4',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')->withTimestamps();
    }

    public function variantDisplayOptions(): HasMany
    {
        return $this->hasMany(ProductVariantDisplayOption::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function mainImageRecord(): ?Image
    {
        return $this->images()
            ->where('role', 'main')
            ->where('download_status', Image::STATUS_COMPLETED)
            ->whereNotNull('path')
            ->orderBy('sort_order')
            ->first();
    }

    public function mainImageRelativePath(): ?string
    {
        $path = $this->mainImageRecord()?->path ?? $this->default_main_image_path;

        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        return app(CatalogImageStorage::class)->normalizeRelativePath($path);
    }

    public function mainImageUrl(): ?string
    {
        $record = $this->mainImageRecord();
        if ($record) {
            return $record->publicUrl();
        }

        if (filled($this->default_main_image_path)) {
            return app(CatalogImageStorage::class)->publicUrl($this->default_main_image_path);
        }

        return null;
    }
}
