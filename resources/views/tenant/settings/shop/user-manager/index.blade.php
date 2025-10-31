@extends('admin.layouts.tenant-app')

@section('title', 'Quản lý người dùng')

@section('style')
<link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/css/table-loading.css') }}" rel="stylesheet" type="text/css" />
<style>
    /* Toolbar styling */
    #kt_users_table_toolbar {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        position: relative;
    }

    /* Border tĩnh full width */
    #kt_users_table_toolbar::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #e4e6ef;
        z-index: 1;
    }

    /* Border động theo tab */
    .nav-line-tabs .nav-link {
        position: relative;
        padding-bottom: 1rem;
    }

    .nav-line-tabs .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background-color: transparent;
        z-index: 2;
        transition: background-color 0.3s ease;
    }

    .nav-line-tabs .nav-link:hover::after,
    .nav-line-tabs .nav-link.active::after {
        background-color: #009ef7;
    }

    /* Button hover effects */
    .btn-light:hover {
        background-color: #f5f8fa !important;
        border-color: #e4e6ef !important;
    }
</style>
@endsection

@section('content')
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
     <!--begin::Toolbar-->
    @include('tenant.settings.elements.toolbar')
    <!--end::Toolbar-->
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="d-flex flex-column flex-lg-row">
            <!--begin::Sidebar-->
            <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-250px mb-10 order-1 order-lg-1" id="kt_users_sidebar">
                @include('tenant.settings.elements.sidebar')
            </div>
            <!--end::Sidebar-->

            <!--begin::Filter Panel (Hidden by default)-->
            <div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-250px mb-10 order-1 order-lg-1" id="kt_users_filter_panel" style="display: none;">
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header p-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Bộ lọc</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" id="kt_users_filter_close">
                                <i class="fas fa-times fs-3"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body p-5">
                        <!--begin::Search-->
                        <div class="mb-7">
                            <label class="form-label fw-bold text-gray-700">Tìm kiếm</label>
                            <div class="position-relative">
                                <i class="fas fa-search fs-3 position-absolute ms-4 mt-3 text-gray-500"></i>
                                <input type="text" id="filter_search" class="form-control form-control-solid ps-12" placeholder="Tên, email, số điện thoại..." />
                            </div>
                        </div>
                        <!--end::Search-->

                        <!--begin::Branch filter-->
                        <div class="mb-7">
                            <label class="form-label fw-bold text-gray-700">Chi nhánh</label>
                            <select id="filter_branch" class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn chi nhánh" data-allow-clear="true">
                                <option value="">Tất cả chi nhánh</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        <!--end::Branch filter-->

                        <!--begin::Role filter-->
                        <div class="mb-7">
                            <label class="form-label fw-bold text-gray-700">Vai trò</label>
                            <select id="filter_role" class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn vai trò" data-allow-clear="true" multiple>
                                <option value="">Chọn vai trò</option>
                                <!-- Will be populated dynamically via AJAX -->
                            </select>
                        </div>
                        <!--end::Role filter-->

                        <!--begin::Status filter-->
                        <div class="mb-7">
                            <label class="form-label fw-bold text-gray-700 mb-3">Trạng thái</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-primary filter-status-btn rounded-pill" data-status="all">
                                    Tất cả
                                </button>
                                <button type="button" class="btn btn-sm btn-light filter-status-btn rounded-pill" data-status="active">
                                    Đang hoạt động
                                </button>
                                <button type="button" class="btn btn-sm btn-light filter-status-btn rounded-pill" data-status="inactive">
                                    Ngừng hoạt động
                                </button>
                            </div>
                        </div>
                        <!--end::Status filter-->
                    </div>
                    <!--end::Card body-->

                    <!--begin::Card footer-->
                    <div class="card-footer p-5">
                        <div class="d-flex justify-content-between gap-2">
                            <button type="button" class="btn btn-sm btn-light-primary" id="kt_users_filter_reset">
                                Đặt lại
                            </button>
                            <button type="button" class="btn btn-sm btn-light" id="kt_users_filter_close_btn">
                                Đóng
                            </button>
                        </div>
                    </div>
                    <!--end::Card footer-->
                </div>
            </div>
            <!--end::Filter Panel-->

            <!--begin::Content-->
            <div class="flex-lg-row-fluid ms-lg-10 order-2 order-lg-2">
                <div class="d-flex flex-column gap-7 gap-lg-10">
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div id="kt_users_table_toolbar" class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Tabs-->
                                <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_users">Tài khoản người dùng</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_roles">Quản lý vai trò</a>
                                    </li>
                                </ul>
                                <!--end::Tabs-->
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Tab content-->
                            <div class="tab-content" id="mainTabContent">
                                <!--begin::Tab pane - Tài khoản người dùng-->
                                <div class="tab-pane fade show active" id="kt_tab_users" role="tabpanel">
                                    <!--begin::Toolbar-->
                                    <div class="d-flex justify-content-between gap-2 mb-5">
                                        <!--begin::Filter button-->
                                        <button type="button" class="btn btn-sm btn-light" id="kt_users_filter_btn">
                                            <i class="fas fa-filter fs-6"></i>
                                            Lọc
                                        </button>
                                        <!--end::Filter button-->

                                        <!--begin::Right buttons-->
                                        <div class="d-flex gap-2">
                                            <!--begin::Add user-->
                                            <button type="button" class="btn btn-sm btn-light" id="kt_users_add_btn">
                                                <i class="fas fa-plus fs-6"></i>
                                                Tạo tài khoản
                                            </button>
                                            <!--end::Add user-->

                                            <!--begin::More options-->
                                            <button type="button" class="btn btn-sm btn-light btn-icon" id="kt_users_more_btn">
                                                <i class="fas fa-ellipsis-h fs-6"></i>
                                            </button>
                                            <!--end::More options-->
                                        </div>
                                        <!--end::Right buttons-->
                                    </div>
                                    <!--end::Toolbar-->
                            <!--begin::Table container-->
                            <div id="kt_users_table_container" class="table-responsive">
                                <!--begin::Table-->
                                <table id="kt_users_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" id="select_all_users" />
                                                </div>
                                            </th>
                                            <th class="min-w-150px">Họ tên</th>
                                            <th class="min-w-150px">Email</th>
                                            <th class="min-w-100px">Số điện thoại</th>
                                            <th class="min-w-100px">Vai trò</th>
                                            <th class="min-w-100px">Trạng thái</th>
                                            <th class="min-w-100px">Ngày tạo</th>
                                            <th class="text-end min-w-70px">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="fw-semibold text-gray-600">
                                        <tr>
                                            <td colspan="8" class="text-center">Đang tải...</td>
                                        </tr>
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table container-->

                            <!--begin::Pagination-->
                            <div id="kt_users_pagination" class="d-flex justify-content-between align-items-center mt-5">
                                <div class="text-gray-600">
                                    Hiển thị <span id="showing_from">0</span> - <span id="showing_to">0</span>
                                    trong tổng số <span id="total_records">0</span> người dùng
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <select id="per_page_select" class="form-select form-select-sm w-auto">
                                        <option value="10">10 / trang</option>
                                        <option value="25">25 / trang</option>
                                        <option value="50">50 / trang</option>
                                        <option value="100">100 / trang</option>
                                    </select>
                                    <ul id="pagination_links" class="pagination mb-0">
                                        <!-- Pagination will be rendered here -->
                                    </ul>
                                </div>
                            </div>
                            <!--end::Pagination-->
                                </div>
                                <!--end::Tab pane - Tài khoản người dùng-->

                                <!--begin::Tab pane - Quản lý vai trò-->
                                <div class="tab-pane fade" id="kt_tab_roles" role="tabpanel">
                                    <!--begin::Toolbar-->
                                    <div class="d-flex justify-content-end gap-2 mb-5">
                                        <!--begin::Add role-->
                                        <button type="button" class="btn btn-sm btn-light" id="kt_roles_add_btn">
                                            <i class="fas fa-plus fs-6"></i>
                                            Tạo vai trò
                                        </button>
                                        <!--end::Add role-->
                                    </div>
                                    <!--end::Toolbar-->

                                    <!--begin::Roles table-->
                                    <div class="table-responsive">
                                        <table id="kt_roles_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                    <th class="min-w-150px">Vai trò</th>
                                                    <th class="min-w-200px">Mô tả</th>
                                                    <th class="min-w-100px">Tài khoản</th>
                                                    <th class="text-end min-w-70px">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody id="kt_roles_table_body" class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td colspan="4" class="text-center">Đang tải...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--end::Roles table-->

                                    <!--begin::Pagination-->
                                    <div id="kt_roles_pagination" class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                                        <div class="d-flex align-items-center">
                                            <span>Hiển thị <span id="roles_from">0</span>-<span id="roles_to">0</span> trong tổng số <span id="roles_total">0</span> vai trò</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <select id="roles_per_page" class="form-select form-select-sm w-auto">
                                                <option value="10" selected>10 / trang</option>
                                                <option value="25">25 / trang</option>
                                                <option value="50">50 / trang</option>
                                                <option value="100">100 / trang</option>
                                            </select>
                                            <ul id="roles_pagination_links" class="pagination mb-0"></ul>
                                        </div>
                                    </div>
                                    <!--end::Pagination-->
                                </div>
                                <!--end::Tab pane - Quản lý vai trò-->
                            </div>
                            <!--end::Tab content-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
            <!--end::Content-->
        </div>
    </div>
    <!--end::Content container-->
