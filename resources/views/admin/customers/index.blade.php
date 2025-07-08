@extends('admin.index')
@section('page-header', __('customer.customers'))
@section('page-sub_header', __('customer.manage_customers'))

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-12">
            <!--begin::Card-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">{{ __('customer.customers') }}</span>
                        <span class="text-muted mt-1 fw-bold fs-7">{{ __('customer.manage_customers_description') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-light-primary">
                            <span class="svg-icon svg-icon-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                        transform="rotate(-90 11.364 20.364)" fill="black" />
                                    <rect x="4.364" y="11.364" width="16" height="2" rx="1"
                                        fill="black" />
                                </svg>
                            </span>
                            {{ __('customer.add_customer') }}
                        </a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Statistics-->
                    <div class="row mb-6">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ __('customer.total') }}</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" id="total_customers">0</span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                                    <div class="d-flex align-items-center me-2">
                                        <span class="me-2 text-gray-400 fs-7">{{ __('customer.customers') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ __('customer.active') }}</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2"
                                            id="active_customers">0</span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                                    <div class="d-flex align-items-center me-2">
                                        <span class="me-2 text-gray-400 fs-7">{{ __('customer.active_customers') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ __('customer.this_month') }}</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2"
                                            id="new_customers">0</span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                                    <div class="d-flex align-items-center me-2">
                                        <span class="me-2 text-gray-400 fs-7">{{ __('customer.new_customers') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <div class="card-header pt-5">
                                    <div class="card-title d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ __('customer.total_spent') }}</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2"
                                            id="total_revenue">0₫</span>
                                    </div>
                                </div>
                                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                                    <div class="d-flex align-items-center me-2">
                                        <span class="me-2 text-gray-400 fs-7">{{ __('customer.revenue') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Statistics-->

                    <!--begin::Table Toolbar-->
                    <div class="d-flex justify-content-between" data-kt-customer-table-toolbar="base">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                        rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                    <path
                                        d="m20.9 10.8c0-2.6-2.1-4.6-4.6-4.6s-4.6 2.1-4.6 4.6c0 1.3 0.5 2.4 1.4 3.2l-5.2 5.2c-0.3 0.3-0.3 0.7 0 1s0.7 0.3 1 0l5.2-5.2c0.8 0.9 1.9 1.4 3.2 1.4 2.6 0 4.6-2.1 4.6-4.6zm-1.5 0c0 1.7-1.4 3.1-3.1 3.1s-3.1-1.4-3.1-3.1 1.4-3.1 3.1-3.1 3.1 1.4 3.1 3.1z"
                                        fill="black" />
                                </svg>
                            </span>
                            <input type="text" data-kt-customer-table-filter="search"
                                class="form-control form-control-solid w-250px ps-15" placeholder="Tìm kiếm đơn hàng..." />
                        </div>
                        <!--end::Search-->
                        <!--begin::Filters-->
                        <div class="d-flex align-items-center gap-2">
                            <!-- Filter Button -->
                           
                        </div>
                        <!--end::Filters-->

                    </div>
                    <!--end::Table Toolbar-->
                    <!--begin::Group actions-->
                    <div class="d-flex justify-content-end d-none" data-kt-customer-table-toolbar="selected">
                        <div class="fw-bolder me-5">
                            <span class="me-2 p-3" data-kt-customer-table-select="selected_count"></span>Đã chọn
                        </div>
                        <button type="button" class="btn btn-danger" data-kt-customer-table-select="delete_selected">
                            <i class="fas fa-trash me-2 "></i>
                            Xóa đã chọn
                        </button>
                    </div>
                    <!--end::Group actions-->
                    <!--begin::Table container-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table id="kt_customers_table"
                            class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th class="w-25px">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                data-kt-check="true" data-kt-check-target=".widget-13-check" />
                                        </div>
                                    </th>
                                    <th class="min-w-150px">{{ __('customer.customer') }}</th>
                                    <th class="min-w-140px">{{ __('customer.contact') }}</th>
                                    <th class="min-w-120px">{{ __('customer.type') }}</th>
                                    <th class="min-w-120px">{{ __('customer.orders') }}</th>
                                    <th class="min-w-120px">{{ __('customer.total_spent') }}</th>
                                    <th class="min-w-120px">{{ __('customer.last_order') }}</th>
                                    <th class="min-w-100px">{{ __('customer.status') }}</th>
                                    <th class="text-end min-w-100px">{{ __('customer.actions') }}</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table container-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Modals-->
    @include('admin.customers.modals.delete')
    <!--end::Modals-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/customers/list.js') }}"></script>
@endsection
