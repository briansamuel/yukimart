@extends('admin.index')
@section('page-header', __('permissions.title'))
@section('page-sub_header', __('permissions.subtitle'))

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
                <input type="text" data-kt-permission-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="{{ __('permissions.search_permissions') }}" />
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-permission-table-toolbar="base">
                <!--begin::Filter-->
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('permissions.filters.filter_by_module') }}
                </button>
                <!--begin::Menu 1-->
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <!--begin::Header-->
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">{{ __('permissions.filters.filter_by_module') }}</div>
                    </div>
                    <!--end::Header-->
                    <!--begin::Separator-->
                    <div class="separator border-gray-200"></div>
                    <!--end::Separator-->
                    <!--begin::Content-->
                    <div class="px-7 py-5" data-kt-permission-table-filter="form">
                        <!--begin::Input group-->
                        <div class="mb-10">
                            <label class="form-label fs-6 fw-semibold">{{ __('permissions.module') }}:</label>
                            <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('permissions.select_module') }}" data-allow-clear="true" data-kt-permission-table-filter="module" data-hide-search="true">
                                <option></option>
                                @foreach($modules as $key => $module)
                                    <option value="{{ $key }}">{{ $module }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10">
                            <label class="form-label fs-6 fw-semibold">{{ __('permissions.action') }}:</label>
                            <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('permissions.select_action') }}" data-allow-clear="true" data-kt-permission-table-filter="action" data-hide-search="true">
                                <option></option>
                                @foreach($actions as $key => $action)
                                    <option value="{{ $key }}">{{ $action }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="mb-10">
                            <label class="form-label fs-6 fw-semibold">{{ __('permissions.status') }}:</label>
                            <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('permissions.select_permission') }}" data-allow-clear="true" data-kt-permission-table-filter="status" data-hide-search="true">
                                <option></option>
                                <option value="1">{{ __('permissions.active') }}</option>
                                <option value="0">{{ __('permissions.inactive') }}</option>
                            </select>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Actions-->
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-permission-table-filter="reset">{{ __('common.reset') }}</button>
                            <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-permission-table-filter="filter">{{ __('common.apply') }}</button>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Menu 1-->
                <!--end::Filter-->
                <!--begin::Generate-->
                <button type="button" class="btn btn-light-success me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_generate_permissions">
                    <i class="ki-duotone ki-gear fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('permissions.generate_permissions') }}
                </button>
                <!--end::Generate-->
                <!--begin::Export-->
                <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_export_permissions">
                    <i class="ki-duotone ki-exit-up fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('permissions.export_permissions') }}
                </button>
                <!--end::Export-->
                <!--begin::Add permission-->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_permission">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    {{ __('permissions.add_permission') }}
                </button>
                <!--end::Add permission-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Group actions-->
            <div class="d-flex justify-content-end align-items-center d-none" data-kt-permission-table-toolbar="selected">
                <div class="fw-bold me-5">
                    <span class="me-2" data-kt-permission-table-select="selected_count"></span>{{ __('common.selected') }}
                </div>
                <button type="button" class="btn btn-danger" data-kt-permission-table-select="delete_selected">
                    {{ __('permissions.delete_permission') }}
                </button>
            </div>
            <!--end::Group actions-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_permissions">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_permissions .form-check-input" value="1" />
                        </div>
                    </th>
                    <th class="min-w-125px">{{ __('permissions.name') }}</th>
                    <th class="min-w-125px">{{ __('permissions.display_name') }}</th>
                    <th class="min-w-125px">{{ __('permissions.module') }}</th>
                    <th class="min-w-125px">{{ __('permissions.action') }}</th>
                    <th class="min-w-125px">{{ __('permissions.roles_count') }}</th>
                    <th class="min-w-125px">{{ __('permissions.status') }}</th>
                    <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                <!-- Data will be loaded via DataTables AJAX -->
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<!--begin::Modals-->
@include('admin.permissions.modals.add')
@include('admin.permissions.modals.generate')
@include('admin.permissions.modals.export')
<!--end::Modals-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/apps/permissions/list/table.js') }}"></script>
<script src="{{ asset('admin-assets/assets/js/custom/apps/permissions/list/export.js') }}"></script>
<script src="{{ asset('admin-assets/assets/js/custom/apps/permissions/list/add.js') }}"></script>
<script src="{{ asset('admin-assets/assets/js/custom/apps/permissions/list/generate.js') }}"></script>
@endpush
