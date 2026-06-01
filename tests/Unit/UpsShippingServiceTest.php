<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\OrderStatus;
use App\Models\ShippingMethod;
use App\Models\Zone;
use App\Services\Store\UpsShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpsShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_returns_sorted_ups_quotes(): void
    {
        Http::fake([
            '*/security/v1/oauth/token' => Http::response(['access_token' => 'test-token']),
            '*/rating/v2409/shop' => Http::response([
                'RateResponse' => [
                    'RatedShipment' => [
                        [
                            'Service' => ['Code' => '12', 'Description' => 'UPS 3 Day Select'],
                            'TotalCharges' => ['MonetaryValue' => '25.50', 'CurrencyCode' => 'USD'],
                        ],
                        [
                            'Service' => ['Code' => '03', 'Description' => 'UPS Ground'],
                            'TotalCharges' => ['MonetaryValue' => '10.00', 'CurrencyCode' => 'USD'],
                        ],
                    ],
                ],
            ]),
        ]);

        $method = $this->createUpsMethod();
        $country = Country::query()->create(['name' => 'US', 'iso_code_2' => 'US', 'is_enabled' => true]);
        $zone = Zone::query()->create(['country_id' => $country->id, 'name' => 'CA', 'code' => 'CA', 'is_enabled' => true]);

        $result = app(UpsShippingService::class)->getQuotes(
            $method,
            $country,
            $zone,
            'Los Angeles',
            '90001',
            2.5,
            1,
            100.0,
        );

        $this->assertNull($result['error']);
        $this->assertCount(2, $result['quotes']);
        $this->assertSame('03', $result['quotes'][0]['service_code']);
        $this->assertEquals(10.0, $result['quotes'][0]['cost']);
        $this->assertSame('12', $result['quotes'][1]['service_code']);
    }

    private function createUpsMethod(): ShippingMethod
    {
        return ShippingMethod::query()->create([
            'code' => 'ups',
            'name' => 'UPS',
            'is_enabled' => true,
            'config' => [
                'client_id' => 'client',
                'client_secret' => 'secret',
                'sandbox' => true,
                'shipper_number' => '0H25Y0',
                'origin_city' => 'New York',
                'origin_state' => 'NY',
                'origin_postcode' => '10001',
                'origin_country' => 'US',
                'length' => 10,
                'width' => 10,
                'height' => 10,
                'enabled_services' => ['03', '12'],
                'additional_charges' => 0,
                'percentage_charges' => 0,
                'test_mode' => true,
                'quote_type' => 'residential',
                'display_delivery_weight' => false,
            ],
        ]);
    }
}
