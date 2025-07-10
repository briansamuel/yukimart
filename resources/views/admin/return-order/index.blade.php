@extends('admin.main-content')

@section('title', 'Quản lý trả hàng')

@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" href="{{ asset('admin-assets/css/return-order-list.css') }}" />
@include('admin.return-order.elements.row-expansion-styles')
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
                @include('admin.return-order.elements.filter')

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
                                    <input type="text" id="return_order_search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm đơn trả hàng..." />
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
                                <!--begin::Add return order-->
                                <a href="{{ route('admin.return-order.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus fs-2"></i>Tạo đơn trả hàng
                                </a>
                                <!--end::Add return order-->

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
                                                            <input class="form-check-input column-toggle" type="checkbox" value="1" id="col_return_number" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_return_number">Mã trả hàng</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="2" id="col_invoice_number" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_invoice_number">Hóa đơn gốc</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="3" id="col_customer" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_customer">Khách hàng</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="4" id="col_return_date" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_return_date">Ngày trả</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="5" id="col_reason" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_reason">Lý do</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="6" id="col_total_amount" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_total_amount">Tổng tiền</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="7" id="col_status" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_status">Trạng thái</label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid mb-3">
                                                            <input class="form-check-input column-toggle" type="checkbox" value="8" id="col_creator" checked/>
                                                            <label class="form-check-label fw-semibold" for="col_creator">Người tạo</label>
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
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_return_orders_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_return_orders_table .form-check-input" value="1" />
                                            </div>
                                        </th>
                                        <th class="min-w-125px">Mã trả hàng</th>
                                        <th class="min-w-125px">Hóa đơn gốc</th>
                                        <th class="min-w-125px">Khách hàng</th>
                                        <th class="min-w-100px">Ngày trả</th>
                                        <th class="min-w-100px">Lý do</th>
                                        <th class="min-w-100px">Tổng tiền</th>
                                        <th class="min-w-100px">Trạng thái</th>
                                        <th class="min-w-100px">Người tạo</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
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
<!--end::Content-->

<!-- Modals -->
@include('admin.return-order.partials.view-modal')
@include('admin.return-order.partials.approve-modal')
@include('admin.return-order.partials.reject-modal')
@include('admin.return-order.partials.complete-modal')
@endsection

@section('scripts')
<script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="{{ asset('admin-assets/js/return-order-list.js') }}?v={{ time() }}"></script>
<script src="{{ asset('admin-assets/js/return-order-actions.js') }}?v={{ time() }}"></script>
@endsection
