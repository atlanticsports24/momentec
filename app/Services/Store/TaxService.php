<?php

namespace App\Services\Store;

use App\Models\Zone;

class TaxService
{
    public function __construct(
        private readonly StoreSettings $settings,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get('tax_enabled', false);
    }

    public function rateForZone(?int $zoneId): float
    {
        if (! $this->isEnabled() || $zoneId === null) {
            return 0.0;
        }

        $rate = Zone::query()->whereKey($zoneId)->value('tax_rate');

        return max(0.0, (float) $rate);
    }

    /**
     * @return array{enabled: bool, rate: float, amount: float, title: string}
     */
    public function calculate(float $subtotal, ?int $zoneId): array
    {
        $enabled = $this->isEnabled();
        $rate = $enabled ? $this->rateForZone($zoneId) : 0.0;
        $amount = $enabled && $rate > 0
            ? round($subtotal * ($rate / 100), 2)
            : 0.0;

        return [
            'enabled' => $enabled,
            'rate' => $rate,
            'amount' => $amount,
            'title' => $this->titleForRate($rate),
        ];
    }

    public function titleForRate(float $rate): string
    {
        if ($rate <= 0) {
            return 'Tax';
        }

        $formatted = rtrim(rtrim(number_format($rate, 2), '0'), '.');

        return "Tax ({$formatted}%)";
    }

    public function toArray(float $subtotal, ?int $zoneId): array
    {
        return $this->calculate($subtotal, $zoneId);
    }
}
