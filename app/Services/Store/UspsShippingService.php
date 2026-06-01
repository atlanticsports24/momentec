<?php

namespace App\Services\Store;

use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class UspsShippingService
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
        $userId = (string) $method->configValue('user_id', '');

        if ($userId === '') {
            return ['quotes' => [], 'error' => 'USPS is not configured (User ID required).'];
        }

        if ($country->iso_code_2 !== 'US') {
            return ['quotes' => [], 'error' => 'USPS rates are only available for US destinations.'];
        }

        $originZip = preg_replace('/\D/', '', (string) $method->configValue('origin_postcode', ''));
        $destZip = preg_replace('/\D/', '', $postcode);

        if (strlen($originZip) < 5 || strlen($destZip) < 5) {
            return ['quotes' => [], 'error' => 'Valid origin and destination postcodes are required for USPS.'];
        }

        $pounds = $this->normalizeWeight($cartWeight);
        $poundsInt = (int) floor($pounds);
        $ounces = (int) round(($pounds - $poundsInt) * 16);

        $xml = $this->buildRateXml(
            $userId,
            substr($originZip, 0, 5),
            substr($destZip, 0, 5),
            $poundsInt,
            $ounces,
            (string) $method->configValue('container', 'VARIABLE'),
            (int) ceil((float) $method->configValue('length', 5)),
            (int) ceil((float) $method->configValue('width', 5)),
            (int) ceil((float) $method->configValue('height', 5)),
        );

        $testMode = (bool) $method->configValue('test_mode', true);
        $url = config('usps.rate_url.'.($testMode ? 'test' : 'production'));

        if ($method->configValue('debug_mode', false)) {
            Log::debug('USPS rate request', ['url' => $url, 'xml' => $xml]);
        }

        try {
            $response = Http::timeout(30)->get($url, [
                'API' => 'RateV4',
                'XML' => $xml,
            ]);
        } catch (\Throwable $e) {
            Log::error('USPS rate request failed', ['message' => $e->getMessage()]);

            return ['quotes' => [], 'error' => 'Unable to retrieve USPS rates.'];
        }

        if ($method->configValue('debug_mode', false)) {
            Log::debug('USPS rate response', ['body' => $response->body()]);
        }

        if (! $response->successful()) {
            return ['quotes' => [], 'error' => 'USPS rate request failed.'];
        }

        return $this->parseXmlResponse($response->body(), $method, $pounds, $lineCount, $subtotal);
    }

    private function buildRateXml(
        string $userId,
        string $originZip,
        string $destZip,
        int $pounds,
        int $ounces,
        string $container,
        int $length,
        int $width,
        int $height,
    ): string {
        return '<?xml version="1.0"?>'
            .'<RateV4Request USERID="'.htmlspecialchars($userId, ENT_XML1).'">'
            .'<Revision>2</Revision>'
            .'<Package ID="0">'
            .'<Service>ALL</Service>'
            .'<ZipOrigination>'.htmlspecialchars($originZip, ENT_XML1).'</ZipOrigination>'
            .'<ZipDestination>'.htmlspecialchars($destZip, ENT_XML1).'</ZipDestination>'
            .'<Pounds>'.max(0, $pounds).'</Pounds>'
            .'<Ounces>'.max(0, min(15, $ounces)).'</Ounces>'
            .'<Container>'.htmlspecialchars($container, ENT_XML1).'</Container>'
            .'<Width>'.max(0, $width).'</Width>'
            .'<Length>'.max(0, $length).'</Length>'
            .'<Height>'.max(0, $height).'</Height>'
            .'<Girth></Girth>'
            .'<Machinable>TRUE</Machinable>'
            .'<ShipDate></ShipDate>'
            .'</Package>'
            .'</RateV4Request>';
    }

    /**
     * @return array{quotes: array<int, array{service_code: string, title: string, cost: float}>, error: ?string}
     */
    private function parseXmlResponse(string $body, ShippingMethod $method, float $weight, int $lineCount, float $subtotal): array
    {
        try {
            $xml = new SimpleXMLElement($body);
        } catch (\Throwable $e) {
            return ['quotes' => [], 'error' => 'Invalid USPS response.'];
        }

        if (isset($xml->Number) && (int) $xml->Number > 0) {
            $description = (string) ($xml->Description ?? 'USPS error');

            return ['quotes' => [], 'error' => $description];
        }

        $enabled = collect($method->configValue('enabled_services', array_keys(config('usps.services', []))))
            ->map(fn ($s) => strtoupper((string) $s))
            ->all();

        $labels = collect(config('usps.services', []))->mapWithKeys(
            fn ($label, $code) => [strtoupper((string) $code) => $label]
        );

        $quotes = [];
        $weightSuffix = '';

        if ($method->configValue('display_delivery_weight', true)) {
            $weightSuffix = ' ('.number_format($weight, 1).' lbs)';
        }

        foreach ($xml->Package->Postage ?? [] as $postage) {
            $mailService = strtoupper(trim((string) $postage->MailService));
            $serviceCode = $this->serviceCodeFromMailService($mailService);

            if (! empty($enabled) && ! $this->serviceIsEnabled($mailService, $serviceCode, $enabled)) {
                continue;
            }

            $cost = (float) ($postage->Rate ?? 0);
            $cost = $this->adjustCost($method, $cost, $subtotal);

            $title = $labels->get($serviceCode) ?? (string) $postage->MailService;
            $title .= $weightSuffix;

            $quotes[] = [
                'service_code' => $serviceCode,
                'title' => $title,
                'cost' => round($cost, 2),
            ];
        }

        usort($quotes, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        if ($quotes === []) {
            return ['quotes' => [], 'error' => 'No USPS services available for this address.'];
        }

        return ['quotes' => $quotes, 'error' => null];
    }

    private function serviceCodeFromMailService(string $mailService): string
    {
        return str_replace(' ', '_', $mailService);
    }

    private function serviceIsEnabled(string $mailService, string $serviceCode, array $enabled): bool
    {
        foreach ($enabled as $needle) {
            if ($mailService === $needle || str_contains($mailService, $needle) || $serviceCode === str_replace(' ', '_', $needle)) {
                return true;
            }
        }

        return false;
    }

    private function adjustCost(ShippingMethod $method, float $cost, float $subtotal): float
    {
        $additional = (float) $method->configValue('additional_charges', 0);
        $percent = (float) $method->configValue('percentage_charges', 0);

        return $cost + $additional + ($subtotal * ($percent / 100));
    }

    private function normalizeWeight(float $weight): float
    {
        return max(0.1, min(70.0, $weight));
    }
}
