<?php

namespace App\Services\Store\Carriers;

use App\Contracts\Store\CarrierShippingProvider;
use App\Models\Country;
use App\Models\ShippingMethod;
use App\Models\Zone;
use App\Services\Store\UspsShippingService;

class UspsCarrier implements CarrierShippingProvider
{
    public function __construct(
        private readonly UspsShippingService $usps,
    ) {}

    public function code(): string
    {
        return 'usps';
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
        return $this->usps->getQuotes(
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