</div>
<!--end::Content wrapper-->
@endsection

@section('scripts')
<script>
// User routes configuration
window.userRoutes = {
    data: '{{ route('admin.settings.user-manager.data') }}',
    roles: '{{ route('admin.settings.user-manager.roles') }}',
    rolesData: '{{ route('admin.settings.user-manager.roles-data') }}',
    store: '{{ route('admin.settings.user-manager.store') }}'
};

// Global variables for users
let currentPage = 1;
let perPage = 10;
let searchQuery = '';
let filterBranch = '';
let filterRole = [];
let filterStatus = 'all';
let currentRequest = null;
let currentExpandedRow = null;
let searchDebounceTimer = null;

// Global variables for roles
let rolesCurrentPage = 1;
let rolesPerPage = 10;
let rolesCurrentRequest = null;

// Global variables for role modal
let roleModalMode = 'create'; // 'create', 'edit', 'copy'
let roleModalId = null; // Role ID when editing/copying

$(document).ready(function() {
    // Initialize menu
    KTMenu.createInstances();

    // Initialize Select2 for roles with AJAX
    initRoleSelect2();

    // Load saved filter state
    loadFilterState();

    // Load initial data
    loadUsers();

    // Filter panel toggle
    $('#kt_users_filter_btn').on('click', function() {
        $('#kt_users_sidebar').toggle();
        $('#kt_users_filter_panel').toggle();
    });

    // Close filter panel (X button)
    $('#kt_users_filter_close').on('click', function() {
        $('#kt_users_filter_panel').hide();
        $('#kt_users_sidebar').show();
    });

    // Close filter panel (Đóng button)
    $('#kt_users_filter_close_btn').on('click', function() {
        $('#kt_users_filter_panel').hide();
        $('#kt_users_sidebar').show();
    });

    // Filter search with debounce
    $('#filter_search').on('keyup', function() {
        clearTimeout(searchDebounceTimer);
        searchQuery = $(this).val();
        searchDebounceTimer = setTimeout(function() {
            currentPage = 1;
            saveFilterState();
            loadUsers();
        }, 500);
    });

    // Filter branch change - auto load
    $('#filter_branch').on('change', function() {
        filterBranch = $(this).val();
        currentPage = 1;
        saveFilterState();
        loadUsers();
    });

    // Filter role change - auto load
    $('#filter_role').on('change', function() {
        filterRole = $(this).val() || [];
        currentPage = 1;
        saveFilterState();
        loadUsers();
    });

    // Filter status buttons
    $(document).on('click', '.filter-status-btn', function() {
        const status = $(this).data('status');

        // Update button states
        $('.filter-status-btn').removeClass('btn-primary').addClass('btn-light');
        $(this).removeClass('btn-light').addClass('btn-primary');

        // Update filter value
        filterStatus = status;
        currentPage = 1;
        saveFilterState();
        loadUsers();
    });

    // Reset filter
    $('#kt_users_filter_reset').on('click', function() {
        $('#filter_search').val('');
        $('#filter_branch').val('').trigger('change');
        $('#filter_role').val(null).trigger('change');

        // Reset status buttons
        $('.filter-status-btn').removeClass('btn-primary').addClass('btn-light');
        $('.filter-status-btn[data-status="all"]').removeClass('btn-light').addClass('btn-primary');

        searchQuery = '';
        filterBranch = '';
        filterRole = [];
        filterStatus = 'all';
        currentPage = 1;

        saveFilterState();
        loadUsers();
    });

    // Per page change
    $('#per_page_select').on('change', function() {
        perPage = $(this).val();
        currentPage = 1;
        loadUsers();
    });

    // Select all checkbox
    $('#select_all_users').on('change', function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Row click to show detail
    $(document).on('click', '#kt_users_table tbody tr', function(e) {
        // Ignore clicks on checkboxes, action buttons, and detail rows
        if ($(e.target).closest('.form-check-input, .btn').length || $(this).hasClass('kt-table-detail-row')) {
            return;
        }

        const userId = $(this).data('user-id');
        if (!userId) return;

        // Toggle detail panel
        toggleUserDetailRow($(this), userId);
    });

    // ============================================
    // DETAIL PANEL ACTION BUTTONS
    // ============================================

    // Edit user button
    $(document).on('click', '.btn-edit-user', function() {
        const userId = $(this).data('user-id');
        openEditUserModal(userId);
    });

    // Change password button
    $(document).on('click', '.btn-change-password', function() {
        const userId = $(this).data('user-id');
        openChangePasswordModal(userId);
    });

    // Deactivate user button
    $(document).on('click', '.btn-deactivate-user', function() {
        const userId = $(this).data('user-id');
        deactivateUser(userId);
    });

    // ============================================
    // MODAL - TẠO TÀI KHOẢN
    // ============================================

    // Open modal when click "Tạo tài khoản" button
    $('#kt_users_add_btn').on('click', function() {
        // Reset form
        $('#kt_modal_add_user_form')[0].reset();

        // Load roles for select2
        loadRolesForModal();

        // Show modal
        $('#kt_modal_add_user').modal('show');
    });

    // Toggle password visibility
    $('#toggle_password').on('click', function() {
        const input = $('#password_input');
        const icon = $('#password_icon');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    $('#toggle_password_confirmation').on('click', function() {
        const input = $('#password_confirmation_input');
        const icon = $('#password_confirmation_icon');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    // Handle form submission
    $('#kt_modal_add_user_form').on('submit', function(e) {
        e.preventDefault();

        const submitButton = $('#kt_modal_add_user_submit');
        const form = $(this);

        // Validate
        if (!validateUserForm(form)) {
            return;
        }

        // Show loading
        submitButton.attr('data-kt-indicator', 'on');
        submitButton.prop('disabled', true);

        // Prepare data
        const formData = {
            full_name: form.find('[name="full_name"]').val(),
            phone: form.find('[name="phone"]').val(),
            email: form.find('[name="email"]').val(),
            username: form.find('[name="username"]').val(),
            password: form.find('[name="password"]').val(),
            password_confirmation: form.find('[name="password_confirmation"]').val(),
            role_id: form.find('[name="role_id"]').val(),
            notify_transactions: form.find('[name="notify_transactions"]').is(':checked') ? 1 : 0,
            notify_daily_reports: form.find('[name="notify_daily_reports"]').is(':checked') ? 1 : 0,
            birthday: form.find('[name="birthday"]').val(),
            address: form.find('[name="address"]').val(),
            district: form.find('[name="district"]').val(),
            ward: form.find('[name="ward"]').val(),
            notes: form.find('[name="notes"]').val()
        };

        // Send AJAX request
        $.ajax({
            url: window.userRoutes.store,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message || 'Tạo tài khoản thành công',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Close modal
                    $('#kt_modal_add_user').modal('hide');

                    // Reload users table
                    loadUsers();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: response.message || 'Có lỗi xảy ra'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Có lỗi xảy ra khi tạo tài khoản';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    html: errorMessage
                });
            },
            complete: function() {
                // Hide loading
                submitButton.removeAttr('data-kt-indicator');
                submitButton.prop('disabled', false);
            }
        });
    });

    // ============================================
    // MODAL - CHỈNH SỬA TÀI KHOẢN
    // ============================================

    // Toggle password visibility for edit user modal
    $('#toggle_edit_password').on('click', function() {
        const input = $('#edit_password');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggle_edit_password_confirmation').on('click', function() {
        const input = $('#edit_password_confirmation');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Handle edit user form submission
    $('#kt_modal_edit_user_form').on('submit', function(e) {
        e.preventDefault();

        const submitButton = $('#kt_modal_edit_user_submit');
        const form = $(this);
        const userId = $('#edit_user_id').val();

        // Show loading
        submitButton.attr('data-kt-indicator', 'on');
        submitButton.prop('disabled', true);

        // Prepare data
        const formData = {
            full_name: form.find('[name="full_name"]').val(),
            username: form.find('[name="username"]').val(),
            phone: form.find('[name="phone"]').val(),
            email: form.find('[name="email"]').val(),
            role_id: form.find('[name="role_id"]').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Add password only if filled
        const password = form.find('[name="password"]').val();
        if (password) {
            formData.password = password;
            formData.password_confirmation = form.find('[name="password_confirmation"]').val();
        }

        // Send AJAX request
        $.ajax({
            url: `/admin/settings/user-manager/${userId}/update`,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Cập nhật tài khoản thành công',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#kt_modal_edit_user').modal('hide');

                        // Check if detail panel is currently open for this user
                        const $detailRow = $(`#user-detail-${userId}`);
                        const wasDetailPanelOpen = $detailRow.length > 0 && $detailRow.is(':visible');

                        // Reload users list
                        loadUsers().then(() => {
                            // If detail panel was open, reopen it after reload
                            if (wasDetailPanelOpen) {
                                setTimeout(() => {
                                    const $row = $(`tr[data-user-id="${userId}"]`).first();
                                    if ($row.length > 0) {
                                        $row.click();
                                    }
                                }, 300);
                            }
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: response.message || 'Có lỗi xảy ra'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Có lỗi xảy ra khi cập nhật tài khoản';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = '<ul class="text-start">';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errorMessage += '<li>' + value[0] + '</li>';
                    });
                    errorMessage += '</ul>';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    html: errorMessage
                });
            },
            complete: function() {
                submitButton.removeAttr('data-kt-indicator');
                submitButton.prop('disabled', false);
            }
        });
    });

    // ============================================
    // MODAL - ĐỔI MẬT KHẨU
    // ============================================

    // Toggle password visibility for change password modal
    $('#toggle_new_password').on('click', function() {
        const input = $('#new_password_input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#toggle_new_password_confirmation').on('click', function() {
        const input = $('#new_password_confirmation_input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Handle change password form submission
    $('#kt_modal_change_password_form').on('submit', function(e) {
        e.preventDefault();

        const submitButton = $('#kt_modal_change_password_submit');
        const form = $(this);
        const userId = $('#change_password_user_id').val();

        // Show loading
        submitButton.attr('data-kt-indicator', 'on');
        submitButton.prop('disabled', true);

        // Prepare data - No old password required
        const formData = {
            new_password: form.find('[name="new_password"]').val(),
            new_password_confirmation: form.find('[name="new_password_confirmation"]').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Send AJAX request
        $.ajax({
            url: `/admin/settings/user-manager/${userId}/change-password`,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đổi mật khẩu thành công',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#kt_modal_change_password').modal('hide');

                        // Note: Password change doesn't affect visible user data,
                        // so no need to reload detail panel or user list
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: response.message || 'Có lỗi xảy ra'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Có lỗi xảy ra khi đổi mật khẩu';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = '<ul class="text-start">';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errorMessage += '<li>' + value[0] + '</li>';
                    });
                    errorMessage += '</ul>';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    html: errorMessage
                });
            },
            complete: function() {
                submitButton.removeAttr('data-kt-indicator');
                submitButton.prop('disabled', false);
            }
        });
    });
});

// Initialize Select2 for roles with AJAX
function initRoleSelect2() {
    $('#filter_role').select2({
        ajax: {
            url: userRoutes.roles,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1,
                    per_page: 20
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        placeholder: 'Chọn vai trò',
        allowClear: true,
        minimumInputLength: 0
    });
}

// Save filter state to localStorage
function saveFilterState() {
    const state = {
        search: searchQuery,
        branch: filterBranch,
        role: filterRole,
        status: filterStatus
    };
    localStorage.setItem('userManagerFilterState', JSON.stringify(state));
}

// Load filter state from localStorage
function loadFilterState() {
    const savedState = localStorage.getItem('userManagerFilterState');
    if (savedState) {
        try {
            const state = JSON.parse(savedState);
            searchQuery = state.search || '';
            filterBranch = state.branch || '';
            filterRole = state.role || [];
            filterStatus = state.status || 'all';

            // Apply to UI
            $('#filter_search').val(searchQuery);
            $('#filter_branch').val(filterBranch).trigger('change');

            // Set status button
            $('.filter-status-btn').removeClass('btn-primary').addClass('btn-light');
            $(`.filter-status-btn[data-status="${filterStatus}"]`).removeClass('btn-light').addClass('btn-primary');

            // Role will be loaded by Select2 AJAX
            if (filterRole && filterRole.length > 0) {
                // We'll set this after Select2 initializes
                setTimeout(function() {
                    $('#filter_role').val(filterRole).trigger('change');
                }, 500);
            }
        } catch (e) {
            console.error('Error loading filter state:', e);
        }
    }
}

function loadUsers() {
    // Cancel previous request
    if (currentRequest) {
        currentRequest.abort();
    }

    // Show loading
    const tbody = $('#kt_users_table tbody');
    tbody.html('<tr><td colspan="8" class="text-center">Đang tải...</td></tr>');

    // Build params
    const params = {
        page: currentPage,
        per_page: perPage,
        search: searchQuery,
        branch: filterBranch,
        role: Array.isArray(filterRole) ? filterRole.join(',') : filterRole,
        status: filterStatus === 'all' ? '' : filterStatus
    };

    // Make AJAX request and return promise
    currentRequest = $.ajax({
        url: userRoutes.data,
        type: 'GET',
        data: params,
        success: function(response) {
            if (response.success) {
                renderUsers(response.data);
                updatePagination(response);
            } else {
                tbody.html('<tr><td colspan="8" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>');
            }
        },
        error: function(xhr) {
            if (xhr.statusText !== 'abort') {
                tbody.html('<tr><td colspan="8" class="text-center text-danger">Lỗi kết nối</td></tr>');
            }
        },
        complete: function() {
            currentRequest = null;
        }
    });

    // Return promise
    return currentRequest;
}

function renderUsers(users) {
    const tbody = $('#kt_users_table tbody');

    if (!users || users.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>');
        return;
    }

    let html = '';
    users.forEach(function(user) {
        html += `
            <tr data-user-id="${user.id}" style="cursor: pointer;">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input user-checkbox" type="checkbox" value="${user.id}" />
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        ${user.avatar ? `<div class="symbol symbol-circle symbol-35px me-3">
                            <img src="${user.avatar}" alt="${user.full_name}" />
                        </div>` : ''}
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold">${user.full_name}</span>
                        </div>
                    </div>
                </td>
                <td>${user.email}</td>
                <td>${user.phone}</td>
                <td>
                    ${user.roles && user.roles.length > 0
                        ? user.roles.map(role => `<span class="badge badge-light-info me-1 mb-1">${role.display_name}</span>`).join('')
                        : '<span class="text-muted">Chưa có vai trò</span>'}
                </td>
                <td>${user.status_label}</td>
                <td>${user.created_at}</td>
                <td class="text-end">
                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
}

function updatePagination(response) {
    const pagination = response.pagination;

    // Update showing info
    const from = (pagination.current_page - 1) * pagination.per_page + 1;
    const to = Math.min(pagination.current_page * pagination.per_page, pagination.total);
    $('#showing_from').text(from);
    $('#showing_to').text(to);
    $('#total_records').text(pagination.total);

    // Render pagination links
    let paginationHtml = '';

    // Previous button
    paginationHtml += `
        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1}); return false;">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            paginationHtml += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next button
    paginationHtml += `
        <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1}); return false;">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;

    $('#pagination_links').html(paginationHtml);
}

function changePage(page) {
    currentPage = page;
    loadUsers();
}

/**
 * Toggle user detail row expansion (similar to orders)
 */
function toggleUserDetailRow($row, userId) {
    console.log('Toggle user detail for ID:', userId);

    // Remove all existing detail rows
    $('.kt-table-detail-row').slideUp(300, function() {
        $(this).remove();
    });

    // Remove expanded class from all rows
    $('.user-row').removeClass('expanded kt-table-row-active');

    // Check if this row is already expanded
    const $existingDetailRow = $row.next('.kt-table-detail-row');
    if ($existingDetailRow.length) {
        console.log('Row already expanded, closing...');
        $row.removeClass('expanded kt-table-row-active');
        currentExpandedRow = null;
        return;
    }

    // Expand this row
    console.log('Expanding row for user:', userId);
    $row.addClass('expanded kt-table-row-active');
    currentExpandedRow = userId;

    // Create detail row
    const columnCount = $row.find('td').length;
    const tableContainer = $('#kt_users_table_container');
    const containerWidth = tableContainer.width();

    const $detailRow = $(`
        <tr class="kt-table-detail-row" style="display: none;">
            <td colspan="${columnCount}" class="kt-table-detail-row-td p-0">
                <div class="kt-table-detail-container p-5" style="width: ${containerWidth}px; max-width: ${containerWidth}px; overflow: visible; position: relative;">
                    <div class="loading-placeholder p-4 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2">Đang tải thông tin người dùng...</div>
                    </div>
                </div>
            </td>
        </tr>
    `);

    // Insert detail row after clicked row
    $row.after($detailRow);

    // Show detail row with animation
    $detailRow.slideDown(300, () => {
        // Load user detail content
        loadUserDetailContent(userId, $detailRow, $row);
    });
}

/**
 * Load user detail content
 */
function loadUserDetailContent(userId, $detailRow, $clickedRow) {
    console.log('Loading user detail for ID:', userId);

    // Get user data from row
    const userData = {
        id: userId,
        full_name: $clickedRow.find('td:eq(1) .text-gray-800').text(),
        email: $clickedRow.find('td:eq(2)').text(),
        phone: $clickedRow.find('td:eq(3)').text(),
        role_name: $clickedRow.find('td:eq(4) .badge').text(),
        status_label: $clickedRow.find('td:eq(5)').html(),
        created_at: $clickedRow.find('td:eq(6)').text()
    };

    // Build detail panel HTML
    const detailHtml = buildUserDetailPanel(userData);

    // Replace loading placeholder with detail content
    $detailRow.find('.loading-placeholder').replaceWith(detailHtml);
}

/**
 * Build user detail panel HTML
 */
function buildUserDetailPanel(user) {
    return `
        <div class="card border-primary" style="border-width: 2px;">
            <!--begin::Card body-->
            <div class="card-body">
                <!--begin::Tabs-->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_user_info_${user.id}">Thông tin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_user_permissions_${user.id}">Phân quyền</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_user_access_time_${user.id}">Thời gian truy cập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_user_devices_${user.id}">Thiết bị đăng nhập</a>
                    </li>
                </ul>
                <!--end::Tabs-->

                <!--begin::Tab content-->
                <div class="tab-content">
                    <!--begin::Tab pane - Thông tin-->
                    <div class="tab-pane fade show active" id="kt_tab_user_info_${user.id}" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-row-dashed table-row-gray-300 gy-4">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-muted w-150px">Tên hiển thị</td>
                                            <td class="fw-bold">${user.full_name || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Tên đăng nhập</td>
                                            <td>${user.username || (user.email ? user.email.split('@')[0] : '-')}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Email</td>
                                            <td>${user.email || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Điện thoại</td>
                                            <td>${user.phone || '-'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-row-dashed table-row-gray-300 gy-4">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-muted w-150px">Sinh nhật</td>
                                            <td>Chưa có</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Trạng thái</td>
                                            <td>${user.status_label || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Địa chỉ</td>
                                            <td>Chưa có</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Vai trò</td>
                                            <td><span class="badge badge-light-info">${user.role_name || '-'}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Tab pane - Thông tin-->

                    <!--begin::Tab pane - Phân quyền-->
                    <div class="tab-pane fade" id="kt_tab_user_permissions_${user.id}" role="tabpanel">
                        <div class="text-center text-muted py-10">
                            <i class="fas fa-user-shield fs-3x mb-5"></i>
                            <p class="fs-5">Chưa có dữ liệu phân quyền</p>
                        </div>
                    </div>
                    <!--end::Tab pane - Phân quyền-->

                    <!--begin::Tab pane - Thời gian truy cập-->
                    <div class="tab-pane fade" id="kt_tab_user_access_time_${user.id}" role="tabpanel">
                        <div class="text-center text-muted py-10">
                            <i class="fas fa-clock fs-3x mb-5"></i>
                            <p class="fs-5">Chưa có dữ liệu thời gian truy cập</p>
                        </div>
                    </div>
                    <!--end::Tab pane - Thời gian truy cập-->

                    <!--begin::Tab pane - Thiết bị đăng nhập-->
                    <div class="tab-pane fade" id="kt_tab_user_devices_${user.id}" role="tabpanel">
                        <div class="text-center text-muted py-10">
                            <i class="fas fa-mobile-alt fs-3x mb-5"></i>
                            <p class="fs-5">Chưa có dữ liệu thiết bị đăng nhập</p>
                        </div>
                    </div>
                    <!--end::Tab pane - Thiết bị đăng nhập-->
                </div>
                <!--end::Tab content-->

                <!--begin::Action Buttons-->
                <div class="d-flex justify-content-end gap-3 mt-7 pt-7 border-top">
                    <button type="button" class="btn btn-primary btn-edit-user" data-user-id="${user.id}">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa
                    </button>
                    <button type="button" class="btn btn-light-warning btn-change-password" data-user-id="${user.id}">
                        <i class="fas fa-key"></i>
                        Đổi mật khẩu
                    </button>
                    <button type="button" class="btn btn-light-danger btn-deactivate-user" data-user-id="${user.id}">
                        <i class="fas fa-ban"></i>
                        Ngừng hoạt động
                    </button>
                </div>
                <!--end::Action Buttons-->
            </div>
            <!--end::Card body-->
        </div>
    `;
}

/**
 * Open edit user modal
 */
function openEditUserModal(userId) {
    console.log('Opening edit modal for user ID:', userId);

    // Store user ID
    $('#edit_user_id').val(userId);

    // Load user data via AJAX
    $.ajax({
        url: `/admin/settings/user-manager/${userId}/show`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const user = response.data;

                // Populate form fields
                $('#edit_full_name').val(user.full_name);
                $('#edit_username').val(user.username);
                $('#edit_phone').val(user.phone);
                $('#edit_email').val(user.email);

                // Clear password fields (always empty by default)
                $('#edit_password').val('');
                $('#edit_password_confirmation').val('');

                // Set role_id in select2
                if (user.role_id) {
                    $('#edit_role_id').val(user.role_id).trigger('change');
                }

                // Show modal after data is loaded
                $('#kt_modal_edit_user').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: response.message || 'Không thể tải thông tin người dùng'
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Có lỗi xảy ra khi tải thông tin người dùng'
            });
        }
    });
}

/**
 * Open change password modal
 */
function openChangePasswordModal(userId) {
    console.log('Opening change password modal for user ID:', userId);

    // Reset form
    $('#kt_modal_change_password_form')[0].reset();

    // Store user ID
    $('#change_password_user_id').val(userId);

    // Show modal
    $('#kt_modal_change_password').modal('show');
}

/**
 * Deactivate user
 */
function deactivateUser(userId) {
    console.log('Deactivating user ID:', userId);

    Swal.fire({
        title: 'Xác nhận ngừng hoạt động',
        text: 'Bạn có chắc chắn muốn ngừng hoạt động tài khoản này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f1416c',
        cancelButtonColor: '#e4e6ef',
        confirmButtonText: 'Ngừng hoạt động',
        cancelButtonText: 'Hủy',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-light'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Send AJAX request to deactivate user
            $.ajax({
                url: `/admin/settings/user-manager/${userId}/deactivate`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Tài khoản đã được ngừng hoạt động',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then(() => {
                            // Reload users table
                            loadUsers();
                        });
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: response.message || 'Có lỗi xảy ra',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi ngừng hoạt động tài khoản',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        }
    });
}

// ============================================
// ROLES TAB FUNCTIONS
// ============================================

/**
 * Load roles data
 */
function loadRoles() {
    // Cancel previous request if exists
    if (rolesCurrentRequest) {
        rolesCurrentRequest.abort();
    }

    rolesCurrentRequest = $.ajax({
        url: window.userRoutes.rolesData,
        type: 'GET',
        data: {
            page: rolesCurrentPage,
            per_page: rolesPerPage
        },
        beforeSend: function() {
            $('#kt_roles_table_body').html('<tr><td colspan="4" class="text-center">Đang tải...</td></tr>');
        },
        success: function(response) {
            if (response.success && response.data) {
                renderRolesTable(response.data);
                renderRolesPagination(response.pagination);
            } else {
                $('#kt_roles_table_body').html('<tr><td colspan="4" class="text-center text-danger">Không thể tải dữ liệu</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            if (status !== 'abort') {
                console.error('Error loading roles:', error);
                $('#kt_roles_table_body').html('<tr><td colspan="4" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
            }
        },
        complete: function() {
            rolesCurrentRequest = null;
        }
    });
}

/**
 * Render roles table
 */
function renderRolesTable(roles) {
    const tbody = $('#kt_roles_table_body');
    tbody.empty();

    if (roles.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center text-muted">Không có dữ liệu</td></tr>');
        return;
    }

    roles.forEach(function(role) {
        const usersCountText = role.users_count > 0
            ? `${role.users_count} tài khoản. <a href="#" class="text-primary">Xem</a>`
            : 'Chưa có';

        const row = `
            <tr data-role-id="${role.id}">
                <td>${role.display_name}</td>
                <td>${role.description || 'Chưa có'}</td>
                <td>${usersCountText}</td>
                <td class="text-end">
                    <a href="#" class="btn btn-sm btn-icon btn-light-primary me-1 btn-edit-role"
                       data-role-id="${role.id}" title="Chỉnh sửa">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-icon btn-light-primary me-1 btn-copy-role"
                       data-role-id="${role.id}" title="Sao chép">
                        <i class="fas fa-copy"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-icon btn-light-danger btn-delete-role"
                       data-role-id="${role.id}" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

/**
 * Render roles pagination
 */
function renderRolesPagination(pagination) {
    const from = pagination.total > 0 ? ((pagination.current_page - 1) * pagination.per_page) + 1 : 0;
    const to = Math.min(pagination.current_page * pagination.per_page, pagination.total);

    $('#roles_from').text(from);
    $('#roles_to').text(to);
    $('#roles_total').text(pagination.total);

    const paginationLinks = $('#roles_pagination_links');
    paginationLinks.empty();

    if (pagination.last_page <= 1) {
        return;
    }

    // Previous button
    const prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
    paginationLinks.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `);

    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        const active = i === pagination.current_page ? 'active' : '';
        paginationLinks.append(`
            <li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }

    // Next button
    const nextDisabled = pagination.current_page === pagination.last_page ? 'disabled' : '';
    paginationLinks.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `);
}

// ============================================
// MODAL HELPER FUNCTIONS
// ============================================

/**
 * Load roles for modal select2
 */
function loadRolesForModal() {
    $('[name="role_id"]').select2({
        ajax: {
            url: userRoutes.roles,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1,
                    per_page: 20
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        placeholder: 'Chọn vai trò',
        allowClear: true,
        minimumInputLength: 0,
        dropdownParent: $('#kt_modal_add_user')
    });
}

/**
 * Validate user form
 */
function validateUserForm(form) {
    let isValid = true;
    let errorMessage = '';

    // Required fields
    const fullName = form.find('[name="full_name"]').val();
    const phone = form.find('[name="phone"]').val();
    const username = form.find('[name="username"]').val();
    const password = form.find('[name="password"]').val();
    const passwordConfirmation = form.find('[name="password_confirmation"]').val();

    if (!fullName) {
        errorMessage += 'Vui lòng nhập tên hiển thị<br>';
        isValid = false;
    }

    if (!phone) {
        errorMessage += 'Vui lòng nhập số điện thoại<br>';
        isValid = false;
    }

    if (!username) {
        errorMessage += 'Vui lòng nhập tên đăng nhập<br>';
        isValid = false;
    }

    if (!password) {
        errorMessage += 'Vui lòng nhập mật khẩu<br>';
        isValid = false;
    }

    if (password !== passwordConfirmation) {
        errorMessage += 'Mật khẩu xác nhận không khớp<br>';
        isValid = false;
    }

    if (!isValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Thiếu thông tin!',
            html: errorMessage
        });
    }

    return isValid;
}

// ============================================
// ROLES TAB EVENT HANDLERS
// ============================================

// Load roles when tab is shown
$('a[data-bs-toggle="tab"][href="#kt_tab_roles"]').on('shown.bs.tab', function() {
    loadRoles();
});

// Roles per page change
$(document).on('change', '#roles_per_page', function() {
    rolesPerPage = parseInt($(this).val());
    rolesCurrentPage = 1;
    loadRoles();
});

// Roles pagination click
$(document).on('click', '#roles_pagination_links .page-link', function(e) {
    e.preventDefault();
    if ($(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) {
        return;
    }
    rolesCurrentPage = parseInt($(this).data('page'));
    loadRoles();
});

// ============================================
// MODAL - TẠO VAI TRÒ
// ============================================

// Load permissions from API
let permissionsData = null;

// Module display names mapping
const moduleDisplayNames = {
    'overview': 'Tổng quan',
    'catalog': 'Hàng hóa',
    'inventory': 'Kho hàng',
    'purchasing': 'Nhập hàng',
    'orders': 'Đơn hàng',
    'delivery': 'Giao hàng',
    'customers': 'Khách hàng',
    'promotions': 'Khuyến mại',
    'cash_book': 'Sổ quỹ',
    'online_sales': 'Bán online',
    'analytics': 'Phân tích',
    'reports': 'Báo cáo',
    'staff': 'Nhân viên',
    'settings': 'Thiết lập'
};

// Sub-module display names mapping
const subModuleDisplayNames = {
    'dashboard': 'Dashboard',
    'products': 'Danh sách hàng hóa',
    'categories': 'Danh mục',
    'warehouses': 'Kho hàng',
    'stock': 'Tồn kho',
    'stock_check': 'Kiểm kho',
    'transfer': 'Chuyển kho',
    'suppliers': 'Nhà cung cấp',
    'purchase_orders': 'Đơn đặt hàng',
    'receipts': 'Phiếu nhập',
    'returns': 'Trả hàng',
    'payments': 'Thanh toán',
    'payables': 'Công nợ',
    'list': 'Danh sách',
    'invoices': 'Hóa đơn',
    'online_orders': 'Đơn online',
    'pos': 'POS',
    'shipments': 'Đơn giao hàng',
    'carriers': 'Đơn vị vận chuyển',
    'groups': 'Nhóm khách hàng',
    'loyalty_points': 'Điểm thưởng',
    'receivables': 'Công nợ',
    'campaigns': 'Chương trình',
    'coupons': 'Mã giảm giá',
    'transactions': 'Giao dịch',
    'channels': 'Kênh bán hàng',
    'business': 'Phân tích kinh doanh',
    'end_of_day': 'Báo cáo cuối ngày',
    'sales': 'Báo cáo bán hàng',
    'financial': 'Báo cáo tài chính',
    'employees': 'Nhân viên',
    'attendance': 'Chấm công',
    'payroll': 'Lương',
    'shifts': 'Ca làm việc',
    'time_clock': 'Máy chấm công',
    'shop': 'Cửa hàng',
    'branches': 'Chi nhánh',
    'users': 'Người dùng',
    'roles': 'Vai trò',
    'system': 'Hệ thống'
};

// Action display names mapping
const actionDisplayNames = {
    'read': 'Xem',
    'create': 'Tạo',
    'update': 'Sửa',
    'delete': 'Xóa',
    'import': 'Nhập',
    'export': 'Xuất',
    'view': 'Xem',
    'manage': 'Quản lý',
    'approve': 'Duyệt',
    'cancel': 'Hủy',
    'print': 'In',
    'download': 'Tải xuống'
};

function loadPermissions() {
    return $.ajax({
        url: '{{ route("admin.settings.roles.permissions") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                permissionsData = response.data;
                console.log('Permissions loaded:', permissionsData);
                renderPermissionsModal();
            }
        },
        error: function(xhr) {
            console.error('Failed to load permissions:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Không thể tải danh sách phân quyền'
            });
        }
    });
}

// Render permissions modal
function renderPermissionsModal() {
    if (!permissionsData) return;

    // Render sidebar
    let sidebarHtml = '<div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6">';
    let contentHtml = '';

    let isFirst = true;
    Object.keys(permissionsData).forEach(function(module) {
        const moduleDisplayName = moduleDisplayNames[module] || module;
        const sectionId = 'section_' + module;

        // Add to sidebar
        sidebarHtml += `
            <div class="menu-item">
                <a href="#${sectionId}" class="menu-link ${isFirst ? 'active' : ''}" data-section="${module}">
                    <span class="menu-title">${moduleDisplayName}</span>
                </a>
            </div>
        `;

        // Add to content
        contentHtml += `
            <div class="permission-section" id="${sectionId}">
                <h3 class="fs-5 fw-bold mb-5 text-primary">${moduleDisplayName}</h3>
        `;

        // Render sub-modules
        Object.keys(permissionsData[module]).forEach(function(subModule) {
            const subModuleDisplayName = subModuleDisplayNames[subModule] || subModule;
            const permissions = permissionsData[module][subModule];
            const groupId = 'group_' + module + '_' + subModule;
            const collapseId = 'collapse_' + module + '_' + subModule;

            contentHtml += `
                <div class="permission-group mb-7">
                    <div class="d-flex align-items-center mb-3">
                        <input class="form-check-input me-3 group-checkbox" type="checkbox" id="${groupId}" data-group="${module}_${subModule}" />
                        <label class="form-label fw-bold mb-0 cursor-pointer" for="${groupId}">
                            ${subModuleDisplayName}
                        </label>
                        <button type="button" class="btn btn-sm btn-icon btn-light-primary ms-auto" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                            <i class="fas fa-chevron-down fs-7"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="${collapseId}">
                        <div class="ps-10">
                            <div class="row">
            `;

            // Group permissions by action
            const permissionsByAction = {};
            permissions.forEach(function(perm) {
                if (!permissionsByAction[perm.action]) {
                    permissionsByAction[perm.action] = [];
                }
                permissionsByAction[perm.action].push(perm);
            });

            // Render permissions
            Object.keys(permissionsByAction).forEach(function(action) {
                const actionDisplayName = actionDisplayNames[action] || action;
                const perms = permissionsByAction[action];

                contentHtml += `
                    <div class="col-md-3 mb-3">
                        <div class="fw-semibold text-gray-600 mb-2">${actionDisplayName}</div>
                `;

                perms.forEach(function(perm) {
                    const permId = 'perm_' + perm.id;
                    contentHtml += `
                        <div class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="${perm.name}" id="${permId}" data-group="${module}_${subModule}" />
                            <label class="form-check-label" for="${permId}">${perm.display_name}</label>
                        </div>
                    `;
                });

                contentHtml += `
                    </div>
                `;
            });

            contentHtml += `
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        contentHtml += `
            </div>
        `;

        if (!isFirst) {
            contentHtml = '<div class="separator my-10"></div>' + contentHtml;
        }

        isFirst = false;
    });

    sidebarHtml += '</div>';

    // Update DOM
    $('.permissions-sidebar').html(sidebarHtml);
    $('.permissions-content').html(contentHtml);
}

// Load permissions on page load
$(document).ready(function() {
    loadPermissions();
});

/**
 * Open role modal
 * @param {string} mode - 'create', 'edit', or 'copy'
 * @param {number|null} roleId - Role ID for edit/copy mode
 */
function openRoleModal(mode = 'create', roleId = null) {
    roleModalMode = mode;
    roleModalId = roleId;

    // Reset form
    $('#kt_modal_create_role_form')[0].reset();

    // Uncheck all permissions
    $('#kt_modal_create_role_form input[type="checkbox"]').prop('checked', false);

    // Reset sidebar menu
    $('.permissions-sidebar .menu-link').removeClass('active');
    $('.permissions-sidebar .menu-link[data-section="overview"]').addClass('active');

    // Update modal title
    let modalTitle = 'Tạo vai trò mới';
    if (mode === 'edit') {
        modalTitle = 'Sửa vai trò';
    } else if (mode === 'copy') {
        modalTitle = 'Nhân bản vai trò';
    }
    $('#kt_modal_create_role .modal-title').text(modalTitle);

    // Update submit button text
    let submitBtnText = 'Tạo vai trò';
    if (mode === 'edit') {
        submitBtnText = 'Cập nhật';
    } else if (mode === 'copy') {
        submitBtnText = 'Nhân bản';
    }
    $('#kt_modal_create_role_submit').text(submitBtnText);

    // Load role data if editing or copying
    if ((mode === 'edit' || mode === 'copy') && roleId) {
        loadRoleData(roleId, mode);
    }

    // Show modal
    $('#kt_modal_create_role').modal('show');
}

/**
 * Load role data for editing/copying
 */
function loadRoleData(roleId, mode) {
    $.ajax({
        url: `{{ url('admin/settings/roles') }}/${roleId}`,
        method: 'GET',
        beforeSend: function() {
            // Show loading spinner
            $('#kt_modal_create_role .modal-body').append(`
                <div class="loading-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;
                     background: rgba(255,255,255,0.8); z-index: 9999; display: flex; align-items: center;
                     justify-content: center;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            `);
        },
        success: function(response) {
            if (response.success && response.data) {
                const role = response.data;

                // Fill role name (empty for copy mode)
                if (mode === 'edit') {
                    $('#role_name').val(role.display_name);
                } else if (mode === 'copy') {
                    $('#role_name').val(''); // Empty for copy
                }

                // Fill description
                $('#role_description').val(role.description || '');

                // Check permissions
                if (role.permissions && role.permissions.length > 0) {
                    role.permissions.forEach(function(permission) {
                        $(`#kt_modal_create_role_form input[type="checkbox"][value="${permission.name}"]`).prop('checked', true);
                    });

                    // Update group checkboxes
                    updateGroupCheckboxes();
                }
            }
        },
        error: function(xhr) {
            console.error('Failed to load role data:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Không thể tải thông tin vai trò'
            });
            $('#kt_modal_create_role').modal('hide');
        },
        complete: function() {
            // Remove loading spinner
            $('#kt_modal_create_role .loading-overlay').remove();
        }
    });
}

/**
 * Update group checkboxes based on child checkboxes
 */
function updateGroupCheckboxes() {
    $('.permission-group').each(function() {
        const groupCheckbox = $(this).find('.group-checkbox');
        const childCheckboxes = $(this).find('.permission-checkbox');
        const checkedCount = childCheckboxes.filter(':checked').length;
        const totalCount = childCheckboxes.length;

        if (checkedCount === 0) {
            groupCheckbox.prop('checked', false);
            groupCheckbox.prop('indeterminate', false);
        } else if (checkedCount === totalCount) {
            groupCheckbox.prop('checked', true);
            groupCheckbox.prop('indeterminate', false);
        } else {
            groupCheckbox.prop('checked', false);
            groupCheckbox.prop('indeterminate', true);
        }
    });
}

// Open create role modal
$('#kt_roles_add_btn').on('click', function() {
    openRoleModal('create');
});

// Edit role button click
$(document).on('click', '.btn-edit-role', function(e) {
    e.preventDefault();
    const roleId = $(this).data('role-id');
    openRoleModal('edit', roleId);
});

// Copy role button click
$(document).on('click', '.btn-copy-role', function(e) {
    e.preventDefault();
    const roleId = $(this).data('role-id');
    openRoleModal('copy', roleId);
});

// Delete role button click
$(document).on('click', '.btn-delete-role', function(e) {
    e.preventDefault();
    const roleId = $(this).data('role-id');
    const roleName = $(this).closest('tr').find('td:first').text();

    // Show confirmation dialog
    Swal.fire({
        title: 'Xác nhận xóa vai trò',
        html: `Bạn có chắc chắn muốn xóa vai trò <strong>${roleName}</strong>?<br><small class="text-muted">Hành động này không thể hoàn tác.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            deleteRole(roleId);
        }
    });
});

// Delete role function
function deleteRole(roleId) {
    $.ajax({
        url: `{{ url('admin/settings/roles') }}/${roleId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Reload roles table
                loadRolesData();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra khi xóa vai trò';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: errorMessage
            });
        }
    });
}

// ============================================
// ASSIGN ROLES - REMOVED (Roles are assigned in Create/Edit User form)
// ============================================

// Sidebar navigation
$(document).on('click', '.permissions-sidebar .menu-link', function(e) {
    e.preventDefault();

    // Update active state
    $('.permissions-sidebar .menu-link').removeClass('active');
    $(this).addClass('active');

    // Scroll to section
    const sectionId = $(this).attr('href');
    if (sectionId && $(sectionId).length) {
        $('.permissions-content').animate({
            scrollTop: $(sectionId).offset().top - $('.permissions-content').offset().top + $('.permissions-content').scrollTop() - 20
        }, 300);
    }
});

// Group checkbox - check/uncheck all permissions in group
$(document).on('change', '.group-checkbox', function() {
    const isChecked = $(this).is(':checked');
    const groupName = $(this).data('group');

    // Check/uncheck all permissions in this group
    $(`.permission-checkbox[data-group="${groupName}"]`).prop('checked', isChecked);
});

// Individual permission checkbox - update group checkbox state
$(document).on('change', '.permission-checkbox', function() {
    const groupName = $(this).data('group');
    const groupCheckbox = $(`.group-checkbox[data-group="${groupName}"]`);

    // Count checked permissions
    const totalPerms = $(`.permission-checkbox[data-group="${groupName}"]`).length;
    const checkedPerms = $(`.permission-checkbox[data-group="${groupName}"]:checked`).length;

    // Update group checkbox state
    if (checkedPerms === 0) {
        groupCheckbox.prop('checked', false);
        groupCheckbox.prop('indeterminate', false);
    } else if (checkedPerms === totalPerms) {
        groupCheckbox.prop('checked', true);
        groupCheckbox.prop('indeterminate', false);
    } else {
        groupCheckbox.prop('checked', false);
        groupCheckbox.prop('indeterminate', true);
    }
});

// Handle create/edit/copy role form submission
$('#kt_modal_create_role_form').on('submit', function(e) {
    e.preventDefault();

    const submitButton = $('#kt_modal_create_role_submit');
    const form = $(this);

    // Validate
    const roleName = $('#role_name').val().trim();
    if (!roleName) {
        Swal.fire({
            icon: 'warning',
            title: 'Thiếu thông tin!',
            text: 'Vui lòng nhập tên vai trò'
        });
        return;
    }

    // Get selected permissions
    const selectedPermissions = [];
    form.find('input[name="permissions[]"]:checked').each(function() {
        selectedPermissions.push($(this).val());
    });

    // Show loading
    submitButton.attr('data-kt-indicator', 'on');
    submitButton.prop('disabled', true);

    // Prepare data
    const formData = {
        name: roleName,
        display_name: roleName,
        description: $('#role_description').val(),
        permissions: selectedPermissions
    };

    // Determine URL and method based on mode
    let url = '{{ route("admin.settings.roles.store") }}';
    let method = 'POST';
    let successMessage = 'Tạo vai trò thành công';

    if (roleModalMode === 'edit' && roleModalId) {
        url = `{{ url('admin/settings/roles') }}/${roleModalId}`;
        method = 'PUT';
        successMessage = 'Cập nhật vai trò thành công';
    } else if (roleModalMode === 'copy') {
        // Copy mode uses POST (create new role)
        successMessage = 'Nhân bản vai trò thành công';
    }

    // Send AJAX request to backend
    $.ajax({
        url: url,
        method: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: response.message || successMessage,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Close modal
                $('#kt_modal_create_role').modal('hide');

                // Reload roles table
                loadRoles();

                // Reset modal state
                roleModalMode = 'create';
                roleModalId = null;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: response.message || 'Có lỗi xảy ra'
                });
            }
        },
        error: function(xhr) {
            let errorMessage = 'Có lỗi xảy ra';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                if (xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                html: errorMessage
            });
        },
        complete: function() {
            // Hide loading
            submitButton.removeAttr('data-kt-indicator');
            submitButton.prop('disabled', false);
        }
    });
});

