@extends('admin.layouts.tenant-app')

@section('title', 'Quản lý phiếu thu/chi')

@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin-assets/css/table-loading.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('admin-assets/css/payment-list.css') }}" />
@include('admin.payment.elements.row-expansion-styles')
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
                @include('admin.payment.elements.filter')

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-10 order-2 order-lg-2">
                    <div class="d-flex flex-column gap-7 gap-lg-10">

                    <!--begin::Summary Cards-->
                    <div class="row g-5 g-xl-8">
                        <div class="col-xl-3 col-sm-6">
                            <div class="summary-card mb-0 income">
                                <div class="summary-title">Quỹ đầu kỳ</div>
                                <div class="summary-value" id="opening_balance">
                                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="summary-card mb-0 total">
                                <div class="summary-title">Tổng thu</div>
                                <div class="summary-value text-success" id="total_income">
                                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="summary-card mb-0 expense">
                                <div class="summary-title">Tổng chi</div>
                                <div class="summary-value text-danger" id="total_expense">
                                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="summary-card mb-0 balance">
                                <div class="summary-title">Quỹ cuối kỳ</div>
                                <div class="summary-value" id="closing_balance">
                                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Summary Cards-->

                    <!--begin::Card-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Search-->
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                    <input type="text" id="payment_search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm phiếu thu/chi..." />
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                <!--begin::Bulk actions (hidden by default)-->
                                <div id="bulk_actions" class="d-none align-items-center gap-2">
                                    <span class="text-muted">Đã chọn <span id="selected_count">0</span> mục:</span>
                                    <button type="button" class="btn btn-sm btn-light-danger" id="bulk_delete">
                                        <i class="fas fa-trash fs-3"></i>Xóa
                                    </button>
                                </div>
                                <!--end::Bulk actions-->

                                <!--begin::Export-->
                                <button type="button" class="btn btn-light-primary">
                                    <i class="fas fa-file-excel fs-2"></i>Xuất Excel
                                </button>
                                <!--end::Export-->

                                <!--begin::Add payment dropdown-->
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-plus fs-2"></i>Phiếu thu
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.payment.create', ['type' => 'receipt']) }}">
                                            <i class="fas fa-arrow-down text-success me-2"></i>Phiếu thu
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.payment.create', ['type' => 'disbursement']) }}">
                                            <i class="fas fa-arrow-up text-danger me-2"></i>Phiếu chi
                                        </a></li>
                                    </ul>
                                </div>
                                <!--end::Add payment dropdown-->

                                <!--begin::Column visibility-->
                                <div class="position-relative">
                                    <button type="button" class="btn btn-success" id="column_visibility_trigger">
                                        <i class="fas fa-list fs-2"></i>
                                    </button>
                                    <!-- Column visibility panel -->
                                    <div id="column_visibility_panel" class="column-visibility-panel position-absolute" style="display: none;">
                                        <div class="panel-content">
                                            <div class="panel-header">
                                                <h6 class="fw-bold text-dark mb-0">Chọn cột hiển thị</h6>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="0" id="col_checkbox" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_checkbox">Checkbox</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="1" id="col_payment_number" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_payment_number">Mã phiếu</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="2" id="col_date" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_date">Thời gian</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="3" id="col_type" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_type">Loại thu chi</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="4" id="col_customer" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_customer">Người nộp/nhận</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="5" id="col_amount" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_amount">Giá trị</label>
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
                        <div class="card-body pt-0">
                            <!--begin::Table container-->
                            <div id="payments_table_container" class="table-responsive" style="position: relative; overflow-x: auto; max-height: 523px;">
                                <!--begin::Table-->
                                <table id="payments_custom_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                    <!--begin::Table head-->
                                    <thead>
                                        <!--begin::Table row-->
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#payments_custom_table .form-check-input" value="1" />
                                                </div>
                                            </th>
                                            <th class="min-w-100px">Mã phiếu</th>
                                            <th class="min-w-100px">Thời gian</th>
                                            <th class="min-w-100px">Loại thu chi</th>
                                            <th class="min-w-150px">Người nộp/nhận</th>
                                            <th class="min-w-100px text-end">Giá trị</th>
                                        </tr>
                                        <!--end::Table row-->
                                    </thead>
                                    <!--begin::Table body-->
                                    <tbody class="fw-semibold text-gray-600">
                                        <!-- Data will be loaded via AJAX -->
                                        <tr>
                                            <td colspan="6" class="text-center py-10">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Đang tải...</span>
                                                    </div>
                                                    <div class="mt-3 text-muted">Đang tải dữ liệu...</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table container-->
                            
                            <!--begin::Pagination-->
                            <div class="d-flex flex-stack flex-wrap pt-10">
                                <div class="d-flex align-items-center">
                                    <div class="fs-6 fw-semibold text-gray-700" id="kt_payments_table_info">
                                        Hiển thị 0 đến 0 của 0 kết quả
                                    </div>
                                    <div class="ms-5">
                                        <select class="form-select form-select-sm w-auto" id="kt_payments_per_page">
                                            <option value="10">10 / trang</option>
                                            <option value="25" selected>25 / trang</option>
                                            <option value="50">50 / trang</option>
                                            <option value="100">100 / trang</option>
                                        </select>
                                    </div>
                                </div>
                                <ul class="pagination kt_table_pagination" id="kt_payments_table_pagination">
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

