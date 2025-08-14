<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google_cloud' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'key_file' => env('GOOGLE_CLOUD_KEY_FILE'),
        'storage_bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'app_key' => env('PUSHER_APP_KEY'),
        'app_secret' => env('PUSHER_APP_SECRET'),
        'app_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
        'useTLS' => true,
    ],

    'algolia' => [
        'app_id' => env('ALGOLIA_APP_ID'),
        'secret' => env('ALGOLIA_SECRET'),
        'search_key' => env('ALGOLIA_SEARCH'),
    ],

    'fapshi' => [
        'base_url' => env('FAPSHI_BASE_URL', 'https://live.fapshi.com'),
        'api_user' => env('FAPSHI_API_USER'),
        'api_key' => env('FAPSHI_API_KEY'),
        'currency' => env('FAPSHI_CURRENCY', 'XAF'),
        'min_amount' => env('FAPSHI_MIN_AMOUNT', 100),
        'xaf_per_eur' => env('XAF_PER_EUR', 650),
        'webhook_secret' => env('FAPSHI_WEBHOOK_SECRET'), // optional if provided
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', env('APP_URL') . '/api/v1/auth/social/google/callback'),
    ],

    // Mobile Money providers (placeholders for environment configuration)
    'mobile_money' => [
        'mtn_momo' => [
            'base_url' => env('MTN_MOMO_BASE_URL'),
            'api_key' => env('MTN_MOMO_API_KEY'),
            'api_user' => env('MTN_MOMO_API_USER'),
            'api_secret' => env('MTN_MOMO_API_SECRET'),
            'currency' => env('MTN_MOMO_CURRENCY', 'XAF'),
            'callback_url' => env('MTN_MOMO_CALLBACK_URL', env('APP_URL') . '/api/webhooks/mobile-money/mtn_momo'),
        ],
        'orange_money' => [
            'base_url' => env('ORANGE_MONEY_BASE_URL'),
            'api_key' => env('ORANGE_MONEY_API_KEY'),
            'merchant_code' => env('ORANGE_MONEY_MERCHANT_CODE'),
            'currency' => env('ORANGE_MONEY_CURRENCY', 'XOF'),
            'callback_url' => env('ORANGE_MONEY_CALLBACK_URL', env('APP_URL') . '/api/webhooks/mobile-money/orange_money'),
        ],
    ],

];
