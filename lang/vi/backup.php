<?php

return [
    // General
    'backup' => 'Sao lưu',
    'backups' => 'Sao lưu',
    'backup_restore' => 'Sao lưu & Khôi phục',
    'backup_management' => 'Quản lý sao lưu',
    'restore' => 'Khôi phục',
    'restore_management' => 'Quản lý khôi phục',

    // Actions
    'create_backup' => 'Tạo sao lưu',
    'new_backup' => 'Sao lưu mới',
    'restore_backup' => 'Khôi phục sao lưu',
    'download_backup' => 'Tải xuống sao lưu',
    'delete_backup' => 'Xóa sao lưu',
    'validate_backup' => 'Kiểm tra sao lưu',
    'cleanup_backups' => 'Dọn dẹp sao lưu',
    'upload_backup' => 'Tải lên sao lưu',

    // Types
    'full_backup' => 'Sao lưu đầy đủ',
    'database_backup' => 'Sao lưu cơ sở dữ liệu',
    'files_backup' => 'Sao lưu tệp tin',
    'manual_backup' => 'Sao lưu thủ công',
    'automatic_backup' => 'Sao lưu tự động',
    'scheduled_backup' => 'Sao lưu theo lịch',

    // Options
    'backup_options' => 'Tùy chọn sao lưu',
    'restore_options' => 'Tùy chọn khôi phục',
    'include_database' => 'Bao gồm cơ sở dữ liệu',
    'include_files' => 'Bao gồm tệp tin',
    'compress_backup' => 'Nén sao lưu',
    'restore_database' => 'Khôi phục cơ sở dữ liệu',
    'restore_files' => 'Khôi phục tệp tin',

    // Fields
    'backup_name' => 'Tên sao lưu',
    'backup_size' => 'Kích thước',
    'backup_date' => 'Ngày sao lưu',
    'backup_type' => 'Loại sao lưu',
    'backup_status' => 'Trạng thái',
    'restore_date' => 'Ngày khôi phục',
    'created_by' => 'Người tạo',
    'file_count' => 'Số tệp tin',
    'description' => 'Mô tả',

    // Status
    'completed' => 'Hoàn thành',
    'in_progress' => 'Đang thực hiện',
    'failed' => 'Thất bại',
    'pending' => 'Đang chờ',
    'cancelled' => 'Đã hủy',
    'corrupted' => 'Bị hỏng',
    'valid' => 'Hợp lệ',
    'invalid' => 'Không hợp lệ',

    // Messages
    'backup_created' => 'Sao lưu đã được tạo thành công',
    'backup_failed' => 'Tạo sao lưu thất bại',
    'backup_deleted' => 'Sao lưu đã được xóa thành công',
    'backup_restored' => 'Khôi phục thành công',
    'restore_failed' => 'Khôi phục thất bại',
    'backup_validated' => 'Sao lưu hợp lệ',
    'backup_invalid' => 'Sao lưu không hợp lệ',
    'cleanup_completed' => 'Dọn dẹp hoàn thành',
    'no_backups' => 'Không có sao lưu nào',
    'backup_in_progress' => 'Đang tạo sao lưu...',
    'restore_in_progress' => 'Đang khôi phục...',

    // Warnings
    'restore_warning' => 'Cảnh báo: Khôi phục sẽ ghi đè dữ liệu hiện tại',
    'backup_large_warning' => 'Sao lưu có kích thước lớn, có thể mất nhiều thời gian',
    'old_backup_warning' => 'Sao lưu này đã cũ, có thể không tương thích',
    'delete_warning' => 'Bạn có chắc chắn muốn xóa sao lưu này?',

    // Validation
    'name_required' => 'Tên sao lưu là bắt buộc',
    'name_invalid' => 'Tên sao lưu chỉ được chứa chữ cái, số, dấu gạch dưới và dấu gạch ngang',
    'backup_not_found' => 'Không tìm thấy sao lưu',
    'confirm_required' => 'Bạn phải xác nhận để thực hiện thao tác này',
    'file_required' => 'Vui lòng chọn tệp sao lưu',
    'file_invalid' => 'Tệp sao lưu không hợp lệ',
    'file_too_large' => 'Tệp quá lớn',

    // Statistics
    'total_backups' => 'Tổng số sao lưu',
    'total_size' => 'Tổng kích thước',
    'latest_backup' => 'Sao lưu mới nhất',
    'oldest_backup' => 'Sao lưu cũ nhất',
    'backup_frequency' => 'Tần suất sao lưu',
    'success_rate' => 'Tỷ lệ thành công',
    'average_size' => 'Kích thước trung bình',

    // History
    'backup_history' => 'Lịch sử sao lưu',
    'restore_history' => 'Lịch sử khôi phục',
    'recent_backups' => 'Sao lưu gần đây',
    'recent_restores' => 'Khôi phục gần đây',

    // Schedule
    'schedule_backup' => 'Lên lịch sao lưu',
    'backup_schedule' => 'Lịch sao lưu',
    'daily' => 'Hàng ngày',
    'weekly' => 'Hàng tuần',
    'monthly' => 'Hàng tháng',
    'custom' => 'Tùy chỉnh',

    // Storage
    'storage_location' => 'Vị trí lưu trữ',
    'local_storage' => 'Lưu trữ cục bộ',
    'cloud_storage' => 'Lưu trữ đám mây',
    'external_storage' => 'Lưu trữ ngoài',
    'storage_usage' => 'Sử dụng lưu trữ',
    'available_space' => 'Dung lượng khả dụng',

    // Cleanup
    'cleanup_old_backups' => 'Dọn dẹp sao lưu cũ',
    'keep_days' => 'Giữ lại (ngày)',
    'keep_count' => 'Giữ lại (số lượng)',
    'cleanup_policy' => 'Chính sách dọn dẹp',
    'auto_cleanup' => 'Tự động dọn dẹp',

    // Errors
    'backup_error' => 'Lỗi sao lưu',
    'restore_error' => 'Lỗi khôi phục',
    'file_error' => 'Lỗi tệp tin',
    'database_error' => 'Lỗi cơ sở dữ liệu',
    'permission_error' => 'Lỗi quyền truy cập',
    'disk_space_error' => 'Không đủ dung lượng đĩa',
    'network_error' => 'Lỗi mạng',

    // Progress
    'preparing' => 'Đang chuẩn bị...',
    'backing_up_database' => 'Đang sao lưu cơ sở dữ liệu...',
    'backing_up_files' => 'Đang sao lưu tệp tin...',
    'compressing' => 'Đang nén...',
    'uploading' => 'Đang tải lên...',
    'downloading' => 'Đang tải xuống...',
    'extracting' => 'Đang giải nén...',
    'restoring_database' => 'Đang khôi phục cơ sở dữ liệu...',
    'restoring_files' => 'Đang khôi phục tệp tin...',
    'finalizing' => 'Đang hoàn thiện...',

    // File types
    'database_files' => 'Tệp cơ sở dữ liệu',
    'config_files' => 'Tệp cấu hình',
    'upload_files' => 'Tệp tải lên',
    'log_files' => 'Tệp nhật ký',
    'cache_files' => 'Tệp cache',
    'temp_files' => 'Tệp tạm',

    // Buttons
    'create' => 'Tạo',
    'restore' => 'Khôi phục',
    'download' => 'Tải xuống',
    'delete' => 'Xóa',
    'validate' => 'Kiểm tra',
    'cleanup' => 'Dọn dẹp',
    'upload' => 'Tải lên',
    'cancel' => 'Hủy',
    'confirm' => 'Xác nhận',
    'close' => 'Đóng',
    'refresh' => 'Làm mới',

    // Tooltips
    'create_tooltip' => 'Tạo sao lưu mới cho hệ thống',
    'restore_tooltip' => 'Khôi phục hệ thống từ sao lưu',
    'download_tooltip' => 'Tải xuống tệp sao lưu',
    'delete_tooltip' => 'Xóa sao lưu khỏi hệ thống',
    'validate_tooltip' => 'Kiểm tra tính toàn vẹn của sao lưu',
    'cleanup_tooltip' => 'Xóa các sao lưu cũ để tiết kiệm dung lượng',

    // Placeholders
    'enter_backup_name' => 'Nhập tên sao lưu',
    'enter_description' => 'Nhập mô tả',
    'select_files' => 'Chọn tệp tin',
    'choose_backup_file' => 'Chọn tệp sao lưu',

    // Time
    'time_ago' => 'trước',
    'just_now' => 'vừa xong',
    'minutes_ago' => 'phút trước',
    'hours_ago' => 'giờ trước',
    'days_ago' => 'ngày trước',
    'weeks_ago' => 'tuần trước',
    'months_ago' => 'tháng trước',

    // Size units
    'bytes' => 'Bytes',
    'kb' => 'KB',
    'mb' => 'MB',
    'gb' => 'GB',
    'tb' => 'TB',

    // Tabs
    'backup_tab' => 'Sao lưu',
    'restore_tab' => 'Khôi phục',
    'history_tab' => 'Lịch sử',
    'settings_tab' => 'Cài đặt',
    'statistics_tab' => 'Thống kê',

    // Settings
    'backup_settings' => 'Cài đặt sao lưu',
    'auto_backup_enabled' => 'Bật sao lưu tự động',
    'backup_retention' => 'Thời gian lưu trữ',
    'compression_level' => 'Mức độ nén',
    'notification_settings' => 'Cài đặt thông báo',
    'email_notifications' => 'Thông báo email',

    // Advanced
    'advanced_options' => 'Tùy chọn nâng cao',
    'exclude_files' => 'Loại trừ tệp tin',
    'include_logs' => 'Bao gồm nhật ký',
    'verify_backup' => 'Xác minh sao lưu',
    'encryption' => 'Mã hóa',
    'password_protection' => 'Bảo vệ mật khẩu',

    // Notifications
    'backup_completed_notification' => 'Sao lưu hoàn thành',
    'backup_failed_notification' => 'Sao lưu thất bại',
    'restore_completed_notification' => 'Khôi phục hoàn thành',
    'restore_failed_notification' => 'Khôi phục thất bại',
    'cleanup_completed_notification' => 'Dọn dẹp hoàn thành',
];