</script>

<!--begin::Modal - Tạo tài khoản người dùng-->
<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                <!--begin::Heading-->
                <div class="text-center mb-10">
                    <h1 class="mb-3">Tạo tài khoản người dùng</h1>
                </div>
                <!--end::Heading-->

                <!--begin::Form-->
                <form id="kt_modal_add_user_form" class="form">
                    <!--begin::Thông tin cơ bản-->
                    <div class="row g-5 mb-7">
                        <!--begin::Tên hiển thị-->
                        <div class="col-md-4">
                            <label class="required form-label">Tên hiển thị</label>
                            <input type="text" class="form-control form-control-solid" name="full_name" placeholder="Bắt buộc" />
                        </div>
                        <!--end::Tên hiển thị-->

                        <!--begin::Điện thoại-->
                        <div class="col-md-4">
                            <label class="required form-label">Điện thoại</label>
                            <div class="input-group">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="https://flagcdn.com/w20/vn.png" alt="VN" class="me-2" style="width: 20px;">
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><img src="https://flagcdn.com/w20/vn.png" alt="VN" style="width: 20px;"> +84</a></li>
                                </ul>
                                <input type="text" class="form-control form-control-solid" name="phone" placeholder="0912 345 678" />
                            </div>
                        </div>
                        <!--end::Điện thoại-->

                        <!--begin::Email-->
                        <div class="col-md-4">
                            <label class="form-label">Email*</label>
                            <input type="email" class="form-control form-control-solid" name="email" placeholder="email@gmail.com" required />
                        </div>
                        <!--end::Email-->
                    </div>

                    <div class="row g-5 mb-7">
                        <!--begin::Tên đăng nhập-->
                        <div class="col-md-4">
                            <label class="required form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control form-control-solid" name="username" placeholder="Bắt buộc" />
                        </div>
                        <!--end::Tên đăng nhập-->

                        <!--begin::Mật khẩu-->
                        <div class="col-md-4">
                            <label class="required form-label">Mật khẩu</label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-solid" name="password" placeholder="Bắt buộc" id="password_input" />
                                <button type="button" class="btn btn-sm btn-icon position-absolute end-0 top-0 h-100" id="toggle_password">
                                    <i class="fas fa-eye-slash" id="password_icon"></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Mật khẩu-->

                        <!--begin::Nhập lại mật khẩu-->
                        <div class="col-md-4">
                            <label class="required form-label">Nhập lại mật khẩu</label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-solid" name="password_confirmation" placeholder="Bắt buộc" id="password_confirmation_input" />
                                <button type="button" class="btn btn-sm btn-icon position-absolute end-0 top-0 h-100" id="toggle_password_confirmation">
                                    <i class="fas fa-eye-slash" id="password_confirmation_icon"></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Nhập lại mật khẩu-->
                    </div>
                    <!--end::Thông tin cơ bản-->

                    <!--begin::Phân quyền-->
                    <div class="mb-7">
                        <label class="form-label fw-bold fs-6 mb-3">Phân quyền</label>
                        <p class="text-muted fs-7 mb-3">Chọn phân quyền cho người dùng này</p>

                        <!--begin::Vai trò-->
                        <div class="mb-5">
                            <label class="form-label">Vai trò</label>
                            <select class="form-select form-select-solid" name="role_id" data-control="select2" data-placeholder="Chọn vai trò" data-dropdown-parent="#kt_modal_add_user">
                                <option value="">Chọn vai trò</option>
                            </select>
                        </div>
                        <!--end::Vai trò-->

                        <!--begin::Checkboxes-->
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="notify_transactions" id="notify_transactions" checked />
                            <label class="form-check-label" for="notify_transactions">
                                Xem thông tin chung của hàng hóa, giao dịch, đối tác
                                <i class="fas fa-info-circle ms-1 text-gray-500"></i>
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="notify_daily_reports" id="notify_daily_reports" checked />
                            <label class="form-check-label" for="notify_daily_reports">
                                Xem, chỉnh sửa giao dịch và xem báo cáo cuối ngày của nhân viên khác
                            </label>
                        </div>
                        <!--end::Checkboxes-->
                    </div>
                    <!--end::Phân quyền-->

                    <!--begin::Thời gian truy cập-->
                    <div class="mb-7">
                        <div class="d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" data-bs-target="#kt_user_access_time">
                            <label class="form-label fw-bold fs-6 mb-0">Thời gian truy cập</label>
                            <button type="button" class="btn btn-sm btn-light-primary">Thiết lập</button>
                        </div>
                        <p class="text-muted fs-7 mt-2">Tài khoản được phép truy cập vào thời gian</p>

                        <div class="collapse" id="kt_user_access_time">
                            <div class="border border-gray-300 border-dashed rounded p-5 mt-3">
                                <p class="text-muted fs-7">Chức năng thiết lập thời gian truy cập sẽ được cập nhật sau</p>
                            </div>
                        </div>
                    </div>
                    <!--end::Thời gian truy cập-->

                    <!--begin::Thông tin khác-->
                    <div class="mb-7">
                        <div class="d-flex align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#kt_user_other_info">
                            <i class="fas fa-chevron-down fs-4 me-2"></i>
                            <label class="form-label fw-bold fs-6 mb-0">Thông tin khác</label>
                        </div>

                        <div class="collapse" id="kt_user_other_info">
                            <div class="row g-5 mt-3">
                                <!--begin::Sinh nhật-->
                                <div class="col-md-6">
                                    <label class="form-label">Sinh nhật</label>
                                    <input type="date" class="form-control form-control-solid" name="birthday" placeholder="--/--/----" />
                                </div>
                                <!--end::Sinh nhật-->

                                <!--begin::Địa chỉ-->
                                <div class="col-md-6">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control form-control-solid" name="address" placeholder="Nhập địa chỉ" />
                                </div>
                                <!--end::Địa chỉ-->

                                <!--begin::Khu vực-->
                                <div class="col-md-6">
                                    <label class="form-label">Khu vực</label>
                                    <select class="form-select form-select-solid" name="district" data-control="select2" data-placeholder="Chọn khu vực" data-dropdown-parent="#kt_modal_add_user">
                                        <option value="">Chọn khu vực</option>
                                    </select>
                                </div>
                                <!--end::Khu vực-->

                                <!--begin::Phường/Xã-->
                                <div class="col-md-6">
                                    <label class="form-label">Phường/Xã</label>
                                    <select class="form-select form-select-solid" name="ward" data-control="select2" data-placeholder="Chọn Phường/Xã" data-dropdown-parent="#kt_modal_add_user">
                                        <option value="">Chọn Phường/Xã</option>
                                    </select>
                                </div>
                                <!--end::Phường/Xã-->
                            </div>
                        </div>
                    </div>
                    <!--end::Thông tin khác-->

                    <!--begin::Ghi chú-->
                    <div class="mb-10">
                        <div class="d-flex align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#kt_user_notes">
                            <i class="fas fa-chevron-down fs-4 me-2"></i>
                            <label class="form-label fw-bold fs-6 mb-0">Ghi chú</label>
                        </div>

                        <div class="collapse" id="kt_user_notes">
                            <div class="mt-3">
                                <textarea class="form-control form-control-solid" name="notes" rows="3" placeholder="Nhập ghi chú"></textarea>
                            </div>
                        </div>
                    </div>
                    <!--end::Ghi chú-->

                    <!--begin::Actions-->
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="kt_modal_add_user_submit">
                            <span class="indicator-label">Lưu</span>
                            <span class="indicator-progress">Đang xử lý...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
    </div>
