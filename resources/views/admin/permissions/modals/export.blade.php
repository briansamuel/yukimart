<!--begin::Modal - Export permissions-->
<div class="modal fade" id="kt_modal_export_permissions" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bolder">{{ __('permissions.export_permissions') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-modal-action="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <!--begin::Form-->
                <form id="kt_modal_export_permissions_form" class="form" action="#">
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('common.select_export_format') }}:</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="format" data-control="select2" data-placeholder="{{ __('common.select_format') }}" data-hide-search="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            <option value="excel">{{ __('common.excel') }}</option>
                            <option value="pdf">{{ __('common.pdf') }}</option>
                            <option value="csv">{{ __('common.csv') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('permissions.filters.filter_by_module') }}:</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="module" data-control="select2" data-placeholder="{{ __('permissions.filters.all_permissions') }}" data-allow-clear="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            @if(isset($modules))
                                @foreach($modules as $key => $module)
                                    <option value="{{ $key }}">{{ $module }}</option>
                                @endforeach
                            @endif
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('permissions.filters.filter_by_action') }}:</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="action" data-control="select2" data-placeholder="{{ __('permissions.filters.all_permissions') }}" data-allow-clear="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            @if(isset($actions))
                                @foreach($actions as $key => $action)
                                    <option value="{{ $key }}">{{ $action }}</option>
                                @endforeach
                            @endif
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('permissions.filters.filter_by_status') }}:</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="status" data-control="select2" data-placeholder="{{ __('permissions.filters.all_permissions') }}" data-hide-search="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            <option value="all">{{ __('permissions.filters.all_permissions') }}</option>
                            <option value="1">{{ __('permissions.filters.active_permissions') }}</option>
                            <option value="0">{{ __('permissions.filters.inactive_permissions') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <!--begin::Label-->
                        <label class="required fs-6 fw-semibold form-label mb-2">{{ __('common.select_date_range') }}:</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input class="form-control form-control-solid" placeholder="{{ __('common.pick_date_range') }}" name="date_range" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-10">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('common.export_options') }}:</label>
                        <!--end::Label-->
                        <!--begin::Options-->
                        <div class="d-flex flex-column">
                            <!--begin::Option-->
                            <label class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input" type="checkbox" value="roles" name="include[]" checked />
                                <span class="form-check-label fw-semibold text-gray-700 fs-6">{{ __('permissions.tabs.roles') }}</span>
                            </label>
                            <!--end::Option-->
                            <!--begin::Option-->
                            <label class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input" type="checkbox" value="users" name="include[]" />
                                <span class="form-check-label fw-semibold text-gray-700 fs-6">{{ __('permissions.tabs.users') }}</span>
                            </label>
                            <!--end::Option-->
                            <!--begin::Option-->
                            <label class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input" type="checkbox" value="description" name="include[]" />
                                <span class="form-check-label fw-semibold text-gray-700 fs-6">{{ __('permissions.description') }}</span>
                            </label>
                            <!--end::Option-->
                        </div>
                        <!--end::Options-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-kt-modal-action="cancel">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" data-kt-modal-action="submit">
                            <span class="indicator-label">{{ __('common.export') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Export permissions-->
