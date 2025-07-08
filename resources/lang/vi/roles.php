<?php

return [
    // General
    'title' => 'Quản lý vai trò',
    'subtitle' => 'Quản lý vai trò và quyền hạn người dùng',
    'role' => 'Vai trò',
    'roles' => 'Vai trò',
    'role_management' => 'Quản lý vai trò',
    'role_list' => 'Danh sách vai trò',
    'role_details' => 'Chi tiết vai trò',
    'role_permissions' => 'Quyền hạn vai trò',

    // Actions
    'add_role' => 'Thêm vai trò',
    'create_role' => 'Tạo vai trò mới',
    'edit_role' => 'Chỉnh sửa vai trò',
    'update_role' => 'Cập nhật vai trò',
    'delete_role' => 'Xóa vai trò',
    'view_role' => 'Xem vai trò',
    'assign_permissions' => 'Gán quyền hạn',
    'manage_permissions' => 'Quản lý quyền hạn',
    'toggle_status' => 'Thay đổi trạng thái',
    'bulk_delete' => 'Xóa hàng loạt',
    'export_roles' => 'Xuất danh sách vai trò',

    // Fields
    'name' => 'Tên vai trò',
    'display_name' => 'Tên hiển thị',
    'description' => 'Mô tả',
    'permissions' => 'Quyền hạn',
    'status' => 'Trạng thái',
    'is_active' => 'Kích hoạt',
    'sort_order' => 'Thứ tự sắp xếp',
    'settings' => 'Cài đặt',
    'users_count' => 'Số người dùng',
    'permissions_count' => 'Số quyền hạn',
    'created_at' => 'Ngày tạo',
    'updated_at' => 'Ngày cập nhật',

    // Role Types
    'role_types' => [
        'admin' => 'Quản trị viên',
        'shop_manager' => 'Quản lý cửa hàng',
        'staff' => 'Nhân viên',
        'partime' => 'Nhân viên bán thời gian',
    ],

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',
    'enabled' => 'Bật',
    'disabled' => 'Tắt',

    // Placeholders
    'search_roles' => 'Tìm kiếm vai trò...',
    'select_role' => 'Chọn vai trò',
    'select_permissions' => 'Chọn quyền hạn',
    'enter_role_name' => 'Nhập tên vai trò',
    'enter_display_name' => 'Nhập tên hiển thị',
    'enter_description' => 'Nhập mô tả vai trò',

    // Messages
    'messages' => [
        'created_success' => 'Tạo vai trò thành công!',
        'created_error' => 'Có lỗi xảy ra khi tạo vai trò!',
        'updated_success' => 'Cập nhật vai trò thành công!',
        'updated_error' => 'Có lỗi xảy ra khi cập nhật vai trò!',
        'deleted_success' => 'Xóa vai trò thành công!',
        'deleted_error' => 'Có lỗi xảy ra khi xóa vai trò!',
        'status_updated' => 'Cập nhật trạng thái thành công!',
        'status_error' => 'Có lỗi xảy ra khi cập nhật trạng thái!',
        'permissions_updated' => 'Cập nhật quyền hạn thành công!',
        'permissions_error' => 'Có lỗi xảy ra khi cập nhật quyền hạn!',
        'bulk_delete_success' => 'Xóa hàng loạt thành công!',
        'bulk_delete_error' => 'Có lỗi xảy ra khi xóa hàng loạt!',
        'not_found' => 'Không tìm thấy vai trò!',
        'cannot_delete_system_role' => 'Không thể xóa vai trò hệ thống!',
        'cannot_delete_role_with_users' => 'Không thể xóa vai trò đang được sử dụng!',
        'cannot_modify_system_role' => 'Không thể chỉnh sửa vai trò hệ thống!',
        'cannot_deactivate_admin_role' => 'Không thể vô hiệu hóa vai trò quản trị viên!',
        'role_assigned_success' => 'Gán vai trò thành công!',
        'role_removed_success' => 'Gỡ bỏ vai trò thành công!',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Tên vai trò là bắt buộc',
        'name_unique' => 'Tên vai trò đã tồn tại',
        'name_max' => 'Tên vai trò không được vượt quá 255 ký tự',
        'display_name_required' => 'Tên hiển thị là bắt buộc',
        'display_name_max' => 'Tên hiển thị không được vượt quá 255 ký tự',
        'description_max' => 'Mô tả không được vượt quá 1000 ký tự',
        'permissions_array' => 'Quyền hạn phải là một mảng',
        'permission_exists' => 'Quyền hạn không tồn tại',
        'sort_order_numeric' => 'Thứ tự sắp xếp phải là số',
    ],

    // Confirmations
    'confirmations' => [
        'delete_role' => 'Bạn có chắc chắn muốn xóa vai trò này?',
        'delete_roles' => 'Bạn có chắc chắn muốn xóa các vai trò đã chọn?',
        'toggle_status' => 'Bạn có chắc chắn muốn thay đổi trạng thái vai trò này?',
        'remove_permission' => 'Bạn có chắc chắn muốn gỡ bỏ quyền hạn này?',
        'assign_role' => 'Bạn có chắc chắn muốn gán vai trò này cho người dùng?',
        'remove_role' => 'Bạn có chắc chắn muốn gỡ bỏ vai trò này khỏi người dùng?',
    ],

    // Tooltips
    'tooltips' => [
        'add_role' => 'Thêm vai trò mới',
        'edit_role' => 'Chỉnh sửa vai trò',
        'delete_role' => 'Xóa vai trò',
        'view_permissions' => 'Xem quyền hạn',
        'toggle_status' => 'Thay đổi trạng thái',
        'system_role' => 'Vai trò hệ thống - Không thể xóa',
        'active_role' => 'Vai trò đang hoạt động',
        'inactive_role' => 'Vai trò không hoạt động',
    ],

    // Filters
    'filters' => [
        'all_roles' => 'Tất cả vai trò',
        'active_roles' => 'Vai trò hoạt động',
        'inactive_roles' => 'Vai trò không hoạt động',
        'system_roles' => 'Vai trò hệ thống',
        'custom_roles' => 'Vai trò tùy chỉnh',
        'filter_by_status' => 'Lọc theo trạng thái',
        'filter_by_type' => 'Lọc theo loại',
        'sort_by_name' => 'Sắp xếp theo tên',
        'sort_by_created' => 'Sắp xếp theo ngày tạo',
        'sort_by_users' => 'Sắp xếp theo số người dùng',
    ],

    // Statistics
    'statistics' => [
        'total_roles' => 'Tổng số vai trò',
        'active_roles' => 'Vai trò hoạt động',
        'inactive_roles' => 'Vai trò không hoạt động',
        'roles_with_users' => 'Vai trò có người dùng',
        'roles_without_users' => 'Vai trò không có người dùng',
        'system_roles' => 'Vai trò hệ thống',
        'custom_roles' => 'Vai trò tùy chỉnh',
    ],

    // Permissions by Module
    'permission_modules' => [
        'pages' => 'Trang',
        'products' => 'Sản phẩm',
        'categories' => 'Danh mục',
        'orders' => 'Đơn hàng',
        'customers' => 'Khách hàng',
        'inventory' => 'Tồn kho',
        'transactions' => 'Giao dịch',
        'suppliers' => 'Nhà cung cấp',
        'branches' => 'Chi nhánh',
        'branch_shops' => 'Cửa hàng',
        'users' => 'Người dùng',
        'roles' => 'Vai trò',
        'settings' => 'Cài đặt',
        'reports' => 'Báo cáo',
        'notifications' => 'Thông báo',
    ],

    // Permission Actions
    'permission_actions' => [
        'view' => 'Xem',
        'create' => 'Tạo',
        'edit' => 'Sửa',
        'delete' => 'Xóa',
        'export' => 'Xuất',
        'import' => 'Nhập',
        'manage' => 'Quản lý',
    ],

    // Help Text
    'help' => [
        'role_name' => 'Tên vai trò duy nhất, chỉ chứa chữ cái, số và dấu gạch dưới',
        'display_name' => 'Tên hiển thị thân thiện với người dùng',
        'description' => 'Mô tả ngắn gọn về vai trò và chức năng',
        'permissions' => 'Chọn các quyền hạn mà vai trò này được phép thực hiện',
        'status' => 'Chỉ các vai trò hoạt động mới có thể được gán cho người dùng',
        'sort_order' => 'Thứ tự hiển thị trong danh sách (số nhỏ hơn sẽ hiển thị trước)',
        'system_role' => 'Vai trò hệ thống không thể bị xóa hoặc chỉnh sửa',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'home' => 'Trang chủ',
        'roles' => 'Vai trò',
        'create' => 'Tạo mới',
        'edit' => 'Chỉnh sửa',
        'view' => 'Xem chi tiết',
        'permissions' => 'Quyền hạn',
    ],

    // Tabs
    'tabs' => [
        'general' => 'Thông tin chung',
        'permissions' => 'Quyền hạn',
        'users' => 'Người dùng',
        'settings' => 'Cài đặt',
        'history' => 'Lịch sử',
    ],

    // Empty States
    'empty_states' => [
        'no_roles' => 'Chưa có vai trò nào',
        'no_permissions' => 'Chưa có quyền hạn nào',
        'no_users' => 'Chưa có người dùng nào',
        'no_search_results' => 'Không tìm thấy kết quả phù hợp',
        'create_first_role' => 'Tạo vai trò đầu tiên',
    ],

    // Actions
    'view_role' => 'Xem vai trò',
    'edit_role' => 'Sửa vai trò',
    'delete_role' => 'Xóa vai trò',
    'add_new_role' => 'Thêm vai trò mới',

    // Grid view
    'total_users_with_role' => 'Tổng số người dùng có vai trò này: :count',
    'and_more_permissions' => 'và :count quyền khác...',
    'no_permissions_assigned' => 'Chưa có quyền nào được gán',
];
