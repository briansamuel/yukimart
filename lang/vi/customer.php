<?php

return [
    // General
    'customer' => 'Khách hàng',
    'customers' => 'Khách hàng',
    'customer_management' => 'Quản lý khách hàng',
    'customer_list' => 'Danh sách khách hàng',
    'customer_details' => 'Chi tiết khách hàng',
    'new_customer' => 'Khách hàng mới',
    'create_customer' => 'Tạo khách hàng',
    'edit_customer' => 'Sửa khách hàng',
    'delete_customer' => 'Xóa khách hàng',

    // Fields
    'name' => 'Họ và tên',
    'email' => 'Email',
    'phone' => 'Số điện thoại',
    'address' => 'Địa chỉ',
    'city' => 'Thành phố',
    'district' => 'Quận/Huyện',
    'ward' => 'Phường/Xã',
    'postal_code' => 'Mã bưu điện',
    'customer_type' => 'Loại khách hàng',
    'status' => 'Trạng thái',
    'date_of_birth' => 'Ngày sinh',
    'gender' => 'Giới tính',
    'notes' => 'Ghi chú',
    'avatar' => 'Ảnh đại diện',
    'created_at' => 'Ngày tạo',
    'updated_at' => 'Ngày cập nhật',

    // Customer Types
    'individual' => 'Cá nhân',
    'business' => 'Doanh nghiệp',

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',

    // Gender
    'male' => 'Nam',
    'female' => 'Nữ',
    'other' => 'Khác',

    // Statistics
    'total_customers' => 'Tổng khách hàng',
    'active_customers' => 'Khách hàng hoạt động',
    'inactive_customers' => 'Khách hàng không hoạt động',
    'individual_customers' => 'Khách hàng cá nhân',
    'business_customers' => 'Khách hàng doanh nghiệp',
    'new_customers_this_month' => 'Khách hàng mới tháng này',
    'customers_with_orders' => 'Khách hàng có đơn hàng',
    'top_customers' => 'Khách hàng VIP',
    'customer_statistics' => 'Thống kê khách hàng',

    // Order Statistics
    'total_orders' => 'Tổng đơn hàng',
    'total_spent' => 'Tổng chi tiêu',
    'avg_order_value' => 'Giá trị đơn hàng TB',
    'last_order_date' => 'Đơn hàng cuối',
    'first_order_date' => 'Đơn hàng đầu',
    'recent_orders' => 'Đơn hàng gần đây',
    'order_history' => 'Lịch sử đơn hàng',
    'monthly_orders' => 'Đơn hàng theo tháng',

    // Actions
    'add_customer' => 'Thêm khách hàng',
    'save_customer' => 'Lưu khách hàng',
    'update_customer' => 'Cập nhật khách hàng',
    'view_customer' => 'Xem khách hàng',
    'edit_customer' => 'Sửa khách hàng',
    'delete_customer' => 'Xóa khách hàng',
    'view_orders' => 'Xem đơn hàng',
    'create_order' => 'Tạo đơn hàng',
    'send_email' => 'Gửi email',
    'call_customer' => 'Gọi điện',

    // Filters
    'filter_by_status' => 'Lọc theo trạng thái',
    'filter_by_type' => 'Lọc theo loại',
    'filter_by_city' => 'Lọc theo thành phố',
    'all_customers' => 'Tất cả khách hàng',
    'all_types' => 'Tất cả loại',
    'all_cities' => 'Tất cả thành phố',

    // Placeholders
    'enter_name' => 'Nhập họ và tên',
    'enter_email' => 'Nhập địa chỉ email',
    'enter_phone' => 'Nhập số điện thoại',
    'enter_address' => 'Nhập địa chỉ',
    'enter_city' => 'Nhập thành phố',
    'enter_district' => 'Nhập quận/huyện',
    'enter_ward' => 'Nhập phường/xã',
    'enter_postal_code' => 'Nhập mã bưu điện',
    'select_type' => 'Chọn loại khách hàng',
    'select_status' => 'Chọn trạng thái',
    'select_gender' => 'Chọn giới tính',
    'enter_notes' => 'Nhập ghi chú',
    'choose_avatar' => 'Chọn ảnh đại diện',
    'search_customers' => 'Tìm kiếm khách hàng...',

    // Messages
    'no_customers' => 'Không có khách hàng nào',
    'customer_created' => 'Khách hàng đã được tạo thành công',
    'customer_updated' => 'Khách hàng đã được cập nhật thành công',
    'customer_deleted' => 'Khách hàng đã được xóa thành công',
    'no_orders' => 'Khách hàng chưa có đơn hàng nào',

    // Validation
    'name_required' => 'Họ và tên là bắt buộc',
    'name_max' => 'Họ và tên không được vượt quá 255 ký tự',
    'email_required' => 'Email là bắt buộc',
    'email_invalid' => 'Email không hợp lệ',
    'email_unique' => 'Email đã được sử dụng',
    'phone_max' => 'Số điện thoại không được vượt quá 20 ký tự',
    'address_max' => 'Địa chỉ không được vượt quá 500 ký tự',
    'type_required' => 'Loại khách hàng là bắt buộc',
    'status_required' => 'Trạng thái là bắt buộc',
    'date_of_birth_date' => 'Ngày sinh phải là ngày hợp lệ',
    'notes_max' => 'Ghi chú không được vượt quá 1000 ký tự',
    'avatar_image' => 'File phải là hình ảnh',
    'avatar_max' => 'Kích thước ảnh không được vượt quá 2MB',

    // Errors
    'validation_failed' => 'Dữ liệu không hợp lệ',
    'create_failed' => 'Lỗi khi tạo khách hàng',
    'update_failed' => 'Lỗi khi cập nhật khách hàng',
    'delete_failed' => 'Lỗi khi xóa khách hàng',
    'has_orders' => 'Không thể xóa khách hàng có đơn hàng',
    'not_found' => 'Không tìm thấy khách hàng',

    // Success messages
    'created_successfully' => 'Khách hàng đã được tạo thành công',
    'updated_successfully' => 'Khách hàng đã được cập nhật thành công',
    'deleted_successfully' => 'Khách hàng đã được xóa thành công',

    // Confirmations
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa khách hàng này?',
    'confirm_delete_with_orders' => 'Khách hàng này có {count} đơn hàng. Bạn có chắc chắn muốn xóa?',
    'confirm_status_change' => 'Bạn có chắc chắn muốn thay đổi trạng thái khách hàng này?',

    // Tooltips
    'name_tooltip' => 'Họ và tên đầy đủ của khách hàng',
    'email_tooltip' => 'Địa chỉ email để liên hệ và gửi thông báo',
    'phone_tooltip' => 'Số điện thoại để liên hệ',
    'address_tooltip' => 'Địa chỉ giao hàng của khách hàng',
    'type_tooltip' => 'Phân loại khách hàng cá nhân hoặc doanh nghiệp',
    'status_tooltip' => 'Trạng thái hoạt động của khách hàng',
    'avatar_tooltip' => 'Ảnh đại diện của khách hàng',

    // Customer Profile
    'customer_profile' => 'Hồ sơ khách hàng',
    'personal_info' => 'Thông tin cá nhân',
    'contact_info' => 'Thông tin liên hệ',
    'address_info' => 'Thông tin địa chỉ',
    'order_summary' => 'Tóm tắt đơn hàng',
    'activity_timeline' => 'Dòng thời gian hoạt động',

    // Customer Segments
    'customer_segments' => 'Phân khúc khách hàng',
    'vip_customers' => 'Khách hàng VIP',
    'loyal_customers' => 'Khách hàng thân thiết',
    'new_customers' => 'Khách hàng mới',
    'inactive_customers_segment' => 'Khách hàng không hoạt động',

    // Import/Export
    'import_customers' => 'Nhập khách hàng',
    'export_customers' => 'Xuất khách hàng',
    'import_template' => 'Tải template nhập',
    'export_selected' => 'Xuất khách hàng đã chọn',

    // Bulk actions
    'bulk_actions' => 'Thao tác hàng loạt',
    'select_all' => 'Chọn tất cả',
    'deselect_all' => 'Bỏ chọn tất cả',
    'bulk_activate' => 'Kích hoạt',
    'bulk_deactivate' => 'Vô hiệu hóa',
    'bulk_delete' => 'Xóa hàng loạt',
    'bulk_export' => 'Xuất hàng loạt',
    'selected_count' => 'đã chọn',

    // Communication
    'communication' => 'Liên lạc',
    'send_notification' => 'Gửi thông báo',
    'email_history' => 'Lịch sử email',
    'sms_history' => 'Lịch sử SMS',
    'call_history' => 'Lịch sử cuộc gọi',
    'last_contact' => 'Liên hệ cuối',

    // Preferences
    'preferences' => 'Tùy chọn',
    'email_notifications' => 'Thông báo email',
    'sms_notifications' => 'Thông báo SMS',
    'marketing_emails' => 'Email marketing',
    'newsletter' => 'Bản tin',

    // Tags
    'tags' => 'Thẻ',
    'add_tag' => 'Thêm thẻ',
    'remove_tag' => 'Xóa thẻ',
    'popular_tags' => 'Thẻ phổ biến',

    // Notes
    'customer_notes' => 'Ghi chú khách hàng',
    'add_note' => 'Thêm ghi chú',
    'edit_note' => 'Sửa ghi chú',
    'delete_note' => 'Xóa ghi chú',
    'note_date' => 'Ngày ghi chú',
    'note_author' => 'Người ghi',

    // Advanced
    'advanced_search' => 'Tìm kiếm nâng cao',
    'custom_fields' => 'Trường tùy chỉnh',
    'customer_groups' => 'Nhóm khách hàng',
    'loyalty_program' => 'Chương trình khách hàng thân thiết',
    'credit_limit' => 'Hạn mức tín dụng',
    'payment_terms' => 'Điều khoản thanh toán',

    // Reports
    'customer_reports' => 'Báo cáo khách hàng',
    'customer_analysis' => 'Phân tích khách hàng',
    'customer_lifetime_value' => 'Giá trị trọn đời khách hàng',
    'customer_acquisition' => 'Thu hút khách hàng',
    'customer_retention' => 'Giữ chân khách hàng',
    'churn_rate' => 'Tỷ lệ rời bỏ',

    // Buttons
    'save' => 'Lưu',
    'save_and_continue' => 'Lưu và tiếp tục',
    'save_and_new' => 'Lưu và tạo mới',
    'cancel' => 'Hủy',
    'back' => 'Quay lại',
    'reset' => 'Đặt lại',
    'preview' => 'Xem trước',
    'print' => 'In',
    'download' => 'Tải xuống',

    // Navigation
    'previous_customer' => 'Khách hàng trước',
    'next_customer' => 'Khách hàng tiếp',
    'customer_list_link' => 'Danh sách khách hàng',

    // Time
    'never' => 'Chưa bao giờ',
    'today' => 'Hôm nay',
    'yesterday' => 'Hôm qua',
    'this_week' => 'Tuần này',
    'this_month' => 'Tháng này',
    'this_year' => 'Năm này',
];
