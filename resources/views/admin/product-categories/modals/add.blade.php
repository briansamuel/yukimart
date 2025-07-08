<!--begin::Modal - Add category-->
<div class="modal fade" id="kt_modal_add_category" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_category_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('product_category.add_category') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-categories-modal-action="close">
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
                <form id="kt_modal_add_category_form" class="form" action="#">
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_category_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_category_header" data-kt-scroll-wrappers="#kt_modal_add_category_scroll" data-kt-scroll-offset="300px">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">{{ __('product_category.name') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('product_category.enter_category_name') }}" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.slug') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="slug" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('product_category.enter_slug') }}" />
                            <!--end::Input-->
                            <!--begin::Hint-->
                            <div class="form-text">{{ __('product_category.slug_hint') }}</div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.description') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea name="description" class="form-control form-control-solid" rows="3" placeholder="{{ __('product_category.enter_description') }}"></textarea>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.parent_category') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="parent_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('product_category.select_parent') }}" data-allow-clear="true">
                                <option></option>
                                <!-- Options will be loaded via AJAX -->
                            </select>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="row g-9 mb-7">
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <!--begin::Label-->
                                <label class="required fw-semibold fs-6 mb-2">{{ __('product_category.status') }}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="is_active" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="{{ __('product_category.select_status') }}">
                                    <option value="1" selected>{{ __('product_category.active') }}</option>
                                    <option value="0">{{ __('product_category.inactive') }}</option>
                                </select>
                                <!--end::Input-->
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <!--begin::Label-->
                                <label class="fw-semibold fs-6 mb-2">{{ __('product_category.sort_order') }}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" name="sort_order" class="form-control form-control-solid" placeholder="0" value="0" min="0" />
                                <!--end::Input-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.image') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="file" name="image" class="form-control form-control-solid" accept="image/*" />
                            <!--end::Input-->
                            <!--begin::Hint-->
                            <div class="form-text">{{ __('product_category.image_hint') }}</div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.meta_title') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="meta_title" class="form-control form-control-solid" placeholder="{{ __('product_category.enter_meta_title') }}" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.meta_description') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea name="meta_description" class="form-control form-control-solid" rows="3" placeholder="{{ __('product_category.enter_meta_description') }}"></textarea>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">{{ __('product_category.meta_keywords') }}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="meta_keywords" class="form-control form-control-solid" placeholder="{{ __('product_category.enter_meta_keywords') }}" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-kt-categories-modal-action="cancel">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" data-kt-categories-modal-action="submit">
                            <span class="indicator-label">{{ __('product_category.create') }}</span>
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
<!--end::Modal - Add category-->
