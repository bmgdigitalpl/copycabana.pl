<?php

return [
    'currency' => env('BUSINESS_CURRENCY', 'PLN'),
    'tax_rate' => (float) env('BUSINESS_TAX_RATE', 23),
    'quote_email' => env('BUSINESS_QUOTE_EMAIL', 'biuro@copycabana.pl'),

    'company' => [
        'name' => 'CopyCabana',
        'legal_name' => 'CopyCabana',
        'street' => 'ul. Bankowa 11',
        'postal_code' => '40-007',
        'city' => 'Katowice',
        'region' => 'śląskie',
        'country' => 'PL',
        'email' => 'biuro@copycabana.pl',
        'phones' => ['+48502293849', '+48504939094'],
        'opening_hours' => [
            ['days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '08:00', 'closes' => '16:00'],
            ['days' => ['Saturday'], 'opens' => '09:00', 'closes' => '15:00'],
        ],
    ],

    'shipping' => [
        'pickup' => (float) env('SHIPPING_PICKUP', 0),
        'parcel' => (float) env('SHIPPING_PARCEL', 12),
        'courier' => (float) env('SHIPPING_COURIER', 18),
    ],

    'thesis' => [
        'max_pages' => 500,
        'upload_retention_hours' => 24,
    ],
];
