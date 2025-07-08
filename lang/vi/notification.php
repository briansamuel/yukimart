<?php

return [
    // General
    'notification' => 'Thông báo',
    'notifications' => 'Thông báo',
    'notification_management' => 'Quản lý thông báo',
    'notification_list' => 'Danh sách thông báo',
    'notification_details' => 'Chi tiết thông báo',
    'new_notification' => 'Thông báo mới',
    'create_notification' => 'Tạo thông báo',
    'send_notification' => 'Gửi thông báo',

    // Properties
    'title' => 'Tiêu đề',
    'message' => 'Nội dung',
    'type' => 'Loại thông báo',
    'priority' => 'Mức độ ưu tiên',
    'status' => 'Trạng thái',
    'recipients' => 'Người nhận',
    'channels' => 'Kênh gửi',
    'expires_at' => 'Hết hạn lúc',
    'created_at' => 'Thời gian tạo',
    'read_at' => 'Thời gian đọc',
    'time_ago' => 'Thời gian',

    // Types
    'product_created' => 'Sản phẩm mới',
    'product_updated' => 'Cập nhật sản phẩm',
    'product_deleted' => 'Xóa sản phẩm',
    'inventory_import' => 'Nhập kho',
    'inventory_export' => 'Xuất kho',
    'inventory_adjustment' => 'Điều chỉnh kho',
    'inventory_low_stock' => 'Sắp hết hàng',
    'inventory_out_of_stock' => 'Hết hàng',
    'order_created' => 'Đơn hàng mới',
    'order_updated' => 'Cập nhật đơn hàng',
    'order_cancelled' => 'Hủy đơn hàng',
    'order_completed' => 'Hoàn thành đơn hàng',
    'invoice_created' => 'Hóa đơn mới',
    'invoice_paid' => 'Thanh toán hóa đơn',
    'user_login' => 'Đăng nhập',
    'system_update' => 'Cập nhật hệ thống',
    'system_maintenance' => 'Bảo trì hệ thống',
    'general' => 'Thông báo chung',

    // Priority levels
    'low' => 'Thấp',
    'normal' => 'Bình thường',
    'high' => 'Cao',
    'urgent' => 'Khẩn cấp',

    // Status
    'read' => 'Đã đọc',
    'unread' => 'Chưa đọc',
    'expired' => 'Đã hết hạn',
    'active' => 'Hoạt động',

    // Channels
    'web' => 'Web',
    'email' => 'Email',
    'sms' => 'SMS',
    'push' => 'Push notification',

    // Actions
    'mark_as_read' => 'Đánh dấu đã đọc',
    'mark_as_unread' => 'Đánh dấu chưa đọc',
    'mark_all_read' => 'Đánh dấu tất cả đã đọc',
    'delete_notification' => 'Xóa thông báo',
    'delete_all' => 'Xóa tất cả',
    'view_all' => 'Xem tất cả',
    'refresh' => 'Làm mới',
    'filter' => 'Lọc',
    'search' => 'Tìm kiếm',
    'export' => 'Xuất',
    'cleanup' => 'Dọn dẹp',

    // Filters
    'filter_by_type' => 'Lọc theo loại',
    'filter_by_priority' => 'Lọc theo mức độ',
    'filter_by_status' => 'Lọc theo trạng thái',
    'filter_by_date' => 'Lọc theo ngày',
    'show_unread_only' => 'Chỉ hiển thị chưa đọc',
    'show_all' => 'Hiển thị tất cả',

    // Messages
    'no_notifications' => 'Không có thông báo nào',
    'no_unread_notifications' => 'Không có thông báo chưa đọc',
    'notification_sent' => 'Thông báo đã được gửi thành công',
    'notification_marked_read' => 'Thông báo đã được đánh dấu đã đọc',
    'all_notifications_marked_read' => 'Tất cả thông báo đã được đánh dấu đã đọc',
    'notification_deleted' => 'Thông báo đã được xóa',
    'notifications_cleaned' => 'Đã dọn dẹp thông báo thành công',
    'notification_not_found' => 'Không tìm thấy thông báo',

    // Validation
    'title_required' => 'Tiêu đề là bắt buộc',
    'message_required' => 'Nội dung là bắt buộc',
    'type_required' => 'Loại thông báo là bắt buộc',
    'priority_required' => 'Mức độ ưu tiên là bắt buộc',
    'recipients_required' => 'Người nhận là bắt buộc',
    'expires_at_future' => 'Thời gian hết hạn phải sau thời điểm hiện tại',

    // Placeholders
    'enter_title' => 'Nhập tiêu đề thông báo',
    'enter_message' => 'Nhập nội dung thông báo',
    'select_type' => 'Chọn loại thông báo',
    'select_priority' => 'Chọn mức độ ưu tiên',
    'select_recipients' => 'Chọn người nhận',
    'select_channels' => 'Chọn kênh gửi',
    'search_notifications' => 'Tìm kiếm thông báo...',

    // Statistics
    'total_notifications' => 'Tổng số thông báo',
    'unread_count' => 'Chưa đọc',
    'read_count' => 'Đã đọc',
    'expired_count' => 'Đã hết hạn',
    'by_type' => 'Theo loại',
    'by_priority' => 'Theo mức độ',
    'recent_activity' => 'Hoạt động gần đây',

    // Cleanup
    'cleanup_expired' => 'Dọn dẹp thông báo hết hạn',
    'cleanup_old' => 'Dọn dẹp thông báo cũ',
    'cleanup_days' => 'Dọn dẹp thông báo cũ hơn (ngày)',
    'confirm_cleanup' => 'Bạn có chắc chắn muốn dọn dẹp thông báo?',
    'cleanup_success' => 'Dọn dẹp thành công',

    // Notifications in header
    'you_have' => 'Bạn có',
    'new_notifications' => 'thông báo mới',
    'view_all_notifications' => 'Xem tất cả thông báo',
    'no_new_notifications' => 'Không có thông báo mới',

    // Real-time
    'new_notification_received' => 'Bạn có thông báo mới',
    'notification_sound' => 'Âm thanh thông báo',
    'desktop_notifications' => 'Thông báo desktop',
    'enable_notifications' => 'Bật thông báo',
    'disable_notifications' => 'Tắt thông báo',

    // Settings
    'notification_settings' => 'Cài đặt thông báo',
    'email_notifications' => 'Thông báo email',
    'sms_notifications' => 'Thông báo SMS',
    'web_notifications' => 'Thông báo web',
    'notification_frequency' => 'Tần suất thông báo',
    'immediate' => 'Ngay lập tức',
    'daily_digest' => 'Tóm tắt hàng ngày',
    'weekly_digest' => 'Tóm tắt hàng tuần',

    // Templates
    'notification_templates' => 'Mẫu thông báo',
    'create_template' => 'Tạo mẫu',
    'edit_template' => 'Sửa mẫu',
    'template_name' => 'Tên mẫu',
    'template_content' => 'Nội dung mẫu',
    'use_template' => 'Sử dụng mẫu',

    // Bulk actions
    'bulk_actions' => 'Thao tác hàng loạt',
    'select_all' => 'Chọn tất cả',
    'deselect_all' => 'Bỏ chọn tất cả',
    'mark_selected_read' => 'Đánh dấu đã đọc',
    'delete_selected' => 'Xóa đã chọn',
    'selected_count' => 'đã chọn',

    // Confirmations
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa thông báo này?',
    'confirm_delete_all' => 'Bạn có chắc chắn muốn xóa tất cả thông báo?',
    'confirm_mark_all_read' => 'Bạn có chắc chắn muốn đánh dấu tất cả thông báo đã đọc?',

    // Errors
    'error_loading' => 'Lỗi khi tải thông báo',
    'error_sending' => 'Lỗi khi gửi thông báo',
    'error_marking_read' => 'Lỗi khi đánh dấu đã đọc',
    'error_deleting' => 'Lỗi khi xóa thông báo',
    'error_cleanup' => 'Lỗi khi dọn dẹp thông báo',

    // Success messages
    'success_sent' => 'Thông báo đã được gửi thành công',
    'success_marked_read' => 'Đã đánh dấu thông báo đã đọc',
    'success_deleted' => 'Đã xóa thông báo thành công',
    'success_cleanup' => 'Đã dọn dẹp thông báo thành công',

    // Time formats
    'just_now' => 'Vừa xong',
    'minutes_ago' => 'phút trước',
    'hours_ago' => 'giờ trước',
    'days_ago' => 'ngày trước',
    'weeks_ago' => 'tuần trước',
    'months_ago' => 'tháng trước',

    // Buttons
    'save' => 'Lưu',
    'cancel' => 'Hủy',
    'send' => 'Gửi',
    'close' => 'Đóng',
    'ok' => 'OK',
    'yes' => 'Có',
    'no' => 'Không',
];
