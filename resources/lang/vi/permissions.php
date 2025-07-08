<?php

return [
    // General
    'title' => 'Quản lý quyền hạn',
    'subtitle' => 'Quản lý quyền hạn hệ thống',
    'permission' => 'Quyền hạn',
    'permissions' => 'Quyền hạn',
    'permission_management' => 'Quản lý quyền hạn',
    'permission_list' => 'Danh sách quyền hạn',
    'permission_details' => 'Chi tiết quyền hạn',
    'permission_roles' => 'Vai trò có quyền hạn',

    // Actions
    'add_permission' => 'Thêm quyền hạn',
    'create_permission' => 'Tạo quyền hạn mới',
    'edit_permission' => 'Chỉnh sửa quyền hạn',
    'update_permission' => 'Cập nhật quyền hạn',
    'delete_permission' => 'Xóa quyền hạn',
    'view_permission' => 'Xem quyền hạn',
    'assign_to_roles' => 'Gán cho vai trò',
    'remove_from_roles' => 'Gỡ bỏ khỏi vai trò',
    'toggle_status' => 'Thay đổi trạng thái',
    'bulk_delete' => 'Xóa hàng loạt',
    'generate_permissions' => 'Tạo quyền hạn tự động',
    'export_permissions' => 'Xuất danh sách quyền hạn',

    // Fields
    'name' => 'Tên quyền hạn',
    'display_name' => 'Tên hiển thị',
    'module' => 'Module',
    'action' => 'Hành động',
    'description' => 'Mô tả',
    'status' => 'Trạng thái',
    'is_active' => 'Kích hoạt',
    'sort_order' => 'Thứ tự sắp xếp',
    'roles_count' => 'Số vai trò',
    'users_count' => 'Số người dùng',
    'created_at' => 'Ngày tạo',
    'updated_at' => 'Ngày cập nhật',

    // Modules
    'modules' => [
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

    // Actions
    'actions' => [
        'view' => 'Xem',
        'create' => 'Tạo',
        'edit' => 'Sửa',
        'delete' => 'Xóa',
        'export' => 'Xuất',
        'import' => 'Nhập',
        'manage' => 'Quản lý',
    ],

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',
    'enabled' => 'Bật',
    'disabled' => 'Tắt',

    // Placeholders
    'search_permissions' => 'Tìm kiếm quyền hạn...',
    'select_permission' => 'Chọn quyền hạn',
    'select_module' => 'Chọn module',
    'select_action' => 'Chọn hành động',
    'enter_permission_name' => 'Nhập tên quyền hạn',
    'enter_display_name' => 'Nhập tên hiển thị',
    'enter_description' => 'Nhập mô tả quyền hạn',

    // Messages
    'messages' => [
        'created_success' => 'Tạo quyền hạn thành công!',
        'created_error' => 'Có lỗi xảy ra khi tạo quyền hạn!',
        'updated_success' => 'Cập nhật quyền hạn thành công!',
        'updated_error' => 'Có lỗi xảy ra khi cập nhật quyền hạn!',
        'deleted_success' => 'Xóa quyền hạn thành công!',
        'deleted_error' => 'Có lỗi xảy ra khi xóa quyền hạn!',
        'status_updated' => 'Cập nhật trạng thái thành công!',
        'status_error' => 'Có lỗi xảy ra khi cập nhật trạng thái!',
        'bulk_delete_success' => 'Xóa hàng loạt thành công!',
        'bulk_delete_error' => 'Có lỗi xảy ra khi xóa hàng loạt!',
        'generate_success' => 'Tạo quyền hạn tự động thành công!',
        'generate_error' => 'Có lỗi xảy ra khi tạo quyền hạn tự động!',
        'not_found' => 'Không tìm thấy quyền hạn!',
        'cannot_delete_assigned_permission' => 'Không thể xóa quyền hạn đang được gán!',
        'module_error' => 'Có lỗi xảy ra khi lấy quyền hạn theo module!',
        'permission_exists' => 'Quyền hạn đã tồn tại!',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Tên quyền hạn là bắt buộc',
        'name_unique' => 'Tên quyền hạn đã tồn tại',
        'name_max' => 'Tên quyền hạn không được vượt quá 255 ký tự',
        'display_name_required' => 'Tên hiển thị là bắt buộc',
        'display_name_max' => 'Tên hiển thị không được vượt quá 255 ký tự',
        'module_required' => 'Module là bắt buộc',
        'module_max' => 'Module không được vượt quá 255 ký tự',
        'action_required' => 'Hành động là bắt buộc',
        'action_max' => 'Hành động không được vượt quá 255 ký tự',
        'description_max' => 'Mô tả không được vượt quá 1000 ký tự',
        'sort_order_numeric' => 'Thứ tự sắp xếp phải là số',
    ],

    // Confirmations
    'confirmations' => [
        'delete_permission' => 'Bạn có chắc chắn muốn xóa quyền hạn này?',
        'delete_permissions' => 'Bạn có chắc chắn muốn xóa các quyền hạn đã chọn?',
        'toggle_status' => 'Bạn có chắc chắn muốn thay đổi trạng thái quyền hạn này?',
        'generate_permissions' => 'Bạn có chắc chắn muốn tạo quyền hạn tự động cho module này?',
        'assign_to_role' => 'Bạn có chắc chắn muốn gán quyền hạn này cho vai trò?',
        'remove_from_role' => 'Bạn có chắc chắn muốn gỡ bỏ quyền hạn này khỏi vai trò?',
    ],

    // Tooltips
    'tooltips' => [
        'add_permission' => 'Thêm quyền hạn mới',
        'edit_permission' => 'Chỉnh sửa quyền hạn',
        'delete_permission' => 'Xóa quyền hạn',
        'view_roles' => 'Xem vai trò có quyền hạn này',
        'toggle_status' => 'Thay đổi trạng thái',
        'generate_permissions' => 'Tạo quyền hạn tự động cho module',
        'active_permission' => 'Quyền hạn đang hoạt động',
        'inactive_permission' => 'Quyền hạn không hoạt động',
        'assigned_permission' => 'Quyền hạn đã được gán',
    ],

    // Filters
    'filters' => [
        'all_permissions' => 'Tất cả quyền hạn',
        'active_permissions' => 'Quyền hạn hoạt động',
        'inactive_permissions' => 'Quyền hạn không hoạt động',
        'assigned_permissions' => 'Quyền hạn đã gán',
        'unassigned_permissions' => 'Quyền hạn chưa gán',
        'filter_by_module' => 'Lọc theo module',
        'filter_by_action' => 'Lọc theo hành động',
        'filter_by_status' => 'Lọc theo trạng thái',
        'sort_by_name' => 'Sắp xếp theo tên',
        'sort_by_module' => 'Sắp xếp theo module',
        'sort_by_action' => 'Sắp xếp theo hành động',
        'sort_by_created' => 'Sắp xếp theo ngày tạo',
    ],

    // Statistics
    'statistics' => [
        'total_permissions' => 'Tổng số quyền hạn',
        'active_permissions' => 'Quyền hạn hoạt động',
        'inactive_permissions' => 'Quyền hạn không hoạt động',
        'permissions_by_module' => 'Quyền hạn theo module',
        'permissions_by_action' => 'Quyền hạn theo hành động',
        'assigned_permissions' => 'Quyền hạn đã gán',
        'unassigned_permissions' => 'Quyền hạn chưa gán',
    ],

    // Generator
    'generator' => [
        'title' => 'Tạo quyền hạn tự động',
        'description' => 'Tạo quyền hạn tự động cho module và các hành động được chọn',
        'select_module' => 'Chọn module',
        'select_actions' => 'Chọn hành động',
        'generate_button' => 'Tạo quyền hạn',
        'preview' => 'Xem trước',
        'will_create' => 'Sẽ tạo các quyền hạn sau:',
        'already_exists' => 'Đã tồn tại:',
        'success_message' => 'Đã tạo {count} quyền hạn cho module {module}',
    ],

    // Help Text
    'help' => [
        'permission_name' => 'Tên quyền hạn duy nhất, thường theo định dạng module.action',
        'display_name' => 'Tên hiển thị thân thiện với người dùng',
        'module' => 'Module mà quyền hạn này áp dụng',
        'action' => 'Hành động cụ thể mà quyền hạn này cho phép',
        'description' => 'Mô tả chi tiết về quyền hạn này',
        'status' => 'Chỉ các quyền hạn hoạt động mới có thể được gán cho vai trò',
        'sort_order' => 'Thứ tự hiển thị trong danh sách (số nhỏ hơn sẽ hiển thị trước)',
        'auto_generate' => 'Hệ thống có thể tự động tạo tên quyền hạn từ module và hành động',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'home' => 'Trang chủ',
        'permissions' => 'Quyền hạn',
        'create' => 'Tạo mới',
        'edit' => 'Chỉnh sửa',
        'view' => 'Xem chi tiết',
        'generate' => 'Tạo tự động',
    ],

    // Tabs
    'tabs' => [
        'general' => 'Thông tin chung',
        'roles' => 'Vai trò',
        'users' => 'Người dùng',
        'history' => 'Lịch sử',
    ],

    // Empty States
    'empty_states' => [
        'no_permissions' => 'Chưa có quyền hạn nào',
        'no_roles' => 'Chưa có vai trò nào',
        'no_users' => 'Chưa có người dùng nào',
        'no_search_results' => 'Không tìm thấy kết quả phù hợp',
        'create_first_permission' => 'Tạo quyền hạn đầu tiên',
        'no_module_permissions' => 'Module này chưa có quyền hạn nào',
    ],

    // Permission Groups
    'groups' => [
        'content_management' => 'Quản lý nội dung',
        'user_management' => 'Quản lý người dùng',
        'system_management' => 'Quản lý hệ thống',
        'sales_management' => 'Quản lý bán hàng',
        'inventory_management' => 'Quản lý kho',
        'report_management' => 'Quản lý báo cáo',
    ],
];
