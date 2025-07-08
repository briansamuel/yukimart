<?php

return [
    // General
    'title' => 'Quản lý kho hàng',
    'subtitle' => 'Quản lý thông tin kho hàng',
    'warehouse' => 'Kho hàng',
    'warehouses' => 'Kho hàng',
    'name' => 'Tên kho hàng',
    'code' => 'Mã kho hàng',
    'description' => 'Mô tả',
    'address' => 'Địa chỉ',
    'phone' => 'Số điện thoại',
    'email' => 'Email',
    'manager_name' => 'Tên quản lý',
    'status' => 'Trạng thái',
    'is_default' => 'Kho mặc định',

    // Actions
    'add_warehouse' => 'Thêm kho hàng',
    'edit_warehouse' => 'Sửa kho hàng',
    'delete_warehouse' => 'Xóa kho hàng',
    'view_warehouse' => 'Xem kho hàng',
    'view_details' => 'Xem chi tiết',
    'export' => 'Xuất dữ liệu',
    'import' => 'Nhập dữ liệu',
    'filter_warehouses' => 'Lọc kho hàng',
    'search_placeholder' => 'Tìm kiếm kho hàng...',
    'delete_selected' => 'Xóa đã chọn',

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',
    'select_status' => 'Chọn trạng thái',

    // Placeholders
    'name_placeholder' => 'Nhập tên kho hàng',
    'code_placeholder' => 'Nhập mã kho hàng',
    'description_placeholder' => 'Nhập mô tả kho hàng',
    'address_placeholder' => 'Nhập địa chỉ kho hàng',
    'phone_placeholder' => 'Nhập số điện thoại',
    'email_placeholder' => 'Nhập email',
    'manager_name_placeholder' => 'Nhập tên quản lý kho',
    'select_warehouse' => 'Chọn kho hàng',

    // Descriptions
    'name_description' => 'Tên kho hàng sẽ được hiển thị trong hệ thống',
    'code_description' => 'Mã kho hàng duy nhất để phân biệt các kho',
    'description_description' => 'Mô tả chi tiết về kho hàng',
    'is_default_description' => 'Kho mặc định sẽ được sử dụng khi không chỉ định kho cụ thể',

    // Validation Messages
    'name_required' => 'Vui lòng nhập tên kho hàng',
    'code_required' => 'Vui lòng nhập mã kho hàng',
    'code_unique' => 'Mã kho hàng đã tồn tại',
    'status_required' => 'Vui lòng chọn trạng thái',
    'email_invalid' => 'Email không hợp lệ',

    // Messages
    'created_successfully' => 'Tạo kho hàng thành công',
    'updated_successfully' => 'Cập nhật kho hàng thành công',
    'deleted_successfully' => 'Xóa kho hàng thành công',
    'cannot_delete_in_use' => 'Không thể xóa kho hàng này vì đang được sử dụng',
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa kho hàng này?',
    'confirm_delete_selected' => 'Bạn có chắc chắn muốn xóa các kho hàng đã chọn?',
    'delete_error' => 'Có lỗi xảy ra khi xóa kho hàng',
    'not_found' => 'Không tìm thấy kho hàng',
    'load_error' => 'Có lỗi xảy ra khi tải danh sách kho hàng',

    // Statistics
    'total_products' => 'Tổng sản phẩm',
    'total_quantity' => 'Tổng số lượng',
    'total_value' => 'Tổng giá trị',
    'low_stock_count' => 'Sản phẩm sắp hết',
    'out_of_stock_count' => 'Sản phẩm hết hàng',
    'in_stock_count' => 'Sản phẩm còn hàng',
    'stock_health' => 'Tình trạng kho',

    // Inventory
    'inventory_summary' => 'Tổng quan tồn kho',
    'products_in_warehouse' => 'Sản phẩm trong kho',
    'low_stock_products' => 'Sản phẩm sắp hết',
    'out_of_stock_products' => 'Sản phẩm hết hàng',
    'stock_movements' => 'Biến động tồn kho',
    'recent_transactions' => 'Giao dịch gần đây',

    // Branch Shops
    'branch_shops_using' => 'Chi nhánh sử dụng kho này',
    'no_branch_shops' => 'Chưa có chi nhánh nào sử dụng kho này',
    'branch_shop_count' => 'Số chi nhánh',

    // Default Warehouse
    'set_as_default' => 'Đặt làm kho mặc định',
    'default_warehouse' => 'Kho mặc định',
    'default_warehouse_description' => 'Kho mặc định sẽ được sử dụng khi không chỉ định kho cụ thể',
    'only_one_default' => 'Chỉ có thể có một kho mặc định',

    // Form Labels
    'general_information' => 'Thông tin chung',
    'contact_information' => 'Thông tin liên hệ',
    'warehouse_settings' => 'Cài đặt kho hàng',
    'inventory_information' => 'Thông tin tồn kho',

    // Buttons
    'save' => 'Lưu',
    'cancel' => 'Hủy',
    'edit' => 'Sửa',
    'delete' => 'Xóa',
    'view' => 'Xem',
    'back' => 'Quay lại',
    'refresh' => 'Làm mới',
    'filter' => 'Lọc',
    'reset' => 'Đặt lại',
    'apply' => 'Áp dụng',

    // Table Headers
    'warehouse_name' => 'Tên kho',
    'warehouse_code' => 'Mã kho',
    'warehouse_address' => 'Địa chỉ',
    'warehouse_manager' => 'Quản lý',
    'warehouse_status' => 'Trạng thái',
    'warehouse_default' => 'Mặc định',
    'actions' => 'Thao tác',
    'created_at' => 'Ngày tạo',
    'updated_at' => 'Ngày cập nhật',

    // Empty States
    'no_warehouses' => 'Chưa có kho hàng nào',
    'no_warehouses_found' => 'Không tìm thấy kho hàng nào',
    'create_first_warehouse' => 'Tạo kho hàng đầu tiên',

    // Pagination
    'showing' => 'Hiển thị',
    'to' => 'đến',
    'of' => 'trong tổng số',
    'results' => 'kết quả',
    'warehouses_count' => 'kho hàng',
];
