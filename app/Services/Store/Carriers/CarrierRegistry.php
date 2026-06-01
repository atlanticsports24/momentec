<?php

namespace App\Services\Store\Carriers;

use App\Contracts\Store\CarrierShippingProvider;
use App\Models\ShippingMethod;

class CarrierRegistry
{
    /** @var array<string, class-string<CarrierShippingProvider>> */
    private array $providers = [
        'ups' => UpsCarrier::class,
        'usps' => UspsCarrier::class,
    ];

    public function supports(string $code): bool
    {
        return isset($this->providers[$code]);
    }

    public function forMethod(ShippingMethod $method): ?CarrierShippingProvider
    {
        $class = $this->providers[$method->code] ?? null;

        if ($class === null) {
            return null;
        }

        return app($class);
    }

    /**
     * @return array<int, string>
     */
    public function carrierCodes(): array
    {
        return array_keys($this->providers);
    }
}