</div>
<!--end::Modal - Tạo tài khoản người dùng-->

<!--begin::Modal - Chỉnh sửa tài khoản-->
<div class="modal fade" id="kt_modal_edit_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                <!--begin::Heading-->
                <div class="text-center mb-10">
                    <h1 class="mb-3">Chỉnh sửa tài khoản</h1>
                </div>
                <!--end::Heading-->

                <!--begin::Form-->
                <form id="kt_modal_edit_user_form" class="form">
                    <input type="hidden" name="user_id" id="edit_user_id" />

                    <!--begin::Thông tin cơ bản-->
                    <div class="row g-5 mb-7">
                        <!--begin::Tên hiển thị-->
                        <div class="col-md-4">
                            <label class="required form-label">Tên hiển thị</label>
                            <input type="text" class="form-control form-control-solid" name="full_name" id="edit_full_name" placeholder="Bắt buộc" />
                        </div>
                        <!--end::Tên hiển thị-->

                        <!--begin::Điện thoại-->
                        <div class="col-md-4">
                            <label class="required form-label">Điện thoại</label>
                            <input type="text" class="form-control form-control-solid" name="phone" id="edit_phone" placeholder="0912 345 678" />
                        </div>
                        <!--end::Điện thoại-->

                        <!--begin::Email-->
                        <div class="col-md-4">
                            <label class="form-label">Email*</label>
                            <input type="email" class="form-control form-control-solid" name="email" id="edit_email" placeholder="email@gmail.com" required />
                        </div>
                        <!--end::Email-->
                    </div>
                    <!--end::Thông tin cơ bản-->

                    <!--begin::Tên đăng nhập-->
                    <div class="mb-7">
                        <label class="required form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control form-control-solid" name="username" id="edit_username" placeholder="Tên đăng nhập" />
                    </div>
                    <!--end::Tên đăng nhập-->

                    <!--begin::Mật khẩu (Optional)-->
                    <div class="row g-5 mb-7">
                        <!--begin::Mật khẩu-->
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu <span class="text-muted">(Để trống nếu không đổi)</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-solid" name="password" id="edit_password" placeholder="Nhập mật khẩu mới" />
                                <button type="button" class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" id="toggle_edit_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Mật khẩu-->

                        <!--begin::Xác nhận mật khẩu-->
                        <div class="col-md-6">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <div class="position-relative">
                                <input type="password" class="form-control form-control-solid" name="password_confirmation" id="edit_password_confirmation" placeholder="Nhập lại mật khẩu" />
                                <button type="button" class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" id="toggle_edit_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Xác nhận mật khẩu-->
                    </div>
                    <!--end::Mật khẩu (Optional)-->

                    <!--begin::Phân quyền-->
                    <div class="mb-10">
                        <label class="form-label fw-bold fs-6 mb-2">Vai trò</label>
                        <select class="form-select form-select-solid" name="role_id" id="edit_role_id">
                            <option value="">Chọn vai trò</option>
                        </select>
                    </div>
                    <!--end::Phân quyền-->

                    <!--begin::Actions-->
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="kt_modal_edit_user_submit">
                            <span class="indicator-label">Cập nhật</span>
                            <span class="indicator-progress">Đang xử lý...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
    </div>
