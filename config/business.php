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

    'pdf' => [
        'page_prices' => [
            'bw' => (float) env('PDF_PAGE_PRICE_BW', 0.20),
            'color' => (float) env('PDF_PAGE_PRICE_COLOR', 0.50),
        ],
        'finishes' => [
            'none' => ['label' => 'Bez wykończenia', 'price' => 0],
            'staples' => ['label' => 'Spinanie zeszytowe', 'price' => 4],
            'folder' => ['label' => 'Teczka', 'price' => 8],
            'channel' => ['label' => 'Oprawa kanałowa', 'price' => 18],
        ],
        'max_copies' => 50,
    ],

    'thesis' => [
        'page_prices' => [
            'bw' => (float) env('THESIS_PAGE_PRICE_BW', 0.20),
            'color' => (float) env('THESIS_PAGE_PRICE_COLOR', 0.50),
        ],
        'bindings' => [
            'soft' => ['label' => 'Oprawa miękka', 'price' => 0],
            'channel' => ['label' => 'Oprawa kanałowa', 'price' => 25],
            'hard' => ['label' => 'Oprawa twarda', 'price' => 50],
        ],
        'covers' => [
            'none' => ['label' => 'Bez napisu', 'price' => 0],
            'standard' => ['label' => 'Standardowy napis', 'price' => 15],
            'custom' => ['label' => 'Własny napis', 'price' => 10],
        ],
        'max_copies' => 10,
        'max_pages' => 500,
        'upload_retention_hours' => 24,
    ],
];
