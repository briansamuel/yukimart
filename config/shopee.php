<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopee API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Shopee Open Platform API integration
    | Documentation: https://open.shopee.com/developer-guide/229
    |
    */

    'api' => [
        'base_url' => env('SHOPEE_API_BASE_URL', 'https://partner.shopeemobile.com'),
        'version' => env('SHOPEE_API_VERSION', 'v2'),
        'timeout' => env('SHOPEE_API_TIMEOUT', 30),
        'retry_attempts' => env('SHOPEE_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('SHOPEE_API_RETRY_DELAY', 1000), // milliseconds
    ],

    'credentials' => [
        'partner_id' => env('SHOPEE_PARTNER_ID'),
        'partner_key' => env('SHOPEE_PARTNER_KEY'),
        'redirect_uri' => env('SHOPEE_REDIRECT_URI', env('APP_URL') . '/shopee/callback'),
    ],

    'oauth' => [
        'auth_url' => env('SHOPEE_AUTH_URL', 'https://partner.shopeemobile.com/api/v2/shop/auth_partner'),
        'token_url' => env('SHOPEE_TOKEN_URL', 'https://partner.shopeemobile.com/api/v2/auth/token/get'),
        'refresh_url' => env('SHOPEE_REFRESH_URL', 'https://partner.shopeemobile.com/api/v2/auth/access_token/get'),
        'scope' => env('SHOPEE_OAUTH_SCOPE', 'item.base_read,item.full_write,order.read'),
    ],

    'sync' => [
        'enabled' => env('SHOPEE_SYNC_ENABLED', true),
        'use_jobs' => env('SHOPEE_SYNC_USE_JOBS', true), // Use job queues for sync
        'order_sync_interval' => env('SHOPEE_ORDER_SYNC_INTERVAL', 15), // minutes
        'product_sync_interval' => env('SHOPEE_PRODUCT_SYNC_INTERVAL', 60), // minutes
        'max_orders_per_sync' => env('SHOPEE_MAX_ORDERS_PER_SYNC', 100),
        'sync_days_back' => env('SHOPEE_SYNC_DAYS_BACK', 7), // days to look back for orders
        'auto_create_products' => env('SHOPEE_AUTO_CREATE_PRODUCTS', false),
        'auto_update_inventory' => env('SHOPEE_AUTO_UPDATE_INVENTORY', true),
    ],

    'notifications' => [
        'enabled' => env('SHOPEE_NOTIFICATIONS_ENABLED', true),
        'admin_email' => env('SHOPEE_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
        'webhook_url' => env('SHOPEE_WEBHOOK_URL'),
        'webhook_secret' => env('SHOPEE_WEBHOOK_SECRET'),
        'notify_on_error' => env('SHOPEE_NOTIFY_ON_ERROR', true),
        'notify_on_token_expiry' => env('SHOPEE_NOTIFY_ON_TOKEN_EXPIRY', true),
        'token_expiry_warning_hours' => env('SHOPEE_TOKEN_EXPIRY_WARNING_HOURS', 24),
    ],

    'logging' => [
        'enabled' => env('SHOPEE_LOGGING_ENABLED', true),
        'level' => env('SHOPEE_LOG_LEVEL', 'info'), // debug, info, warning, error
        'channel' => env('SHOPEE_LOG_CHANNEL', 'daily'),
        'log_requests' => env('SHOPEE_LOG_REQUESTS', false),
        'log_responses' => env('SHOPEE_LOG_RESPONSES', false),
    ],

    'rate_limiting' => [
        'enabled' => env('SHOPEE_RATE_LIMITING_ENABLED', true),
        'requests_per_minute' => env('SHOPEE_REQUESTS_PER_MINUTE', 1000),
        'requests_per_day' => env('SHOPEE_REQUESTS_PER_DAY', 50000),
    ],

    'cache' => [
        'enabled' => env('SHOPEE_CACHE_ENABLED', true),
        'ttl' => env('SHOPEE_CACHE_TTL', 3600), // seconds
        'prefix' => env('SHOPEE_CACHE_PREFIX', 'shopee_'),
    ],

    'product_mapping' => [
        'auto_link_by_sku' => env('SHOPEE_AUTO_LINK_BY_SKU', true),
        'create_missing_products' => env('SHOPEE_CREATE_MISSING_PRODUCTS', false),
        'update_product_info' => env('SHOPEE_UPDATE_PRODUCT_INFO', true),
        'sync_product_images' => env('SHOPEE_SYNC_PRODUCT_IMAGES', false),
        'default_category_id' => env('SHOPEE_DEFAULT_CATEGORY_ID', 1),
    ],

    'order_mapping' => [
        'default_customer_id' => env('SHOPEE_DEFAULT_CUSTOMER_ID', 1),
        'default_branch_shop_id' => env('SHOPEE_DEFAULT_BRANCH_SHOP_ID', 1),
        'order_prefix' => env('SHOPEE_ORDER_PREFIX', 'SP'),
        'payment_method_mapping' => [
            'COD' => 'cod',
            'CREDIT_CARD' => 'card',
            'BANK_TRANSFER' => 'transfer',
            'SHOPEE_PAY' => 'e_wallet',
            'INSTALLMENT' => 'installment',
        ],
        'status_mapping' => [
            'UNPAID' => 'processing',
            'TO_SHIP' => 'processing',
            'SHIPPED' => 'processing',
            'TO_CONFIRM_RECEIVE' => 'processing',
            'IN_CANCEL' => 'processing',
            'CANCELLED' => 'cancelled',
            'TO_RETURN' => 'processing',
            'COMPLETED' => 'completed',
        ],
    ],

    'validation' => [
        'required_fields' => [
            'order' => ['order_sn', 'total_amount', 'create_time'],
            'item' => ['item_id', 'item_name', 'item_sku', 'model_quantity_purchased'],
        ],
        'max_retries' => env('SHOPEE_VALIDATION_MAX_RETRIES', 3),
    ],

    'queue' => [
        'enabled' => env('SHOPEE_QUEUE_ENABLED', true),
        'connection' => env('SHOPEE_QUEUE_CONNECTION', 'database'),
        'order_sync_queue' => env('SHOPEE_ORDER_SYNC_QUEUE', 'shopee-sync'),
        'inventory_sync_queue' => env('SHOPEE_INVENTORY_SYNC_QUEUE', 'shopee-inventory'),
        'token_check_queue' => env('SHOPEE_TOKEN_CHECK_QUEUE', 'shopee-tokens'),
        'job_timeout' => env('SHOPEE_JOB_TIMEOUT', 300), // seconds
        'job_tries' => env('SHOPEE_JOB_TRIES', 3),
        'job_backoff' => env('SHOPEE_JOB_BACKOFF', '30,120,300'), // seconds between retries
    ],

    'features' => [
        'product_sync' => env('SHOPEE_FEATURE_PRODUCT_SYNC', true),
        'order_sync' => env('SHOPEE_FEATURE_ORDER_SYNC', true),
        'inventory_sync' => env('SHOPEE_FEATURE_INVENTORY_SYNC', true),
        'webhook_support' => env('SHOPEE_FEATURE_WEBHOOK_SUPPORT', false),
        'bulk_operations' => env('SHOPEE_FEATURE_BULK_OPERATIONS', true),
    ],
];
