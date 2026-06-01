<?php

namespace App\Services\Store\Carriers;

use App\Contracts\Store\CarrierShippingProvider;
use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;
use App\Services\Store\UpsShippingService;

class UpsCarrier implements CarrierShippingProvider
{
    public function __construct(
        private readonly UpsShippingService $ups,
    ) {}

    public function code(): string
    {
        return 'ups';
    }

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
        return $this->ups->getQuotes(
            $method,
            $country,
            $zone,
            $city,
            $postcode,
            $cartWeight,
            $lineCount,
            $subtotal,
        );
    }
}
