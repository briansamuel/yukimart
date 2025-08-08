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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'fcm' => [
        // Service Account (Recommended - replaces deprecated server key)
        'service_account_path' => env('FCM_SERVICE_ACCOUNT_PATH', storage_path('app/firebase/service-account.json')),

        // Firebase Project Configuration
        'project_id' => env('FCM_PROJECT_ID', 'saas-techcura'),
        'sender_id' => env('FCM_SENDER_ID', '185186239234'),
        'api_key' => env('FCM_API_KEY', ''),
        'auth_domain' => env('FCM_AUTH_DOMAIN', 'saas-techcura.firebaseapp.com'),
        'storage_bucket' => env('FCM_STORAGE_BUCKET', 'saas-techcura.firebasestorage.app'),
        'app_id' => env('FCM_APP_ID', '1:185186239234:web:9717b33e89ce7c71fd381b'),
        'measurement_id' => env('FCM_MEASUREMENT_ID', 'G-PWKCFCL5ZQ'),

        // Web Push Configuration
        'vapid_key' => env('FCM_VAPID_KEY'), // For web push notifications

        // Legacy Configuration (Deprecated)
        'server_key' => env('FCM_SERVER_KEY'), // Deprecated - use service account instead
    ],

];
