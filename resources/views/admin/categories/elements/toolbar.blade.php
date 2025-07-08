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
            <input type="text" data-kt-category-table-filter="search"
                class="form-control form-control-solid w-250px ps-14"
                placeholder="{{ __('admin.categories.search_category') }}">
        </div>
        <!--end::Search-->
    </div>
    <!--begin::Card title-->
    <!--begin::Card toolbar-->
    <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
        <!--begin::Toolbar-->
        <div class="d-flex w-100 justify-content-end" data-kt-category-table-toolbar="base">
            <div class="input-group w-250px me-3">
                <input class="form-control form-control-solid rounded rounded-end-0" placeholder="Pick date range"
                    id="kt_category_created_at" data-kt-category-table-filter="date_picker">
                <button class="btn btn-icon btn-light" id="kt_category_created_at_clear">
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
                    data-placeholder="Status" data-kt-category-table-filter="status" id="kt_page_status">
                    <option></option>
                    <option value="publish">{{ __('admin.categories.status_publish') }}</option>
                    <option value="pending">{{ __('admin.categories.status_pending') }} </option>
                    <option value="draft">{{ __('admin.categories.status_draft') }} </option>
                    <option value="trash">{{ __('admin.categories.status_trash') }}</option>
                </select>
                <!--end::Select2-->
            </div>

        </div>
        <!--begin::Flatpickr-->

        <!--end::Toolbar-->
        <!--begin::Group actions-->
        <div class="d-flex justify-content-end align-items-center d-none" data-kt-category-table-toolbar="selected">
            <div class="fw-bolder me-5">
                <span class="me-2"
                    data-kt-category-table-select="selected_count"></span>{{ __('admin.general.selected') }}
            </div>
            <button type="button" class="btn btn-danger"
                data-kt-category-table-select="delete_selected">{{ __('admin.general.delete_selected') }}</button>
        </div>
        <!--end::Group actions-->


    </div>
    <!--end::Card toolbar-->
</div>
<!--end::Card header-->
