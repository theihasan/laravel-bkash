<?php

return [
    /*
    |--------------------------------------------------------------------------
    | bKash Credentials
    |--------------------------------------------------------------------------
    */
    'sandbox' => env('BKASH_SANDBOX', true),

    'credentials' => [
        'app_key' => env('BKASH_APP_KEY', ''),
        'app_secret' => env('BKASH_APP_SECRET', ''),
        'username' => env('BKASH_USERNAME', ''),
        'password' => env('BKASH_PASSWORD', ''),
    ],

    'sandbox_base_url' => env('SANDBOX_BASE_URL', 'https://tokenized.sandbox.bka.sh'),
    'live_base_url' => env('LIVE_BASE_URL', 'https://tokenized.pay.bka.sh'),

    'version' => 'v1.2.0-beta',

    'cache' => [
        'token_lifetime' => 3600,
    ],

    'default_currency' => 'BDT',
    'default_intent' => 'sale',
];