</div>
<!--end::Modal - Chỉnh sửa tài khoản-->

<!--begin::Modal - Đổi mật khẩu-->
<div class="modal fade" id="kt_modal_change_password" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                <!--begin::Heading-->
                <div class="text-center mb-10">
                    <h1 class="mb-3">Đổi mật khẩu</h1>
                    <div class="text-muted fw-semibold fs-5">Thay đổi mật khẩu cho tài khoản <span id="change_password_username" class="fw-bold"></span></div>
                </div>
                <!--end::Heading-->

                <!--begin::Form-->
                <form id="kt_modal_change_password_form" class="form">
                    <input type="hidden" name="user_id" id="change_password_user_id" />

                    <!--begin::Mật khẩu mới-->
                    <div class="mb-7">
                        <label class="required form-label">Mật khẩu mới</label>
                        <div class="position-relative">
                            <input type="password" class="form-control form-control-solid" name="new_password" id="new_password_input" placeholder="Nhập mật khẩu mới" />
                            <button type="button" class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" id="toggle_new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Mật khẩu mới-->

                    <!--begin::Xác nhận mật khẩu mới-->
                    <div class="mb-10">
                        <label class="required form-label">Xác nhận mật khẩu mới</label>
                        <div class="position-relative">
                            <input type="password" class="form-control form-control-solid" name="new_password_confirmation" id="new_password_confirmation_input" placeholder="Nhập lại mật khẩu mới" />
                            <button type="button" class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" id="toggle_new_password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Xác nhận mật khẩu mới-->

                    <!--begin::Actions-->
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="kt_modal_change_password_submit">
                            <span class="indicator-label">Đổi mật khẩu</span>
                            <span class="indicator-progress">Đang xử lý...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
    </div>
