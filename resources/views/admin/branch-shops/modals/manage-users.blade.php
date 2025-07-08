<!--begin::Modal - Manage Branch Shop Users-->
<div class="modal fade" id="kt_modal_manage_branch_shop_users" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_manage_users_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">
                    <span id="modal_branch_shop_name">{{ __('branch_shops.manage_users') }}</span>
                    <small class="text-muted fs-6 d-block mt-1" id="modal_branch_shop_subtitle">{{ __('branch_shops.manage_users_subtitle') }}</small>
                </h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" id="search_users" class="form-control form-control-solid w-250px ps-13" placeholder="{{ __('branch_shops.search_users') }}" />
                    </div>
                    <button type="button" class="btn btn-primary" id="btn_add_user_to_branch">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('branch_shops.add_user_to_branch') }}
                    </button>
                </div>
                <!--end::Toolbar-->

                <!--begin::Current Users-->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">{{ __('branch_shops.current_users') }}</h3>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_branch_shop_users_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-200px">{{ __('users.name') }}</th>
                                        <th class="min-w-150px">{{ __('users.email') }}</th>
                                        <th class="min-w-125px">{{ __('branch_shops.role_in_shop') }}</th>
                                        <th class="min-w-100px">{{ __('branch_shops.start_date') }}</th>
                                        <th class="min-w-100px">{{ __('branch_shops.status') }}</th>
                                        <th class="min-w-100px">{{ __('branch_shops.is_primary') }}</th>
                                        <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                </div>
                <!--end::Current Users-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Manage Branch Shop Users-->

<!--begin::Modal - Add User to Branch Shop-->
<div class="modal fade" id="kt_modal_add_user_to_branch" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('branch_shops.add_user_to_branch') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <!--begin::Form-->
                <form id="kt_modal_add_user_form" class="form" action="#">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('users.select_user') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('users.select_user') }}" data-allow-clear="true" name="user_id" id="user_select">
                            <option></option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shops.role_in_shop') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="{{ __('branch_shops.select_role') }}" name="role_in_shop" id="role_select">
                            <option></option>
                            <option value="manager">{{ __('branch_shops.roles.manager') }}</option>
                            <option value="staff">{{ __('branch_shops.roles.staff') }}</option>
                            <option value="cashier">{{ __('branch_shops.roles.cashier') }}</option>
                            <option value="sales">{{ __('branch_shops.roles.sales') }}</option>
                            <option value="warehouse_keeper">{{ __('branch_shops.roles.warehouse_keeper') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('branch_shops.start_date') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input class="form-control form-control-solid" placeholder="{{ __('branch_shops.select_start_date') }}" name="start_date" id="start_date" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('branch_shops.notes') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea class="form-control form-control-solid" rows="3" name="notes" placeholder="{{ __('branch_shops.notes_placeholder') }}"></textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Checkbox-->
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="1" id="is_primary" name="is_primary" />
                            <label class="form-check-label" for="is_primary">
                                {{ __('branch_shops.set_as_primary') }}
                            </label>
                        </div>
                        <!--end::Checkbox-->
                        <div class="text-muted fs-7">{{ __('branch_shops.primary_branch_description') }}</div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                            <span class="indicator-label">{{ __('branch_shops.add_user') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
<!--end::Modal - Add User to Branch Shop-->
