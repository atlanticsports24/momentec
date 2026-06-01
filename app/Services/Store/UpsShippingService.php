<?php

namespace App\Services\Store;

use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpsShippingService
{
    /**
     * @return array{quotes: array<int, array{service_code: string, title: string, cost: float}>, error: ?string}
     */
    public function getQuotes(
        ShippingMethod $method,
        Country $country,
        Zone $zone,
        string $city,
        string $postcode,
        float $cartWeight,
        int $lineCount,
        float $subtotal,
    ): array {
        $clientId = (string) $method->configValue('client_id', '');
        $clientSecret = (string) $method->configValue('client_secret', '');

        if ($clientId === '' || $clientSecret === '') {
            return ['quotes' => [], 'error' => 'Client ID and Client secret required in admin settings.'];
        }

        $shipperNumber = (string) $method->configValue('shipper_number', '');

        if ($shipperNumber === '') {
            return ['quotes' => [], 'error' => 'Shipper account number required in admin settings.'];
        }

        $sandbox = $this->isTestMode($method);
        $token = $this->getAccessToken($clientId, $clientSecret, $sandbox);

        if (! $token) {
            return ['quotes' => [], 'error' => 'Unable to authenticate with UPS.'];
        }

        $weight = $this->normalizeWeight($cartWeight);
        $weightCode = $this->weightUnitCode($method);
        $lengthUnit = strtoupper((string) $method->configValue('length_unit', 'IN'));
        $length = max(1, (int) ceil((float) $method->configValue('length', 5)));
        $width = max(1, (int) ceil((float) $method->configValue('width', 5)));
        $height = max(1, (int) ceil((float) $method->configValue('height', 5)));

        $url = config('ups.rating_url.'.($sandbox ? 'sandbox' : 'production'));

        $shipToAddress = [
            'City' => $city,
            'StateProvinceCode' => $zone->code,
            'PostalCode' => $postcode,
            'CountryCode' => $country->iso_code_2,
        ];

        if ($method->configValue('quote_type', 'residential') === 'residential') {
            $shipToAddress['ResidentialAddressIndicator'] = '';
        }

        $originAddress = [
            'City' => (string) $method->configValue('origin_city', ''),
            'StateProvinceCode' => (string) $method->configValue('origin_state', ''),
            'PostalCode' => (string) $method->configValue('origin_postcode', ''),
            'CountryCode' => (string) $method->configValue('origin_country', 'US'),
        ];

        $shipment = [
            'Shipper' => [
                'ShipperNumber' => (string) $method->configValue('shipper_number', ''),
                'Address' => $originAddress,
            ],
            'ShipTo' => [
                'Address' => $shipToAddress,
            ],
            'ShipFrom' => [
                'Address' => $originAddress,
            ],
            'Package' => [
                'PackagingType' => [
                    'Code' => (string) $method->configValue('packaging', '02'),
                ],
                'Dimensions' => [
                    'UnitOfMeasurement' => ['Code' => $lengthUnit],
                    'Length' => (string) $length,
                    'Width' => (string) $width,
                    'Height' => (string) $height,
                ],
                'PackageWeight' => [
                    'UnitOfMeasurement' => ['Code' => $weightCode],
                    'Weight' => (string) ceil($weight),
                ],
            ],
        ];

        $shipment['ShipmentRatingOptions'] = array_filter([
            'NegotiatedRatesIndicator' => $this->useNegotiatedRates($method) ? '' : null,
        ]);

        $shipment['PaymentDetails'] = [
            'ShipmentCharge' => [
                'Type' => '01',
                'BillShipper' => [
                    'AccountNumber' => (string) $method->configValue('shipper_number', ''),
                ],
            ],
        ];

        if ($method->configValue('enable_insurance', false)) {
            $shipment['Package']['PackageServiceOptions'] = [
                'DeclaredValue' => [
                    'CurrencyCode' => 'USD',
                    'MonetaryValue' => (string) number_format(max(1, $subtotal), 2, '.', ''),
                ],
            ];
        }

        $payload = [
            'RateRequest' => [
                'Request' => [
                    'TransactionReference' => [
                        'CustomerContext' => 'Momentec Checkout',
                    ],
                ],
                'Shipment' => $shipment,
            ],
        ];

        if ($method->configValue('debug_mode', false)) {
            Log::debug('UPS rate request', ['url' => $url, 'payload' => $payload]);
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'transId' => uniqid('momentec_', true),
                    'transactionSrc' => 'Momentec',
                ])
                ->timeout(30)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('UPS rating request failed', ['message' => $e->getMessage()]);

            return ['quotes' => [], 'error' => 'Unable to retrieve UPS rates.'];
        }

        if ($method->configValue('debug_mode', false)) {
            Log::debug('UPS rate response', ['status' => $response->status(), 'body' => $response->body()]);
        }

        if (! $response->successful()) {
            $message = $response->json('response.errors.0.message')
                ?? $response->json('Fault.detail.Errors.ErrorDetail.PrimaryErrorCode.Description')
                ?? 'UPS rating request failed.';

            Log::warning('UPS rating error', ['body' => $response->body()]);

            return ['quotes' => [], 'error' => $message];
        }

        return $this->parseQuotes($response->json(), $method, $lineCount, $subtotal, $weight, $weightCode);
    }

    private function isTestMode(ShippingMethod $method): bool
    {
        if ($method->configValue('test_mode') !== null) {
            return (bool) $method->configValue('test_mode');
        }

        return (bool) $method->configValue('sandbox', true);
    }

    private function useNegotiatedRates(ShippingMethod $method): bool
    {
        return (string) $method->configValue('pickup_method', '11') !== '11';
    }

    private function weightUnitCode(ShippingMethod $method): string
    {
        $code = strtoupper((string) $method->configValue('weight_unit', 'LBS'));
        if ($code === 'LB') {
            return 'LBS';
        }
        if ($code === 'KG') {
            return 'KGS';
        }

        return $code;
    }

    private function additionalCharges(ShippingMethod $method): float
    {
        $charges = $method->configValue('additional_charges');
        if ($charges !== null && $charges !== '') {
            return (float) $charges;
        }

        return (float) $method->configValue('additional_cost', 0);
    }

    private function percentageCharges(ShippingMethod $method): float
    {
        $percent = $method->configValue('percentage_charges');
        if ($percent !== null && $percent !== '') {
            return (float) $percent;
        }

        return (float) $method->configValue('additional_cost_percent', 0);
    }

    private function getAccessToken(string $clientId, string $clientSecret, bool $sandbox): ?string
    {
        $cacheKey = 'ups_oauth_'.md5($clientId.($sandbox ? 'sandbox' : 'live'));

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($clientId, $clientSecret, $sandbox) {
            $url = config('ups.oauth_url.'.($sandbox ? 'sandbox' : 'production'));

            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->withHeaders(['Accept' => 'application/json'])
                ->post($url, ['grant_type' => 'client_credentials']);

            if (! $response->successful()) {
                Log::warning('UPS OAuth failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * @return array{quotes: array<int, array{service_code: string, title: string, cost: float}>, error: ?string}
     */
    private function parseQuotes(
        array $result,
        ShippingMethod $method,
        int $lineCount,
        float $subtotal,
        float $weight,
        string $weightCode,
    ): array {
        $error = $result['response']['errors'][0]['message'] ?? null;
        $shipments = $result['RateResponse']['RatedShipment'] ?? [];

        if (! is_array($shipments)) {
            return ['quotes' => [], 'error' => $error ?: 'No UPS rates returned.'];
        }

        if (isset($shipments['Service'])) {
            $shipments = [$shipments];
        }

        $enabled = collect($method->configValue('enabled_services', ['01', '02', '03']))
            ->map(fn ($c) => (string) $c)
            ->all();

        $serviceLabels = config('ups.services', []);
        $quotes = [];
        $weightSuffix = '';

        if ($method->configValue('display_delivery_weight', true)) {
            $unitLabel = $weightCode === 'KGS' ? 'kg' : 'lbs';
            $weightSuffix = ' ('.number_format($weight, 1).' '.$unitLabel.')';
        }

        foreach ($shipments as $shipment) {
            $serviceCode = (string) ($shipment['Service']['Code'] ?? '');

            if ($serviceCode === '' || (! empty($enabled) && ! in_array($serviceCode, $enabled, true))) {
                continue;
            }

            $cost = (float) ($shipment['TotalCharges']['MonetaryValue'] ?? 0);
            $cost = $this->adjustCost($method, $serviceCode, $cost, $lineCount, $subtotal);

            $title = $shipment['Service']['Description'] ?? ($serviceLabels[$serviceCode] ?? 'UPS '.$serviceCode);
            $title .= $weightSuffix;

            $quotes[] = [
                'service_code' => $serviceCode,
                'title' => $title,
                'cost' => round($cost, 2),
            ];
        }

        usort($quotes, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        if ($quotes === [] && $error) {
            return ['quotes' => [], 'error' => $error];
        }

        return ['quotes' => $quotes, 'error' => $quotes === [] ? ($error ?: 'No UPS services available for this address.') : null];
    }

    private function adjustCost(ShippingMethod $method, string $serviceCode, float $cost, int $lineCount, float $subtotal): float
    {
        $additionalCost = $this->additionalCharges($method);
        $additionalPercent = $this->percentageCharges($method);

        if ($serviceCode === '03') {
            if ($lineCount <= 1) {
                return $additionalCost > 0 ? $additionalCost : $cost;
            }

            return $additionalCost + ($subtotal * ($additionalPercent / 100));
        }

        return $cost + ($cost * ($additionalPercent / 100));
    }

    private function normalizeWeight(float $weight): float
    {
        $weight = max(0.1, $weight);

        return min(150.0, $weight);
    }
}
