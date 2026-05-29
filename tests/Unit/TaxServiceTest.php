<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\StoreSetting;
use App\Models\Zone;
use App\Services\Store\StoreSettings;
use App\Services\Store\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tax_is_zero_when_disabled(): void
    {
        StoreSetting::query()->create(['key' => 'tax_enabled', 'value' => false]);
        app(StoreSettings::class)->flush();

        $zone = $this->createZone(10);

        $result = app(TaxService::class)->calculate(100, $zone->id);

        $this->assertFalse($result['enabled']);
        $this->assertSame(0.0, $result['amount']);
    }

    public function test_tax_calculates_from_zone_rate_when_enabled(): void
    {
        StoreSetting::query()->create(['key' => 'tax_enabled', 'value' => true]);
        app(StoreSettings::class)->flush();

        $zone = $this->createZone(8.25);

        $result = app(TaxService::class)->calculate(100, $zone->id);

        $this->assertTrue($result['enabled']);
        $this->assertEquals(8.25, $result['rate']);
        $this->assertEquals(8.25, $result['amount']);
        $this->assertSame('Tax (8.25%)', $result['title']);
    }

    private function createZone(float $taxRate): Zone
    {
        $country = Country::query()->create([
            'name' => 'Test Country',
            'iso_code_2' => 'TC',
            'iso_code_3' => 'TST',
            'is_enabled' => true,
        ]);

        return Zone::query()->create([
            'country_id' => $country->id,
            'name' => 'Test Zone',
            'code' => 'TZ',
            'is_enabled' => true,
            'tax_rate' => $taxRate,
        ]);
    }
}
