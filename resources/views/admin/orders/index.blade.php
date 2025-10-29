@extends('admin.layouts.tenant-app')

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
                <div class="flex-lg-row-fluid ms-lg-10 order-2 order-lg-2">
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
                                    
                                    <!--begin::Reset Filters-->
                                    <button type="button" class="btn btn-light-warning" id="reset_filters_btn">
                                        <i class="fas fa-redo"></i>
                                        Reset Filters
                                    </button>
                                    <!--end::Reset Filters-->

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
                                    <a href="{{ route('admin.order.create') }}" class="btn btn-primary">
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

                                <!-- Order Detail Panel -->
                                <div id="order-detail-panel" class="order-detail-panel mt-10" style="display: none;">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h3 class="fw-bold">Chi tiết đơn hàng</h3>
                                            </div>
                                            <div class="card-toolbar">
                                                <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" id="close-order-detail">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Order Information -->
                                                <div class="col-md-6">
                                                    <div class="card card-flush h-md-100">
                                                        <div class="card-header">
                                                            <div class="card-title">
                                                                <h4>Thông tin đơn hàng</h4>
                                                            </div>
                                                        </div>
                                                        <div class="card-body pt-5">
                                                            <div class="table-responsive">
                                                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class="text-muted">Mã đơn hàng:</td>
                                                                            <td class="fw-bold" id="detail-order-code">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Trạng thái:</td>
                                                                            <td id="detail-status">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Thanh toán:</td>
                                                                            <td id="detail-payment-status">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Giao hàng:</td>
                                                                            <td id="detail-delivery-status">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Ngày tạo:</td>
                                                                            <td id="detail-created-at">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Người tạo:</td>
                                                                            <td id="detail-creator">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Người bán:</td>
                                                                            <td id="detail-seller">-</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Customer Information -->
                                                <div class="col-md-6">
                                                    <div class="card card-flush h-md-100">
                                                        <div class="card-header">
                                                            <div class="card-title">
                                                                <h4>Thông tin khách hàng</h4>
                                                            </div>
                                                        </div>
                                                        <div class="card-body pt-5">
                                                            <div class="table-responsive">
                                                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class="text-muted">Tên khách hàng:</td>
                                                                            <td class="fw-bold" id="detail-customer-name">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Số điện thoại:</td>
                                                                            <td id="detail-customer-phone">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Email:</td>
                                                                            <td id="detail-customer-email">-</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-muted">Chi nhánh:</td>
                                                                            <td id="detail-branch-shop">-</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Financial Summary -->
                                            <div class="row mt-5">
                                                <div class="col-12">
                                                    <div class="card card-flush">
                                                        <div class="card-header">
                                                            <div class="card-title">
                                                                <h4>Tổng kết tài chính</h4>
                                                            </div>
                                                        </div>
                                                        <div class="card-body pt-5">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="symbol symbol-50px me-5">
                                                                            <span class="symbol-label bg-light-primary">
                                                                                <i class="fas fa-shopping-cart text-primary fs-2x"></i>
                                                                            </span>
                                                                        </div>
                                                                        <div class="d-flex flex-column">
                                                                            <span class="text-muted fs-7">Số lượng</span>
                                                                            <span class="fw-bold fs-3" id="detail-total-quantity">0</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="symbol symbol-50px me-5">
                                                                            <span class="symbol-label bg-light-success">
                                                                                <i class="fas fa-money-bill text-success fs-2x"></i>
                                                                            </span>
                                                                        </div>
                                                                        <div class="d-flex flex-column">
                                                                            <span class="text-muted fs-7">Tổng tiền</span>
                                                                            <span class="fw-bold fs-3 text-success" id="detail-total-amount">0 ₫</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="symbol symbol-50px me-5">
                                                                            <span class="symbol-label bg-light-info">
                                                                                <i class="fas fa-credit-card text-info fs-2x"></i>
                                                                            </span>
                                                                        </div>
                                                                        <div class="d-flex flex-column">
                                                                            <span class="text-muted fs-7">Đã thanh toán</span>
                                                                            <span class="fw-bold fs-3 text-info" id="detail-paid-amount">0 ₫</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="symbol symbol-50px me-5">
                                                                            <span class="symbol-label bg-light-warning">
                                                                                <i class="fas fa-exclamation-triangle text-warning fs-2x"></i>
                                                                            </span>
                                                                        </div>
                                                                        <div class="d-flex flex-column">
                                                                            <span class="text-muted fs-7">Còn lại</span>
                                                                            <span class="fw-bold fs-3 text-warning" id="detail-remaining-amount">0 ₫</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row mt-5">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-end">
                                                        <button type="button" class="btn btn-light me-3" id="close-order-detail-bottom">
                                                            <i class="fas fa-times"></i>
                                                            Đóng
                                                        </button>
                                                        <button type="button" class="btn btn-primary me-3" id="edit-order-btn">
                                                            <i class="fas fa-edit"></i>
                                                            Chỉnh sửa
                                                        </button>
                                                        <button type="button" class="btn btn-success" id="export-order-btn">
                                                            <i class="fas fa-download"></i>
                                                            Xuất Excel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Order Detail Panel -->

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
     @include('admin.elements.time_options_panel')
