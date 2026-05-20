<?php

namespace App\Filament\Concerns;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\CatalogImageStorage;

trait HandlesCatalogImageUploads
{
    protected function persistProductMainImageUpload(Product $product, array $data): void
    {
        $storage = app(CatalogImageStorage::class);
        $path = $storage->pathFromUploadState($data['main_image_upload'] ?? null);

        if ($path) {
            $storage->setProductMainImage($product, $path);
        }
    }

    protected function persistVariantImageUploads(ProductVariant $variant, array $data): void
    {
        $storage = app(CatalogImageStorage::class);

        foreach (['main', 'swatch', 'other', 'size_chart'] as $role) {
            $key = "variant_image_upload_{$role}";
            $path = $storage->pathFromUploadState($data[$key] ?? null);

            if ($path) {
                $storage->setVariantImage($variant, $path, $role);
            }
        }
    }

    protected function fillProductMainImageUpload(array $data, Product $product): array
    {
        if ($path = $product->mainImageRelativePath()) {
            $data['main_image_upload'] = $path;
        }

        return $data;
    }

    protected function fillVariantImageUploads(array $data, ProductVariant $variant): array
    {
        foreach (['main', 'swatch', 'other', 'size_chart'] as $role) {
            if ($path = $variant->imageRelativePath($role)) {
                $data["variant_image_upload_{$role}"] = $path;
            }
        }

        return $data;
    }
}
