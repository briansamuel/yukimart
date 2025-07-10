@extends('admin.main-content')

@section('title', 'Quản lý phiếu thu/chi')

@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" href="{{ asset('admin-assets/css/payment-list.css') }}" />
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
                                    <input type="text" id="payment_search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm phiếu thu/chi..." />
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
                                <!--begin::Add payment-->
                                <a href="{{ route('admin.payment.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus fs-2"></i>Tạo phiếu thu/chi
                                </a>
                                <!--end::Add payment-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_payments_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_payments_table .form-check-input" value="1" />
                                            </div>
                                        </th>
                                        <th class="min-w-125px">Số phiếu</th>
                                        <th class="min-w-100px">Loại</th>
                                        <th class="min-w-125px">Khách hàng</th>
                                        <th class="min-w-100px">Ngày</th>
                                        <th class="min-w-100px">Phương thức</th>
                                        <th class="min-w-125px">Tài khoản NH</th>
                                        <th class="min-w-100px">Số tiền</th>
                                        <th class="min-w-100px">Trạng thái</th>
                                        <th class="min-w-100px">Người tạo</th>
                                        <th class="text-end min-w-100px">Thao tác</th>
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
@include('admin.payment.partials.view-modal')
@include('admin.payment.partials.approve-modal')
@include('admin.payment.partials.cancel-modal')
@endsection

@section('scripts')
<script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="{{ asset('admin-assets/js/payment-list.js') }}?v={{ time() }}"></script>
@endsection
