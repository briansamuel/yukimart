<!--begin::Modal - Export Branch Shops-->
<div class="modal fade" id="kt_modal_export_branch_shops" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('branch_shop.export_branch_shops') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-modal="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_export_branch_shops_form" class="form" action="#">
                    <div class="fv-row mb-10">
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('admin.select_export_format') }}:</label>
                        <select name="format" data-control="select2" data-placeholder="{{ __('admin.select_format') }}" data-hide-search="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="fv-row mb-10">
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('admin.select_date_range') }}:</label>
                        <input class="form-control form-control-solid" placeholder="{{ __('admin.pick_date_range') }}" id="kt_modal_export_branch_shops_date" />
                    </div>
                    <div class="fv-row mb-10">
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('branch_shop.status') }}:</label>
                        <select name="status" data-control="select2" data-placeholder="{{ __('admin.select_all') }}" data-allow-clear="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            <option value="active">{{ __('branch_shop.active') }}</option>
                            <option value="inactive">{{ __('branch_shop.inactive') }}</option>
                            <option value="maintenance">{{ __('branch_shop.maintenance') }}</option>
                        </select>
                    </div>
                    <div class="fv-row mb-10">
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('branch_shop.shop_type') }}:</label>
                        <select name="shop_type" data-control="select2" data-placeholder="{{ __('admin.select_all') }}" data-allow-clear="true" class="form-select form-select-solid fw-bold">
                            <option></option>
                            <option value="flagship">{{ __('branch_shop.flagship') }}</option>
                            <option value="standard">{{ __('branch_shop.standard') }}</option>
                            <option value="mini">{{ __('branch_shop.mini') }}</option>
                            <option value="kiosk">{{ __('branch_shop.kiosk') }}</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-kt-modal="close">{{ __('admin.discard') }}</button>
                        <button type="submit" class="btn btn-primary" data-kt-modal-export-action="submit">
                            <span class="indicator-label">{{ __('admin.export') }}</span>
                            <span class="indicator-progress">{{ __('admin.please_wait') }}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Export Branch Shops-->
