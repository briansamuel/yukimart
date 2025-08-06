@extends('admin.main-content')

@section('title', 'Quản lý đơn hàng')

@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/css/table-loading.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/css/order-list.css') }}" rel="stylesheet" type="text/css" />
@include('admin.orders.elements.row-expansion-styles')
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
                @include('admin.orders.elements.filter')

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-15 order-2 order-lg-2">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::Card-->
                        <div class="card card-flush">
                            <!--begin::Card header-->
                            <div id="kt_orders_table_toolbar" class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <!--begin::Search-->
                                    <div class="d-flex align-items-center position-relative my-1">
                                        <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                        <input type="text" id="kt_orders_search"
                                            class="form-control form-control-solid w-250px ps-12"
                                            placeholder="Tìm kiếm đơn hàng..." />
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
                                    
                                    <!--begin::Export dropdown-->
                                    <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="fas fa-download"></i>
                                        Xuất Excel
                                    </button>
                                    <!--end::Export dropdown-->

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
                                                                    type="checkbox" value="1" id="col_order_code"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_order_code">Mã đơn hàng</label>
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
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="4" id="col_amount_paid"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_amount_paid">Đã thanh toán</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="5" id="col_status"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_status">Trạng thái</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="6" id="col_payment_status"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_payment_status">TT Thanh toán</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="7" id="col_delivery_status"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_delivery_status">TT Giao hàng</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="8" id="col_channel"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_channel">Kênh bán</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="9" id="col_created_at"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_created_at">Ngày tạo</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="10" id="col_seller"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_seller">Người bán</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="11" id="col_creator"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_creator">Người tạo</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="12" id="col_email"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_email">Email</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="13" id="col_branch_shop"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_branch_shop">Chi nhánh</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Column visibility-->

                                    <!--begin::Add order-->
                                    <a href="{{ route('admin.order.add') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Thêm mới
                                    </a>
                                    <!--end::Add order-->
                                </div>
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card header-->

                            @if ($orderCodeSearch)
                                <!--begin::Order code search info-->
                                <div class="card-body border-bottom py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <div class="me-5">
                                                <i class="fas fa-shopping-cart text-primary fs-2 me-2"></i>
                                                <span class="fw-bold text-gray-800">Tìm kiếm theo mã đơn hàng:</span>
                                                <span
                                                    class="badge badge-light-primary fs-7 ms-2">{{ $orderCodeSearch }}</span>
                                            </div>
                                            @if ($searchedOrder)
                                                <div class="d-flex align-items-center">
                                                    <span class="text-muted me-2">Đơn hàng:</span>
                                                    <span
                                                        class="fw-bold text-success">{{ $searchedOrder->customer_id == 0 ? 'Khách lẻ' : ($searchedOrder->customer->name ?? 'N/A') }}</span>
                                                    <span
                                                        class="text-muted ms-2">({{ number_format($searchedOrder->final_amount) }}
                                                        ₫)</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Không tìm thấy đơn hàng với mã này
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ms-auto">
                                            <a href="{{ route('admin.order.list') }}"
                                                class="btn btn-light-danger btn-sm">
                                                <i class="fas fa-times"></i>Xóa bộ lọc
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Order code search info-->
                            @endif

                            <!--begin::Card body-->
                            <div id="kt_orders_container_body" class="card-body pt-0 kt_table_container_body">
                                <!--begin::Table container-->
                                <div id="kt_orders_table_container" class="kt_table_responsive_container">
                                    <!--begin::Table-->
                                    <table id="kt_orders_table" class="table align-middle table-row-dashed fs-6 gy-5 kt_table_responsive">
                                        <!--begin::Table head-->
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="kt_orders_select_all" />
                                                    </div>
                                                </th>
                                                <th class="min-w-100px">Mã đơn hàng</th>
                                                <th class="min-w-125px">Khách hàng</th>
                                                <th class="min-w-100px">Tổng tiền</th>
                                                <th class="min-w-100px">Đã thanh toán</th>
                                                <th class="min-w-100px">Trạng thái</th>
                                                <th class="min-w-100px">TT Thanh toán</th>
                                                <th class="min-w-100px">TT Giao hàng</th>
                                                <th class="min-w-100px">Kênh bán</th>
                                                <th class="min-w-125px">Ngày tạo</th>
                                                <th class="min-w-100px">Người bán</th>
                                                <th class="min-w-100px">Người tạo</th>
                                                <th class="min-w-125px">Email</th>
                                                <th class="min-w-100px">Chi nhánh</th>
                                            </tr>
                                        </thead>
                                        <!--end::Table head-->
                                        <!--begin::Table body-->
                                        <tbody class="fw-semibold text-gray-600">
                                            <!-- Data will be loaded here -->
                                        </tbody>
                                        <!--end::Table body-->
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->

                                <!--begin::Pagination-->
                                <div class="d-flex flex-stack flex-wrap pt-10">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-6 fw-semibold text-gray-700" id="kt_orders_table_info">
                                            Hiển thị 0 đến 0 của 0 kết quả
                                        </div>
                                        <div class="ms-7">
                                            <select class="form-select form-select-sm w-auto" id="kt_orders_per_page">
                                                <option value="10">10 / trang</option>
                                                <option value="25">25 / trang</option>
                                                <option value="50">50 / trang</option>
                                                <option value="100">100 / trang</option>
                                            </select>
                                        </div>
                                    </div>
                                    <ul class="pagination kt_table_pagination" id="kt_orders_table_pagination">
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
    <!-- Include base table manager and order-specific manager -->
    <script src="{{ asset('admin-assets/js/base/table-manager.js') }}"></script>
    <script src="{{ asset('admin-assets/js/orders/order-manager.js') }}"></script>

    <script>
        // Order routes configuration
        window.orderRoutes = {
            data: '{{ route('admin.order.ajax') }}',
            get: '{{ route('admin.order.get', ':id') }}',
            detail: '{{ route('admin.order.detail', ':id') }}',
            update: '{{ route('admin.order.edit.action', ':id') }}',
            delete: '{{ route('admin.order.delete', ':id') }}',
            bulkDelete: '{{ route('admin.order.bulk.delete') }}',
            bulkUpdate: '{{ route('admin.order.bulk.status.update') }}',
            export: '{{ route('admin.order.export.single', ':id') }}'
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing Orders Page...');

            // Initialize order table manager FIRST
            window.orderTableManager = new OrderTableManager();

            // Initialize filters using KTGlobalFilter AFTER orderTableManager is created
            if (typeof KTGlobalFilter !== 'undefined') {
                KTGlobalFilter.initAllFilters('#kt_orders_filter_form', 'orders', function() {
                    if (window.orderTableManager) {
                        window.orderTableManager.loadData();
                    }
                });
            }

            // Load initial data immediately after initialization
            setTimeout(function() {
                if (window.orderTableManager) {
                    console.log('Loading initial order data...');
                    window.orderTableManager.loadData();
                }
            }, 100);
        });
    </script>
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('page-script')
    <!-- Global scripts -->
    <script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
    <script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>

    <!-- Page specific scripts -->
    <script src="{{ asset('admin-assets/js/base/table-manager.js') }}"></script>
    <script src="{{ asset('admin-assets/js/orders/order-manager.js') }}"></script>
@endsection
