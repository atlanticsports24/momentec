<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantDisplayOption;
use App\Models\SyncRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\Csv\Reader;

/**
 * Parses Augusta / Momentec standard product CSV rows (header row required).
 */
class ProductCsvReader
{
    /**
     * @return \Generator<int, array<string, string|null>>
     */
    public function rows(string $absolutePath): \Generator
    {
        $reader = Reader::createFromPath($absolutePath, 'r');
        $reader->setHeaderOffset(0);

        foreach ($reader->getRecords() as $row) {
            /** @var array<string, string|null> $row */
            yield $row;
        }
    }

    public function countDataRows(string $absolutePath): int
    {
        $reader = Reader::createFromPath($absolutePath, 'r');
        $reader->setHeaderOffset(0);

        return iterator_count($reader->getRecords());
    }
}
