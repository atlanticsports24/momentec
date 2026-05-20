<?php

namespace App\Services\Catalog;

use App\Models\Image;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogImageStorage
{
    public const DISK = 'public';

    public function setProductMainImage(Product $product, string $relativePath): Image
    {
        $relativePath = $this->normalizeRelativePath($relativePath);

        $image = Image::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'product_variant_id' => null,
                'role' => 'main',
            ],
            [
                'disk' => self::DISK,
                'path' => $relativePath,
                'source_url' => $this->publicUrl($relativePath) ?? '',
                'download_status' => Image::STATUS_COMPLETED,
                'error_message' => null,
                'sort_order' => 0,
            ]
        );

        $product->update(['default_main_image_path' => $relativePath]);

        return $image;
    }

    public function setVariantImage(ProductVariant $variant, string $relativePath, string $role): Image
    {
        $relativePath = $this->normalizeRelativePath($relativePath);

        $image = Image::query()->updateOrCreate(
            [
                'product_variant_id' => $variant->id,
                'role' => $role,
            ],
            [
                'product_id' => $variant->product_id,
                'disk' => self::DISK,
                'path' => $relativePath,
                'source_url' => $this->publicUrl($relativePath) ?? '',
                'download_status' => Image::STATUS_COMPLETED,
                'error_message' => null,
                'sort_order' => match ($role) {
                    'main' => 0,
                    'swatch' => 1,
                    'other' => 2,
                    'size_chart' => 3,
                    default => 9,
                },
            ]
        );

        if ($role === 'main') {
            $variant->update(['main_image_url' => $this->publicUrl($relativePath)]);
        } elseif ($role === 'swatch') {
            $variant->update(['swatch_image_url' => $this->publicUrl($relativePath)]);
        } elseif ($role === 'other') {
            $variant->update(['other_image_url' => $this->publicUrl($relativePath)]);
        } elseif ($role === 'size_chart') {
            $variant->update(['size_chart_image_url' => $this->publicUrl($relativePath)]);
        }

        return $image;
    }

    public function publicUrl(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return $relativePath;
        }

        return Storage::disk(self::DISK)->url($relativePath);
    }

    public function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        return $path;
    }

    /**
     * @param  mixed  $uploadState  string|array|null from Filament FileUpload
     */
    public function pathFromUploadState(mixed $uploadState): ?string
    {
        if ($uploadState === null || $uploadState === '') {
            return null;
        }

        if (is_array($uploadState)) {
            $uploadState = $uploadState[array_key_first($uploadState)] ?? null;
        }

        if (! is_string($uploadState) || $uploadState === '') {
            return null;
        }

        return $this->normalizeRelativePath($uploadState);
    }
}