</div>
<!--end::Modal - Đổi mật khẩu-->

<!--begin::Modal - Tạo vai trò-->
<div class="modal fade" id="kt_modal_create_role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                <!--begin::Heading-->
                <div class="mb-10">
                    <h1 class="mb-3 modal-title">Tạo vai trò</h1>

                    <!--begin::Thông tin cơ bản-->
                    <div class="row g-5 mb-5">
                        <div class="col-md-6">
                            <label class="form-label">Tên vai trò</label>
                            <input type="text" class="form-control form-control-solid" name="role_name" id="role_name" placeholder="Nhập tên vai trò" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mô tả</label>
                            <input type="text" class="form-control form-control-solid" name="role_description" id="role_description" placeholder="Nhập mô tả" />
                        </div>
                    </div>
                    <!--end::Thông tin cơ bản-->

                    <!--begin::Search permissions-->
                    <div class="d-flex align-items-center text-primary mb-5">
                        <i class="fas fa-search me-2"></i>
                        <span class="fs-7">Ctrl+F để tìm phân quyền</span>
                    </div>
                    <!--end::Search permissions-->
                </div>
                <!--end::Heading-->

                <!--begin::Form-->
                <form id="kt_modal_create_role_form" class="form">
                    <!--begin::Permissions sections-->
                    <div class="permissions-container">

                        <!--begin::Right sidebar - Modules menu-->
                        <div class="permissions-sidebar">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        <!--end::Right sidebar-->

                        <!--begin::Permissions content-->
                        <div class="permissions-content">
                            <div class="text-center py-10">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-3 text-muted">Đang tải phân quyền...</div>
                            </div>
                        </div>
                        <!--end::Permissions content-->
                    </div>
                    <!--end::Permissions sections-->

                    <!--begin::Actions-->
                    <div class="text-end mt-10">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="submit" class="btn btn-primary" id="kt_modal_create_role_submit">
                            <span class="indicator-label">Lưu</span>
                            <span class="indicator-progress">Đang xử lý...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
    </div>
