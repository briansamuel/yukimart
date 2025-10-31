<?php

return [
    // ==========================================
    // QUẢN LÝ - Management
    // ==========================================
    
    // Hàng hóa - Products
    [
        'id' => 'product_barcode',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Mã vạch hàng hóa',
        'description' => 'Quản lý hàng hóa bằng mã vạch chuẩn hoặc mã vạch do cửa hàng tạo ra',
        'keywords' => ['mã vạch', 'barcode', 'ma vach', 'hang hoa', 'product'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_barcode',
        'permission' => 'settings.products.read',
    ],
    [
        'id' => 'product_auto_suggest',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Tự động gợi ý thông tin hàng hóa',
        'description' => 'KiotViet sẽ tự động gợi ý tên, mã, mô tả, hình ảnh hàng hóa khi tạo hàng hóa',
        'keywords' => ['tự động', 'gợi ý', 'tu dong', 'goi y', 'hang hoa', 'auto suggest', 'product'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_auto_suggest',
        'permission' => 'settings.products.read',
    ],
    [
        'id' => 'product_units',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Đơn vị tính',
        'description' => 'Quản lý hàng hóa theo đơn vị tính khác nhau như chiếc, lốc, thùng',
        'keywords' => ['đơn vị', 'đơn vị tính', 'don vi', 'don vi tinh', 'units', 'hang hoa'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_units',
        'permission' => 'settings.products.read',
    ],
    [
        'id' => 'product_attributes',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Thuộc tính',
        'description' => 'Quản lý hàng hóa theo đặc điểm riêng như màu sắc, kích cỡ, chất liệu',
        'keywords' => ['thuộc tính', 'thuoc tinh', 'attributes', 'màu sắc', 'kích cỡ', 'hang hoa'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_attributes',
        'permission' => 'settings.products.read',
    ],
    [
        'id' => 'product_groups',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Nhóm hàng',
        'description' => 'Quản lý hàng hóa theo nhóm chủng loại, đặc tính, công năng',
        'keywords' => ['nhóm hàng', 'nhom hang', 'groups', 'category', 'hang hoa'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_groups',
        'permission' => 'settings.products.read',
    ],
    [
        'id' => 'product_brands',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Thương hiệu',
        'description' => 'Quản lý hàng hóa theo thương hiệu nhà sản xuất hoặc dòng sản phẩm',
        'keywords' => ['thương hiệu', 'thuong hieu', 'brands', 'nhà sản xuất', 'hang hoa'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_brands',
        'permission' => 'settings.products.read',
    ],
    [
        'id' => 'product_locations',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Vị trí',
        'description' => 'Quản lý hàng hóa theo vị trí bán hàng hoặc lưu trữ như giá, kệ, tủ',
        'keywords' => ['vị trí', 'vi tri', 'locations', 'kho', 'hang hoa'],
        'route' => 'admin.settings.products.index',
        'section_id' => 'product_locations',
        'permission' => 'settings.products.read',
    ],

    // Đơn hàng - Orders (Placeholder)
    [
        'id' => 'order_settings',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Cài đặt đơn hàng',
        'description' => 'Quản lý cài đặt chung cho đơn hàng, hóa đơn, giao hàng',
        'keywords' => ['đơn hàng', 'don hang', 'orders', 'invoice', 'hóa đơn'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.orders.read',
    ],

    // Khách hàng - Customers (Placeholder)
    [
        'id' => 'customer_settings',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Cài đặt khách hàng',
        'description' => 'Quản lý cài đặt chung cho khách hàng, nhóm khách hàng, điểm thưởng',
        'keywords' => ['khách hàng', 'khach hang', 'customers', 'loyalty', 'điểm thưởng'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.customers.read',
    ],

    // Sổ quỹ - Cash Book (Placeholder)
    [
        'id' => 'cashbook_settings',
        'category' => 'Quản lý',
        'category_slug' => 'management',
        'label' => 'Cài đặt sổ quỹ',
        'description' => 'Quản lý cài đặt chung cho sổ quỹ, thu chi, tài khoản ngân hàng',
        'keywords' => ['sổ quỹ', 'so quy', 'cashbook', 'thu chi', 'ngân hàng'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.cashbook.read',
    ],

    // ==========================================
    // CỬA HÀNG - Store
    // ==========================================
    
    [
        'id' => 'store_info',
        'category' => 'Cửa hàng',
        'category_slug' => 'store',
        'label' => 'Thông tin cửa hàng',
        'description' => 'Quản lý thông tin cơ bản, tên cửa hàng, địa chỉ, store info',
        'keywords' => ['thông tin', 'cửa hàng', 'ten cua hang', 'dia chi', 'store info'],
        'route' => 'admin.settings.shop.retailer-info',
        'section_id' => null,
        'permission' => 'settings.store.read',
    ],
    [
        'id' => 'user_management',
        'category' => 'Cửa hàng',
        'category_slug' => 'store',
        'label' => 'Quản lý người dùng',
        'description' => 'Quản lý người dùng, nhân viên, phân quyền, quyền truy cập',
        'keywords' => ['user', 'nhân viên', 'nhan vien', 'phân quyền', 'quyền truy cập'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.users.read',
    ],
    [
        'id' => 'branch_management',
        'category' => 'Cửa hàng',
        'category_slug' => 'store',
        'label' => 'Quản lý chi nhánh',
        'description' => 'Quản lý chi nhánh, cửa hàng phụ, địa điểm, cửa hàng phụ',
        'keywords' => ['chi nhánh', 'branch', 'cửa hàng phụ', 'địa điểm'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.branches.read',
    ],
    [
        'id' => 'api_integration',
        'category' => 'Cửa hàng',
        'category_slug' => 'store',
        'label' => 'Tích hợp API',
        'description' => 'Tích hợp với các nền tảng bên ngoài, webhook, token',
        'keywords' => ['api', 'tích hợp', 'webhook', 'token'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.api.read',
    ],
    [
        'id' => 'tax_settings',
        'category' => 'Cửa hàng',
        'category_slug' => 'store',
        'label' => 'Thuế',
        'description' => 'Cài đặt thuế VAT, hóa đơn, invoice, kê toán',
        'keywords' => ['thuế', 'VAT', 'hóa đơn', 'invoice', 'kê toán'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.tax.read',
    ],

    // ==========================================
    // HỆ THỐNG - System
    // ==========================================
    
    [
        'id' => 'general_settings',
        'category' => 'Hệ thống',
        'category_slug' => 'system',
        'label' => 'Cài đặt chung',
        'description' => 'Cài đặt chung của hệ thống, múi giờ, ngôn ngữ, định dạng',
        'keywords' => ['cài đặt chung', 'general', 'múi giờ', 'ngôn ngữ', 'language'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.general.read',
    ],
    [
        'id' => 'notification_settings',
        'category' => 'Hệ thống',
        'category_slug' => 'system',
        'label' => 'Thông báo',
        'description' => 'Cài đặt thông báo email, SMS, push notification',
        'keywords' => ['thông báo', 'notification', 'email', 'sms', 'push'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.notifications.read',
    ],
    [
        'id' => 'backup_settings',
        'category' => 'Hệ thống',
        'category_slug' => 'system',
        'label' => 'Sao lưu dữ liệu',
        'description' => 'Cài đặt sao lưu tự động, backup, restore dữ liệu',
        'keywords' => ['sao lưu', 'backup', 'restore', 'dữ liệu'],
        'route' => null, // Placeholder
        'section_id' => null,
        'permission' => 'settings.backup.read',
    ],
];

