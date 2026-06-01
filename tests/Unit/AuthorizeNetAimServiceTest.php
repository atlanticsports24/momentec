<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Zone;
use App\Services\Store\AuthorizeNetAimService;
use App\Services\Store\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthorizeNetAimServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_charge_parses_aim_response(): void
    {
        $method = $this->createAuthorizeNetMethod();
        $order = $this->createOrder($method);

        $body = '"1"|"1"|"1"|"This transaction has been approved."|"000000"|"X"|"123456"|"';
        Http::fake(['*' => Http::response($body)]);

        $result = app(AuthorizeNetAimService::class)->charge($order, $method, [
            'number' => '4111111111111111',
            'exp_month' => '12',
            'exp_year' => '2030',
            'cvv' => '123',
        ]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Transaction ID: 123456', $result['history_comment']);
    }

    public function test_declined_charge_returns_gateway_message(): void
    {
        $method = $this->createAuthorizeNetMethod();
        $order = $this->createOrder($method);

        $body = '"2"|"2"|"2"|"This transaction has been declined."|"000000"|"X"|""|""';
        Http::fake(['*' => Http::response($body)]);

        $result = app(AuthorizeNetAimService::class)->charge($order, $method, [
            'number' => '4111111111111111',
            'exp_month' => '12',
            'exp_year' => '2030',
            'cvv' => '123',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('This transaction has been declined.', $result['error']);
    }

    private function createAuthorizeNetMethod(): PaymentMethod
    {
        $status = OrderStatus::query()->create([
            'name' => 'Processing',
            'code' => 'processing_test',
            'sort_order' => 1,
        ]);

        return PaymentMethod::query()->create([
            'code' => 'authorize_net',
            'name' => 'Authorize.Net',
            'is_enabled' => true,
            'success_order_status_id' => $status->id,
            'config' => [
                'api_login_id' => 'login',
                'transaction_key' => 'key',
                'server' => 'test',
                'mode' => 'test',
                'method' => 'capture',
            ],
        ]);
    }

    private function createOrder(PaymentMethod $method): Order
    {
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
            'tax_rate' => 0,
        ]);

        $status = OrderStatus::query()->create([
            'name' => 'Pending',
            'code' => 'pending_test',
            'sort_order' => 1,
        ]);

        app(StoreSettings::class)->set('store_name', 'Momentec Test');

        return Order::query()->create([
            'order_number' => 'TEST001',
            'order_status_id' => $status->id,
            'payment_method_id' => $method->id,
            'currency_code' => 'USD',
            'currency_value' => 1,
            'customer_email' => 'test@example.com',
            'customer_firstname' => 'John',
            'customer_lastname' => 'Doe',
            'payment_firstname' => 'John',
            'payment_lastname' => 'Doe',
            'payment_address_1' => '123 Main St',
            'payment_city' => 'Los Angeles',
            'payment_postcode' => '90001',
            'payment_country_id' => $country->id,
            'payment_zone_id' => $zone->id,
            'shipping_country_id' => $country->id,
            'shipping_zone_id' => $zone->id,
            'subtotal' => 100,
            'shipping_total' => 10,
            'tax_total' => 0,
            'total' => 110,
        ]);
    }
}
