<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for platform vs tenant detection and routing
    |
    */

    'platform_host' => env('TENANCY_PLATFORM_HOST', 'platform.yukimart.local'),
    'app_domain' => env('TENANCY_APP_DOMAIN', 'yukimart.local'),

    /*
    |--------------------------------------------------------------------------
    | Platform Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to platform functionality
    |
    */

    'platform' => [
        'session_name' => 'yukimart_platform_session',
        'cookie_name' => 'yukimart_platform',
        'rate_limit' => [
            'requests' => 1000,
            'per_minute' => 60,
        ],
        'middleware' => [
            'auth' => 'auth:platform',
            'guest' => 'guest:platform',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to tenant functionality
    |
    */

    'tenant' => [
        'session_name' => 'yukimart_tenant_session',
        'cookie_name' => 'yukimart_tenant',
        'rate_limit' => [
            'requests' => 500,
            'per_minute' => 60,
        ],
        'middleware' => [
            'auth' => 'auth:admin',
            'guest' => 'guest:admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Detection Rules
    |--------------------------------------------------------------------------
    |
    | Rules for detecting platform vs tenant requests
    |
    */

    'detection' => [
        'platform_patterns' => [
            'platform.yukimart.local',
            'platform.techcera.com',
        ],
        'tenant_patterns' => [
            '*.yukimart.local',
            '*.techcera.com',
        ],
    ],
];
