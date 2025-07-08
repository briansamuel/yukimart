<?php

return [
    // General
    'audit_log' => 'Nhật ký kiểm toán',
    'audit_logs' => 'Nhật ký kiểm toán',
    'activity_log' => 'Nhật ký hoạt động',
    'activity_logs' => 'Nhật ký hoạt động',
    'system_logs' => 'Nhật ký hệ thống',
    'log_management' => 'Quản lý nhật ký',
    'log_viewer' => 'Xem nhật ký',

    // Actions
    'created' => 'Tạo mới',
    'updated' => 'Cập nhật',
    'deleted' => 'Xóa',
    'restored' => 'Khôi phục',
    'viewed' => 'Xem',
    'login' => 'Đăng nhập',
    'logout' => 'Đăng xuất',
    'exported' => 'Xuất dữ liệu',
    'imported' => 'Nhập dữ liệu',
    'backup' => 'Sao lưu',
    'restore' => 'Khôi phục',
    'cleanup' => 'Dọn dẹp',

    // Fields
    'user' => 'Người dùng',
    'action' => 'Hành động',
    'model' => 'Đối tượng',
    'model_type' => 'Loại đối tượng',
    'model_id' => 'ID đối tượng',
    'description' => 'Mô tả',
    'ip_address' => 'Địa chỉ IP',
    'user_agent' => 'User Agent',
    'url' => 'URL',
    'method' => 'Phương thức',
    'old_values' => 'Giá trị cũ',
    'new_values' => 'Giá trị mới',
    'changes' => 'Thay đổi',
    'timestamp' => 'Thời gian',
    'created_at' => 'Thời gian tạo',
    'tags' => 'Thẻ',

    // Models
    'product' => 'Sản phẩm',
    'order' => 'Đơn hàng',
    'customer' => 'Khách hàng',
    'user_model' => 'Người dùng',
    'inventory' => 'Tồn kho',
    'inventory_transaction' => 'Giao dịch tồn kho',
    'product_category' => 'Danh mục sản phẩm',
    'supplier' => 'Nhà cung cấp',
    'warehouse' => 'Kho',

    // Filters
    'filter_by_user' => 'Lọc theo người dùng',
    'filter_by_action' => 'Lọc theo hành động',
    'filter_by_model' => 'Lọc theo đối tượng',
    'filter_by_date' => 'Lọc theo ngày',
    'all_users' => 'Tất cả người dùng',
    'all_actions' => 'Tất cả hành động',
    'all_models' => 'Tất cả đối tượng',
    'date_range' => 'Khoảng thời gian',
    'from_date' => 'Từ ngày',
    'to_date' => 'Đến ngày',

    // Actions buttons
    'view_details' => 'Xem chi tiết',
    'export_logs' => 'Xuất nhật ký',
    'cleanup_logs' => 'Dọn dẹp nhật ký',
    'refresh' => 'Làm mới',
    'search' => 'Tìm kiếm',
    'filter' => 'Lọc',
    'clear_filters' => 'Xóa bộ lọc',

    // Export
    'export_format' => 'Định dạng xuất',
    'export_csv' => 'Xuất CSV',
    'export_excel' => 'Xuất Excel',
    'export_json' => 'Xuất JSON',
    'export_success' => 'Xuất nhật ký thành công',
    'export_error' => 'Lỗi khi xuất nhật ký',

    // Cleanup
    'cleanup_old_logs' => 'Dọn dẹp nhật ký cũ',
    'keep_logs_days' => 'Giữ nhật ký trong (ngày)',
    'cleanup_confirm' => 'Bạn có chắc chắn muốn xóa các nhật ký cũ?',
    'cleanup_success' => 'Dọn dẹp nhật ký thành công',
    'cleanup_error' => 'Lỗi khi dọn dẹp nhật ký',
    'logs_deleted' => 'nhật ký đã được xóa',

    // Statistics
    'statistics' => 'Thống kê',
    'total_logs' => 'Tổng số nhật ký',
    'recent_activity' => 'Hoạt động gần đây',
    'top_users' => 'Người dùng hoạt động nhiều nhất',
    'top_actions' => 'Hành động phổ biến',
    'activity_by_date' => 'Hoạt động theo ngày',
    'activity_by_hour' => 'Hoạt động theo giờ',
    'activity_trend' => 'Xu hướng hoạt động',

    // Details
    'log_details' => 'Chi tiết nhật ký',
    'system_info' => 'Thông tin hệ thống',
    'request_info' => 'Thông tin yêu cầu',
    'changes_made' => 'Thay đổi đã thực hiện',
    'field_changed' => 'Trường thay đổi',
    'old_value' => 'Giá trị cũ',
    'new_value' => 'Giá trị mới',
    'no_changes' => 'Không có thay đổi',
    'no_old_values' => 'Không có giá trị cũ',
    'no_new_values' => 'Không có giá trị mới',

    // Messages
    'no_logs_found' => 'Không tìm thấy nhật ký nào',
    'loading_logs' => 'Đang tải nhật ký...',
    'log_not_found' => 'Không tìm thấy nhật ký',
    'access_denied' => 'Không có quyền truy cập',

    // Time
    'time_ago' => 'trước',
    'just_now' => 'vừa xong',
    'minutes_ago' => 'phút trước',
    'hours_ago' => 'giờ trước',
    'days_ago' => 'ngày trước',
    'weeks_ago' => 'tuần trước',
    'months_ago' => 'tháng trước',

    // Validation
    'invalid_date_range' => 'Khoảng thời gian không hợp lệ',
    'date_to_after_from' => 'Ngày kết thúc phải sau ngày bắt đầu',
    'invalid_format' => 'Định dạng không hợp lệ',
    'cleanup_days_required' => 'Số ngày giữ lại là bắt buộc',
    'cleanup_days_min' => 'Số ngày phải lớn hơn 0',
    'cleanup_days_max' => 'Số ngày không được vượt quá 365',

    // Placeholders
    'search_logs' => 'Tìm kiếm nhật ký...',
    'select_user' => 'Chọn người dùng',
    'select_action' => 'Chọn hành động',
    'select_model' => 'Chọn đối tượng',
    'enter_days' => 'Nhập số ngày',

    // Tooltips
    'view_tooltip' => 'Xem chi tiết nhật ký',
    'export_tooltip' => 'Xuất nhật ký ra file',
    'cleanup_tooltip' => 'Xóa các nhật ký cũ để tiết kiệm dung lượng',
    'filter_tooltip' => 'Lọc nhật ký theo điều kiện',
    'refresh_tooltip' => 'Làm mới danh sách nhật ký',

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',
    'success' => 'Thành công',
    'failed' => 'Thất bại',
    'pending' => 'Đang chờ',
    'completed' => 'Hoàn thành',

    // Tabs
    'logs_tab' => 'Nhật ký',
    'statistics_tab' => 'Thống kê',
    'settings_tab' => 'Cài đặt',

    // Settings
    'log_settings' => 'Cài đặt nhật ký',
    'enable_logging' => 'Bật ghi nhật ký',
    'log_level' => 'Mức độ ghi log',
    'auto_cleanup' => 'Tự động dọn dẹp',
    'retention_days' => 'Số ngày lưu trữ',
    'log_sensitive_data' => 'Ghi dữ liệu nhạy cảm',
    'anonymize_ip' => 'Ẩn danh địa chỉ IP',

    // Levels
    'level_all' => 'Tất cả',
    'level_info' => 'Thông tin',
    'level_warning' => 'Cảnh báo',
    'level_error' => 'Lỗi',
    'level_critical' => 'Nghiêm trọng',

    // Advanced
    'advanced_search' => 'Tìm kiếm nâng cao',
    'bulk_actions' => 'Thao tác hàng loạt',
    'select_all' => 'Chọn tất cả',
    'deselect_all' => 'Bỏ chọn tất cả',
    'bulk_delete' => 'Xóa hàng loạt',
    'bulk_export' => 'Xuất hàng loạt',
    'selected_count' => 'đã chọn',

    // Errors
    'error_loading' => 'Lỗi khi tải nhật ký',
    'error_exporting' => 'Lỗi khi xuất nhật ký',
    'error_deleting' => 'Lỗi khi xóa nhật ký',
    'error_filtering' => 'Lỗi khi lọc nhật ký',
    'error_searching' => 'Lỗi khi tìm kiếm nhật ký',

    // Success messages
    'success_exported' => 'Xuất nhật ký thành công',
    'success_deleted' => 'Xóa nhật ký thành công',
    'success_cleaned' => 'Dọn dẹp nhật ký thành công',

    // Confirmations
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa nhật ký này?',
    'confirm_bulk_delete' => 'Bạn có chắc chắn muốn xóa các nhật ký đã chọn?',
    'confirm_cleanup' => 'Bạn có chắc chắn muốn dọn dẹp các nhật ký cũ?',

    // Navigation
    'previous' => 'Trước',
    'next' => 'Tiếp',
    'first' => 'Đầu',
    'last' => 'Cuối',
    'page' => 'Trang',
    'of' => 'của',
    'showing' => 'Hiển thị',
    'to' => 'đến',
    'entries' => 'mục',

    // Buttons
    'view' => 'Xem',
    'export' => 'Xuất',
    'delete' => 'Xóa',
    'cleanup' => 'Dọn dẹp',
    'apply' => 'Áp dụng',
    'reset' => 'Đặt lại',
    'close' => 'Đóng',
    'cancel' => 'Hủy',
    'confirm' => 'Xác nhận',
    'save' => 'Lưu',

    // Help
    'help' => 'Trợ giúp',
    'documentation' => 'Tài liệu',
    'about_audit_logs' => 'Về nhật ký kiểm toán',
    'audit_log_description' => 'Nhật ký kiểm toán ghi lại tất cả các hoạt động của người dùng trong hệ thống để đảm bảo tính minh bạch và bảo mật.',

    // Privacy
    'privacy_notice' => 'Thông báo riêng tư',
    'data_retention' => 'Lưu trữ dữ liệu',
    'sensitive_data_warning' => 'Nhật ký có thể chứa dữ liệu nhạy cảm',
    'gdpr_compliance' => 'Tuân thủ GDPR',

    // Performance
    'performance_impact' => 'Tác động hiệu suất',
    'large_dataset_warning' => 'Tập dữ liệu lớn có thể ảnh hưởng đến hiệu suất',
    'optimize_query' => 'Tối ưu hóa truy vấn',
    'index_recommendation' => 'Khuyến nghị tạo chỉ mục',
];
