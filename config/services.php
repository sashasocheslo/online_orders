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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'uah'),
    ],

    'ai' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'gemini'),

        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL'),
            'base_url' => 'https://api.openai.com/v1',
        ],

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL'),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ],

        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL'),
            'base_url' => 'https://api.anthropic.com/v1',
            'version' => '2023-06-01',
        ],
    ],

];
