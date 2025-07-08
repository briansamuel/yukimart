@extends('admin.index')
@section('page-header', __('product.products'))
@section('page-sub_header', __('product.product_list'))
@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
<link rel="stylesheet" href="{{ asset('admin-assets/assets/css/custom/product-tabs.css') }}" />
@include('admin.products.elements.stock-status-styles')
@include('admin.products.elements.row-expansion-styles')
@endsection
@section('content')
    <div class="card">
        @include('admin.products.elements.toolbar')
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_products">
                <!--begin::Table head-->
                <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th class="w-20px pe-2" title="{{ __('common.click_to_expand_details') }}">
                            <i class="fas fa-info-circle text-muted"></i>
                        </th>
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_table_products .form-check-input" value="1">
                            </div>
                        </th>
                        <th>{{ __('product.product_name') }}</th>
                        <th>{{ __('product.sku') }}</th>
                        <th>{{ __('product.price') }}</th>
                        <th>{{ __('product.stock') }}</th>
                        <th>{{ __('product.product_status') }}</th>
                        <th>{{ __('product.created_at') }}</th>
                        <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                    </tr>
                    <!--end::Table row-->
                </thead>
                <!--end::Table head-->
                <!--begin::Table body-->
                <tbody class="text-gray-600 fw-bold">

                </tbody>
                <!--end::Table body-->
            </table>
            <!--end::Table-->
        </div>
    </div>
@endsection
@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
@section('scripts')
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/js/custom/apps/products/list/table.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/products/list/export-products.js') }}"></script>
    <!--end::Page Custom Javascript-->
@endsection
