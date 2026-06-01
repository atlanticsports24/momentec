<?php

namespace App\Services\Store;

use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;
use App\Services\Store\Carriers\CarrierRegistry;
use Illuminate\Support\Collection;

class ShippingQuoteService
{
    public function __construct(
        private readonly GeoZoneResolver $geoZones,
        private readonly CartService $cart,
        private readonly CarrierRegistry $carriers,
    ) {}

    /**
     * @return Collection<int, array{
     *   key: string,
     *   method_id: int,
     *   service_code: ?string,
     *   code: string,
     *   name: string,
     *   cost: float,
     *   error: ?string
     * }>
     */
    public function quotesForAddress(
        int $countryId,
        ?int $zoneId,
        string $city,
        string $postcode,
        ?float $subtotal = null,
    ): Collection {
        $subtotal ??= $this->cart->subtotal();
        $methods = $this->geoZones->availableShippingMethods($countryId, $zoneId, $subtotal);

        $country = Country::query()->find($countryId);
        $zone = $zoneId ? Zone::query()->find($zoneId) : null;

        $options = collect();

        foreach ($methods as $method) {
            $provider = $this->carriers->forMethod($method);

            if ($provider && $country && $zone && $city !== '' && $postcode !== '') {
                $result = $provider->getQuotes(
                    $method,
                    $country,
                    $zone,
                    $city,
                    $postcode,
                    $this->cart->totalWeight(),
                    $this->cart->lineCount(),
                    $subtotal,
                );

                foreach ($result['quotes'] as $quote) {
                    $options->push([
                        'key' => $method->id.'|'.$quote['service_code'],
                        'method_id' => $method->id,
                        'service_code' => $quote['service_code'],
                        'code' => $method->code,
                        'name' => $quote['title'],
                        'cost' => $quote['cost'],
                        'error' => null,
                    ]);
                }

                if ($options->where('code', $method->code)->isEmpty() && ($result['error'] ?? null)) {
                    $options->push([
                        'key' => (string) $method->id.'|error',
                        'method_id' => $method->id,
                        'service_code' => null,
                        'code' => $method->code,
                        'name' => strtoupper($method->code),
                        'cost' => 0,
                        'error' => $result['error'],
                    ]);
                }

                continue;
            }

            if ($provider && ($city === '' || $postcode === '')) {
                continue;
            }

            $options->push([
                'key' => (string) $method->id,
                'method_id' => $method->id,
                'service_code' => null,
                'code' => $method->code,
                'name' => $method->name,
                'cost' => $method->calculateCost($subtotal),
                'error' => null,
            ]);
        }

        return $options->sortBy('cost')->values();
    }

    /**
     * @return array{method: ShippingMethod, name: string, cost: float, service_code: ?string}|null
     */
    public function resolveSelection(
        int $countryId,
        ?int $zoneId,
        string $city,
        string $postcode,
        int $shippingMethodId,
        ?string $serviceCode = null,
    ): ?array {
        $quotes = $this->quotesForAddress($countryId, $zoneId, $city, $postcode);

        $match = $quotes->first(function (array $q) use ($shippingMethodId, $serviceCode) {
            if ($q['method_id'] !== $shippingMethodId) {
                return false;
            }

            if ($this->carriers->supports($q['code'])) {
                return $serviceCode !== null && $q['service_code'] === $serviceCode;
            }

            return $serviceCode === null;
        });

        if (! $match || ($match['error'] ?? null)) {
            return null;
        }

        $method = ShippingMethod::query()->find($match['method_id']);

        if (! $method) {
            return null;
        }

        return [
            'method' => $method,
            'name' => $match['name'],
            'cost' => $match['cost'],
            'service_code' => $match['service_code'],
        ];
    }
}
