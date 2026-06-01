<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;
use App\Services\Store\UspsShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UspsShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_parses_usps_ratev4_response(): void
    {
        $xml = <<<'XML'
<?xml version="1.0"?>
<RateV4Response>
  <Package ID="0">
    <Postage CLASSID="0">
      <MailService>Priority Mail</MailService>
      <Rate>12.50</Rate>
    </Postage>
    <Postage CLASSID="1">
      <MailService>Priority Mail Express</MailService>
      <Rate>28.00</Rate>
    </Postage>
  </Package>
</RateV4Response>
XML;

        Http::fake([
            '*' => Http::response($xml),
        ]);

        $method = ShippingMethod::query()->create([
            'code' => 'usps',
            'name' => 'USPS',
            'is_enabled' => true,
            'config' => [
                'user_id' => 'test-user',
                'origin_postcode' => '11780',
                'test_mode' => true,
                'enabled_services' => ['PRIORITY MAIL', 'PRIORITY MAIL EXPRESS'],
                'display_delivery_weight' => false,
            ],
        ]);

        $country = Country::query()->create([
            'name' => 'United States',
            'iso_code_2' => 'US',
            'is_enabled' => true,
        ]);

        $zone = Zone::query()->create([
            'country_id' => $country->id,
            'name' => 'California',
            'code' => 'CA',
            'is_enabled' => true,
        ]);

        $result = app(UspsShippingService::class)->getQuotes(
            $method,
            $country,
            $zone,
            'Los Angeles',
            '90001',
            2.0,
            1,
            50.0,
        );

        $this->assertNull($result['error']);
        $this->assertCount(2, $result['quotes']);
        $this->assertEquals(12.5, $result['quotes'][0]['cost']);
    }
}