</div>
<!--end::Modal - Tạo vai trò-->


<style>
/* Permissions modal custom styles */
.permissions-container {
    display: flex;
    gap: 20px;
    min-height: 500px;
}

.permissions-sidebar {
    flex: 0 0 200px;
    border-right: 1px solid #e4e6ef;
    padding-right: 20px;
    position: sticky;
    top: 0;
    align-self: flex-start;
}

.permissions-sidebar .menu-link {
    padding: 10px 15px;
    border-radius: 6px;
    transition: all 0.3s;
}

.permissions-sidebar .menu-link:hover,
.permissions-sidebar .menu-link.active {
    background-color: #f1faff;
    color: #009ef7;
}

.permissions-content {
    flex: 1;
    overflow-y: auto;
    max-height: 600px;
    padding-right: 10px;
}

.permission-group {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
}

.permission-group .collapse {
    margin-top: 10px;
}

/* Scrollbar styling */
.permissions-content::-webkit-scrollbar {
    width: 6px;
}

.permissions-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.permissions-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.permissions-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive styles for screens < 1200px */
@media (max-width: 1199px) {
    .permissions-container {
        flex-direction: column;
        gap: 15px;
    }

    .permissions-sidebar {
        flex: 0 0 auto;
        border-right: none;
        border-bottom: 1px solid #e4e6ef;
        padding-right: 0;
        padding-bottom: 15px;
        position: static;
    }

    .permissions-sidebar .menu {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .permissions-sidebar .menu-item {
        flex: 0 0 auto;
    }

    .permissions-sidebar .menu-link {
        padding: 8px 12px;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .permissions-content {
        max-height: 500px;
    }
}

/* Responsive styles for screens < 768px (mobile) */
@media (max-width: 767px) {
    #kt_modal_create_role .modal-dialog {
        max-width: 95% !important;
        margin: 10px auto;
    }

    #kt_modal_create_role .modal-body {
        padding: 15px !important;
    }

    .permissions-sidebar .menu {
        gap: 5px;
    }

    .permissions-sidebar .menu-link {
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    .permissions-content {
        max-height: 400px;
    }

    .permission-group {
        padding: 10px;
    }

    .permission-group .row > div {
        flex: 0 0 100%;
        max-width: 100%;
    }

    /* Stack form inputs vertically on mobile */
    #kt_modal_create_role_form .row.g-5 > div {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Responsive styles for screens < 576px (small mobile) */
@media (max-width: 575px) {
    #kt_modal_create_role .modal-dialog {
        max-width: 100% !important;
        margin: 0;
    }

    .permissions-sidebar .menu-link {
        font-size: 0.8rem;
        padding: 5px 8px;
    }

    .permissions-content {
        max-height: 350px;
    }
}
</style>

@endsection
