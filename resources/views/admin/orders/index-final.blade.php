@extends('admin.main-content')

@section('title', 'Quản lý đơn hàng')

@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/css/order-list.css') }}" rel="stylesheet" type="text/css" />
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
                                    <!--begin::Bulk actions-->
                                    <div class="bulk-actions-container d-flex align-items-center gap-2" style="display: none !important;">
                                        <span class="text-muted fs-7">
                                            Đã chọn <span class="selected-count fw-bold">0</span> đơn hàng
                                        </span>
                                        <div class="separator separator-dashed mx-3"></div>
                                        <button type="button" class="btn btn-sm btn-light-danger" id="bulk-delete-btn">
                                            <i class="fas fa-trash"></i>
                                            Xóa
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light-primary" id="bulk-status-btn">
                                            <i class="fas fa-edit"></i>
                                            Cập nhật trạng thái
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light-success" id="bulk-export-btn">
                                            <i class="fas fa-download"></i>
                                            Xuất Excel
                                        </button>
                                    </div>
                                    <!--end::Bulk actions-->
                                    
                                    <!--begin::Export dropdown-->
                                    <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="fas fa-download"></i>
                                        Xuất Excel
                                    </button>
                                    <!--end::Export dropdown-->
                                    
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

                            <!--begin::Card body-->
                            <div class="card-body pt-0">
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
            detail: '{{ route('admin.order.detail.modal', ':id') }}',
            update: '{{ route('admin.order.update', ':id') }}',
            delete: '{{ route('admin.order.delete', ':id') }}',
            bulkDelete: '{{ route('admin.order.bulk.delete') }}',
            bulkUpdate: '{{ route('admin.order.bulk.update') }}',
            export: '{{ route('admin.order.export') }}'
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing Orders Page...');

            // Initialize filters using KTGlobalFilter
            if (typeof KTGlobalFilter !== 'undefined') {
                KTGlobalFilter.init('#kt_orders_filter_form', function() {
                    if (window.orderTableManager) {
                        window.orderTableManager.loadData();
                    }
                });
            }

            // Initialize order table manager
            window.orderTableManager = new OrderTableManager();
        });
    </script>
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
