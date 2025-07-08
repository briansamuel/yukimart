<!--begin::Modal - Edit branch shop assignment-->
<div class="modal fade" id="kt_modal_edit_branch_shop" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bolder">{{ __('users.edit_branch_shop') }}</h2>
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
                <form id="kt_modal_edit_branch_shop_form" class="form" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span>{{ __('branch_shops.branch_shop') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Display-->
                        <div class="form-control form-control-solid bg-light" id="edit_branch_shop_name">
                            <!-- Branch shop name will be populated here -->
                        </div>
                        <!--end::Display-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">{{ __('users.role_in_shop') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <select name="role_in_shop" data-control="select2" data-placeholder="{{ __('users.select_role') }}" class="form-select form-select-solid fw-bold" id="edit_role_in_shop">
                            <option></option>
                            <option value="manager">{{ __('branch_shops.roles.manager') }}</option>
                            <option value="staff">{{ __('branch_shops.roles.staff') }}</option>
                            <option value="cashier">{{ __('branch_shops.roles.cashier') }}</option>
                            <option value="sales">{{ __('branch_shops.roles.sales') }}</option>
                            <option value="warehouse_keeper">{{ __('branch_shops.roles.warehouse_keeper') }}</option>
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span>{{ __('users.start_date') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input class="form-control form-control-solid" placeholder="{{ __('users.select_start_date') }}" name="start_date" type="date" id="edit_start_date" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span>{{ __('users.end_date') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input class="form-control form-control-solid" placeholder="{{ __('users.select_end_date') }}" name="end_date" type="date" id="edit_end_date" />
                        <!--end::Input-->
                        <!--begin::Description-->
                        <div class="text-muted fs-7">{{ __('users.help.end_date') }}</div>
                        <!--end::Description-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span>{{ __('users.notes') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea class="form-control form-control-solid" placeholder="{{ __('users.enter_notes') }}" name="notes" rows="3" id="edit_notes"></textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Checkbox-->
                        <label class="form-check form-check-custom form-check-solid me-9">
                            <input class="form-check-input" type="checkbox" value="1" name="is_active" id="edit_is_active" />
                            <span class="form-check-label">{{ __('users.is_active') }}</span>
                        </label>
                        <!--end::Checkbox-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Checkbox-->
                        <label class="form-check form-check-custom form-check-solid me-9">
                            <input class="form-check-input" type="checkbox" value="1" name="is_primary" id="edit_is_primary" />
                            <span class="form-check-label">{{ __('users.is_primary') }}</span>
                        </label>
                        <!--end::Checkbox-->
                        <!--begin::Description-->
                        <div class="text-muted fs-7">{{ __('users.help.is_primary') }}</div>
                        <!--end::Description-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-kt-modal-action="cancel">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" data-kt-modal-action="submit">
                            <span class="indicator-label">{{ __('users.update_branch_shop') }}</span>
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
<!--end::Modal - Edit branch shop assignment-->
