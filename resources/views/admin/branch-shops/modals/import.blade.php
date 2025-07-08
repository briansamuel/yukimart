<!--begin::Modal - Import Branch Shops-->
<div class="modal fade" id="kt_modal_import_branch_shops" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_import_branch_shops_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('branch_shop.import_branch_shops') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-branch-shops-modal-action="close">
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
                <form id="kt_modal_import_branch_shops_form" class="form" action="#">
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_import_branch_shops_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_import_branch_shops_header" data-kt-scroll-wrappers="#kt_modal_import_branch_shops_scroll" data-kt-scroll-offset="300px">
                        
                        <!--begin::Notice-->
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                            <!--begin::Icon-->
                            <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <!--end::Icon-->
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack flex-grow-1">
                                <!--begin::Content-->
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">{{ __('branch_shop.import_notice_title') }}</h4>
                                    <div class="fs-6 text-gray-700">{{ __('branch_shop.import_notice_description') }}</div>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Notice-->

                        <!--begin::Step 1-->
                        <div class="current" data-kt-stepper-element="content">
                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label class="d-flex align-items-center form-label mb-3">
                                    {{ __('branch_shop.select_import_file') }}
                                    <span class="ms-1" data-bs-toggle="tooltip" title="{{ __('branch_shop.import_file_tooltip') }}">
                                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="file" class="form-control form-control-lg form-control-solid" name="import_file" accept=".xlsx,.xls,.csv" />
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="fv-row mb-10">
                                <!--begin::Label-->
                                <label class="form-label">{{ __('branch_shop.import_options') }}</label>
                                <!--end::Label-->
                                <!--begin::Options-->
                                <div class="d-flex fv-row">
                                    <!--begin::Option-->
                                    <div class="form-check form-check-custom form-check-solid">
                                        <!--begin::Input-->
                                        <input class="form-check-input me-3" name="update_existing" type="checkbox" value="1" id="kt_modal_import_update_existing" checked="checked" />
                                        <!--end::Input-->
                                        <!--begin::Label-->
                                        <label class="form-check-label" for="kt_modal_import_update_existing">
                                            <div class="fw-semibold text-gray-800">{{ __('branch_shop.update_existing') }}</div>
                                            <div class="text-gray-600">{{ __('branch_shop.update_existing_description') }}</div>
                                        </label>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Option-->
                                </div>
                                <!--end::Options-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Actions-->
                            <div class="d-flex flex-stack">
                                <!--begin::Wrapper-->
                                <div class="me-2">
                                    <button type="button" class="btn btn-light btn-active-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_download_template">
                                        {{ __('branch_shop.download_template') }}
                                    </button>
                                </div>
                                <!--end::Wrapper-->
                                <!--begin::Wrapper-->
                                <div>
                                    <button type="button" class="btn btn-lg btn-primary" data-kt-branch-shops-modal-action="submit">
                                        <span class="indicator-label">{{ __('branch_shop.import_data') }}</span>
                                        <span class="indicator-progress">{{ __('common.please_wait') }}...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Step 1-->
                    </div>
                    <!--end::Scroll-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Import Branch Shops-->

<!--begin::Modal - Download Template-->
<div class="modal fade" id="kt_modal_download_template" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('branch_shop.download_template') }}</h2>
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
                <div class="text-center">
                    <p class="text-gray-600 mb-5">{{ __('branch_shop.template_description') }}</p>
                    <a href="{{ route('admin.branch-shops.template') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-exit-down fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('branch_shop.download_excel_template') }}
                    </a>
                </div>
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Download Template-->
