<?php

namespace Tests\Unit;

use App\Services\Catalog\ProductCsvReader;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductCsvReaderTest extends TestCase
{
    #[Test]
    public function it_yields_associative_rows(): void
    {
        $path = storage_path('framework/testing/product_sample.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, implode("\n", [
            'Parent_SKU,Item_SKU,Category',
            'P1,V1,Adult | Tops',
            'P1,V2,Adult | Tops',
        ]));

        $reader = new ProductCsvReader;
        $rows = iterator_to_array($reader->rows($path));

        $this->assertCount(2, $rows);
        $this->assertSame('P1', $rows[0]['Parent_SKU']);
        $this->assertSame('V2', $rows[1]['Item_SKU']);
        $this->assertSame(2, $reader->countDataRows($path));
    }
}
