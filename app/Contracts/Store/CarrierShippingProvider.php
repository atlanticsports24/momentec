<?php

namespace App\Contracts\Store;

use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;

interface CarrierShippingProvider
{
    public function code(): string;

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
    ): array;
}
