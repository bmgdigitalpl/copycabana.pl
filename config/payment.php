<?php

return [
    'provider' => env('PAYMENT_PROVIDER', 'payu'),
    'methods' => ['blik'],
    'currency' => env('BUSINESS_CURRENCY', 'PLN'),
];
