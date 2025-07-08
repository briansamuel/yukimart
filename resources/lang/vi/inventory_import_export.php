<?php

return [
    // General
    'inventory_import_export' => 'Nhập/Xuất tồn kho',
    'import_export_management' => 'Quản lý nhập/xuất tồn kho',
    'import_inventory' => 'Nhập tồn kho',
    'export_inventory' => 'Xuất tồn kho',
    'import_export_history' => 'Lịch sử nhập/xuất',

    // Import
    'import' => 'Nhập',
    'import_file' => 'File nhập',
    'import_data' => 'Nhập dữ liệu',
    'import_options' => 'Tùy chọn nhập',
    'import_template' => 'Template nhập',
    'download_template' => 'Tải template',
    'upload_file' => 'Tải file lên',
    'select_file' => 'Chọn file',
    'file_format' => 'Định dạng file',
    'supported_formats' => 'Định dạng hỗ trợ: Excel (.xlsx, .xls), CSV',
    'max_file_size' => 'Kích thước tối đa: 10MB',

    // Export
    'export' => 'Xuất',
    'export_data' => 'Xuất dữ liệu',
    'export_options' => 'Tùy chọn xuất',
    'export_format' => 'Định dạng xuất',
    'export_filters' => 'Bộ lọc xuất',

    // Options
    'allow_negative_inventory' => 'Cho phép tồn kho âm',
    'create_missing_products' => 'Tạo sản phẩm mới nếu không tồn tại',
    'create_missing_warehouses' => 'Tạo kho mới nếu không tồn tại',
    'update_product_cost' => 'Cập nhật giá vốn sản phẩm',
    'low_stock_only' => 'Chỉ sản phẩm sắp hết hàng',
    'out_of_stock_only' => 'Chỉ sản phẩm hết hàng',

    // Filters
    'warehouse' => 'Kho',
    'select_warehouse' => 'Chọn kho',
    'all_warehouses' => 'Tất cả kho',
    'product_category' => 'Danh mục sản phẩm',
    'select_category' => 'Chọn danh mục',
    'all_categories' => 'Tất cả danh mục',
    'date_range' => 'Khoảng thời gian',
    'from_date' => 'Từ ngày',
    'to_date' => 'Đến ngày',

    // Fields
    'product_sku' => 'Mã SKU',
    'product_name' => 'Tên sản phẩm',
    'warehouse_code' => 'Mã kho',
    'warehouse_name' => 'Tên kho',
    'current_quantity' => 'Tồn kho hiện tại',
    'adjustment_quantity' => 'Số lượng điều chỉnh',
    'adjustment_type' => 'Loại điều chỉnh',
    'unit_cost' => 'Đơn giá',
    'total_cost' => 'Tổng tiền',
    'reason' => 'Lý do',
    'reference_number' => 'Số tham chiếu',
    'notes' => 'Ghi chú',
    'transaction_date' => 'Ngày giao dịch',

    // Adjustment Types
    'import_type' => 'Nhập kho',
    'export_type' => 'Xuất kho',
    'adjustment_type_label' => 'Điều chỉnh',

    // Status
    'in_stock' => 'Còn hàng',
    'low_stock' => 'Sắp hết hàng',
    'out_of_stock' => 'Hết hàng',
    'sufficient_stock' => 'Đủ hàng',

    // Actions
    'start_import' => 'Bắt đầu nhập',
    'start_export' => 'Bắt đầu xuất',
    'validate_file' => 'Kiểm tra file',
    'process_import' => 'Xử lý nhập',
    'download_export' => 'Tải xuống',
    'view_history' => 'Xem lịch sử',
    'refresh_data' => 'Làm mới dữ liệu',

    // Results
    'import_results' => 'Kết quả nhập',
    'export_results' => 'Kết quả xuất',
    'total_rows' => 'Tổng số dòng',
    'processed_rows' => 'Đã xử lý',
    'skipped_rows' => 'Bỏ qua',
    'error_rows' => 'Lỗi',
    'success_rate' => 'Tỷ lệ thành công',

    // Messages
    'import_success' => 'Nhập dữ liệu thành công',
    'import_completed' => 'Hoàn thành nhập dữ liệu',
    'export_success' => 'Xuất dữ liệu thành công',
    'export_completed' => 'Hoàn thành xuất dữ liệu',
    'file_uploaded' => 'File đã được tải lên',
    'file_validated' => 'File hợp lệ',
    'template_downloaded' => 'Template đã được tải xuống',
    'processing_file' => 'Đang xử lý file...',
    'preparing_export' => 'Đang chuẩn bị xuất dữ liệu...',

    // Errors
    'file_required' => 'Vui lòng chọn file để nhập',
    'file_invalid' => 'File không hợp lệ',
    'file_too_large' => 'File quá lớn (tối đa 10MB)',
    'unsupported_format' => 'Định dạng file không được hỗ trợ',
    'import_failed' => 'Nhập dữ liệu thất bại',
    'export_failed' => 'Xuất dữ liệu thất bại',
    'no_data_to_export' => 'Không có dữ liệu để xuất',
    'product_not_found' => 'Không tìm thấy sản phẩm',
    'warehouse_not_found' => 'Không tìm thấy kho',
    'invalid_quantity' => 'Số lượng không hợp lệ',
    'negative_inventory_not_allowed' => 'Không cho phép tồn kho âm',

    // Validation
    'sku_required' => 'Mã SKU là bắt buộc',
    'warehouse_code_required' => 'Mã kho là bắt buộc',
    'quantity_required' => 'Số lượng là bắt buộc',
    'quantity_numeric' => 'Số lượng phải là số',
    'adjustment_type_required' => 'Loại điều chỉnh là bắt buộc',
    'adjustment_type_invalid' => 'Loại điều chỉnh không hợp lệ',
    'unit_cost_numeric' => 'Đơn giá phải là số',
    'total_cost_numeric' => 'Tổng tiền phải là số',

    // Statistics
    'inventory_statistics' => 'Thống kê tồn kho',
    'total_products' => 'Tổng sản phẩm',
    'total_value' => 'Tổng giá trị',
    'low_stock_count' => 'Sắp hết hàng',
    'out_of_stock_count' => 'Hết hàng',
    'in_stock_count' => 'Còn hàng',

    // History
    'transaction_history' => 'Lịch sử giao dịch',
    'import_history' => 'Lịch sử nhập',
    'export_history' => 'Lịch sử xuất',
    'created_by' => 'Người tạo',
    'old_quantity' => 'Tồn kho cũ',
    'new_quantity' => 'Tồn kho mới',
    'quantity_change' => 'Thay đổi',

    // Template Instructions
    'template_instructions' => 'Hướng dẫn sử dụng template',
    'required_fields' => 'Trường bắt buộc',
    'optional_fields' => 'Trường tùy chọn',
    'field_descriptions' => 'Mô tả các trường',
    'important_notes' => 'Lưu ý quan trọng',
    'example_data' => 'Dữ liệu mẫu',

    // Progress
    'uploading' => 'Đang tải lên...',
    'processing' => 'Đang xử lý...',
    'validating' => 'Đang kiểm tra...',
    'importing' => 'Đang nhập...',
    'exporting' => 'Đang xuất...',
    'completed' => 'Hoàn thành',
    'failed' => 'Thất bại',

    // Buttons
    'browse' => 'Duyệt',
    'upload' => 'Tải lên',
    'download' => 'Tải xuống',
    'import_now' => 'Nhập ngay',
    'export_now' => 'Xuất ngay',
    'cancel' => 'Hủy',
    'close' => 'Đóng',
    'retry' => 'Thử lại',
    'continue' => 'Tiếp tục',

    // Tabs
    'import_tab' => 'Nhập dữ liệu',
    'export_tab' => 'Xuất dữ liệu',
    'history_tab' => 'Lịch sử',
    'statistics_tab' => 'Thống kê',

    // Tooltips
    'import_tooltip' => 'Nhập dữ liệu tồn kho từ file Excel hoặc CSV',
    'export_tooltip' => 'Xuất dữ liệu tồn kho ra file Excel',
    'template_tooltip' => 'Tải template mẫu để nhập dữ liệu',
    'history_tooltip' => 'Xem lịch sử các lần nhập/xuất dữ liệu',
    'negative_inventory_tooltip' => 'Cho phép tồn kho có giá trị âm',
    'create_products_tooltip' => 'Tự động tạo sản phẩm mới nếu không tìm thấy',
    'update_cost_tooltip' => 'Cập nhật giá vốn sản phẩm từ dữ liệu nhập',

    // Confirmations
    'confirm_import' => 'Bạn có chắc chắn muốn nhập dữ liệu này?',
    'confirm_export' => 'Bạn có chắc chắn muốn xuất dữ liệu?',
    'confirm_overwrite' => 'Dữ liệu sẽ được ghi đè. Bạn có chắc chắn?',

    // File Info
    'file_name' => 'Tên file',
    'file_size' => 'Kích thước',
    'file_type' => 'Loại file',
    'upload_time' => 'Thời gian tải lên',
    'rows_count' => 'Số dòng',

    // Summary
    'import_summary' => 'Tóm tắt nhập',
    'export_summary' => 'Tóm tắt xuất',
    'before_import' => 'Trước khi nhập',
    'after_import' => 'Sau khi nhập',
    'changes_made' => 'Thay đổi đã thực hiện',
];
