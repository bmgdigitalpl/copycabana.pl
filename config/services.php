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
        'base_url' => env('INPOST_API_BASE_URL', 'https://api-pl-points.easypack24.net/v1'),
        'token' => env('INPOST_API_TOKEN'),
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
