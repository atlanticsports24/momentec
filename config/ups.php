<?php

return [
    'oauth_url' => [
        'sandbox' => 'https://wwwcie.ups.com/security/v1/oauth/token',
        'production' => 'https://onlinetools.ups.com/security/v1/oauth/token',
    ],
    'rating_url' => [
        'sandbox' => 'https://wwwcie.ups.com/api/rating/v2409/shop',
        'production' => 'https://onlinetools.ups.com/api/rating/v2409/shop',
    ],
    'services' => [
        '01' => 'UPS Next Day Air',
        '02' => 'UPS Second Day Air',
        '03' => 'UPS Ground',
        '07' => 'UPS Worldwide Express',
        '08' => 'UPS Worldwide Expedited',
        '11' => 'UPS Standard',
        '12' => 'UPS 3 Day Select',
        '13' => 'UPS Next Day Air Saver',
        '14' => 'UPS Next Day Air Early A.M.',
        '54' => 'UPS Worldwide Express Plus',
        '59' => 'UPS 2nd Day Air A.M.',
        '65' => 'UPS Worldwide Saver',
    ],
    'pickup_methods' => [
        '01' => 'Daily Pickup',
        '03' => 'Customer Counter',
        '06' => 'One Time Pickup',
        '07' => 'On Call Air',
        '11' => 'Suggested Retail Rates (UPS Store)',
        '19' => 'Letter Center',
        '20' => 'Air Service Center',
    ],
    'packaging_types' => [
        '01' => 'UPS Letter',
        '02' => 'Package (Customer Supplied)',
        '03' => 'Tube',
        '04' => 'PAK',
        '21' => 'UPS Express Box',
        '24' => 'UPS 25KG Box',
        '25' => 'UPS 10KG Box',
        '30' => 'Pallet',
        '2a' => 'Small Express Box',
        '2b' => 'Medium Express Box',
        '2c' => 'Large Express Box',
    ],
    'customer_classifications' => [
        '00' => 'Rates Associated with Shipper Number',
        '01' => 'Daily Rates',
        '04' => 'Retail Rates',
        '53' => 'Standard List Rates',
    ],
    'origin_codes' => [
        'US' => 'US Origin',
        'PR' => 'Puerto Rico Origin',
        'CA' => 'Canada Origin',
        'EU' => 'European Union Origin',
        'MX' => 'Mexico Origin',
        'other' => 'Other Origin',
    ],
    'quote_types' => [
        'residential' => 'Residential',
        'commercial' => 'Commercial',
    ],
    'weight_classes' => [
        'LBS' => 'Pound',
        'KGS' => 'Kilogram',
    ],
    'length_classes' => [
        'IN' => 'Inch',
        'CM' => 'Centimeter',
    ],
];