<!-- Detail Panel -->
@include('admin.payment.elements.detail-panel')

<!-- Panel Overlay -->
<div id="payment_detail_overlay" class="payment-detail-overlay"></div>



<!-- Modals -->
@include('admin.payment.partials.view-modal')
@include('admin.payment.partials.approve-modal')
@include('admin.payment.partials.cancel-modal')
@endsection

@section('scripts')
<script>
// Setup CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
    }
});

// Completely disable DataTables for this page
(function() {
    // Override DataTables before any other scripts load
    if (typeof $ !== 'undefined' && $.fn) {
        $.fn.dataTable = function() {
            console.log('DataTables initialization blocked');
            return this;
        };
        $.fn.DataTable = $.fn.dataTable;
    }

    // Suppress all alerts containing DataTables
    var originalAlert = window.alert;
    window.alert = function(message) {
        if (typeof message === 'string' && message.includes('DataTables')) {
            console.log('DataTables alert suppressed:', message);
            return;
        }
        if (originalAlert) {
            originalAlert.call(window, message);
        }
    };

    // Override console.error for DataTables
    var originalError = console.error;
    console.error = function() {
        var args = Array.prototype.slice.call(arguments);
        var message = args.join(' ');
        if (message.includes('DataTables') || message.includes('kt_payments_table')) {
            console.log('DataTables error suppressed:', message);
            return;
        }
        originalError.apply(console, args);
    };
})();
</script>

<!-- Include global utilities and filter scripts -->
<script src="{{ asset('admin-assets/globals/date-utils.js') }}?v={{ time() }}"></script>
<script src="{{ asset('admin-assets/globals/filter.js') }}?v={{ time() }}"></script>
<script src="{{ asset('admin-assets/globals/column-visibility.js') }}?v={{ time() }}"></script>

<!-- Include base table manager and payment-specific scripts -->
<script src="{{ asset('admin-assets/js/base/table-manager.js') }}?v={{ time() }}"></script>
<script src="{{ asset('admin-assets/js/payments/payment-manager.js') }}?v={{ time() }}"></script>

<script>
// Set global variable for AJAX URL
window.paymentAjaxUrl = "{{ route('admin.payment.ajax') }}";

