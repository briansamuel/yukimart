<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Postman API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Postman API integration to sync collections
    |
    */

    'api_key' => env('POSTMAN_API_KEY'),
    
    'workspace_id' => env('POSTMAN_WORKSPACE_ID'),
    
    'collection_id' => env('POSTMAN_COLLECTION_ID'),
    
    'auto_sync' => env('POSTMAN_AUTO_SYNC', false),
    
    'collection_name' => env('POSTMAN_COLLECTION_NAME', 'YukiMart API v1 - Complete với Examples'),
    
    'base_url' => env('API_BASE_URL', 'http://yukimart.local/api/v1'),
    
    'test_credentials' => [
        'email' => env('TEST_USER_EMAIL', 'yukimart@gmail.com'),
        'password' => env('TEST_USER_PASSWORD', '123456'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Collection Settings
    |--------------------------------------------------------------------------
    */
    
    'collection_settings' => [
        'version' => '1.0.0',
        'description' => 'Comprehensive API collection for YukiMart Flutter App with full test cases and examples',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    */
    
    'sync_settings' => [
        'timeout' => 60, // seconds
        'retry_attempts' => 3,
        'retry_delay' => 2, // seconds
    ],
];
