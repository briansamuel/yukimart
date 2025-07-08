@extends('admin.index')
@section('page-header', __('product_category.product_categories'))
@section('page-sub_header', __('product_category.manage_product_categories'))

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-product-category-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="{{ __('product_category.search_categories') }}" />
                </div>
                <!--end::Search-->
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-product-category-table-toolbar="base">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-filter fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('product_category.filter') }}
                    </button>
                    <!--begin::Menu 1-->
                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                        <!--begin::Header-->
                        <div class="px-7 py-5">
                            <div class="fs-5 text-dark fw-bold">{{ __('product_category.filter_options') }}</div>
                        </div>
                        <!--end::Header-->
                        <!--begin::Separator-->
                        <div class="separator border-gray-200"></div>
                        <!--end::Separator-->
                        <!--begin::Content-->
                        <div class="px-7 py-5" data-kt-product-category-table-filter="form">
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('product_category.status') }}:</label>
                                <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('product_category.select_status') }}" data-allow-clear="true" data-kt-product-category-table-filter="status" data-hide-search="true">
                                    <option></option>
                                    <option value="active">{{ __('product_category.active') }}</option>
                                    <option value="inactive">{{ __('product_category.inactive') }}</option>
                                </select>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('product_category.parent_category') }}:</label>
                                <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('product_category.select_parent') }}" data-allow-clear="true" data-kt-product-category-table-filter="parent" data-hide-search="true">
                                    <option></option>
                                    <option value="root">{{ __('product_category.root_categories') }}</option>
                                </select>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-product-category-table-filter="reset">{{ __('common.reset') }}</button>
                                <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-product-category-table-filter="filter">{{ __('common.apply') }}</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Menu 1-->
                    <!--end::Filter-->
                    <!--begin::Export-->
                    <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_export_categories">
                        <i class="ki-duotone ki-exit-up fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('product_category.export') }}
                    </button>
                    <!--end::Export-->
                    <!--begin::Add category-->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_category">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('product_category.add_category') }}
                    </button>
                    <!--end::Add category-->
                </div>
                <!--end::Toolbar-->
                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-product-category-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-product-category-table-select="selected_count"></span>{{ __('product_category.selected') }}
                    </div>
                    <button type="button" class="btn btn-danger" data-kt-product-category-table-select="delete_selected">{{ __('product_category.delete_selected') }}</button>
                </div>
                <!--end::Group actions-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_product_categories">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_product_categories .form-check-input" value="1" />
                            </div>
                        </th>
                        <th class="min-w-125px">{{ __('product_category.name') }}</th>
                        <th class="min-w-125px">{{ __('product_category.parent_category') }}</th>
                        <th class="min-w-125px">{{ __('product_category.products_count') }}</th>
                        <th class="min-w-125px">{{ __('product_category.status') }}</th>
                        <th class="min-w-125px">{{ __('product_category.sort_order') }}</th>
                        <th class="min-w-125px">{{ __('product_category.created_at') }}</th>
                        <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    <!-- Data will be loaded via DataTables -->
                </tbody>
            </table>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Modals-->
    @include('admin.product-categories.modals.add')
    @include('admin.product-categories.modals.export')
    <!--end::Modals-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/product-categories/list/table.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/product-categories/list/export.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/product-categories/list/add.js') }}"></script>
@endsection
