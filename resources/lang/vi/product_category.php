<?php

return [
    // General
    'product_category' => 'Danh mục sản phẩm',
    'product_categories' => 'Danh mục sản phẩm',
    'category' => 'Danh mục',
    'categories' => 'Danh mục',
    'category_management' => 'Quản lý danh mục',
    'category_list' => 'Danh sách danh mục',
    'category_details' => 'Chi tiết danh mục',
    'new_category' => 'Danh mục mới',
    'create_category' => 'Tạo danh mục',
    'edit_category' => 'Sửa danh mục',
    'delete_category' => 'Xóa danh mục',

    // Fields
    'name' => 'Tên danh mục',
    'slug' => 'Đường dẫn',
    'description' => 'Mô tả',
    'parent_category' => 'Danh mục cha',
    'parent_id' => 'Danh mục cha',
    'status' => 'Trạng thái',
    'sort_order' => 'Thứ tự sắp xếp',
    'image' => 'Hình ảnh',
    'meta_title' => 'Tiêu đề SEO',
    'meta_description' => 'Mô tả SEO',
    'meta_keywords' => 'Từ khóa SEO',
    'products_count' => 'Số sản phẩm',
    'children_count' => 'Số danh mục con',
    'level' => 'Cấp độ',
    'breadcrumb' => 'Đường dẫn',

    // Status
    'active' => 'Hoạt động',
    'inactive' => 'Không hoạt động',

    // Actions
    'add_category' => 'Thêm danh mục',
    'save_category' => 'Lưu danh mục',
    'update_category' => 'Cập nhật danh mục',
    'view_category' => 'Xem danh mục',
    'edit_category' => 'Sửa danh mục',
    'delete_category' => 'Xóa danh mục',
    'duplicate_category' => 'Nhân bản danh mục',
    'move_category' => 'Di chuyển danh mục',
    'sort_categories' => 'Sắp xếp danh mục',

    // Filters
    'filter_by_status' => 'Lọc theo trạng thái',
    'filter_by_parent' => 'Lọc theo danh mục cha',
    'all_categories' => 'Tất cả danh mục',
    'root_categories' => 'Danh mục gốc',
    'child_categories' => 'Danh mục con',

    // Placeholders
    'enter_name' => 'Nhập tên danh mục',
    'enter_slug' => 'Nhập đường dẫn (tự động tạo nếu để trống)',
    'enter_description' => 'Nhập mô tả danh mục',
    'select_parent' => 'Chọn danh mục cha',
    'select_category' => 'Chọn danh mục',
    'select_status' => 'Chọn trạng thái',
    'enter_sort_order' => 'Nhập thứ tự sắp xếp',
    'choose_image' => 'Chọn hình ảnh',
    'enter_meta_title' => 'Nhập tiêu đề SEO',
    'enter_meta_description' => 'Nhập mô tả SEO',
    'enter_meta_keywords' => 'Nhập từ khóa SEO',
    'search_categories' => 'Tìm kiếm danh mục...',

    // Messages
    'no_categories' => 'Không có danh mục nào',
    'category_created' => 'Danh mục đã được tạo thành công',
    'category_updated' => 'Danh mục đã được cập nhật thành công',
    'category_deleted' => 'Danh mục đã được xóa thành công',
    'categories_sorted' => 'Danh mục đã được sắp xếp thành công',
    'category_duplicated' => 'Danh mục đã được nhân bản thành công',
    'category_moved' => 'Danh mục đã được di chuyển thành công',

    // Validation
    'name_required' => 'Tên danh mục là bắt buộc',
    'name_max' => 'Tên danh mục không được vượt quá 255 ký tự',
    'slug_unique' => 'Đường dẫn đã tồn tại',
    'slug_max' => 'Đường dẫn không được vượt quá 255 ký tự',
    'parent_not_found' => 'Danh mục cha không tồn tại',
    'status_required' => 'Trạng thái là bắt buộc',
    'sort_order_integer' => 'Thứ tự sắp xếp phải là số nguyên',
    'sort_order_min' => 'Thứ tự sắp xếp phải lớn hơn hoặc bằng 0',
    'image_invalid' => 'File phải là hình ảnh',
    'image_too_large' => 'Kích thước hình ảnh không được vượt quá 2MB',
    'meta_title_max' => 'Tiêu đề SEO không được vượt quá 255 ký tự',
    'meta_description_max' => 'Mô tả SEO không được vượt quá 500 ký tự',
    'meta_keywords_max' => 'Từ khóa SEO không được vượt quá 255 ký tự',

    // Errors
    'validation_failed' => 'Dữ liệu không hợp lệ',
    'create_failed' => 'Lỗi khi tạo danh mục',
    'update_failed' => 'Lỗi khi cập nhật danh mục',
    'delete_failed' => 'Lỗi khi xóa danh mục',
    'has_products' => 'Không thể xóa danh mục có sản phẩm',
    'has_children' => 'Không thể xóa danh mục có danh mục con',
    'not_found' => 'Không tìm thấy danh mục',
    'cannot_be_parent_of_itself' => 'Danh mục không thể là cha của chính nó',
    'cannot_move_to_child' => 'Không thể di chuyển danh mục vào danh mục con của nó',

    // Success messages
    'created_successfully' => 'Danh mục đã được tạo thành công',
    'updated_successfully' => 'Danh mục đã được cập nhật thành công',
    'deleted_successfully' => 'Danh mục đã được xóa thành công',
    'sorted_successfully' => 'Danh mục đã được sắp xếp thành công',

    // Confirmations
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa danh mục này?',
    'confirm_delete_with_products' => 'Danh mục này có {count} sản phẩm. Bạn có chắc chắn muốn xóa?',
    'confirm_delete_with_children' => 'Danh mục này có {count} danh mục con. Bạn có chắc chắn muốn xóa?',
    'confirm_status_change' => 'Bạn có chắc chắn muốn thay đổi trạng thái danh mục này?',

    // Tooltips
    'name_tooltip' => 'Tên hiển thị của danh mục',
    'slug_tooltip' => 'Đường dẫn thân thiện SEO (tự động tạo từ tên nếu để trống)',
    'parent_tooltip' => 'Chọn danh mục cha để tạo cấu trúc phân cấp',
    'status_tooltip' => 'Chỉ danh mục hoạt động mới hiển thị trên website',
    'sort_order_tooltip' => 'Số thứ tự để sắp xếp danh mục (số nhỏ hơn sẽ hiển thị trước)',
    'image_tooltip' => 'Hình ảnh đại diện cho danh mục',
    'meta_title_tooltip' => 'Tiêu đề hiển thị trên kết quả tìm kiếm',
    'meta_description_tooltip' => 'Mô tả hiển thị trên kết quả tìm kiếm',
    'meta_keywords_tooltip' => 'Từ khóa giúp tối ưu SEO',

    // Hierarchy
    'root_category' => 'Danh mục gốc',
    'subcategory' => 'Danh mục con',
    'subcategories' => 'Danh mục con',
    'parent_categories' => 'Danh mục cha',
    'child_categories' => 'Danh mục con',
    'category_tree' => 'Cây danh mục',
    'category_hierarchy' => 'Phân cấp danh mục',
    'move_up' => 'Di chuyển lên',
    'move_down' => 'Di chuyển xuống',
    'indent' => 'Thụt vào',
    'outdent' => 'Thụt ra',

    // Statistics
    'total_categories' => 'Tổng số danh mục',
    'active_categories' => 'Danh mục hoạt động',
    'inactive_categories' => 'Danh mục không hoạt động',
    'categories_with_products' => 'Danh mục có sản phẩm',
    'empty_categories' => 'Danh mục trống',

    // Import/Export
    'import_categories' => 'Nhập danh mục',
    'export_categories' => 'Xuất danh mục',
    'import_template' => 'Tải template nhập',
    'export_selected' => 'Xuất danh mục đã chọn',

    // Bulk actions
    'bulk_actions' => 'Thao tác hàng loạt',
    'select_all' => 'Chọn tất cả',
    'deselect_all' => 'Bỏ chọn tất cả',
    'bulk_activate' => 'Kích hoạt',
    'bulk_deactivate' => 'Vô hiệu hóa',
    'bulk_delete' => 'Xóa hàng loạt',
    'selected_count' => 'đã chọn',

    // SEO
    'seo_settings' => 'Cài đặt SEO',
    'seo_preview' => 'Xem trước SEO',
    'seo_title' => 'Tiêu đề SEO',
    'seo_description' => 'Mô tả SEO',
    'seo_keywords' => 'Từ khóa SEO',
    'seo_url' => 'URL SEO',

    // Advanced
    'advanced_settings' => 'Cài đặt nâng cao',
    'category_template' => 'Template danh mục',
    'custom_fields' => 'Trường tùy chỉnh',
    'category_attributes' => 'Thuộc tính danh mục',
    'display_settings' => 'Cài đặt hiển thị',
    'layout_settings' => 'Cài đặt bố cục',

    // Navigation
    'breadcrumb_navigation' => 'Điều hướng breadcrumb',
    'category_menu' => 'Menu danh mục',
    'category_sidebar' => 'Sidebar danh mục',
    'category_filter' => 'Bộ lọc danh mục',

    // Display
    'show_in_menu' => 'Hiển thị trong menu',
    'show_on_homepage' => 'Hiển thị trên trang chủ',
    'featured_category' => 'Danh mục nổi bật',
    'category_icon' => 'Icon danh mục',
    'category_color' => 'Màu danh mục',

    // Buttons
    'save' => 'Lưu',
    'save_and_continue' => 'Lưu và tiếp tục',
    'save_and_new' => 'Lưu và tạo mới',
    'cancel' => 'Hủy',
    'back' => 'Quay lại',
    'reset' => 'Đặt lại',
    'preview' => 'Xem trước',
    'publish' => 'Xuất bản',
    'draft' => 'Lưu nháp',
];
