<?php

namespace App\Services\Catalog;

use App\Jobs\DownloadCatalogImageJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantDisplayOption;
use App\Models\SyncRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Feed merge rules:
 * - Product.description is skipped on sync when admin_description_locked is true.
 * - ProductVariant.item_description is skipped when admin_variant_description_locked is true.
 * - Pricing, URLs, identifiers, and taxonomy from the feed always update on sync (unless you extend locks).
 */
class ProductCatalogSyncService
{
    public function __construct(
        protected ProductCsvReader $reader,
        protected BrandCodeResolver $brandCodes
    ) {}

    public function truncate(array $flags): void
    {
        if (! array_filter($flags)) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            if ($flags['images'] ?? false) {
                DB::table('images')->truncate();
            }
            if ($flags['variant_display'] ?? false) {
                DB::table('product_variant_display_options')->truncate();
            }
            if ($flags['category_product'] ?? false) {
                DB::table('category_product')->truncate();
            }
            if ($flags['variants'] ?? false) {
                DB::table('product_variants')->truncate();
            }
            if ($flags['products'] ?? false) {
                DB::table('products')->truncate();
            }
            if ($flags['categories'] ?? false) {
                DB::table('categories')->truncate();
            }
            if ($flags['brands'] ?? false) {
                DB::table('brands')->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function syncCategories(string $path, SyncRun $run): void
    {
        $run->update(['current_step' => 'categories', 'processed_rows' => 0]);
        $unique = [];
        foreach ($this->reader->rows($path) as $row) {
            $cat = trim((string) ($row['Category'] ?? ''));
            if ($cat !== '') {
                $unique[$cat] = true;
            }
        }

        $run->update(['total_rows' => count($unique), 'processed_rows' => 0]);

        $processed = 0;
        foreach (array_keys($unique) as $pathString) {
            $this->ensureCategoryTree($pathString);
            $processed++;
            if ($processed % 25 === 0) {
                $run->update(['processed_rows' => $processed]);
            }
        }

        $run->update(['processed_rows' => $processed]);
    }

    /**
     * Build or reuse a category chain from a feed path like "Misc | MULTI-SPORT | ACCESSORIES FSG".
     *
     * For each segment, under its parent: if a category with that name already exists, reuse its id;
     * otherwise create one. The next CSV row with the same path reuses the same Misc → MULTI-SPORT → leaf ids.
     */
    public function ensureCategoryTree(string $categoryPath): Category
    {
        $segments = preg_split('/\s*\|\s*/', trim($categoryPath), -1, PREG_SPLIT_NO_EMPTY);
        if ($segments === false || $segments === []) {
            throw new \InvalidArgumentException('Empty category path.');
        }

        $parentId = null;
        $pathParts = [];
        $last = null;

        foreach ($segments as $segment) {
            $name = trim($segment);
            $slugBase = Str::slug($name) ?: Str::random(8);
            $slug = $slugBase.'-'.($parentId ?? 'root');

            $last = Category::query()->firstOrCreate(
                [
                    'parent_id' => $parentId,
                    'name' => $name,
                ],
                [
                    'slug' => $slug,
                    'path' => '',
                ]
            );

            $pathParts[] = $name;
            $last->update(['path' => implode(' / ', $pathParts)]);
            $parentId = $last->id;
        }

        return $last instanceof Category ? $last : throw new \RuntimeException('Category tree build failed.');
    }

    public function syncProducts(string $path, SyncRun $run): void
    {
        $run->update(['current_step' => 'products', 'processed_rows' => 0]);
        $total = $this->reader->countDataRows($path);
        $run->update(['total_rows' => $total]);

        $processed = 0;
        foreach ($this->reader->rows($path) as $row) {
            try {
                $this->upsertProductRow($row);
            } catch (\Throwable $e) {
                $this->bumpError($run, $e, $row);
            }
            $processed++;
            if ($processed % 250 === 0) {
                $run->update(['processed_rows' => $processed]);
            }
        }

        $run->update(['processed_rows' => $processed]);
    }

    public function syncVariants(string $path, SyncRun $run): void
    {
        $run->update(['current_step' => 'variants', 'processed_rows' => 0]);
        $total = $this->reader->countDataRows($path);
        $run->update(['total_rows' => $total]);

        $processed = 0;
        foreach ($this->reader->rows($path) as $row) {
            try {
                $this->upsertVariantRow($row);
            } catch (\Throwable $e) {
                $this->bumpError($run, $e, $row);
            }
            $processed++;
            if ($processed % 250 === 0) {
                $run->update(['processed_rows' => $processed]);
            }
        }

        $run->update(['processed_rows' => $processed]);
    }

    public function recalculateAggregates(SyncRun $run): void
    {
        $run->update(['current_step' => 'aggregates', 'total_rows' => 0, 'processed_rows' => 0]);

        $processed = 0;
        Product::query()->select('id')->chunkById(200, function ($products) use ($run, &$processed) {
            foreach ($products as $product) {
                $stats = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->selectRaw('MIN(msrp) as min_msrp, MAX(msrp) as max_msrp, MIN(cost) as min_cost, MAX(cost) as max_cost')
                    ->first();

                if ($stats && $stats->min_msrp !== null) {
                    Product::query()->whereKey($product->id)->update([
                        'min_msrp' => $stats->min_msrp,
                        'max_msrp' => $stats->max_msrp,
                        'min_cost' => $stats->min_cost,
                        'max_cost' => $stats->max_cost,
                    ]);
                }

                $rep = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->whereNotNull('main_image_url')
                    ->orderBy('msrp')
                    ->orderBy('id')
                    ->first();

                if ($rep && $rep->main_image_url) {
                    Product::query()->whereKey($product->id)->update([
                        'default_main_image_path' => $rep->main_image_url,
                    ]);
                }

                $processed++;
            }

            $run->update(['processed_rows' => $processed]);
        });
    }

    public function enqueuePendingImages(SyncRun $run): void
    {
        $run->update(['current_step' => 'images_queue', 'processed_rows' => 0]);

        ProductVariant::query()->chunkById(500, function ($variants) {
            foreach ($variants as $variant) {
                foreach (['main' => 'main_image_url', 'swatch' => 'swatch_image_url', 'other' => 'other_image_url', 'size_chart' => 'size_chart_image_url'] as $role => $field) {
                    $url = $variant->{$field} ?? null;
                    if (! is_string($url) || trim($url) === '') {
                        continue;
                    }

                    $image = Image::query()->firstOrCreate(
                        [
                            'product_variant_id' => $variant->id,
                            'role' => $role,
                            'source_url' => $url,
                        ],
                        [
                            'product_id' => $variant->product_id,
                            'download_status' => Image::STATUS_PENDING,
                            'sort_order' => match ($role) {
                                'main' => 0,
                                'swatch' => 1,
                                'other' => 2,
                                default => 3,
                            },
                        ]
                    );

                    if ($image->download_status === Image::STATUS_PENDING) {
                        DownloadCatalogImageJob::dispatch($image->id);
                    }
                }
            }
        });

        $pending = Image::query()->where('download_status', Image::STATUS_PENDING)->count();
        $run->update(['total_rows' => $pending, 'processed_rows' => 0]);
    }

    protected function upsertProductRow(array $row): void
    {
        $parentSku = trim((string) ($row['Parent_SKU'] ?? ''));
        if ($parentSku === '') {
            return;
        }

        $brandCode = trim((string) ($row['Brand'] ?? ''));
        $brand = $brandCode === ''
            ? null
            : Brand::query()->updateOrCreate(
                ['code' => $brandCode],
                [
                    'name' => $this->brandCodes->nameForCode($brandCode),
                    'slug' => $this->brandCodes->slugForCode($brandCode),
                ]
            );

        $product = Product::query()->firstOrNew(['parent_sku' => $parentSku]);
        $product->brand_id = $brand?->id;
        $product->name = (string) ($row['Item_Name'] ?? $product->name ?? $parentSku);
        if (! $product->admin_description_locked) {
            $product->description = (string) ($row['Item_Description'] ?? '');
        }
        $product->division = $row['Division'] ?? $product->division;
        $product->currency = $row['Currency'] ?? $product->currency ?? 'USD';
        $product->variation_theme = $row['Variation_Theme'] ?? $product->variation_theme;
        $product->launch_date = $this->parseDate($row['Launch_Date'] ?? null) ?? $product->launch_date;
        $product->features = $row['Features'] ?? $product->features;
        $product->save();

        $categoryPath = trim((string) ($row['Category'] ?? ''));
        if ($categoryPath !== '') {
            $leaf = $this->ensureCategoryTree($categoryPath);
            $product->categories()->syncWithoutDetaching([$leaf->id]);
        }

        $this->ensureDefaultDisplayOptions($product, (string) ($row['Variation_Theme'] ?? ''));
    }

    protected function upsertVariantRow(array $row): void
    {
        $parentSku = trim((string) ($row['Parent_SKU'] ?? ''));
        $itemSku = trim((string) ($row['Item_SKU'] ?? ''));
        if ($parentSku === '' || $itemSku === '') {
            return;
        }

        $product = Product::query()->where('parent_sku', $parentSku)->first();
        if (! $product) {
            throw new \RuntimeException("Missing product for parent_sku {$parentSku}");
        }

        $variant = ProductVariant::query()->firstOrNew(['item_sku' => $itemSku]);
        $variant->product_id = $product->id;
        $variant->upc_code = $row['UPC_Code'] ?? null;
        $variant->gtin = $row['GTIN'] ?? null;
        $variant->msrp = $this->decimalOrNull($row['MSRP'] ?? null);
        $variant->cost = $this->decimalOrNull($row['Cost'] ?? null);
        $variant->currency = (string) ($row['Currency'] ?? 'USD');
        $variant->division = $row['Division'] ?? null;
        if (! $variant->admin_variant_description_locked) {
            $variant->item_description = (string) ($row['Item_Description'] ?? '');
        }
        $variant->main_image_url = $row['Main_Image_URL'] ?? null;
        $variant->other_image_url = $row['Other_Image_URL'] ?? null;
        $variant->swatch_image_url = $row['Swatch_Image_URL'] ?? null;
        $variant->size_chart_image_url = $row['Size_Chart_Image_URL'] ?? null;
        $variant->variation_theme = $row['Variation_Theme'] ?? null;
        $variant->color = $row['Color'] ?? null;
        $variant->size = $row['Size'] ?? null;
        $variant->weight = $this->decimalOrNull($row['Weight'] ?? null);
        $variant->weight_unit = $row['Weight_Unit'] ?? null;
        $variant->volume = $this->decimalOrNull($row['Volume'] ?? null);
        $variant->volume_unit = $row['Volume_Unit'] ?? null;
        $variant->case_pack_qty = $this->intOrNull($row['Case_Pack_Qty'] ?? null);
        $variant->color_hex_value = $this->normalizeHex($row['Color_Hex_Value'] ?? null);
        $variant->status = $row['Status'] ?? null;
        $variant->product_video_url = $row['ProductVideoUrl'] ?? null;
        $variant->ribbon = $row['Ribbon'] ?? null;
        $variant->country_of_origin = $row['Country_Of_Origin'] ?? null;
        $variant->save();
    }

    protected function ensureDefaultDisplayOptions(Product $product, string $variationTheme): void
    {
        $dims = collect(preg_split('/\s*,\s*/', $variationTheme, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn (string $d) => strtolower(trim($d)))
            ->filter()
            ->values();

        if ($dims->isEmpty()) {
            $dims = collect(['color', 'size']);
        }

        foreach ($dims as $index => $dimension) {
            $display = match ($dimension) {
                'color' => 'swatch',
                'size' => 'select',
                default => 'select',
            };

            ProductVariantDisplayOption::query()->firstOrCreate(
                [
                    'product_id' => $product->id,
                    'dimension' => $dimension,
                ],
                [
                    'display_type' => $display,
                    'sort_order' => $index,
                    'label' => Str::title($dimension),
                ]
            );
        }
    }

    protected function bumpError(SyncRun $run, \Throwable $e, array $row): void
    {
        $sample = $run->error_sample ?? [];
        $sample[] = [
            'message' => $e->getMessage(),
            'sku' => $row['Item_SKU'] ?? null,
            'parent' => $row['Parent_SKU'] ?? null,
        ];
        $sample = array_slice($sample, -20);

        $run->update([
            'error_count' => $run->error_count + 1,
            'error_sample' => $sample,
        ]);

        Log::warning('catalog_sync_row_error', ['exception' => $e->getMessage(), 'row' => $row]);
    }

    protected function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function decimalOrNull(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (string) $value;
    }

    protected function intOrNull(?string $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeHex(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = trim($value);
        if ($v === '') {
            return null;
        }

        if ($v[0] !== '#') {
            $v = '#'.$v;
        }

        return $v;
    }
}
