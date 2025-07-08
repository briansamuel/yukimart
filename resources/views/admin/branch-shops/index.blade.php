@extends('admin.main-content')

@section('title', __('branch_shop.branch_shop_management'))

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('admin-assets/assets/plugins/custom/flatpickr/flatpickr.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* Custom styles for branch-shops page */
        .branch-shop-card {
            transition: all 0.3s ease;
        }

        .branch-shop-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* DataTable custom styling */
        .dataTables_wrapper .dataTables_length select {
            min-width: 60px;
        }

        /* Filter dropdown improvements */
        .menu-sub-dropdown {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border: none;
        }

        /* Action buttons styling */
        .btn-group-actions .btn {
            margin-right: 0.5rem;
        }

        .btn-group-actions .btn:last-child {
            margin-right: 0;
        }

        /* Status badges */
        .badge-status {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }


        /* Loading state */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .card-toolbar {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-group-actions {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ __('branch_shop.branch_shop_management') }}
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}"
                                class="text-muted text-hover-primary">{{ __('admin.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">{{ __('branch_shop.branch_shops') }}</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="#" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_add_branch_shop">
                        <i class="ki-duotone ki-plus fs-2"></i>{{ __('branch_shop.add_branch_shop') }}
                    </a>
                </div>
            </div>
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="fas fa-magnifier fs-3 position-absolute ms-5">

                                </i>
                                <input type="text" data-kt-branch-shop-table-filter="search"
                                    class="form-control form-control-solid w-250px ps-13"
                                    placeholder="{{ __('branch_shop.search_placeholder') }}" />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-branch-shop-table-toolbar="base">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="fas fa-filter fs-2">

                                        </i>{{ __('branch_shop.filter_branch_shops') }}
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                        <div class="px-7 py-5">
                                            <div class="fs-5 text-dark fw-bold">{{ __('branch_shop.filter_branch_shops') }}
                                            </div>
                                        </div>
                                        <div class="separator border-gray-200"></div>
                                        <div class="px-7 py-5" data-kt-branch-shop-table-filter="form">
                                            <div class="mb-10">
                                                <label
                                                    class="form-label fs-6 fw-semibold">{{ __('branch_shop.status') }}:</label>
                                                <select class="form-select form-select-solid fw-bold" data-kt-select2="true"
                                                    data-placeholder="{{ __('branch_shop.select_status') }}"
                                                    data-allow-clear="true" data-kt-branch-shop-table-filter="status"
                                                    data-hide-search="true">
                                                    <option></option>
                                                    <option value="active">{{ __('branch_shop.active') }}</option>
                                                    <option value="inactive">{{ __('branch_shop.inactive') }}</option>
                                                    <option value="maintenance">{{ __('branch_shop.maintenance') }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="mb-10">
                                                <label
                                                    class="form-label fs-6 fw-semibold">{{ __('branch_shop.shop_type') }}:</label>
                                                <select class="form-select form-select-solid fw-bold" data-kt-select2="true"
                                                    data-placeholder="{{ __('branch_shop.select_shop_type') }}"
                                                    data-allow-clear="true" data-kt-branch-shop-table-filter="shop_type"
                                                    data-hide-search="true">
                                                    <option></option>
                                                    <option value="flagship">{{ __('branch_shop.flagship') }}</option>
                                                    <option value="standard">{{ __('branch_shop.standard') }}</option>
                                                    <option value="mini">{{ __('branch_shop.mini') }}</option>
                                                    <option value="kiosk">{{ __('branch_shop.kiosk') }}</option>
                                                </select>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="reset"
                                                    class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6"
                                                    data-kt-menu-dismiss="true"
                                                    data-kt-branch-shop-table-filter="reset">{{ __('common.reset') }}</button>
                                                <button type="submit" class="btn btn-primary fw-semibold px-6"
                                                    data-kt-menu-dismiss="true"
                                                    data-kt-branch-shop-table-filter="filter">{{ __('common.apply') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-light-success me-3" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_import_branch_shops">
                                        <i class="fas fa-exit-down fs-2">

                                        </i>{{ __('branch_shop.import') }}
                                    </button>
                                    <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal"
                                        data-bs-target="#kt_modal_export_branch_shops">
                                        <i class="fas fa-exit-up fs-2">

                                        </i>{{ __('branch_shop.export') }}
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center d-none"
                                data-kt-branch-shop-table-toolbar="selected">
                                <div class="fw-bold me-5">
                                    <span class="me-2"
                                        data-kt-branch-shop-table-select="selected_count"></span>{{ __('common.selected') }}
                                </div>
                                <button type="button" class="btn btn-danger"
                                    data-kt-branch-shop-table-select="delete_selected">{{ __('branch_shop.delete_selected') }}</button>
                            </div>
                        </div>
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body py-4">
                        <!--begin::Table container-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_branch_shops_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                                    data-kt-check-target="#kt_branch_shops_table .form-check-input"
                                                    value="1" />
                                            </div>
                                        </th>
                                        <th class="min-w-125px">{{ __('branch_shops.code') }}</th>
                                        <th class="min-w-200px">{{ __('branch_shops.name') }}</th>
                                        <th class="min-w-250px">{{ __('branch_shops.full_address') }}</th>
                                        <th class="min-w-125px">{{ __('branch_shops.phone') }}</th>
                                        <th class="min-w-100px">{{ __('branch_shops.status') }}</th>
                                        <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <!-- Data will be loaded via DataTables AJAX -->
                                </tbody>
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table container-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->

    <!--begin::Modals-->
    @include('admin.branch-shops.modals.add')
    {{-- @include('admin.branch-shops.modals.import') --}}
    {{-- @include('admin.branch-shops.modals.export') --}}
    @include('admin.branch-shops.modals.manage-users')
    <!--end::Modals-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/plugins/custom/flatpickr/flatpickr.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script>
        // Pass translations to JavaScript
        window.translations = {
            common: {
                actions: @json(__('common.actions')),
                yes_delete: @json(__('common.yes_delete')),
                no_cancel: @json(__('common.no_cancel')),
                ok_got_it: @json(__('common.ok_got_it')),
                please_wait: @json(__('common.please_wait')),
                cancel_confirmation: @json(__('common.cancel_confirmation')),
                yes_cancel: @json(__('common.yes_cancel')),
                no_return: @json(__('common.no_return'))
            },
            branch_shop: {
                view_details: @json(__('branch_shops.view_details')),
                edit_branch_shop: @json(__('branch_shops.edit_branch_shop')),
                delete_branch_shop: @json(__('branch_shops.delete_branch_shop')),
                manage_users: @json(__('branch_shops.manage_users')),
                confirm_delete: @json(__('branch_shops.confirm_delete')),
                confirm_delete_selected: @json(__('branch_shops.confirm_delete_selected')),
                delete_error: @json(__('branch_shops.delete_error')),
                file_required: @json(__('branch_shops.file_required')),
                invalid_file: @json(__('branch_shops.invalid_file')),
                file_too_large: @json(__('branch_shops.file_too_large')),
                invalid_file_type: @json(__('branch_shops.invalid_file_type')),
                import_error: @json(__('branch_shops.import_error')),
                import_errors: @json(__('branch_shops.import_errors'))
            },
            datatable: {
                processing: @json(__('datatable.processing')),
                search: @json(__('datatable.search')),
                lengthMenu: @json(__('datatable.lengthMenu')),
                info: @json(__('datatable.info')),
                infoEmpty: @json(__('datatable.infoEmpty')),
                infoFiltered: @json(__('datatable.infoFiltered')),
                loadingRecords: @json(__('datatable.loadingRecords')),
                zeroRecords: @json(__('datatable.zeroRecords')),
                emptyTable: @json(__('datatable.emptyTable')),
                first: @json(__('datatable.first')),
                previous: @json(__('datatable.previous')),
                next: @json(__('datatable.next')),
                last: @json(__('datatable.last'))
            }
        };
    </script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/branch-shops/list/table.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/branch-shops/list/add.js') }}"></script>
    {{-- <script src="{{ asset('admin-assets/assets/js/custom/apps/branch-shops/list/import.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/branch-shops/list/export.js') }}"></script> --}}
    <script src="{{ asset('admin-assets/assets/js/custom/apps/branch-shops/list/manage-users.js') }}"></script>
@endsection
