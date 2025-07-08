<!--begin::Modal - Export roles-->
<div class="modal fade" id="kt_modal_export_roles" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bolder">{{ __('roles.export_roles') }}</h2>
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
                <form id="kt_modal_export_roles_form" class="form" action="#">
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
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('roles.filters.filter_by_status') }}:</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="status" data-control="select2" data-placeholder="{{ __('roles.filters.all_roles') }}" data-hide-search="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            <option value="all">{{ __('roles.filters.all_roles') }}</option>
                            <option value="1">{{ __('roles.filters.active_roles') }}</option>
                            <option value="0">{{ __('roles.filters.inactive_roles') }}</option>
                        </select>
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
                                <input class="form-check-input" type="checkbox" value="permissions" name="include[]" checked />
                                <span class="form-check-label fw-semibold text-gray-700 fs-6">{{ __('roles.permissions') }}</span>
                            </label>
                            <!--end::Option-->
                            <!--begin::Option-->
                            <label class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input" type="checkbox" value="users" name="include[]" />
                                <span class="form-check-label fw-semibold text-gray-700 fs-6">{{ __('roles.tabs.users') }}</span>
                            </label>
                            <!--end::Option-->
                            <!--begin::Option-->
                            <label class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input" type="checkbox" value="settings" name="include[]" />
                                <span class="form-check-label fw-semibold text-gray-700 fs-6">{{ __('roles.settings') }}</span>
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
<!--end::Modal - Export roles-->
