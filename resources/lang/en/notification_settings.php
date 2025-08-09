<?php

return [
    // General
    'notification_settings' => 'Notification Settings',
    'manage_notifications' => 'Manage Notifications',
    'save_settings' => 'Save Settings',
    'reset_to_default' => 'Reset to Default',
    'test_notification' => 'Test',
    'notification_channels' => 'Notification Channels',
    'quiet_hours' => 'Quiet Hours',
    'quiet_hours_from' => 'Quiet Hours (From)',
    'quiet_hours_to' => 'Quiet Hours (To)',
    'supports_summary' => 'Supports Summary',

    // Categories
    'categories' => [
        'customers' => 'Customers',
        'cashbook' => 'Cashbook',
        'inventory' => 'Inventory',
        'transactions' => 'Transactions',
        'orders' => 'Orders',
        'invoices' => 'Invoices',
        'products' => 'Products',
        'users' => 'Users',
        'system' => 'System',
    ],

    // Category descriptions
    'category_descriptions' => [
        'customers' => 'Manage customer-related notifications',
        'cashbook' => 'Manage cashbook and voucher notifications',
        'inventory' => 'Manage inventory and stock notifications',
        'transactions' => 'Manage transaction notifications',
        'orders' => 'Manage order notifications',
        'invoices' => 'Manage invoice notifications',
        'products' => 'Manage product notifications',
        'users' => 'Manage user notifications',
        'system' => 'Manage system notifications',
    ],

    // Notification types
    'types' => [
        // === CUSTOMERS ===
        'customer_birthday' => 'Customer Birthday',

        // === CASHBOOK ===
        'receipt_voucher' => 'Receipt Voucher',
        'payment_voucher' => 'Payment Voucher',

        // === INVENTORY ===
        'inventory_update' => 'Inventory Update',
        'inventory_check' => 'Inventory Check',
        'inventory_alert' => 'Inventory Alert',

        // === TRANSACTIONS ===
        'order_complete' => 'Order Complete',
        'order_cancel' => 'Order Cancel',
        'invoice_complete' => 'Invoice Complete',
        'invoice_cancel' => 'Invoice Cancel',
        'return_complete' => 'Return Complete',
        'delivery_complete' => 'Delivery Complete',
        'import_complete' => 'Import Complete',
        'import_return' => 'Import Return',
        'transfer_complete' => 'Transfer Complete',
        'transfer_cancel' => 'Transfer Cancel',

        // === OLD TYPES ===
        'order_created' => 'Order Created',
        'order_updated' => 'Order Updated',
        'order_completed' => 'Order Completed',
        'invoice_created' => 'Invoice Created',
        'invoice_paid' => 'Invoice Paid',
        'product_created' => 'Product Created',
        'product_updated' => 'Product Updated',
        'inventory_import' => 'Inventory Import',
        'inventory_export' => 'Inventory Export',
        'inventory_low_stock' => 'Low Stock',
        'inventory_out_of_stock' => 'Out of Stock',
        'user_login' => 'User Login',
        'system_update' => 'System Update',
        'system_maintenance' => 'System Maintenance',
    ],

    // Notification type descriptions
    'type_descriptions' => [
        // === CUSTOMERS ===
        'customer_birthday' => 'Notify when customers have birthdays in the next 2 days',

        // === CASHBOOK ===
        'receipt_voucher' => 'Notify when receipt voucher is created or customer payment is successful',
        'payment_voucher' => 'Notify when payment voucher is created or supplier payment is successful',

        // === INVENTORY ===
        'inventory_update' => 'Notify when inventory is updated',
        'inventory_check' => 'Notify when inventory check is completed',
        'inventory_alert' => 'Notify about inventory alerts',

        // === TRANSACTIONS ===
        'order_complete' => 'Notify when order is completed',
        'order_cancel' => 'Notify when order is cancelled',
        'invoice_complete' => 'Notify when invoice is completed',
        'invoice_cancel' => 'Notify when invoice is cancelled',
        'return_complete' => 'Notify about returns',
        'delivery_complete' => 'Notify when new return is created',
        'import_complete' => 'Notify when import is completed',
        'import_return' => 'Notify when import return is completed',
        'transfer_complete' => 'Notify about transfers',
        'transfer_cancel' => 'Notify when transfer is cancelled',

        // === OLD TYPES ===
        'order_created' => 'Notify when new order is created',
        'order_updated' => 'Notify when order is updated',
        'order_completed' => 'Notify when order is completed',
        'invoice_created' => 'Notify when new invoice is created',
        'invoice_paid' => 'Notify when invoice is paid',
        'product_created' => 'Notify when new product is created',
        'product_updated' => 'Notify when product is updated',
        'inventory_import' => 'Notify when inventory import occurs',
        'inventory_export' => 'Notify when inventory export occurs',
        'inventory_low_stock' => 'Notify when product is low in stock',
        'inventory_out_of_stock' => 'Notify when product is out of stock',
        'user_login' => 'Notify when user logs in',
        'system_update' => 'Notify about system updates',
        'system_maintenance' => 'Notify about system maintenance',
    ],

    // Channels
    'channels' => [
        'web' => 'Web Notification',
        'fcm' => 'Push Notification',
        'email' => 'Email',
        'sms' => 'SMS',
        'phone' => 'Phone',
    ],

    // Messages
    'messages' => [
        'settings_updated' => 'Notification settings updated successfully',
        'settings_reset' => 'Notification settings reset to default',
        'test_sent' => 'Test notification sent successfully',
        'test_failed' => 'Failed to send test notification',
        'notification_disabled' => 'You have disabled this notification type or channel',
        'invalid_type' => 'Invalid notification type',
        'invalid_data' => 'Invalid data',
        'error_occurred' => 'An error occurred',
        'confirm_reset' => 'Are you sure you want to reset settings to default?',
    ],

    // Test notification
    'test' => [
        'title_prefix' => 'Test Notification - ',
        'message_prefix' => 'This is a test notification to check your settings for type: ',
    ],
];
