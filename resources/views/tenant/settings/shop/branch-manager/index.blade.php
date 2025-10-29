@extends('admin.layouts.tenant-app')

@section('title', 'Quản lý chi nhánh')

@section('style')
    <link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/table-loading.css') }}" rel="stylesheet" type="text/css" />
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
                @include('tenant.settings.elements.sidebar')

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-10 order-2 order-lg-2">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::Card-->
                        <div class="card card-flush">
                            <!--begin::Card header-->
                            <div id="kt_branches_table_toolbar" class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <!--begin::Search-->
                                    <div class="d-flex align-items-center position-relative my-1">
                                        <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                        <input type="text" id="kt_branches_search"
                                            class="form-control form-control-solid w-250px ps-12"
                                            placeholder="Tìm kiếm chi nhánh..." />
                                    </div>
                                    <!--end::Search-->
                                </div>
                                <!--end::Card title-->

                                <!--begin::Card toolbar-->
                                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                    <!--begin::Add branch-->
                                    <a href="#" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Thêm mới
                                    </a>
                                    <!--end::Add branch-->
                                </div>
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Table container-->
                                <div id="kt_branches_table_container" class="table-responsive">
                                    <!--begin::Table-->
                                    <table id="kt_branches_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                        <!--begin::Table head-->
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div
                                                        class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="select_all_branches" />
                                                    </div>
                                                </th>
                                                <th class="min-w-150px">Tên chi nhánh</th>
                                                <th class="min-w-100px">Mã chi nhánh</th>
                                                <th class="min-w-200px">Địa chỉ</th>
                                                <th class="min-w-100px">Số điện thoại</th>
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
                                <div id="kt_branches_pagination"
                                    class="d-flex justify-content-between align-items-center mt-5">
                                    <div class="text-gray-600">
                                        Hiển thị <span id="showing_from">0</span> - <span id="showing_to">0</span>
                                        trong tổng số <span id="total_records">0</span> chi nhánh
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
        // Branch routes configuration
        window.branchRoutes = {
            data: '{{ route('admin.settings.branch-manager.data') }}'
        };

        // Global variables
        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let currentRequest = null;

        $(document).ready(function() {
            // Initialize menu
            KTMenu.createInstances();

            // Load initial data
            loadBranches();

            // Search functionality
            let searchTimeout;
            $('#kt_branches_search').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchQuery = $(this).val();
                searchTimeout = setTimeout(function() {
                    currentPage = 1;
                    loadBranches();
                }, 500);
            });

            // Per page change
            $('#per_page_select').on('change', function() {
                perPage = $(this).val();
                currentPage = 1;
                loadBranches();
            });

            // Select all checkbox
            $('#select_all_branches').on('change', function() {
                $('.branch-checkbox').prop('checked', $(this).prop('checked'));
            });
        });

        function loadBranches() {
            // Cancel previous request
            if (currentRequest) {
                currentRequest.abort();
            }

            // Show loading
            const tbody = $('#kt_branches_table tbody');
            tbody.html('<tr><td colspan="8" class="text-center">Đang tải...</td></tr>');

            // Build params
            const params = {
                page: currentPage,
                per_page: perPage,
                search: searchQuery
            };

            // Make AJAX request
            currentRequest = $.ajax({
                url: branchRoutes.data,
                type: 'GET',
                data: params,
                success: function(response) {
                    if (response.success) {
                        renderBranches(response.data);
                        updatePagination(response);
                    } else {
                        tbody.html(
                            '<tr><td colspan="8" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>');
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
        }

        function renderBranches(branches) {
            const tbody = $('#kt_branches_table tbody');

            if (!branches || branches.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center">Không có dữ liệu</td></tr>');
                return;
            }

            let html = '';
            branches.forEach(function(branch) {
                html += `
            <tr>
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input branch-checkbox" type="checkbox" value="${branch.id}" />
                    </div>
                </td>
                <td>
                    <span class="text-gray-800 fw-bold">${branch.name}</span>
                </td>
                <td>${branch.code}</td>
                <td>${branch.address}</td>
                <td>${branch.phone}</td>
                <td>${branch.status_label}</td>
                <td>${branch.created_at}</td>
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
                if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination
                        .current_page + 2)) {
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
            loadBranches();
        }
    </script>
@endsection
