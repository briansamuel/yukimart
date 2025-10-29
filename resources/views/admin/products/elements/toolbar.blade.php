<!--begin::Card header-->
<div class="card-header border-0 pt-6">
    <!--begin::Card title-->
    <div class="card-title">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1">
            <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                        transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                    <path
                        d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                        fill="black"></path>
                </svg>
            </span>
            <!--end::Svg Icon-->
            <input type="text" data-kt-products-table-filter="search"
                class="form-control form-control-solid w-250px ps-14"
                placeholder="{{ __('product.search_products') }}...">
        </div>
        <!--end::Search-->
    </div>
    <!--begin::Card title-->
    <!--begin::Card toolbar-->
    <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
        
        <!--begin::Group actions-->
        <div class="d-flex justify-content-end align-items-center d-none" data-kt-products-table-toolbar="selected">
            <div class="fw-bolder me-5">
                <span class="me-2"
                    data-kt-products-table-select="selected_count"></span>{{ __('common.selected') }}
            </div>
            <button type="button" class="btn btn-danger"
                data-kt-products-table-select="delete_selected">{{ __('common.delete_selected') }}</button>
        </div>
        <!--end::Group actions-->
        <!--begin::Modal - Export Products-->
        <div class="modal fade" id="kt_modal_export_products" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2 class="fw-bolder">{{ __('product.export_products') }}</h2>
                        <!--end::Modal title-->
                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-users-modal-action="close">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16"
                                        height="2" rx="1" transform="rotate(-45 6 17.3137)"
                                        fill="black"></rect>
                                    <rect x="7.41422" y="6" width="16" height="2"
                                        rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                        <!--begin::Form-->
                        <form id="kt_modal_export_products_form" class="form" action="#">
                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mb-2">{{ __('common.select_status') }}:</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="status" data-control="select2" data-placeholder="{{ __('common.select_status') }}"
                                    data-hide-search="false" class="form-select form-select-solid fw-bolder">
                                    <option></option>
                                    <option value="publish">{{ __('product.publish') }}</option>
                                    <option value="pending">{{ __('product.pending') }}</option>
                                    <option value="draft">{{ __('product.draft') }}</option>
                                    <option value="trash">{{ __('common.trash') }}</option>
                                </select>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label class="required fs-6 fw-bold form-label mb-2">{{ __('common.select_export_format') }}:</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="format" data-control="select2" data-placeholder="{{ __('common.select_format') }}"
                                    data-hide-search="false" class="form-select form-select-solid fw-bolder">
                                    <option></option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                    <option value="csv">CSV</option>
                                    <option value="zip">ZIP</option>
                                </select>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Actions-->
                            <div class="text-center">
                                <button type="reset" class="btn btn-light me-3"
                                    data-kt-users-modal-action="cancel">{{ __('common.discard') }}</button>
                                <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                                    <span class="indicator-label">{{ __('common.submit') }}</span>
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
        <!--end::Modal - Export Products-->

    </div>
    <!--end::Card toolbar-->
</div>
<!--end::Card header-->
