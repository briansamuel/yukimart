<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Menu Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the menu structure for the Analytics module.
    | It defines the sidebar navigation items and their properties.
    |
    */

    'menu' => [
        [
            'title' => 'Phân Tích Kinh Doanh',
            'icon' => 'fas fa-chart-line',
            'route' => null,
            'permission' => 'analytics.business.view',
            'children' => [
                [
                    'title' => 'Tổng Quan',
                    'route' => 'admin.analytics.business.overview',
                    'permission' => 'analytics.business.overview',
                ],
                [
                    'title' => 'Chi Phí & Lợi Nhuận',
                    'route' => 'admin.analytics.business.expense-profit',
                    'permission' => 'analytics.business.expense-profit',
                ],
            ],
        ],
        [
            'title' => 'Phân Tích Hàng Hóa',
            'icon' => 'fas fa-boxes',
            'route' => null,
            'permission' => 'analytics.product.view',
            'children' => [
                [
                    'title' => 'Tổng Quan',
                    'route' => 'admin.analytics.product.overview',
                    'permission' => 'analytics.product.overview',
                ],
                [
                    'title' => 'Tồn Kho',
                    'route' => 'admin.analytics.product.inventory',
                    'permission' => 'analytics.product.inventory',
                ],
            ],
        ],
        [
            'title' => 'Phân Tích Khách Hàng',
            'icon' => 'fas fa-users',
            'route' => null,
            'permission' => 'analytics.customer.view',
            'children' => [
                [
                    'title' => 'Tổng Quan',
                    'route' => 'admin.analytics.customer.overview',
                    'permission' => 'analytics.customer.overview',
                ],
            ],
        ],
        [
            'title' => 'Hiệu Suất Nhân Viên',
            'icon' => 'fas fa-user-tie',
            'route' => null,
            'permission' => 'analytics.performance.view',
            'children' => [
                [
                    'title' => 'Tổng Quan',
                    'route' => 'admin.analytics.performance.overview',
                    'permission' => 'analytics.performance.overview',
                ],
            ],
        ],
        [
            'title' => 'Công Nợ',
            'icon' => 'fas fa-file-invoice-dollar',
            'route' => null,
            'permission' => 'analytics.accounts-receivable.view',
            'children' => [
                [
                    'title' => 'Tổng Quan',
                    'route' => 'admin.analytics.accounts-receivable.overview',
                    'permission' => 'analytics.accounts-receivable.overview',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb Configuration
    |--------------------------------------------------------------------------
    |
    | Define breadcrumb patterns for analytics pages
    |
    */

    'breadcrumbs' => [
        'admin.analytics.business.overview' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Kinh Doanh', 'route' => null],
            ['title' => 'Tổng Quan', 'route' => null],
        ],
        'admin.analytics.business.expense-profit' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Kinh Doanh', 'route' => null],
            ['title' => 'Chi Phí & Lợi Nhuận', 'route' => null],
        ],
        'admin.analytics.product.overview' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Hàng Hóa', 'route' => null],
            ['title' => 'Tổng Quan', 'route' => null],
        ],
        'admin.analytics.product.inventory' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Hàng Hóa', 'route' => null],
            ['title' => 'Tồn Kho', 'route' => null],
        ],
        'admin.analytics.customer.overview' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Khách Hàng', 'route' => null],
            ['title' => 'Tổng Quan', 'route' => null],
        ],
        'admin.analytics.performance.overview' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Hiệu Suất Nhân Viên', 'route' => null],
            ['title' => 'Tổng Quan', 'route' => null],
        ],
        'admin.analytics.accounts-receivable.overview' => [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['title' => 'Phân Tích', 'route' => null],
            ['title' => 'Công Nợ', 'route' => null],
            ['title' => 'Tổng Quan', 'route' => null],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Define all analytics permissions
    |
    */

    'permissions' => [
        'analytics.business.view' => 'Xem phân tích kinh doanh',
        'analytics.business.overview' => 'Xem tổng quan kinh doanh',
        'analytics.business.expense-profit' => 'Xem chi phí & lợi nhuận',
        
        'analytics.product.view' => 'Xem phân tích hàng hóa',
        'analytics.product.overview' => 'Xem tổng quan hàng hóa',
        'analytics.product.inventory' => 'Xem phân tích tồn kho',
        
        'analytics.customer.view' => 'Xem phân tích khách hàng',
        'analytics.customer.overview' => 'Xem tổng quan khách hàng',
        
        'analytics.performance.view' => 'Xem hiệu suất nhân viên',
        'analytics.performance.overview' => 'Xem tổng quan hiệu suất',
        
        'analytics.accounts-receivable.view' => 'Xem công nợ',
        'analytics.accounts-receivable.overview' => 'Xem tổng quan công nợ',
    ],
];

