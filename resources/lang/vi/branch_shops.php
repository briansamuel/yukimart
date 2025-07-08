<?php

return [
    // General
    'title' => 'Quản lý chi nhánh',
    'subtitle' => 'Quản lý thông tin chi nhánh cửa hàng',
    'branch_shop' => 'Chi nhánh',
    'branch_shops' => 'Chi nhánh',
    'name' => 'Tên chi nhánh',
    'code' => 'Mã chi nhánh',
    'address' => 'Địa chỉ',
    'phone' => 'Số điện thoại',
    'email' => 'Email',
    'manager' => 'Quản lý',
    'warehouse' => 'Kho hàng',
    'status' => 'Trạng thái',
    'description' => 'Mô tả',

    // Actions
    'add_branch_shop' => 'Thêm chi nhánh',
    'edit_branch_shop' => 'Sửa chi nhánh',
    'delete_branch_shop' => 'Xóa chi nhánh',
    'view_branch_shop' => 'Xem chi nhánh',
    'view_details' => 'Xem chi tiết',
    'export' => 'Xuất dữ liệu',
    'import' => 'Nhập dữ liệu',
    'filter_branch_shops' => 'Lọc chi nhánh',
    'search_placeholder' => 'Tìm kiếm chi nhánh...',
    'delete_selected' => 'Xóa đã chọn',
    'manage_users' => 'Quản lý nhân viên',

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',
    'maintenance' => 'Bảo trì',
    'closed' => 'Đã đóng',
    'select_status' => 'Chọn trạng thái',

    // Shop Types
    'shop_type' => 'Loại cửa hàng',
    'flagship' => 'Cửa hàng chính',
    'standard' => 'Cửa hàng tiêu chuẩn',
    'mini' => 'Cửa hàng mini',
    'kiosk' => 'Quầy hàng',
    'select_shop_type' => 'Chọn loại cửa hàng',

    // Additional Fields
    'full_address' => 'Địa chỉ đầy đủ',
    'delivery_service' => 'Dịch vụ giao hàng',

    // User Roles in Branch Shop
    'roles' => [
        'manager' => 'Quản lý',
        'staff' => 'Nhân viên',
        'cashier' => 'Thu ngân',
        'sales' => 'Bán hàng',
        'warehouse_keeper' => 'Thủ kho',
    ],

    // Branch Shop Selection
    'select_branch_shop' => 'Chọn chi nhánh',
    'select_warehouse' => 'Chọn kho hàng',
    'warehouse_description' => 'Kho hàng được gán cho chi nhánh này để quản lý tồn kho',
    'select_manager' => 'Chọn quản lý',
    'no_manager' => 'Không có quản lý',

    // User Management
    'manage_users_subtitle' => 'Quản lý nhân viên trong chi nhánh',
    'current_users' => 'Nhân viên hiện tại',
    'add_user_to_branch' => 'Thêm nhân viên',
    'search_users' => 'Tìm kiếm nhân viên...',
    'role_in_shop' => 'Vai trò',
    'start_date' => 'Ngày bắt đầu',
    'select_start_date' => 'Chọn ngày bắt đầu',
    'is_primary' => 'Chi nhánh chính',
    'set_as_primary' => 'Đặt làm chi nhánh chính',
    'primary_branch_description' => 'Chi nhánh chính là nơi làm việc chính của nhân viên',
    'notes' => 'Ghi chú',
    'notes_placeholder' => 'Nhập ghi chú về nhân viên...',
    'add_user' => 'Thêm nhân viên',
    'select_role' => 'Chọn vai trò',
    'role_required' => 'Vui lòng chọn vai trò',

    // Import/Export
    'import_branch_shops' => 'Nhập dữ liệu chi nhánh',
    'import_notice_title' => 'Lưu ý quan trọng',
    'import_notice_description' => 'Vui lòng đảm bảo file Excel/CSV của bạn tuân theo định dạng mẫu để tránh lỗi khi nhập dữ liệu.',
    'select_import_file' => 'Chọn file để nhập',
    'import_file_tooltip' => 'Chỉ chấp nhận file Excel (.xlsx, .xls) hoặc CSV với dung lượng tối đa 5MB',
    'import_options' => 'Tùy chọn nhập dữ liệu',
    'update_existing' => 'Cập nhật dữ liệu hiện có',
    'update_existing_description' => 'Cập nhật thông tin chi nhánh nếu mã chi nhánh đã tồn tại',
    'download_template' => 'Tải mẫu Excel',
    'import_data' => 'Nhập dữ liệu',
    'template_description' => 'Tải xuống file mẫu Excel để xem định dạng dữ liệu cần thiết.',
    'download_excel_template' => 'Tải mẫu Excel',
    'export_branch_shops' => 'Xuất dữ liệu chi nhánh',
    'export_format' => 'Định dạng xuất',
    'export_excel_description' => 'Xuất dữ liệu dưới dạng file Excel (.xlsx)',
    'export_csv_description' => 'Xuất dữ liệu dưới dạng file CSV',
    'export_filters' => 'Tùy chọn xuất',
    'include_inactive' => 'Bao gồm chi nhánh không hoạt động',
    'include_inactive_description' => 'Xuất cả những chi nhánh đã bị vô hiệu hóa',
    'include_details' => 'Bao gồm thông tin chi tiết',
    'include_details_description' => 'Xuất đầy đủ thông tin chi nhánh',
    'export_data' => 'Xuất dữ liệu',

    // Messages
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa chi nhánh này?',
    'confirm_delete_selected' => 'Bạn có chắc chắn muốn xóa các chi nhánh đã chọn?',
    'delete_error' => 'Có lỗi xảy ra khi xóa chi nhánh',
    'file_required' => 'Vui lòng chọn file để import',
    'invalid_file' => 'Vui lòng chọn file Excel hoặc CSV hợp lệ (tối đa 5MB)',
    'file_too_large' => 'File quá lớn. Vui lòng chọn file nhỏ hơn 5MB.',
    'invalid_file_type' => 'Loại file không hợp lệ. Vui lòng chọn file Excel (.xlsx, .xls) hoặc CSV.',
    'import_error' => 'Có lỗi xảy ra khi import dữ liệu. Vui lòng thử lại.',
    'import_errors' => 'Lỗi chi tiết:',

    // Empty States
    'empty_states' => [
        'no_branch_shops' => 'Chưa có chi nhánh nào',
        'no_search_results' => 'Không tìm thấy kết quả phù hợp',
        'create_first_branch_shop' => 'Tạo chi nhánh đầu tiên',
    ],
];
