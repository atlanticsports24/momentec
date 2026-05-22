<?php

return [

    'contact_email' => env('SITE_CONTACT_EMAIL', 'info@momentec.com'),

    'contact_phone' => env('SITE_CONTACT_PHONE', '+1-800-000-0000'),

    'address' => env('SITE_ADDRESS', '123 Main St, City, State'),

    'min_free_shipping' => (int) env('SITE_MIN_FREE_SHIPPING', 150),

];
