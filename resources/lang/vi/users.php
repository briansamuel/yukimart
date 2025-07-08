<?php

return [
    // General
    'title' => 'Quản lý người dùng',
    'subtitle' => 'Quản lý thông tin người dùng và phân quyền',
    'user' => 'Người dùng',
    'users' => 'Người dùng',
    'user_management' => 'Quản lý người dùng',
    'user_list' => 'Danh sách người dùng',
    'user_details' => 'Chi tiết người dùng',

    // Actions
    'add_user' => 'Thêm người dùng',
    'create_user' => 'Tạo người dùng mới',
    'edit_user' => 'Chỉnh sửa người dùng',
    'update_user' => 'Cập nhật người dùng',
    'delete_user' => 'Xóa người dùng',
    'view_user' => 'Xem người dùng',
    'assign_role' => 'Gán vai trò',
    'remove_role' => 'Gỡ bỏ vai trò',
    'assign_branch_shop' => 'Gán chi nhánh',
    'remove_branch_shop' => 'Gỡ bỏ chi nhánh',
    'add_branch_shop' => 'Thêm chi nhánh',
    'edit_branch_shop' => 'Sửa chi nhánh',
    'update_branch_shop' => 'Cập nhật chi nhánh',

    // Fields
    'name' => 'Tên',
    'username' => 'Tên đăng nhập',
    'email' => 'Email',
    'full_name' => 'Họ và tên',
    'phone' => 'Số điện thoại',
    'address' => 'Địa chỉ',
    'birth_date' => 'Ngày sinh',
    'password' => 'Mật khẩu',
    'confirm_password' => 'Xác nhận mật khẩu',
    'avatar' => 'Ảnh đại diện',
    'status' => 'Trạng thái',
    'description' => 'Mô tả',
    'created_at' => 'Ngày tạo',
    'updated_at' => 'Ngày cập nhật',
    'last_login' => 'Đăng nhập cuối',
    'email_verified_at' => 'Email đã xác thực',

    // User Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',
    'blocked' => 'Bị khóa',
    'pending' => 'Chờ xác thực',

    // Branch Shop Assignment
    'branch_shops' => 'Chi nhánh làm việc',
    'role_in_shop' => 'Vai trò trong chi nhánh',
    'start_date' => 'Ngày bắt đầu',
    'end_date' => 'Ngày kết thúc',
    'is_active' => 'Đang hoạt động',
    'is_primary' => 'Chi nhánh chính',
    'notes' => 'Ghi chú',
    'assigned_by' => 'Được gán bởi',
    'assigned_at' => 'Thời gian gán',
    'work_duration' => 'Thời gian làm việc',
    'primary' => 'Chính',
    'no_branch_shops' => 'Chưa được gán chi nhánh nào',

    // Roles
    'user_roles' => 'Vai trò người dùng',
    'roles' => 'Vai trò',
    'permissions' => 'Quyền hạn',

    // Placeholders
    'search_users' => 'Tìm kiếm người dùng...',
    'select_user' => 'Chọn người dùng',
    'select_status' => 'Chọn trạng thái',
    'select_role' => 'Chọn vai trò',
    'select_start_date' => 'Chọn ngày bắt đầu',
    'select_end_date' => 'Chọn ngày kết thúc',
    'enter_username' => 'Nhập tên đăng nhập',
    'enter_email' => 'Nhập địa chỉ email',
    'enter_full_name' => 'Nhập họ và tên',
    'enter_phone' => 'Nhập số điện thoại',
    'enter_address' => 'Nhập địa chỉ',
    'enter_password' => 'Nhập mật khẩu',
    'enter_new_password' => 'Nhập mật khẩu mới',
    'enter_description' => 'Nhập mô tả',
    'enter_notes' => 'Nhập ghi chú',

    // Messages
    'messages' => [
        'created_success' => 'Tạo người dùng thành công!',
        'created_error' => 'Có lỗi xảy ra khi tạo người dùng!',
        'updated_success' => 'Cập nhật người dùng thành công!',
        'updated_error' => 'Có lỗi xảy ra khi cập nhật người dùng!',
        'deleted_success' => 'Xóa người dùng thành công!',
        'deleted_error' => 'Có lỗi xảy ra khi xóa người dùng!',
        'status_updated' => 'Cập nhật trạng thái thành công!',
        'status_error' => 'Có lỗi xảy ra khi cập nhật trạng thái!',
        'role_assigned' => 'Gán vai trò thành công!',
        'role_removed' => 'Gỡ bỏ vai trò thành công!',
        'branch_shop_assigned' => 'Gán chi nhánh thành công!',
        'branch_shop_updated' => 'Cập nhật chi nhánh thành công!',
        'branch_shop_removed' => 'Gỡ bỏ chi nhánh thành công!',
        'branch_shop_already_assigned' => 'Người dùng đã được gán vào chi nhánh này!',
        'branch_shop_not_assigned' => 'Người dùng chưa được gán vào chi nhánh này!',
        'password_changed' => 'Đổi mật khẩu thành công!',
        'avatar_updated' => 'Cập nhật ảnh đại diện thành công!',
        'not_found' => 'Không tìm thấy người dùng!',
        'email_exists' => 'Email đã tồn tại!',
        'username_exists' => 'Tên đăng nhập đã tồn tại!',
        'cannot_delete_self' => 'Không thể xóa chính mình!',
        'cannot_delete_admin' => 'Không thể xóa tài khoản quản trị viên!',
    ],

    // Validation
    'user_required' => 'Vui lòng chọn người dùng',
    'validation' => [
        'username_required' => 'Tên đăng nhập là bắt buộc',
        'username_unique' => 'Tên đăng nhập đã tồn tại',
        'username_min' => 'Tên đăng nhập phải có ít nhất 3 ký tự',
        'username_max' => 'Tên đăng nhập không được vượt quá 255 ký tự',
        'email_required' => 'Email là bắt buộc',
        'email_email' => 'Email không đúng định dạng',
        'email_unique' => 'Email đã tồn tại',
        'full_name_required' => 'Họ và tên là bắt buộc',
        'full_name_max' => 'Họ và tên không được vượt quá 255 ký tự',
        'phone_max' => 'Số điện thoại không được vượt quá 20 ký tự',
        'password_required' => 'Mật khẩu là bắt buộc',
        'password_min' => 'Mật khẩu phải có ít nhất 8 ký tự',
        'password_confirmed' => 'Xác nhận mật khẩu không khớp',
        'avatar_image' => 'Ảnh đại diện phải là file hình ảnh',
        'avatar_max' => 'Ảnh đại diện không được vượt quá 2MB',
        'birth_date_date' => 'Ngày sinh không đúng định dạng',
        'birth_date_before' => 'Ngày sinh phải trước ngày hiện tại',
    ],

    // Confirmations
    'confirmations' => [
        'delete_user' => 'Bạn có chắc chắn muốn xóa người dùng này?',
        'delete_users' => 'Bạn có chắc chắn muốn xóa các người dùng đã chọn?',
        'block_user' => 'Bạn có chắc chắn muốn khóa người dùng này?',
        'unblock_user' => 'Bạn có chắc chắn muốn mở khóa người dùng này?',
        'remove_role' => 'Bạn có chắc chắn muốn gỡ bỏ vai trò này?',
        'remove_branch_shop' => 'Bạn có chắc chắn muốn gỡ bỏ chi nhánh này?',
        'change_password' => 'Bạn có chắc chắn muốn đổi mật khẩu?',
    ],

    // Tooltips
    'tooltips' => [
        'add_user' => 'Thêm người dùng mới',
        'edit_user' => 'Chỉnh sửa người dùng',
        'delete_user' => 'Xóa người dùng',
        'view_user' => 'Xem chi tiết người dùng',
        'block_user' => 'Khóa người dùng',
        'unblock_user' => 'Mở khóa người dùng',
        'change_avatar' => 'Thay đổi ảnh đại diện',
        'assign_role' => 'Gán vai trò',
        'assign_branch_shop' => 'Gán chi nhánh',
        'primary_branch' => 'Chi nhánh chính của người dùng',
        'active_assignment' => 'Đang hoạt động',
        'inactive_assignment' => 'Không hoạt động',
    ],

    // Help Text
    'help' => [
        'username' => 'Tên đăng nhập duy nhất, chỉ chứa chữ cái, số và dấu gạch dưới',
        'email' => 'Địa chỉ email hợp lệ để đăng nhập và nhận thông báo',
        'password' => 'Để trống nếu không muốn thay đổi mật khẩu',
        'avatar' => 'Tải lên ảnh đại diện (JPG, PNG, tối đa 2MB)',
        'status' => 'Chỉ người dùng hoạt động mới có thể đăng nhập',
        'roles' => 'Vai trò xác định quyền hạn của người dùng trong hệ thống',
        'branch_shops' => 'Chi nhánh mà người dùng được phép làm việc',
        'is_primary' => 'Chi nhánh chính sẽ được chọn mặc định khi tạo đơn hàng',
        'end_date' => 'Để trống nếu không có thời hạn kết thúc',
        'role_in_shop' => 'Vai trò của người dùng trong chi nhánh cụ thể',
    ],

    // Statistics
    'statistics' => 'Thống kê',
    'total_users' => 'Tổng số người dùng',
    'active_users' => 'Người dùng hoạt động',
    'inactive_users' => 'Người dùng không hoạt động',
    'blocked_users' => 'Người dùng bị khóa',
    'users_with_roles' => 'Người dùng có vai trò',
    'users_without_roles' => 'Người dùng chưa có vai trò',

    // Filters
    'filters' => [
        'all_users' => 'Tất cả người dùng',
        'active_users' => 'Người dùng hoạt động',
        'inactive_users' => 'Người dùng không hoạt động',
        'blocked_users' => 'Người dùng bị khóa',
        'filter_by_status' => 'Lọc theo trạng thái',
        'filter_by_role' => 'Lọc theo vai trò',
        'filter_by_branch' => 'Lọc theo chi nhánh',
        'sort_by_name' => 'Sắp xếp theo tên',
        'sort_by_email' => 'Sắp xếp theo email',
        'sort_by_created' => 'Sắp xếp theo ngày tạo',
        'sort_by_last_login' => 'Sắp xếp theo đăng nhập cuối',
    ],

    // Tabs
    'tabs' => [
        'general' => 'Thông tin chung',
        'roles' => 'Vai trò',
        'permissions' => 'Quyền hạn',
        'branch_shops' => 'Chi nhánh',
        'activity' => 'Hoạt động',
        'settings' => 'Cài đặt',
    ],

    // General Info
    'general_info' => 'Thông tin chung',
    'contact_info' => 'Thông tin liên hệ',
    'account_info' => 'Thông tin tài khoản',
    'work_info' => 'Thông tin công việc',

    // Empty States
    'empty_states' => [
        'no_users' => 'Chưa có người dùng nào',
        'no_roles' => 'Chưa có vai trò nào',
        'no_branch_shops' => 'Chưa có chi nhánh nào',
        'no_search_results' => 'Không tìm thấy kết quả phù hợp',
        'create_first_user' => 'Tạo người dùng đầu tiên',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'home' => 'Trang chủ',
        'users' => 'Người dùng',
        'create' => 'Tạo mới',
        'edit' => 'Chỉnh sửa',
        'view' => 'Xem chi tiết',
        'profile' => 'Hồ sơ',
    ],
];
