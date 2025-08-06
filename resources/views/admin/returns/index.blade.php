@extends('admin.main-content')

@section('title', 'Quản lý đơn trả hàng')

@section('style')
    <link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
    <link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/globals/table-row-expansion.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/table-loading.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/return-list.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">

        </div>
        <!--end::Toolbar-->

        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="d-flex flex-column flex-lg-row">
                @include('admin.returns.elements.filter')

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-15 order-2 order-lg-2">
                    <div class="d-flex flex-column gap-7 gap-lg-10">

                        <!--begin::Card-->
                        <div class="card card-flush">
                            <!--begin::Card header-->
                            <div id="kt_returns_table_toolbar" class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <!--begin::Search-->
                                    <div class="d-flex align-items-center position-relative my-1" bis_skin_checked="1">
                                        <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                        <input type="text" id="return_search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm đơn trả hàng...">
                                    </div>
                                  
                                    <!--end::Search-->
                                </div>
                                <!--end::Card title-->
                                <!--begin::Card toolbar-->
                                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                    <!--begin::Bulk Actions Dropdown-->
                                    <div class="btn-group" id="bulk-actions-dropdown" style="display: none;">
                                        <button type="button" class="btn btn-light-warning dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-tasks fs-2 me-2"></i>
                                            <span id="bulk-actions-text">Thao tác hàng loạt</span>
                                            <span class="badge badge-circle badge-warning ms-2" id="bulk-count">0</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" id="bulk-update-delivery">
                                                    <i class="fas fa-truck text-info me-2"></i>Cập nhật giao hàng
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" id="bulk-update-info">
                                                    <i class="fas fa-edit text-primary me-2"></i>Cập nhật thông tin chung
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" id="bulk-cancel">
                                                    <i class="fas fa-ban text-danger me-2"></i>Huỷ
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <!--end::Bulk Actions Dropdown-->

                                    <!--begin::Export-->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-light-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-download fs-2"></i>Xuất dữ liệu
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="exportReturnsExcel()">
                                                <i class="fas fa-file-excel text-success me-2"></i>Xuất Excel
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportReturnsPdf()">
                                                <i class="fas fa-file-pdf text-danger me-2"></i>Xuất PDF
                                            </a></li>
                                        </ul>
                                    </div>
                                    <!--end::Export-->
                                    <!--begin::Add return-->
                                    <a href="{{ route('admin.quick-order.index', ['type' => 'return']) }}" class="btn btn-primary">
                                        <i class="fas fa-plus fs-2"></i>Tạo đơn trả hàng
                                    </a>
                                    <!--end::Add return-->



                                    <!--begin::Column visibility-->
                                    <div class="position-relative">
                                        <button type="button" class="btn btn-success column-visibility-trigger"
                                            id="column_visibility_trigger">
                                            <i class="fas fa-list fs-2"></i>
                                        </button>
                                        <!-- Column visibility panel -->
                                        <div id="column_visibility_panel" class="column-visibility-panel position-absolute"
                                            style="display: none;">
                                            <div class="panel-content">
                                                <div class="panel-header">
                                                    <h6 class="fw-bold text-dark mb-0">Chọn cột hiển thị</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="0" id="col_checkbox"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_checkbox">Checkbox</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="1" id="col_invoice_number"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_invoice_number">Mã hóa đơn</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="2" id="col_customer"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_customer">Khách hàng</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="3" id="col_total_amount"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_total_amount">Tổng tiền</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="4" id="col_amount_paid"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_amount_paid">Đã thanh toán</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="15"
                                                                    id="col_branch_shop" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_branch_shop">Chi nhánh</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="16" id="col_notes" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_notes">Ghi chú</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="5"
                                                                    id="col_payment_status" checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_payment_status">Trạng thái TT</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="6"
                                                                    id="col_payment_method" checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_payment_method">Phương thức TT</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="7" id="col_channel"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_channel">Kênh bán</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="8" id="col_created_at"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_created_at">Ngày tạo</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="9" id="col_seller" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_seller">Người bán</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="10" id="col_creator" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_creator">Người tạo</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="11" id="col_discount" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_discount">Giảm giá</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="12" id="col_email" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_email">Email</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="13" id="col_phone" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_phone">Phone</label>
                                                            </div>
                                                            <div
                                                                class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="14" id="col_address" />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_address">Địa chỉ</label>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Column visibility-->
                                </div>
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card header-->




                            <!--begin::Card body-->
                            <div id="kt_returns_container_body" class="kt_table_container_body card-body pt-0">

                                <!--begin::Table container with horizontal scroll-->
                                <div id="kt_returns_table_container" class="kt_table_responsive_container">
                                    <table class="kt_table_responsive table align-middle table-row-dashed fs-6 gy-5" id="kt_returns_table">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div
                                                        class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="select-all-returns" value="1" />
                                                    </div>
                                                </th>
                                                <th class="min-w-100px">Mã đơn trả</th>
                                                <th class="min-w-150px">Khách hàng</th>
                                                <th class="min-w-100px">Tổng tiền</th>
                                                <th class="min-w-100px">Đã hoàn</th>
                                                <th class="min-w-100px">Trạng thái</th>
                                                <th class="min-w-100px">Phương thức TT</th>
                                                <th class="min-w-100px">Kênh bán</th>
                                                <th class="min-w-100px">Ngày tạo</th>
                                                <th class="min-w-100px">Người bán</th>
                                                <th class="min-w-100px">Người tạo</th>
                                                <th class="min-w-80px">Giảm giá</th>
                                                <th class="min-w-150px">Email</th>
                                                <th class="min-w-100px">Phone</th>
                                                <th class="min-w-200px">Địa chỉ</th>
                                                <th class="min-w-100px">Chi nhánh</th>
                                                <th class="min-w-150px">Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600" id="returns-table-body">
                                            <!-- Data will be loaded here by JavaScript -->
                                        </tbody>
                                    </table>

                                    <!-- Border spans for return detail panels -->
                                    {{-- <span class="return-detail-border-left"></span>
                                    <span class="return-detail-border-right"></span> --}}
                                </div>
                                <!--end::Table container-->

                                <!--begin::Pagination-->
                                <div class="d-flex flex-stack flex-wrap pt-10">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-6 fw-semibold text-gray-700" id="kt_returns_table_info">
                                            Hiển thị 0 đến 0 của 0 kết quả
                                        </div>
                                        <div class="ms-7">
                                            <select class="form-select form-select-sm w-auto" id="kt_returns_per_page">
                                                <option value="10">10 / trang</option>
                                                <option value="25">25 / trang</option>
                                                <option value="50">50 / trang</option>
                                                <option value="100">100 / trang</option>
                                            </select>
                                        </div>
                                    </div>
                                    <ul class="pagination kt_table_pagination" id="kt_returns_table_pagination">
                                        <!-- Pagination links will be generated here -->
                                    </ul>
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
    </div>

    <!--end::Content-->



