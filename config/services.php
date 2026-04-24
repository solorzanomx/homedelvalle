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

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret' => env('RECAPTCHA_SECRET_KEY'),
        'threshold' => (float) env('RECAPTCHA_THRESHOLD', 0.5),
    ],

    'webhook' => [
        'api_key' => env('WEBHOOK_API_KEY'),
    ],

    'whatsapp' => [
        'enabled' => (bool) env('WHATSAPP_ENABLED', false),
        'provider' => env('WHATSAPP_PROVIDER', 'mock'),
        'api_key' => env('WHATSAPP_API_KEY'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],

    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'google_ai' => [
        'api_key' => env('GOOGLE_AI_STUDIO_KEY'),
    ],

    'n8n' => [
        'carousel_webhook_url' => env('N8N_CAROUSEL_WEBHOOK_URL'),
    ],

    'google_drive' => [
        'credentials_path'  => env('GOOGLE_APPLICATION_CREDENTIALS', 'storage/app/google/service-account.json'),
        'folder_id'         => env('GOOGLE_DRIVE_FOLDER_ID'),
        'admin_email'       => env('GOOGLE_WORKSPACE_ADMIN_EMAIL'),
        'webhook_secret'    => env('GOOGLE_DRIVE_WEBHOOK_SECRET'),
        // IDs de los templates de Google Docs en Drive
        'template_confidencialidad' => env('GOOGLE_DOCS_TEMPLATE_CONFIDENCIALIDAD'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_KEY'),
    ],

];
