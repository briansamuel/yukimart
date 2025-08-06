@extends('admin.main-content')

@section('title', 'Quản lý hóa đơn')

@section('style')
    <link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
    <link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/globals/table-row-expansion.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/invoice-list.css') }}" rel="stylesheet" type="text/css" />
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
                @include('admin.invoice.elements.filter')

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-15 order-2 order-lg-2">
                    <div class="d-flex flex-column gap-7 gap-lg-10">

                        <!--begin::Card-->
                        <div class="card card-flush">
                            <!--begin::Card header-->
                            <div id="kt_invoices_table_toolbar" class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <!--begin::Search-->
                                    <div class="d-flex align-items-center position-relative my-1" bis_skin_checked="1">
                                        <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                        <input type="text" id="invoice_search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm hoá đơn...">
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
                                            <li><a class="dropdown-item" href="#" id="export-excel-btn">
                                                <i class="fas fa-file-excel text-success me-2"></i>Xuất Excel
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" id="export-pdf-btn">
                                                <i class="fas fa-file-pdf text-danger me-2"></i>Xuất PDF
                                            </a></li>
                                        </ul>
                                    </div>
                                    <!--end::Export-->
                                    <!--begin::Add invoice-->
                                    <a href="{{ route('admin.quick-order.index') }}" class="btn btn-primary">
                                        <i class="fas fa-plus fs-2"></i>Thêm mới
                                    </a>
                                    <!--end::Add invoice-->



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

                            @if ($invoiceCodeSearch)
                                <!--begin::Invoice code search info-->
                                <div class="card-body border-bottom py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <div class="me-5">
                                                <i class="fas fa-file-invoice text-primary fs-2 me-2"></i>
                                                <span class="fw-bold text-gray-800">Tìm kiếm theo mã hóa đơn:</span>
                                                <span
                                                    class="badge badge-light-primary fs-7 ms-2">{{ $invoiceCodeSearch }}</span>
                                            </div>
                                            @if ($searchedInvoice)
                                                <div class="d-flex align-items-center">
                                                    <span class="text-muted me-2">Hóa đơn:</span>
                                                    <span
                                                        class="fw-bold text-success">{{ $searchedInvoice->customer_name }}</span>
                                                    <span
                                                        class="text-muted ms-2">({{ number_format($searchedInvoice->total_amount) }}
                                                        ₫)</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Không tìm thấy hóa đơn với mã này
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ms-auto">
                                            <a href="{{ route('admin.invoice.list') }}"
                                                class="btn btn-light-danger btn-sm">
                                                <i class="fas fa-times"></i>Xóa bộ lọc
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Barcode search info-->
                            @endif

                            <!--begin::Card body-->
                            <div id="kt_invoices_container_body" class="kt_table_container_body card-body pt-0">

                                <!--begin::Table container with horizontal scroll-->
                                <div id="kt_invoices_table_container" class="kt_table_responsive_container">
                                    <table class="kt_table_responsive table align-middle table-row-dashed fs-6 gy-5" id="kt_invoices_table">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div
                                                        class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="select-all-invoices" value="1" />
                                                    </div>
                                                </th>
                                                <th class="min-w-100px">Mã hóa đơn</th>
                                                <th class="min-w-150px">Khách hàng</th>
                                                <th class="min-w-100px">Tổng tiền</th>
                                                <th class="min-w-100px">Đã thanh toán</th>
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
                                        <tbody class="fw-semibold text-gray-600" id="invoices-table-body">
                                            <!-- Data will be loaded here by JavaScript -->
                                        </tbody>
                                    </table>

                                    <!-- Border spans for invoice detail panels -->
                                    {{-- <span class="invoice-detail-border-left"></span>
                                    <span class="invoice-detail-border-right"></span> --}}
                                </div>
                                <!--end::Table container-->

                                <!--begin::Pagination-->
                                <div class="d-flex flex-stack flex-wrap pt-10">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-6 fw-semibold text-gray-700" id="kt_invoices_table_info">
                                            Hiển thị 0 đến 0 của 0 kết quả
                                        </div>
                                        <div class="ms-7">
                                            <select class="form-select form-select-sm w-auto" id="kt_invoices_per_page">
                                                <option value="10">10 / trang</option>
                                                <option value="25">25 / trang</option>
                                                <option value="50">50 / trang</option>
                                                <option value="100">100 / trang</option>
                                            </select>
                                        </div>
                                    </div>
                                    <ul class="pagination kt_table_pagination" id="kt_invoices_table_pagination">
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
    <!-- Include base table manager and invoice-specific scripts -->
    <script src="{{ asset('admin-assets/js/base/table-manager.js') }}"></script>
    <script src="{{ asset('admin-assets/js/invoices/invoice-manager.js') }}"></script>

    <script>
        // Invoice routes configuration
        window.invoiceAjaxUrl = '{{ route('admin.invoice.ajax') }}';
        window.invoiceRoutes = {
            data: '{{ route('admin.invoice.ajax') }}',
            get: '{{ route('admin.invoice.show', ':id') }}',
            detail: '{{ route('admin.invoice.detail-panel', ':id') }}',
            update: '{{ route('admin.invoice.update', ':id') }}',
            delete: '{{ route('admin.invoice.delete', ':id') }}',
            bulkDelete: '{{ route('admin.invoice.bulk-cancel') }}',
            bulkUpdate: '{{ route('admin.invoice.bulk-cancel') }}',
            export: '{{ route('admin.invoice.print', ':id') }}'
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing Invoices Page...');

            // Initialize invoice table manager first
            window.invoiceTableManager = new InvoiceTableManager();

            // Initialize filters using KTGlobalFilter
            if (typeof KTGlobalFilter !== 'undefined') {
                KTGlobalFilter.initAllFilters('#kt_invoice_filter_form', function() {
                    if (window.invoiceTableManager) {
                        window.invoiceTableManager.loadData();
                    }
                });
            } else {
                console.error('KTGlobalFilter not found');
            }

            // Load initial data immediately after initialization
            setTimeout(function() {
                if (window.invoiceTableManager) {
                    console.log('Loading initial invoice data...');
                    window.invoiceTableManager.loadData();
                }
            }, 100);
        });
    </script>
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
