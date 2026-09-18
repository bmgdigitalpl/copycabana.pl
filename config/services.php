<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'inpost' => [
        // ShipX generates the actual shipping labels for orders sent to a paczkomat.
        // "mode" decides where (or whether) label requests actually go:
        //   mock    – default outside production. No InPost API call is made at all;
        //             a fake tracking number/label is generated so the full order flow
        //             can be tested locally without ever touching InPost's systems.
        //   sandbox – calls InPost's real sandbox environment with sandbox credentials
        //             from https://sandbox-manager.paczkomaty.pl (My Account > API).
        //   live    – calls the real, production ShipX API and creates real, payable
        //             shipments. Only set this in production.
        'shipx' => [
            'mode' => env('INPOST_SHIPX_MODE', 'mock'),
            'organization_id' => env('INPOST_SHIPX_ORGANIZATION_ID'),
            'token' => env('INPOST_SHIPX_TOKEN'),
            // Leave empty to use InPost's default sandbox/production URL for the mode above.
            'base_url' => env('INPOST_SHIPX_BASE_URL'),
            'sender' => [
                'name' => env('INPOST_SHIPX_SENDER_NAME', 'CopyCabana'),
                'email' => env('INPOST_SHIPX_SENDER_EMAIL', 'biuro@copycabana.pl'),
                'phone' => env('INPOST_SHIPX_SENDER_PHONE'),
                'address' => env('INPOST_SHIPX_SENDER_ADDRESS', 'ul. Bankowa 11'),
                'city' => env('INPOST_SHIPX_SENDER_CITY', 'Katowice'),
                'post_code' => env('INPOST_SHIPX_SENDER_POST_CODE', '40-007'),
                'country_code' => env('INPOST_SHIPX_SENDER_COUNTRY_CODE', 'PL'),
            ],
        ],
    ],

    'payu' => [
        'base_url' => env('PAYU_BASE_URL', 'https://secure.snd.payu.com'),
        'pos_id' => env('PAYU_POS_ID'),
        'client_id' => env('PAYU_CLIENT_ID'),
        'client_secret' => env('PAYU_CLIENT_SECRET'),
        'second_key' => env('PAYU_SECOND_KEY'),
    ],

    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
        'name' => env('ADMIN_NAME', 'CopyCabana Admin'),
    ],

];