// Initialize payment manager when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payment index view JavaScript loaded');
    console.log('paymentAjaxUrl:', window.paymentAjaxUrl);

    // Initialize PaymentTableManager FIRST
    if (typeof PaymentTableManager !== 'undefined') {
        console.log('Initializing PaymentTableManager...');
        const paymentManager = new PaymentTableManager();
        paymentManager.init();

        // Make it globally accessible for debugging
        window.paymentManager = paymentManager;

        // Initialize filters using KTGlobalFilter AFTER paymentManager is created
        if (typeof KTGlobalFilter !== 'undefined') {
            KTGlobalFilter.initAllFilters('#kt_payment_filter_form', function() {
                if (window.paymentManager) {
                    // Reset to page 1 when filter changes
                    window.paymentManager.currentFilters.page = 1;
                    window.paymentManager.loadData();
                }
            }, {
                timeFilter: true,
                statusFilter: false, // We handle status manually
                creatorsFilter: false,
                sellersFilter: false,
                approversFilter: false,
                branchShopsFilter: false,
                saleChannelsFilter: false,
                paymentMethodsFilter: false,
                deliveryStatusFilter: false,
                deliveryTimeFilter: false,
                loadAllData: false // Don't load data automatically, we'll do it manually
            });
            console.log('KTGlobalFilter initialized for payments');
        } else {
            console.error('KTGlobalFilter not found');
        }

        // Initialize manual event listeners for payment-specific filters
        initPaymentFilters();
    } else {
        console.error('PaymentTableManager not found');
    }
});

/**
 * Initialize event listeners for payment-specific filters
 */
function initPaymentFilters() {
    console.log('Initializing payment-specific filters...');

    // Helper function to reload data
    const reloadData = function() {
        if (window.paymentManager) {
            window.paymentManager.currentFilters.page = 1;
            window.paymentManager.loadData();
        }
    };

    // 1. Status checkboxes (status_paid, status_pending, status_cancelled)
    $('#status_paid, #status_pending, #status_cancelled').on('change', function() {
        console.log('Status filter changed:', this.id, this.checked);
        reloadData();
    });

    // 2. Payment method radio buttons
    $('input[name="payment_method"]').on('change', function() {
        console.log('Payment method filter changed:', this.value);
        reloadData();
    });

    // 3. Document type checkboxes (doc_receipt, doc_disbursement)
    $('#doc_receipt, #doc_disbursement').on('change', function() {
        console.log('Document type filter changed:', this.id, this.checked);
        reloadData();
    });

    // 4. Business result buttons
    $('#business_result_all, #business_result_yes, #business_result_no').on('click', function() {
        // Update button states
        $('#business_result_all, #business_result_yes, #business_result_no')
            .removeClass('btn-primary')
            .addClass('btn-light');
        $(this).removeClass('btn-light').addClass('btn-primary');

        // Set hidden input value
        let value = '';
        if (this.id === 'business_result_yes') value = '1';
        else if (this.id === 'business_result_no') value = '0';

        // Create or update hidden input
        let hiddenInput = $('#business_result_filter');
        if (hiddenInput.length === 0) {
            $('<input type="hidden" id="business_result_filter" name="business_result" />').appendTo('#kt_payment_filter_form');
            hiddenInput = $('#business_result_filter');
        }
        hiddenInput.val(value);

        console.log('Business result filter changed:', value);
        reloadData();
    });

    // 4. Select2 dropdowns - need to wait for Select2 initialization
    setTimeout(function() {
        // Income type select2
        $('#income_type_filter').on('change', function() {
            console.log('Income type filter changed:', $(this).val());
            reloadData();
        });

        // Creator select2
        $('#creator_filter').on('change', function() {
            console.log('Creator filter changed:', $(this).val());
            reloadData();
        });

        // Staff select2
        $('#staff_filter').on('change', function() {
            console.log('Staff filter changed:', $(this).val());
            reloadData();
        });

        // Recipient select2
        $('#recipient_filter').on('change', function() {
            console.log('Recipient filter changed:', $(this).val());
            reloadData();
        });

        console.log('Select2 filter listeners initialized');
    }, 500);

    console.log('Payment-specific filters initialized');
}
</script>
@endsection