@endsection

@section('scripts')
    <!-- Include global utilities and filter scripts -->
    <script src="{{ asset('admin-assets/globals/date-utils.js') }}"></script>
    <script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
    <script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>
    <!-- Include base table manager and return-specific scripts -->
    <script src="{{ asset('admin-assets/js/base/table-manager.js') }}"></script>
    <script src="{{ asset('admin-assets/js/returns/return-manager.js') }}?v={{ time() }}"></script>

    <script>
        // Return routes configuration
        window.returnAjaxUrl = '{{ route('admin.return.ajax') }}';
        window.returnRoutes = {
            data: '{{ route('admin.return.ajax') }}',
            get: '{{ route('admin.return.show', ':id') }}',
            detail: '{{ route('admin.return.detail-panel', ':id') }}',
            update: '{{ route('admin.return.update', ':id') }}',
            delete: '{{ route('admin.return.delete', ':id') }}',
            bulkDelete: '{{ route('admin.return.bulk.delete') }}',
            bulkCancel: '{{ route('admin.return.bulk.cancel') }}',
            bulkUpdateStatus: '{{ route('admin.return.bulk.update-status') }}',
            exportExcel: '{{ route('admin.return.export.excel') }}',
            exportPdf: '{{ route('admin.return.export.pdf', ':id') }}',
            print: '{{ route('admin.return.print', ':id') }}'
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing Returns Page...');

            // Initialize return table manager first
            window.returnTableManager = new ReturnTableManager();

            // Initialize filters using KTGlobalFilter
            if (typeof KTGlobalFilter !== 'undefined') {
                KTGlobalFilter.initAllFilters('#kt_return_filter_form', function() {
                    if (window.returnTableManager) {
                        window.returnTableManager.loadData();
                    }
                });
            } else {
                console.error('KTGlobalFilter not found');
            }

            // Load initial data immediately after initialization
            setTimeout(function() {
                if (window.returnTableManager) {
                    console.log('Loading initial return data...');
                    window.returnTableManager.loadData();
                }
            }, 100);
        });

        // Export functions
        window.exportReturn = function(id) {
            const url = window.returnRoutes.exportPdf.replace(':id', id);
            window.open(url, '_blank');
        };

        window.exportReturnsExcel = function() {
            const params = new URLSearchParams();

            // Add current filters
            const timeFilter = document.querySelector('input[name="time_filter"]:checked');
            if (timeFilter) params.append('time_filter', timeFilter.value);

            const statusFilters = document.querySelectorAll('input[name="status_filter[]"]:checked');
            statusFilters.forEach(filter => params.append('status_filter[]', filter.value));

            const creatorFilters = document.querySelectorAll('input[name="creator_filter[]"]:checked');
            creatorFilters.forEach(filter => params.append('creator_filter[]', filter.value));

            const url = window.returnRoutes.exportExcel + '?' + params.toString();
            window.open(url, '_blank');
        };

        window.exportReturnsPdf = function() {
            // For now, just export Excel since PDF export for multiple returns needs special handling
            exportReturnsExcel();
        };

        // Print function
        window.printReturn = function(id) {
            const url = window.returnRoutes.print.replace(':id', id);
            window.open(url, '_blank');
        };

        // Edit function
        window.editReturn = function(id) {
            const url = window.returnRoutes.update.replace(':id', id);
            window.location.href = url;
        };

        // Delete function
        window.deleteReturn = function(id) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn trả hàng này?')) {
                fetch(window.returnRoutes.delete.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        window.returnTableManager.reload();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Có lỗi xảy ra khi xóa đơn trả hàng.');
                });
            }
        };

        // Bulk actions
        window.bulkUpdateReturnStatus = function(status) {
            const selectedIds = getSelectedReturnIds();
            if (selectedIds.length === 0) {
                toastr.warning('Vui lòng chọn ít nhất một đơn trả hàng.');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn cập nhật trạng thái cho ${selectedIds.length} đơn trả hàng?`)) {
                fetch(window.returnRoutes.bulkUpdateStatus, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: selectedIds,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        window.returnTableManager.reload();
                        clearReturnSelection();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Có lỗi xảy ra khi cập nhật trạng thái.');
                });
            }
        };

        window.bulkCancelReturns = function() {
            const selectedIds = getSelectedReturnIds();
            if (selectedIds.length === 0) {
                toastr.warning('Vui lòng chọn ít nhất một đơn trả hàng.');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn hủy ${selectedIds.length} đơn trả hàng?`)) {
                fetch(window.returnRoutes.bulkCancel, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: selectedIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        window.returnTableManager.reload();
                        clearReturnSelection();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Có lỗi xảy ra khi hủy đơn trả hàng.');
                });
            }
        };

        window.bulkDeleteReturns = function() {
            const selectedIds = getSelectedReturnIds();
            if (selectedIds.length === 0) {
                toastr.warning('Vui lòng chọn ít nhất một đơn trả hàng.');
                return;
            }

            if (confirm(`Bạn có chắc chắn muốn xóa ${selectedIds.length} đơn trả hàng? Chỉ có thể xóa đơn trả hàng ở trạng thái nháp.`)) {
                fetch(window.returnRoutes.bulkDelete, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: selectedIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        window.returnTableManager.reload();
                        clearReturnSelection();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Có lỗi xảy ra khi xóa đơn trả hàng.');
                });
            }
        };

        // Helper functions
        function getSelectedReturnIds() {
            const checkboxes = document.querySelectorAll('input[name="return_ids[]"]:checked');
            return Array.from(checkboxes).map(cb => parseInt(cb.value));
        }

        function clearReturnSelection() {
            const checkboxes = document.querySelectorAll('input[name="return_ids[]"]');
            checkboxes.forEach(cb => cb.checked = false);

            const selectAllCheckbox = document.querySelector('#select-all-returns');
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
        }

    </script>
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
