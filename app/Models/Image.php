<?php

namespace App\Models;

use App\Services\Catalog\CatalogImageStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'product_variant_id',
        'product_id',
        'role',
        'source_url',
        'disk',
        'path',
        'width',
        'height',
        'file_hash',
        'download_status',
        'error_message',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function publicUrl(): ?string
    {
        if ($this->download_status === self::STATUS_COMPLETED && filled($this->path)) {
            return app(CatalogImageStorage::class)->publicUrl($this->path);
        }

        if (filled($this->source_url) && str_starts_with($this->source_url, 'http')) {
            return $this->source_url;
        }

        return null;
    }

    /** Relative path on the public disk for Filament FileUpload / ImageColumn. */
    public function storagePath(): ?string
    {
        if ($this->download_status === self::STATUS_COMPLETED && filled($this->path)) {
            return app(CatalogImageStorage::class)->normalizeRelativePath($this->path);
        }

        return null;
    }
}
