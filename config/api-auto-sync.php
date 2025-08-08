<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Auto-Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic API documentation and Postman collection sync
    |
    */

    'enabled' => env('API_AUTO_SYNC_ENABLED', true),

    'sync_triggers' => [
        'route_changes' => env('API_SYNC_ON_ROUTE_CHANGES', true),
        'controller_changes' => env('API_SYNC_ON_CONTROLLER_CHANGES', false),
        'scheduled' => env('API_SYNC_SCHEDULED', true),
    ],

    'sync_targets' => [
        'postman' => env('API_SYNC_POSTMAN', true),
        'documentation' => env('API_SYNC_DOCUMENTATION', true),
        'openapi' => env('API_SYNC_OPENAPI', true),
    ],

    'postman' => [
        'auto_sync' => env('POSTMAN_AUTO_SYNC', true),
        'collection_name' => env('POSTMAN_COLLECTION_NAME', 'YukiMart API v1 - Auto-Generated'),
        'workspace_id' => env('POSTMAN_WORKSPACE_ID'),
        'collection_id' => env('POSTMAN_COLLECTION_ID'),
        'api_key' => env('POSTMAN_API_KEY'),
    ],

    'documentation' => [
        'auto_generate' => env('API_DOC_AUTO_GENERATE', true),
        'output_formats' => ['json', 'markdown', 'html'],
        'output_path' => storage_path('app/api-docs'),
    ],

    'discovery' => [
        'route_patterns' => [
            'api/v1/*'
        ],
        'excluded_routes' => [
            'api/v1/docs',
            'api/v1/docs/*',
            'api/v1/playground',
        ],
        'auto_examples' => env('API_AUTO_EXAMPLES', true),
        'auto_descriptions' => env('API_AUTO_DESCRIPTIONS', true),
    ],

    'monitoring' => [
        'log_changes' => env('API_LOG_CHANGES', true),
        'log_sync_results' => env('API_LOG_SYNC_RESULTS', true),
        'cache_duration' => env('API_CACHE_DURATION', 3600), // seconds
    ],

    'scheduling' => [
        'hourly_check' => env('API_HOURLY_CHECK', true),
        'daily_force_sync' => env('API_DAILY_FORCE_SYNC', true),
        'daily_sync_time' => env('API_DAILY_SYNC_TIME', '09:00'),
    ],

    'performance' => [
        'check_interval' => env('API_CHECK_INTERVAL', 300), // seconds
        'background_sync' => env('API_BACKGROUND_SYNC', true),
        'queue_sync' => env('API_QUEUE_SYNC', false),
    ],

    'notifications' => [
        'slack_webhook' => env('API_SYNC_SLACK_WEBHOOK'),
        'email_notifications' => env('API_SYNC_EMAIL_NOTIFICATIONS', false),
        'notify_on_changes' => env('API_NOTIFY_ON_CHANGES', false),
        'notify_on_errors' => env('API_NOTIFY_ON_ERRORS', true),
    ],
];