@endsection

@section('scripts')
    <!-- Include global utilities and filter scripts -->
    <script src="{{ asset('admin-assets/globals/date-utils.js') }}"></script>
    <script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
    <script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>
    <!-- Include base table manager and order-specific manager -->
    <script src="{{ asset('admin-assets/js/base/table-manager.js') }}"></script>
    <script src="{{ asset('admin-assets/js/orders/order-manager.js') }}?v={{ time() }}"></script>

    <script>
        // Order routes configuration
        window.orderRoutes = {
            data: '{{ url('/admin/orders/ajax') }}',
            get: '{{ url('/admin/orders/get/:id') }}',
            detail: '{{ url('/admin/orders/detail/:id') }}',
            edit: '{{ url('/admin/orders/edit/:id') }}',
            update: '{{ url('/admin/orders/update/:id') }}',
            delete: '{{ url('/admin/orders/delete/:id') }}',
            bulkDelete: '{{ url('/admin/orders/bulk-delete') }}',
            bulkUpdate: '{{ url('/admin/orders/bulk-update') }}',
            export: '{{ url('/admin/orders/export/:id') }}'
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing Orders Page...');

            // Initialize order table manager FIRST
            window.orderTableManager = new OrderTableManager();

            // Initialize filters using KTGlobalFilter AFTER orderTableManager is created
            if (typeof KTGlobalFilter !== 'undefined') {
                KTGlobalFilter.initAllFilters('#kt_orders_filter_form', function() {
                    if (window.orderTableManager) {
                        // Reset to page 1 when filter changes
                        window.orderTableManager.currentFilters.page = 1;
                        window.orderTableManager.loadData();
                    }
                }, {
                    module: 'orders',
                    deliveryStatusFilter: true,
                    deliveryTimeFilter: true
                });
            }

            // Load initial data immediately after initialization
            setTimeout(function() {
                if (window.orderTableManager) {
                    console.log('Loading initial order data...');
                    window.orderTableManager.loadData();
                }
            }, 100);

            // Apply saved filter state to UI after filters are loaded
            setTimeout(() => {
                applySavedFilterState();
            }, 500);

            // Initialize order detail panel functionality
            initOrderDetailPanel();
        });

        /**
         * Apply saved filter state to UI elements
         */
        function applySavedFilterState() {
            const savedState = window.KTGlobalFilter.loadFilterState('orders');
            if (!savedState) return;

            console.log('Applying saved filter state to UI:', savedState);

            // Apply status filter
            if (savedState.status && savedState.status.length > 0) {
                $('#status_filter').val(savedState.status).trigger('change.select2');
            }

            // Apply delivery status filter
            if (savedState.delivery_status) {
                $('#delivery_status_filter').val(savedState.delivery_status).trigger('change.select2');
            }

            // Apply creator filter
            if (savedState.created_by) {
                $('#creator_filter').val(savedState.created_by).trigger('change.select2');
            }

            // Apply seller filter
            if (savedState.sold_by) {
                $('#seller_filter').val(savedState.sold_by).trigger('change.select2');
            }

            // Apply sale channel filter
            if (savedState.sale_channel) {
                $('#sale_channel_filter').val(savedState.sale_channel).trigger('change.select2');
            }

            // Apply payment method filter
            if (savedState.payment_method) {
                $('#payment_method_filter').val(savedState.payment_method).trigger('change.select2');
            }

            // Apply time filter
            if (savedState.time_filter_display) {
                $(`input[name="time_filter_display"][value="${savedState.time_filter_display}"]`).prop('checked', true);
            }
            if (savedState.date_from) {
                $('#date_from').val(savedState.date_from);
            }
            if (savedState.date_to) {
                $('#date_to').val(savedState.date_to);
            }

            // Apply delivery time filter
            if (savedState.delivery_time_filter) {
                $(`input[name="delivery_time_filter"][value="${savedState.delivery_time_filter}"]`).prop('checked', true);
            }
            if (savedState.delivery_date_from) {
                $('#delivery_date_from').val(savedState.delivery_date_from);
            }
            if (savedState.delivery_date_to) {
                $('#delivery_date_to').val(savedState.delivery_date_to);
            }

            console.log('Saved filter state applied to UI');
        }

        /**
         * Initialize order detail panel functionality
         */
        function initOrderDetailPanel() {
            console.log('Initializing order detail panel...');

            // DISABLED: OrderTableManager now handles row clicks with detail panel expansion
            // Handle row click events to show order details
            // $(document).on('click', '#kt_orders_table tbody tr', function(e) {
            //     // Prevent action if clicking on checkbox or action buttons
            //     if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.btn').length > 0) {
            //         return;
            //     }

            //     const orderId = $(this).data('order-id');
            //     if (orderId) {
            //         showOrderDetail(orderId);
            //     }
            // });

            // Handle close button clicks
            $(document).on('click', '#close-order-detail, #close-order-detail-bottom', function() {
                hideOrderDetail();
            });

            // Handle action buttons
            $(document).on('click', '#edit-order-btn', function() {
                const orderId = $('#order-detail-panel').data('current-order-id');
                if (orderId) {
                    window.location.href = window.orderRoutes.edit.replace(':id', orderId);
                }
            });

            $(document).on('click', '#export-order-btn', function() {
                const orderId = $('#order-detail-panel').data('current-order-id');
                if (orderId) {
                    window.location.href = window.orderRoutes.export.replace(':id', orderId);
                }
            });

            console.log('Order detail panel initialized successfully');
        }

        /**
         * Show order detail panel with data
         */
        function showOrderDetail(orderId) {
            console.log('Showing order detail for ID:', orderId);

            // Show loading state
            $('#order-detail-panel').show();
            $('#order-detail-panel').data('current-order-id', orderId);

            // Fetch order details
            $.ajax({
                url: window.orderRoutes.detail.replace(':id', orderId),
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        populateOrderDetail(response.data);
                    } else {
                        console.error('Failed to load order details:', response.message);
                        hideOrderDetail();
                        alert('Không thể tải chi tiết đơn hàng: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading order details:', error);
                    hideOrderDetail();
                    alert('Có lỗi xảy ra khi tải chi tiết đơn hàng');
                }
            });
        }

        /**
         * Hide order detail panel
         */
        function hideOrderDetail() {
            $('#order-detail-panel').hide();
            $('#order-detail-panel').removeData('current-order-id');
        }

        /**
         * Populate order detail panel with data
         */
        function populateOrderDetail(order) {
            console.log('Populating order detail with data:', order);

            // Order information
            $('#detail-order-code').text(order.order_code || '-');
            $('#detail-status').html(order.status_label || '-');
            $('#detail-payment-status').html(order.payment_status_label || '-');
            $('#detail-delivery-status').html(order.delivery_status_label || '-');
            $('#detail-created-at').text(order.created_at || '-');
            $('#detail-creator').text(order.creator_name || '-');
            $('#detail-seller').text(order.seller_name || '-');

            // Customer information
            $('#detail-customer-name').text(order.customer_name || '-');
            $('#detail-customer-phone').text(order.customer_phone || '-');
            $('#detail-customer-email').text(order.customer_email || '-');
            $('#detail-branch-shop').text(order.branch_shop_name || '-');

            // Financial summary
            $('#detail-total-quantity').text(order.total_quantity || 0);
            $('#detail-total-amount').text(order.total_amount_formatted || '0 ₫');
            $('#detail-paid-amount').text(order.paid_amount_formatted || '0 ₫');

            // Calculate remaining amount
            const totalAmount = parseFloat(order.total_amount || 0);
            const paidAmount = parseFloat(order.paid_amount || 0);
            const remainingAmount = totalAmount - paidAmount;
            $('#detail-remaining-amount').text(formatCurrency(remainingAmount) + ' ₫');

            // Update remaining amount color based on value
            const $remainingElement = $('#detail-remaining-amount');
            if (remainingAmount > 0) {
                $remainingElement.removeClass('text-success').addClass('text-warning');
            } else {
                $remainingElement.removeClass('text-warning').addClass('text-success');
            }

            console.log('Order detail populated successfully');
        }

        /**
         * Format currency number
         */
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }
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
    <script src="{{ asset('admin-assets/js/orders/order-manager.js') }}?v={{ time() }}"></script>
@endsection
