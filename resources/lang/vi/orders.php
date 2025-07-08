<?php

return [
    // General
    'title' => 'Quản lý đơn hàng',
    'subtitle' => 'Quản lý đơn hàng và giao dịch',
    'order' => 'Đơn hàng',
    'orders' => 'Đơn hàng',
    'order_code' => 'Mã đơn hàng',
    'order_date' => 'Ngày đặt hàng',
    'order_status' => 'Trạng thái đơn hàng',
    'delivery_status' => 'Trạng thái giao hàng',

    // Branch Shop
    'branch_shop' => 'Chi nhánh',
    'select_branch_shop' => 'Chọn chi nhánh',
    'no_branch_shops' => 'Bạn chưa được gán vào chi nhánh nào',
    'contact_admin_for_branch_assignment' => 'Vui lòng liên hệ quản trị viên để được gán chi nhánh',

    // Customer
    'customer' => 'Khách hàng',
    'customer_information' => 'Thông tin khách hàng',
    'customer_name' => 'Tên khách hàng',
    'customer_phone' => 'Số điện thoại',
    'customer_email' => 'Email khách hàng',
    'customer_address' => 'Địa chỉ khách hàng',
    'customer_type' => 'Loại khách hàng',
    'search_customer' => 'Tìm kiếm khách hàng...',
    'select_customer' => 'Chọn khách hàng',
    'new_customer' => 'Khách hàng mới',
    'add_new_customer' => 'Thêm khách hàng mới',
    'new_customer_info' => 'Thông tin khách hàng mới',
    'create_customer' => 'Tạo khách hàng',
    'enter_customer_name' => 'Nhập tên khách hàng',
    'enter_customer_phone' => 'Nhập số điện thoại',
    'enter_customer_email' => 'Nhập email',
    'enter_customer_address' => 'Nhập địa chỉ',

    // Customer Types
    'individual' => 'Cá nhân',
    'business' => 'Doanh nghiệp',
    'vip' => 'VIP',

    // Order Status
    'processing' => 'Đang xử lý',
    'confirmed' => 'Đã xác nhận',
    'completed' => 'Đã hoàn thành',
    'returned' => 'Đã hoàn hàng',
    'cancelled' => 'Đã hủy',

    // Delivery Status
    'pending' => 'Chờ giao hàng',
    'in_transit' => 'Đang giao hàng',
    'delivered' => 'Đã giao hàng',
    'failed' => 'Giao hàng thất bại',

    // Sales Channel
    'channel' => 'Kênh bán hàng',
    'online' => 'Online',
    'offline' => 'Offline',
    'phone' => 'Điện thoại',
    'direct' => 'Trực tiếp',

    // Products
    'products' => 'Sản phẩm',
    'product_search' => 'Tìm kiếm sản phẩm',
    'quantity' => 'Số lượng',
    'unit_price' => 'Đơn giá',
    'total_price' => 'Thành tiền',
    'stock' => 'Tồn kho',
    'actions' => 'Thao tác',

    // Order Summary
    'subtotal' => 'Tạm tính',
    'discount' => 'Giảm giá',
    'shipping_fee' => 'Phí vận chuyển',
    'tax' => 'Thuế',
    'total' => 'Tổng cộng',
    'notes' => 'Ghi chú',

    // Actions
    'create_order' => 'Tạo đơn hàng',
    'edit_order' => 'Sửa đơn hàng',
    'view_order' => 'Xem đơn hàng',
    'delete_order' => 'Xóa đơn hàng',
    'cancel_order' => 'Hủy đơn hàng',
    'confirm_order' => 'Xác nhận đơn hàng',
    'complete_order' => 'Hoàn thành đơn hàng',

    // Help Text
    'help' => [
        'branch_shop_selection' => 'Chọn chi nhánh mà bạn đang làm việc để tạo đơn hàng',
        'customer_selection' => 'Tìm kiếm và chọn khách hàng hoặc tạo khách hàng mới',
        'product_search' => 'Tìm kiếm sản phẩm theo tên hoặc mã sản phẩm',
    ],

    // Messages
    'messages' => [
        'order_created' => 'Tạo đơn hàng thành công!',
        'order_updated' => 'Cập nhật đơn hàng thành công!',
        'order_deleted' => 'Xóa đơn hàng thành công!',
        'order_cancelled' => 'Hủy đơn hàng thành công!',
        'order_confirmed' => 'Xác nhận đơn hàng thành công!',
        'order_completed' => 'Hoàn thành đơn hàng thành công!',
        'customer_created' => 'Tạo khách hàng thành công!',
        'product_added' => 'Thêm sản phẩm vào đơn hàng thành công!',
        'product_removed' => 'Xóa sản phẩm khỏi đơn hàng thành công!',
        'insufficient_stock' => 'Số lượng tồn kho không đủ!',
        'invalid_quantity' => 'Số lượng không hợp lệ!',
        'no_products' => 'Đơn hàng phải có ít nhất 1 sản phẩm!',
        'branch_shop_required' => 'Vui lòng chọn chi nhánh!',
        'customer_required' => 'Vui lòng chọn khách hàng!',
    ],

    // Validation
    'validation' => [
        'order_code_required' => 'Mã đơn hàng là bắt buộc',
        'branch_shop_required' => 'Chi nhánh là bắt buộc',
        'customer_required' => 'Khách hàng là bắt buộc',
        'status_required' => 'Trạng thái là bắt buộc',
        'channel_required' => 'Kênh bán hàng là bắt buộc',
        'products_required' => 'Đơn hàng phải có ít nhất 1 sản phẩm',
        'quantity_min' => 'Số lượng phải lớn hơn 0',
        'quantity_max' => 'Số lượng vượt quá tồn kho',
        'discount_min' => 'Giảm giá không được âm',
        'shipping_fee_min' => 'Phí vận chuyển không được âm',
        'tax_min' => 'Thuế không được âm',
    ],

    // Empty States
    'empty_states' => [
        'no_orders' => 'Chưa có đơn hàng nào',
        'no_products' => 'Chưa có sản phẩm nào',
        'no_customers' => 'Chưa có khách hàng nào',
        'create_first_order' => 'Tạo đơn hàng đầu tiên',
    ],
];
