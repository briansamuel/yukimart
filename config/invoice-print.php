<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice Print Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for invoice printing templates.
    | You can customize company information, styling, and layout options.
    |
    */

    'company' => [
        'name' => env('INVOICE_COMPANY_NAME', 'YukiMart'),
        'logo' => env('INVOICE_COMPANY_LOGO', null),
        'address' => env('INVOICE_COMPANY_ADDRESS', '123 Đường ABC, Quận XYZ, TP.HCM'),
        'phone' => env('INVOICE_COMPANY_PHONE', '(028) 1234 5678'),
        'email' => env('INVOICE_COMPANY_EMAIL', 'info@yukimart.com'),
        'website' => env('INVOICE_COMPANY_WEBSITE', 'www.yukimart.com'),
        'tax_code' => env('INVOICE_COMPANY_TAX_CODE', null),
    ],

    'template' => [
        'single' => [
            'view' => 'admin.invoice.print',
            'auto_print' => true,
            'page_size' => 'A4',
            'orientation' => 'portrait',
        ],
        'bulk' => [
            'view' => 'admin.invoice.bulk-print',
            'auto_print' => true,
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'page_break' => true,
        ],
    ],

    'styling' => [
        'primary_color' => '#2c3e50',
        'accent_color' => '#e74c3c',
        'success_color' => '#27ae60',
        'warning_color' => '#f39c12',
        'danger_color' => '#e74c3c',
        'font_family' => 'Arial, sans-serif',
        'font_size' => '14px',
        'line_height' => '1.6',
    ],

    'layout' => [
        'show_logo' => true,
        'show_company_details' => true,
        'show_customer_info' => true,
        'show_payment_info' => true,
        'show_notes' => true,
        'show_footer' => true,
        'show_print_time' => true,
        'show_status_badges' => true,
    ],

    'fields' => [
        'invoice_number' => [
            'label' => 'Số hóa đơn',
            'show' => true,
        ],
        'invoice_date' => [
            'label' => 'Ngày',
            'show' => true,
            'format' => 'd/m/Y',
        ],
        'customer_name' => [
            'label' => 'Khách hàng',
            'show' => true,
        ],
        'customer_phone' => [
            'label' => 'Điện thoại',
            'show' => true,
        ],
        'customer_email' => [
            'label' => 'Email',
            'show' => true,
        ],
        'customer_address' => [
            'label' => 'Địa chỉ',
            'show' => true,
        ],
        'status' => [
            'label' => 'Trạng thái',
            'show' => true,
        ],
        'payment_status' => [
            'label' => 'Thanh toán',
            'show' => true,
        ],
        'payment_method' => [
            'label' => 'Phương thức TT',
            'show' => true,
        ],
        'sales_channel' => [
            'label' => 'Kênh bán',
            'show' => true,
        ],
        'branch_shop' => [
            'label' => 'Chi nhánh',
            'show' => true,
        ],
        'product_sku' => [
            'label' => 'SKU',
            'show' => true,
        ],
        'subtotal' => [
            'label' => 'Tạm tính',
            'show' => true,
        ],
        'discount_amount' => [
            'label' => 'Giảm giá',
            'show' => true,
        ],
        'tax_amount' => [
            'label' => 'Thuế',
            'show' => true,
        ],
        'other_amount' => [
            'label' => 'Phí khác',
            'show' => true,
        ],
        'total_amount' => [
            'label' => 'Tổng cộng',
            'show' => true,
        ],
        'paid_amount' => [
            'label' => 'Đã thanh toán',
            'show' => true,
        ],
        'notes' => [
            'label' => 'Ghi chú',
            'show' => true,
        ],
    ],

    'table' => [
        'columns' => [
            'index' => [
                'label' => '#',
                'width' => '5%',
                'show' => true,
            ],
            'product_name' => [
                'label' => 'Sản phẩm',
                'width' => '45%',
                'show' => true,
            ],
            'quantity' => [
                'label' => 'Số lượng',
                'width' => '15%',
                'align' => 'center',
                'show' => true,
            ],
            'unit_price' => [
                'label' => 'Đơn giá',
                'width' => '15%',
                'align' => 'right',
                'show' => true,
            ],
            'total_price' => [
                'label' => 'Thành tiền',
                'width' => '20%',
                'align' => 'right',
                'show' => true,
            ],
        ],
    ],

    'currency' => [
        'symbol' => '₫',
        'position' => 'after', // 'before' or 'after'
        'thousands_separator' => '.',
        'decimal_separator' => ',',
        'decimal_places' => 0,
    ],

    'footer' => [
        'thank_you_message' => 'Cảm ơn quý khách đã mua hàng!',
        'show_print_time' => true,
        'print_time_format' => 'd/m/Y H:i:s',
        'custom_message' => null,
    ],

    'print' => [
        'window_features' => 'width=800,height=600,scrollbars=yes,resizable=yes',
        'auto_close_after_print' => false,
        'show_print_dialog' => true,
    ],
];
