@extends('admin.main-content')

@section('title', 'Quản lý hóa đơn')

@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" href="{{ asset('admin-assets/css/invoice-list.css') }}" />
@include('admin.invoice.elements.row-expansion-styles')
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
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Search-->
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                    <input type="text" id="invoice_search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm hóa đơn..." />
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                <!--begin::Export-->
                                <button type="button" class="btn btn-light-primary">
                                    <i class="fas fa-file-excel fs-2"></i>Xuất Excel
                                </button>
                                <!--end::Export-->
                                <!--begin::Add invoice-->
                                <a href="{{ route('admin.quick-order.index') }}" class="btn btn-primary">
                                    <i class="fas fa-plus fs-2"></i>Thêm mới
                                </a>
                                <!--end::Add invoice-->
                               
                              
                                <!--begin::Print-->
                                <button type="button" class="btn btn-light-success">
                                    <i class="fas fa-print fs-2"></i>In
                                </button>
                                <!--end::Print-->
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
                                                            <input class="form-check-input column-toggle" type="checkbox" value="1" id="col_invoice_number" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_invoice_number">Mã hóa đơn</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="2" id="col_customer" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_customer">Khách hàng</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="3" id="col_total_amount" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_total_amount">Tổng tiền</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="4" id="col_amount_paid" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_amount_paid">Đã thanh toán</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="5" id="col_payment_status" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_payment_status">Trạng thái TT</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="6" id="col_payment_method" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_payment_method">Phương thức TT</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="7" id="col_channel" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_channel">Kênh bán</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="8" id="col_created_at" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_created_at">Ngày tạo</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="9" id="col_seller"/>
                                                            <label class="form-check-label fw-semibold" for="col_seller">Người bán</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="10" id="col_creator"/>
                                                            <label class="form-check-label fw-semibold" for="col_creator">Người tạo</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="11" id="col_discount"/>
                                                            <label class="form-check-label fw-semibold" for="col_discount">Giảm giá</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="12" id="col_email"/>
                                                            <label class="form-check-label fw-semibold" for="col_email">Email</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="13" id="col_phone"/>
                                                            <label class="form-check-label fw-semibold" for="col_phone">Phone</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="14" id="col_address"/>
                                                            <label class="form-check-label fw-semibold" for="col_address">Địa chỉ</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="15" id="col_branch_shop"/>
                                                            <label class="form-check-label fw-semibold" for="col_branch_shop">Chi nhánh</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="16" id="col_notes"/>
                                                            <label class="form-check-label fw-semibold" for="col_notes">Ghi chú</label>
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

                        @if($barcodeSearch)
                        <!--begin::Barcode search info-->
                        <div class="card-body border-bottom py-4">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center flex-wrap">
                                    <div class="me-5">
                                        <i class="fas fa-barcode text-primary fs-2 me-2"></i>
                                        <span class="fw-bold text-gray-800">Tìm kiếm theo mã vạch:</span>
                                        <span class="badge badge-light-primary fs-7 ms-2">{{ $barcodeSearch }}</span>
                                    </div>
                                    @if($searchedProduct)
                                        <div class="d-flex align-items-center">
                                            <span class="text-muted me-2">Sản phẩm:</span>
                                            <span class="fw-bold text-success">{{ $searchedProduct->name }}</span>
                                            <span class="text-muted ms-2">({{ $searchedProduct->sku }})</span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Không tìm thấy sản phẩm với mã vạch này
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ms-auto">
                                    <a href="{{ route('admin.invoice.list') }}" class="btn btn-light-danger btn-sm">
                                        <i class="fas fa-times"></i>Xóa bộ lọc
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Barcode search info-->
                        @endif

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_invoices_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_invoices_table .form-check-input" value="1" />
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
                                        <th class="min-w-100px" style="display: none;">Người bán</th>
                                        <th class="min-w-100px" style="display: none;">Người tạo</th>
                                        <th class="min-w-80px" style="display: none;">Giảm giá</th>
                                        <th class="min-w-150px" style="display: none;">Email</th>
                                        <th class="min-w-100px" style="display: none;">Phone</th>
                                        <th class="min-w-200px" style="display: none;">Địa chỉ</th>
                                        <th class="min-w-100px" style="display: none;">Chi nhánh</th>
                                        <th class="min-w-150px" style="display: none;">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                </tbody>
                            </table>
                            <!--end::Table-->
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
</div>
<!--end::Content-->



@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
@endsection


@section('styles')
<link href="{{ asset('admin/css/invoice-list.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('styles')
<style>
.column-visibility-panel {
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    width: 500px;
    max-height: 400px;
    overflow-y: auto;
    margin-top: 5px;
}

.column-visibility-panel .panel-content {
    padding: 0;
}

.column-visibility-panel .panel-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e4e6ef;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.column-visibility-panel .panel-body {
    padding: 20px;
}

.column-visibility-panel .form-check {
    margin-bottom: 12px;
}

.column-visibility-panel .form-check-label {
    color: #3f4254;
    font-size: 13px;
}

.column-visibility-panel .form-check-input:checked {
    background-color: #009ef7;
    border-color: #009ef7;
}

/* Row click styles */
#kt_invoices_table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

#kt_invoices_table tbody tr:hover {
    background-color: #f8f9fa !important;
}

#kt_invoices_table tbody tr.shown {
    background-color: #e3f2fd !important;
}

/* Prevent cursor pointer on action buttons */
#kt_invoices_table tbody tr .btn,
#kt_invoices_table tbody tr .form-check-input,
#kt_invoices_table tbody tr .dropdown {
    cursor: default;
}
</style>
@endsection

@section('scripts')
<script>
// Set global variable for AJAX URL
var invoiceAjaxUrl = "{{ route('admin.invoice.ajax') }}";

// Initialize invoice list functionality
$(document).ready(function() {
    console.log('Invoice index view JavaScript loaded');

    // Initialize KTInvoicesList when document is ready
    if (typeof KTInvoicesList !== 'undefined') {
        KTInvoicesList.init();
    } else {
        console.error('KTInvoicesList not found');
    }
});
</script>
<script src="{{ asset('admin-assets/js/invoice-list.js') }}"></script>


@endsection
