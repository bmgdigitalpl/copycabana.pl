<?php

return [
    'currency' => env('BUSINESS_CURRENCY', 'PLN'),
    'tax_rate' => (float) env('BUSINESS_TAX_RATE', 23),
    'quote_email' => env('BUSINESS_QUOTE_EMAIL', 'biuro@copycabana.pl'),

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
