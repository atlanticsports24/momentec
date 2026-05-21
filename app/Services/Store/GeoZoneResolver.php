<?php

namespace App\Services\Store;

use App\Models\GeoZone;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GeoZoneResolver
{
    public function geoZoneIdsForAddress(?int $countryId, ?int $zoneId): array
    {
        if ($countryId === null) {
            return [];
        }

        $query = GeoZone::query()
            ->whereHas('zones', function (Builder $q) use ($countryId, $zoneId) {
                $q->where('geo_zone_zone.country_id', $countryId);
                if ($zoneId !== null) {
                    $q->where(function (Builder $inner) use ($zoneId) {
                        $inner->where('geo_zone_zone.zone_id', $zoneId)
                            ->orWhereNull('geo_zone_zone.zone_id');
                    });
                }
            });

        return $query->pluck('id')->all();
    }

    public function availablePaymentMethods(?int $countryId, ?int $zoneId, float $orderTotal): Collection
    {
        $geoIds = $this->geoZoneIdsForAddress($countryId, $zoneId);

        return PaymentMethod::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(function (PaymentMethod $method) use ($geoIds, $orderTotal) {
                if ($method->geo_zone_id !== null && ! in_array($method->geo_zone_id, $geoIds, true)) {
                    return false;
                }

                if ($method->min_total !== null && $orderTotal < (float) $method->min_total) {
                    return false;
                }

                if ($method->max_total !== null && $orderTotal > (float) $method->max_total) {
                    return false;
                }

                return true;
            });
    }

    public function availableShippingMethods(?int $countryId, ?int $zoneId, float $orderTotal): Collection
    {
        $geoIds = $this->geoZoneIdsForAddress($countryId, $zoneId);

        return ShippingMethod::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(function (ShippingMethod $method) use ($geoIds, $orderTotal) {
                if ($method->geo_zone_id !== null && ! in_array($method->geo_zone_id, $geoIds, true)) {
                    return false;
                }

                if ($method->min_total !== null && $orderTotal < (float) $method->min_total) {
                    return false;
                }

                if ($method->max_total !== null && $orderTotal > (float) $method->max_total) {
                    return false;
                }

                return true;
            });
    }
}
