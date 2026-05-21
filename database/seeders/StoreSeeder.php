<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\GeoZone;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\Zone;
use App\Services\Store\StoreSettings;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = $this->seedOrderStatuses();
        $countries = $this->seedCountriesAndZones();
        $this->seedGeoZones($countries);
        $currency = $this->seedCurrencies();
        $this->seedStoreSettings($statuses, $countries, $currency);
        $this->seedPaymentMethods($statuses);
        $this->seedShippingMethods();
    }

    private function seedOrderStatuses(): array
    {
        $rows = [
            ['name' => 'Missing Orders', 'code' => 'missing', 'color' => '#9ca3af', 'sort_order' => 1, 'is_core' => true],
            ['name' => 'Pending', 'code' => 'pending', 'color' => '#f59e0b', 'sort_order' => 2, 'is_core' => true],
            ['name' => 'Processing', 'code' => 'processing', 'color' => '#3b82f6', 'sort_order' => 3, 'is_core' => true],
            ['name' => 'Shipped', 'code' => 'shipped', 'color' => '#10b981', 'sort_order' => 4, 'is_core' => true],
            ['name' => 'Complete', 'code' => 'complete', 'color' => '#059669', 'sort_order' => 5, 'is_core' => true],
            ['name' => 'Canceled', 'code' => 'canceled', 'color' => '#ef4444', 'sort_order' => 6, 'is_core' => true],
            ['name' => 'Denied', 'code' => 'denied', 'color' => '#dc2626', 'sort_order' => 7, 'is_core' => false],
            ['name' => 'Failed', 'code' => 'failed', 'color' => '#b91c1c', 'sort_order' => 8, 'is_core' => false],
        ];

        $map = [];
        foreach ($rows as $row) {
            $status = OrderStatus::query()->updateOrCreate(
                ['code' => $row['code']],
                $row
            );
            $map[$row['code']] = $status;
        }

        return $map;
    }

    private function seedCountriesAndZones(): array
    {
        $definitions = [
            'US' => [
                'name' => 'United States',
                'iso_code_2' => 'US',
                'iso_code_3' => 'USA',
                'postcode_required' => true,
                'zones' => $this->usStates(),
            ],
            'CA' => [
                'name' => 'Canada',
                'iso_code_2' => 'CA',
                'iso_code_3' => 'CAN',
                'postcode_required' => true,
                'zones' => $this->canadianProvinces(),
            ],
            'GB' => [
                'name' => 'United Kingdom',
                'iso_code_2' => 'GB',
                'iso_code_3' => 'GBR',
                'postcode_required' => true,
                'zones' => [
                    ['name' => 'England', 'code' => 'ENG'],
                    ['name' => 'Scotland', 'code' => 'SCT'],
                    ['name' => 'Wales', 'code' => 'WLS'],
                    ['name' => 'Northern Ireland', 'code' => 'NIR'],
                ],
            ],
        ];

        $result = [];
        foreach ($definitions as $iso => $def) {
            $country = Country::query()->updateOrCreate(
                ['iso_code_2' => $iso],
                [
                    'name' => $def['name'],
                    'iso_code_3' => $def['iso_code_3'],
                    'postcode_required' => $def['postcode_required'],
                    'is_enabled' => true,
                ]
            );

            $zones = [];
            foreach ($def['zones'] as $zoneRow) {
                $zones[$zoneRow['code']] = Zone::query()->updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'code' => $zoneRow['code'],
                    ],
                    [
                        'name' => $zoneRow['name'],
                        'is_enabled' => true,
                    ]
                );
            }

            $result[$iso] = ['country' => $country, 'zones' => $zones];
        }

        return $result;
    }

    private function seedGeoZones(array $countries): void
    {
        $northAmerica = GeoZone::query()->updateOrCreate(
            ['name' => 'North America'],
            ['description' => 'United States and Canada']
        );

        foreach (['US', 'CA'] as $iso) {
            $country = $countries[$iso]['country'];
            foreach ($countries[$iso]['zones'] as $zone) {
                $northAmerica->zones()->syncWithoutDetaching([
                    $zone->id => ['country_id' => $country->id],
                ]);
            }
        }

        GeoZone::query()->updateOrCreate(
            ['name' => 'Rest of World'],
            ['description' => 'All other countries — attach zones as needed']
        );
    }

    private function seedCurrencies(): Currency
    {
        Currency::query()->where('is_default', true)->update(['is_default' => false]);

        return Currency::query()->updateOrCreate(
            ['code' => 'USD'],
            [
                'title' => 'US Dollar',
                'symbol_left' => '$',
                'symbol_right' => '',
                'decimal_places' => 2,
                'value' => 1,
                'is_enabled' => true,
                'is_default' => true,
            ]
        );
    }

    private function seedStoreSettings(array $statuses, array $countries, Currency $currency): void
    {
        $settings = app(StoreSettings::class);

        $settings->setMany([
            'store_name' => 'Momentec',
            'store_email' => 'store@momentec.local',
            'default_country_id' => $countries['US']['country']->id,
            'default_zone_id' => $countries['US']['zones']['CA']->id ?? null,
            'default_currency_id' => $currency->id,
            'default_order_status_id' => $statuses['missing']->id,
        ]);
    }

    private function seedPaymentMethods(array $statuses): void
    {
        $methods = [
            [
                'code' => 'cod',
                'name' => 'Cash On Delivery',
                'is_enabled' => true,
                'sort_order' => 1,
                'success_order_status_id' => $statuses['processing']->id,
                'failed_order_status_id' => $statuses['failed']->id,
                'config' => ['instructions' => 'Pay when your order arrives.'],
            ],
            [
                'code' => 'stripe',
                'name' => 'Stripe',
                'is_enabled' => false,
                'sort_order' => 2,
                'success_order_status_id' => $statuses['processing']->id,
                'failed_order_status_id' => $statuses['failed']->id,
                'config' => [
                    'publishable_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => '',
                ],
            ],
            [
                'code' => 'authorize_net',
                'name' => 'Authorize.Net',
                'is_enabled' => false,
                'sort_order' => 3,
                'success_order_status_id' => $statuses['processing']->id,
                'failed_order_status_id' => $statuses['failed']->id,
                'config' => [
                    'api_login_id' => '',
                    'transaction_key' => '',
                    'sandbox' => true,
                ],
            ],
            [
                'code' => 'paypal',
                'name' => 'PayPal',
                'is_enabled' => false,
                'sort_order' => 4,
                'success_order_status_id' => $statuses['processing']->id,
                'failed_order_status_id' => $statuses['failed']->id,
                'config' => ['client_id' => '', 'secret' => '', 'sandbox' => true],
            ],
        ];

        foreach ($methods as $row) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }

    private function seedShippingMethods(): void
    {
        $northAmerica = GeoZone::query()->where('name', 'North America')->first();

        $methods = [
            [
                'code' => 'flat',
                'name' => 'Flat Rate',
                'is_enabled' => true,
                'sort_order' => 1,
                'geo_zone_id' => $northAmerica?->id,
                'cost' => 9.99,
                'free_shipping_min' => null,
            ],
            [
                'code' => 'free',
                'name' => 'Free Shipping',
                'is_enabled' => true,
                'sort_order' => 2,
                'geo_zone_id' => null,
                'cost' => 0,
                'free_shipping_min' => 100,
            ],
            [
                'code' => 'ups',
                'name' => 'UPS',
                'is_enabled' => false,
                'sort_order' => 3,
                'geo_zone_id' => $northAmerica?->id,
                'cost' => 0,
                'config' => ['username' => '', 'password' => '', 'access_key' => ''],
            ],
            [
                'code' => 'usps',
                'name' => 'USPS',
                'is_enabled' => false,
                'sort_order' => 4,
                'geo_zone_id' => $northAmerica?->id,
                'cost' => 0,
                'config' => ['user_id' => ''],
            ],
        ];

        foreach ($methods as $row) {
            ShippingMethod::query()->updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }

    private function usStates(): array
    {
        return [
            ['name' => 'Alabama', 'code' => 'AL'],
            ['name' => 'Alaska', 'code' => 'AK'],
            ['name' => 'Arizona', 'code' => 'AZ'],
            ['name' => 'Arkansas', 'code' => 'AR'],
            ['name' => 'California', 'code' => 'CA'],
            ['name' => 'Colorado', 'code' => 'CO'],
            ['name' => 'Connecticut', 'code' => 'CT'],
            ['name' => 'Delaware', 'code' => 'DE'],
            ['name' => 'District of Columbia', 'code' => 'DC'],
            ['name' => 'Florida', 'code' => 'FL'],
            ['name' => 'Georgia', 'code' => 'GA'],
            ['name' => 'Hawaii', 'code' => 'HI'],
            ['name' => 'Idaho', 'code' => 'ID'],
            ['name' => 'Illinois', 'code' => 'IL'],
            ['name' => 'Indiana', 'code' => 'IN'],
            ['name' => 'Iowa', 'code' => 'IA'],
            ['name' => 'Kansas', 'code' => 'KS'],
            ['name' => 'Kentucky', 'code' => 'KY'],
            ['name' => 'Louisiana', 'code' => 'LA'],
            ['name' => 'Maine', 'code' => 'ME'],
            ['name' => 'Maryland', 'code' => 'MD'],
            ['name' => 'Massachusetts', 'code' => 'MA'],
            ['name' => 'Michigan', 'code' => 'MI'],
            ['name' => 'Minnesota', 'code' => 'MN'],
            ['name' => 'Mississippi', 'code' => 'MS'],
            ['name' => 'Missouri', 'code' => 'MO'],
            ['name' => 'Montana', 'code' => 'MT'],
            ['name' => 'Nebraska', 'code' => 'NE'],
            ['name' => 'Nevada', 'code' => 'NV'],
            ['name' => 'New Hampshire', 'code' => 'NH'],
            ['name' => 'New Jersey', 'code' => 'NJ'],
            ['name' => 'New Mexico', 'code' => 'NM'],
            ['name' => 'New York', 'code' => 'NY'],
            ['name' => 'North Carolina', 'code' => 'NC'],
            ['name' => 'North Dakota', 'code' => 'ND'],
            ['name' => 'Ohio', 'code' => 'OH'],
            ['name' => 'Oklahoma', 'code' => 'OK'],
            ['name' => 'Oregon', 'code' => 'OR'],
            ['name' => 'Pennsylvania', 'code' => 'PA'],
            ['name' => 'Rhode Island', 'code' => 'RI'],
            ['name' => 'South Carolina', 'code' => 'SC'],
            ['name' => 'South Dakota', 'code' => 'SD'],
            ['name' => 'Tennessee', 'code' => 'TN'],
            ['name' => 'Texas', 'code' => 'TX'],
            ['name' => 'Utah', 'code' => 'UT'],
            ['name' => 'Vermont', 'code' => 'VT'],
            ['name' => 'Virginia', 'code' => 'VA'],
            ['name' => 'Washington', 'code' => 'WA'],
            ['name' => 'West Virginia', 'code' => 'WV'],
            ['name' => 'Wisconsin', 'code' => 'WI'],
            ['name' => 'Wyoming', 'code' => 'WY'],
        ];
    }

    private function canadianProvinces(): array
    {
        return [
            ['name' => 'Alberta', 'code' => 'AB'],
            ['name' => 'British Columbia', 'code' => 'BC'],
            ['name' => 'Manitoba', 'code' => 'MB'],
            ['name' => 'New Brunswick', 'code' => 'NB'],
            ['name' => 'Newfoundland and Labrador', 'code' => 'NL'],
            ['name' => 'Nova Scotia', 'code' => 'NS'],
            ['name' => 'Ontario', 'code' => 'ON'],
            ['name' => 'Prince Edward Island', 'code' => 'PE'],
            ['name' => 'Quebec', 'code' => 'QC'],
            ['name' => 'Saskatchewan', 'code' => 'SK'],
            ['name' => 'Northwest Territories', 'code' => 'NT'],
            ['name' => 'Nunavut', 'code' => 'NU'],
            ['name' => 'Yukon', 'code' => 'YT'],
        ];
    }
}
