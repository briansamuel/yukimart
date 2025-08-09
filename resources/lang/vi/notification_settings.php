<?php

return [
    // General
    'notification_settings' => 'Cài đặt thông báo',
    'manage_notifications' => 'Quản lý thông báo',
    'save_settings' => 'Lưu cài đặt',
    'reset_to_default' => 'Khôi phục mặc định',
    'test_notification' => 'Thử nghiệm',
    'notification_channels' => 'Kênh thông báo',
    'quiet_hours' => 'Giờ im lặng',
    'quiet_hours_from' => 'Giờ im lặng (từ)',
    'quiet_hours_to' => 'Giờ im lặng (đến)',
    'supports_summary' => 'Hỗ trợ gộp thông báo',

    // Categories
    'categories' => [
        'customers' => 'Khách hàng',
        'cashbook' => 'Sổ quỹ',
        'inventory' => 'Hàng hóa',
        'transactions' => 'Giao dịch',
        'orders' => 'Đơn hàng',
        'invoices' => 'Hóa đơn',
        'products' => 'Sản phẩm',
        'users' => 'Người dùng',
        'system' => 'Hệ thống',
    ],

    // Category descriptions
    'category_descriptions' => [
        'customers' => 'Quản lý thông báo liên quan đến khách hàng',
        'cashbook' => 'Quản lý thông báo về phiếu thu chi và sổ quỹ',
        'inventory' => 'Quản lý thông báo về hàng hóa và tồn kho',
        'transactions' => 'Quản lý thông báo về các giao dịch',
        'orders' => 'Quản lý thông báo về đơn hàng',
        'invoices' => 'Quản lý thông báo về hóa đơn',
        'products' => 'Quản lý thông báo về sản phẩm',
        'users' => 'Quản lý thông báo về người dùng',
        'system' => 'Quản lý thông báo hệ thống',
    ],

    // Notification types
    'types' => [
        // === KHÁCH HÀNG ===
        'customer_birthday' => 'Sinh nhật khách hàng',

        // === SỔ QUỸ ===
        'receipt_voucher' => 'Phiếu thu',
        'payment_voucher' => 'Phiếu chi',

        // === HÀNG HÓA ===
        'inventory_update' => 'Cập nhật tồn kho hàng hóa',
        'inventory_check' => 'Kiểm kho',
        'inventory_alert' => 'Cảnh báo tồn kho',

        // === GIAO DỊCH ===
        'order_complete' => 'Hoàn thành đặt hàng',
        'order_cancel' => 'Hủy đặt hàng',
        'invoice_complete' => 'Hoàn thành hóa đơn',
        'invoice_cancel' => 'Hủy hóa đơn',
        'return_complete' => 'Vận đơn',
        'delivery_complete' => 'Trả hàng',
        'import_complete' => 'Nhập hàng',
        'import_return' => 'Trả hàng nhập',
        'transfer_complete' => 'Hoàn thành chuyển hàng, nhận hàng',
        'transfer_cancel' => 'Hủy chuyển hàng',

        // === CÁC LOẠI CŨ ===
        'order_created' => 'Đơn hàng mới',
        'order_updated' => 'Cập nhật đơn hàng',
        'order_completed' => 'Hoàn thành đơn hàng',
        'invoice_created' => 'Hóa đơn mới',
        'invoice_paid' => 'Thanh toán hóa đơn',
        'product_created' => 'Sản phẩm mới',
        'product_updated' => 'Cập nhật sản phẩm',
        'inventory_import' => 'Nhập kho',
        'inventory_export' => 'Xuất kho',
        'inventory_low_stock' => 'Sắp hết hàng',
        'inventory_out_of_stock' => 'Hết hàng',
        'user_login' => 'Đăng nhập',
        'system_update' => 'Cập nhật hệ thống',
        'system_maintenance' => 'Bảo trì hệ thống',
    ],

    // Notification type descriptions
    'type_descriptions' => [
        // === KHÁCH HÀNG ===
        'customer_birthday' => 'Thông báo khi có khách hàng sinh nhật trong 2 ngày tới',

        // === SỔ QUỸ ===
        'receipt_voucher' => 'Hiển thị thông báo khi lập phiếu thu hoặc thanh toán công nợ cho KH thành công',
        'payment_voucher' => 'Hiển thị thông báo khi lập phiếu chi hoặc thanh toán công nợ cho NCC, ĐTGH thành công',

        // === HÀNG HÓA ===
        'inventory_update' => 'Hiển thị thông báo khi cập nhật tồn kho của hàng hóa',
        'inventory_check' => 'Hiển thị thông báo khi hoàn thành phiếu kiểm kho',
        'inventory_alert' => 'Hiển thị thông báo về cảnh báo tồn kho',

        // === GIAO DỊCH ===
        'order_complete' => 'Thông báo khi đơn hàng được hoàn thành',
        'order_cancel' => 'Thông báo khi đơn hàng bị hủy',
        'invoice_complete' => 'Thông báo khi hóa đơn được hoàn thành',
        'invoice_cancel' => 'Thông báo khi hóa đơn bị hủy',
        'return_complete' => 'Thông báo về vận đơn',
        'delivery_complete' => 'Hiển thị thông báo khi có phiếu trả hàng mới',
        'import_complete' => 'Hiển thị thông báo khi hoàn thành phiếu nhập hàng',
        'import_return' => 'Hiển thị thông báo khi hoàn thành phiếu trả hàng nhập',
        'transfer_complete' => 'Thông báo về chuyển hàng',
        'transfer_cancel' => 'Thông báo khi chuyển hàng bị hủy',

        // === CÁC LOẠI CŨ ===
        'order_created' => 'Thông báo khi có đơn hàng mới được tạo',
        'order_updated' => 'Thông báo khi đơn hàng được cập nhật',
        'order_completed' => 'Thông báo khi đơn hàng được hoàn thành',
        'invoice_created' => 'Thông báo khi có hóa đơn mới được tạo',
        'invoice_paid' => 'Thông báo khi hóa đơn được thanh toán',
        'product_created' => 'Thông báo khi có sản phẩm mới được tạo',
        'product_updated' => 'Thông báo khi sản phẩm được cập nhật',
        'inventory_import' => 'Thông báo khi có giao dịch nhập kho',
        'inventory_export' => 'Thông báo khi có giao dịch xuất kho',
        'inventory_low_stock' => 'Thông báo khi sản phẩm sắp hết hàng',
        'inventory_out_of_stock' => 'Thông báo khi sản phẩm hết hàng',
        'user_login' => 'Thông báo khi có người dùng đăng nhập',
        'system_update' => 'Thông báo về cập nhật hệ thống',
        'system_maintenance' => 'Thông báo về bảo trì hệ thống',
    ],

    // Channels
    'channels' => [
        'web' => 'Thông báo web',
        'fcm' => 'Thông báo đẩy',
        'email' => 'Email',
        'sms' => 'SMS',
        'phone' => 'Điện thoại',
    ],

    // Messages
    'messages' => [
        'settings_updated' => 'Cài đặt thông báo đã được cập nhật thành công',
        'settings_reset' => 'Cài đặt thông báo đã được khôi phục về mặc định',
        'test_sent' => 'Thông báo thử nghiệm đã được gửi thành công',
        'test_failed' => 'Không thể gửi thông báo thử nghiệm',
        'notification_disabled' => 'Bạn đã tắt loại thông báo này hoặc kênh này',
        'invalid_type' => 'Loại thông báo không hợp lệ',
        'invalid_data' => 'Dữ liệu không hợp lệ',
        'error_occurred' => 'Có lỗi xảy ra',
        'confirm_reset' => 'Bạn có chắc chắn muốn khôi phục cài đặt về mặc định?',
    ],

    // Test notification
    'test' => [
        'title_prefix' => 'Thông báo thử nghiệm - ',
        'message_prefix' => 'Đây là thông báo thử nghiệm để kiểm tra cài đặt của bạn cho loại: ',
    ],
];
