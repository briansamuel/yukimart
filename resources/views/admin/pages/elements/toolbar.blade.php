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
            <input type="text" data-kt-page-table-filter="search"
                class="form-control form-control-solid w-250px ps-14"
                placeholder="{{ __('admin.pages.search_pages') }}">
        </div>
        <!--end::Search-->
    </div>
    <!--begin::Card title-->
    <!--begin::Card toolbar-->
    <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
        <!--begin::Toolbar-->
        <div class="d-flex w-100 justify-content-end" data-kt-page-table-toolbar="base">
            <div class="input-group w-250px me-3">
                <input class="form-control form-control-solid rounded rounded-end-0" placeholder="Pick date range"
                    id="kt_pages_created_at" data-kt-page-table-filter="date_picker">
                <button class="btn btn-icon btn-light" id="kt_pages_created_at_clear">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr088.svg-->
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="7.05025" y="15.5356" width="12" height="2"
                                rx="1" transform="rotate(-45 7.05025 15.5356)" fill="currentColor"></rect>
                            <rect x="8.46447" y="7.05029" width="12" height="2" rx="1"
                                transform="rotate(45 8.46447 7.05029)" fill="currentColor"></rect>
                        </svg>
                    </span>
                    <!--end::Svg Icon-->
                </button>
            </div>
            <!--end::Flatpickr-->
            <div class="w-100 mw-150px">
                <!--begin::Select2-->
                <select class="form-select form-select-solid" data-control="select2" data-hide-search="false"
                    data-placeholder="Status" data-kt-page-table-filter="status" id="kt_page_status">
                    <option></option>
                    <option value="publish">{{ __('admin.pages.status_publish') }}</option>
                    <option value="pending">{{ __('admin.pages.status_pending') }} </option>
                    <option value="draft">{{ __('admin.pages.status_draft') }} </option>
                    <option value="trash">{{ __('admin.pages.status_trash') }}</option>
                </select>
                <!--end::Select2-->
            </div>

            <!--begin::Export-->
            <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal"
                data-bs-target="#kt_modal_export_users">
                <!--begin::Svg Icon | path: icons/duotune/arrows/arr078.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none">
                        <rect opacity="0.3" x="12.75" y="4.25" width="12" height="2" rx="1"
                            transform="rotate(90 12.75 4.25)" fill="black"></rect>
                        <path
                            d="M12.0573 6.11875L13.5203 7.87435C13.9121 8.34457 14.6232 8.37683 15.056 7.94401C15.4457 7.5543 15.4641 6.92836 15.0979 6.51643L12.4974 3.59084C12.0996 3.14332 11.4004 3.14332 11.0026 3.59084L8.40206 6.51643C8.0359 6.92836 8.0543 7.5543 8.44401 7.94401C8.87683 8.37683 9.58785 8.34458 9.9797 7.87435L11.4427 6.11875C11.6026 5.92684 11.8974 5.92684 12.0573 6.11875Z"
                            fill="black"></path>
                        <path
                            d="M18.75 8.25H17.75C17.1977 8.25 16.75 8.69772 16.75 9.25C16.75 9.80228 17.1977 10.25 17.75 10.25C18.3023 10.25 18.75 10.6977 18.75 11.25V18.25C18.75 18.8023 18.3023 19.25 17.75 19.25H5.75C5.19772 19.25 4.75 18.8023 4.75 18.25V11.25C4.75 10.6977 5.19771 10.25 5.75 10.25C6.30229 10.25 6.75 9.80228 6.75 9.25C6.75 8.69772 6.30229 8.25 5.75 8.25H4.75C3.64543 8.25 2.75 9.14543 2.75 10.25V19.25C2.75 20.3546 3.64543 21.25 4.75 21.25H18.75C19.8546 21.25 20.75 20.3546 20.75 19.25V10.25C20.75 9.14543 19.8546 8.25 18.75 8.25Z"
                            fill="#C4C4C4"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->{{ __('admin.general.export') }}
            </button>
            <!--end::Export-->
            <!--begin::Add Page-->
            <a href="{{ route('page.add') }}" type="button" class="btn btn-primary">
                <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none">
                        <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                            rx="1" transform="rotate(-90 11.364 20.364)" fill="black"></rect>
                        <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                            fill="black"></rect>
                    </svg>
                </span>
                <!--end::Svg Icon-->{{ __('admin.pages.add') }}
            </a>
            <!--end::Add Page-->
        </div>
        <!--begin::Flatpickr-->

        <!--end::Toolbar-->
        <!--begin::Group actions-->
        <div class="d-flex justify-content-end align-items-center d-none" data-kt-page-table-toolbar="selected">
            <div class="fw-bolder me-5">
                <span class="me-2"
                    data-kt-page-table-select="selected_count"></span>{{ __('admin.general.selected') }}
            </div>
            <button type="button" class="btn btn-danger"
                data-kt-page-table-select="delete_selected">{{ __('admin.general.delete_selected') }}</button>
        </div>
        <!--end::Group actions-->
        <!--begin::Modal - Adjust Balance-->
        <div class="modal fade" id="kt_modal_export_users" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2 class="fw-bolder">{{ __('admin.pages.export_pages') }}</h2>
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
                        <form id="kt_modal_export_users_form" class="form" action="#">
                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label
                                    class="fs-6 fw-bold form-label mb-2">{{ __('admin.general.select_status') }}:</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="role" data-control="select2" data-placeholder="Select a role"
                                    data-hide-search="false" class="form-select form-select-solid fw-bolder">
                                    <option></option>
                                    <option value="publish">{{ __('admin.pages.status_publish') }}</option>
                                    <option value="pending">{{ __('admin.pages.status_pending') }}</option>
                                    <option value="draft">{{ __('admin.pages.status_draft') }}</option>
                                    <option value="trash">{{ __('admin.pages.status_trash') }}</option>
                                </select>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label
                                    class="required fs-6 fw-bold form-label mb-2">{{ __('admin.general.select_export_format') }}:</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="format" data-control="select2" data-placeholder="Select a format"
                                    data-hide-search="false" class="form-select form-select-solid fw-bolder">
                                    <option></option>
                                    <option value="excel">Excel</option>
                                    <option value="pdf">PDF</option>
                                    <option value="cvs">CVS</option>
                                    <option value="zip">ZIP</option>
                                </select>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Actions-->
                            <div class="text-center">
                                <button type="reset" class="btn btn-light me-3"
                                    data-kt-users-modal-action="cancel">{{ __('admin.general.discard') }}</button>
                                <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                                    <span class="indicator-label">{{ __('admin.general.submit') }}</span>
                                    <span class="indicator-progress">{{ __('admin.general.please_waiting') }}
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
        <!--end::Modal - New Card-->

    </div>
    <!--end::Card toolbar-->
</div>
<!--end::Card header-->
