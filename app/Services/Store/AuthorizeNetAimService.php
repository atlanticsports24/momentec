<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizeNetAimService
{
    public function __construct(
        private readonly StoreSettings $settings,
    ) {}

    /**
     * @param  array{number: string, exp_month: string, exp_year: string, cvv: string}  $card
     * @return array{success: bool, error?: string, history_comment?: string, transaction_id?: string}
     */
    public function charge(Order $order, PaymentMethod $method, array $card): array
    {
        $login = (string) $method->configValue('api_login_id', '');
        $key = (string) $method->configValue('transaction_key', '');

        if ($login === '' || $key === '') {
            return ['success' => false, 'error' => 'Authorize.Net is not configured. Please contact the store.'];
        }

        $order->loadMissing(['paymentCountry', 'paymentZone', 'shippingCountry', 'shippingZone']);

        $url = $this->gatewayUrl($method);
        $payload = $this->buildPayload($order, $method, $card, $login, $key);

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('Authorize.Net AIM request failed', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return ['success' => false, 'error' => 'Unable to connect to the payment gateway. Please try again.'];
        }

        if (! $response->successful() || $response->body() === '') {
            Log::error('Authorize.Net AIM empty response', ['order_id' => $order->id, 'status' => $response->status()]);

            return ['success' => false, 'error' => 'Empty gateway response. Please try again.'];
        }

        return $this->parseResponse($response->body(), $order, $method);
    }

    private function gatewayUrl(PaymentMethod $method): string
    {
        $server = $method->configValue('server', 'test');

        return $server === 'live'
            ? 'https://secure.authorize.net/gateway/transact.dll'
            : 'https://test.authorize.net/gateway/transact.dll';
    }

    /**
     * @param  array{number: string, exp_month: string, exp_year: string, cvv: string}  $card
     */
    private function buildPayload(Order $order, PaymentMethod $method, array $card, string $login, string $key): array
    {
        $expMonth = str_pad((string) (int) $card['exp_month'], 2, '0', STR_PAD_LEFT);
        $expYear = (string) $card['exp_year'];
        if (strlen($expYear) === 4) {
            $expYear = substr($expYear, -2);
        }

        $data = [
            'x_login' => $login,
            'x_tran_key' => $key,
            'x_version' => '3.1',
            'x_delim_data' => 'true',
            'x_delim_char' => '|',
            'x_encap_char' => '"',
            'x_relay_response' => 'false',
            'x_first_name' => $order->payment_firstname ?? $order->customer_firstname,
            'x_last_name' => $order->payment_lastname ?? $order->customer_lastname,
            'x_company' => $order->payment_company ?? '',
            'x_address' => $order->payment_address_1,
            'x_city' => $order->payment_city,
            'x_state' => $order->paymentZone?->code ?? $order->paymentZone?->name ?? '',
            'x_zip' => $order->payment_postcode,
            'x_country' => $order->paymentCountry?->iso_code_2 ?? '',
            'x_phone' => $order->customer_telephone ?? '',
            'x_customer_ip' => $order->ip_address ?? request()->ip(),
            'x_email' => $order->customer_email,
            'x_description' => (string) $this->settings->get('store_name', config('app.name')),
            'x_amount' => number_format((float) $order->total, 2, '.', ''),
            'x_currency_code' => $order->currency_code,
            'x_method' => 'CC',
            'x_type' => $method->configValue('method', 'capture') === 'authorization' ? 'AUTH_ONLY' : 'AUTH_CAPTURE',
            'x_card_num' => preg_replace('/\s+/', '', $card['number']),
            'x_exp_date' => $expMonth.$expYear,
            'x_card_code' => $card['cvv'],
            'x_invoice_num' => (string) $order->id,
            'x_solution_id' => 'A1000015',
        ];

        if ($order->shipping_method_name) {
            $data['x_ship_to_first_name'] = $order->shipping_firstname;
            $data['x_ship_to_last_name'] = $order->shipping_lastname;
            $data['x_ship_to_address'] = trim(($order->shipping_address_1 ?? '').' '.($order->shipping_address_2 ?? ''));
            $data['x_ship_to_city'] = $order->shipping_city;
            $data['x_ship_to_state'] = $order->shippingZone?->code ?? $order->shippingZone?->name ?? '';
            $data['x_ship_to_zip'] = $order->shipping_postcode;
            $data['x_ship_to_country'] = $order->shippingCountry?->iso_code_2 ?? '';
        } else {
            $data['x_ship_to_first_name'] = $data['x_first_name'];
            $data['x_ship_to_last_name'] = $data['x_last_name'];
            $data['x_ship_to_address'] = $data['x_address'];
            $data['x_ship_to_city'] = $data['x_city'];
            $data['x_ship_to_state'] = $data['x_state'];
            $data['x_ship_to_zip'] = $data['x_zip'];
            $data['x_ship_to_country'] = $data['x_country'];
        }

        if ($method->configValue('mode', 'test') === 'test') {
            $data['x_test_request'] = 'true';
        }

        return $data;
    }

  /**
     * @return array{success: bool, error?: string, history_comment?: string, transaction_id?: string}
     */
    private function parseResponse(string $body, Order $order, PaymentMethod $method): array
    {
        $parts = explode('|', $body);
        $response = [];

        foreach ($parts as $index => $part) {
            $response[$index + 1] = trim($part, '"');
        }

        if (($response[1] ?? '') !== '1') {
            return [
                'success' => false,
                'error' => $response[4] ?? 'Payment was declined. Please check your card details.',
            ];
        }

        if (! $this->verifyMd5Hash($response, $order, $method)) {
            Log::warning('Authorize.Net MD5 hash mismatch', ['order_id' => $order->id]);

            return [
                'success' => false,
                'error' => 'Payment verification failed. Please contact support.',
            ];
        }

        $comment = collect([
            isset($response[5]) ? 'Authorization Code: '.$response[5] : null,
            isset($response[6]) ? 'AVS Response: '.$response[6] : null,
            isset($response[7]) ? 'Transaction ID: '.$response[7] : null,
            isset($response[39]) ? 'Card Code Response: '.$response[39] : null,
            isset($response[40]) ? 'Cardholder Authentication Verification Response: '.$response[40] : null,
        ])->filter()->implode("\n");

        return [
            'success' => true,
            'history_comment' => $comment,
            'transaction_id' => $response[7] ?? null,
        ];
    }

    private function verifyMd5Hash(array $response, Order $order, PaymentMethod $method): bool
    {
        $md5Hash = (string) $method->configValue('md5_hash', '');

        if ($md5Hash === '') {
            return true;
        }

        $login = (string) $method->configValue('api_login_id', '');
        $amount = number_format((float) $order->total, 2, '.', '');
        $transactionId = $response[7] ?? '';
        $expected = strtoupper(md5($md5Hash.$login.$transactionId.$amount));

        return strtoupper((string) ($response[38] ?? '')) === $expected;
    }
}
