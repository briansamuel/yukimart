<!--begin::Modal - Add Branch Shop-->
<div class="modal fade" id="kt_modal_add_branch_shop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_branch_shop_header">
                <h2 class="fw-bold">{{ __('branch_shop.add_branch_shop') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-branch-shops-modal-action="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_branch_shop_form" class="form" action="#">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_branch_shop_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_branch_shop_header" data-kt-scroll-wrappers="#kt_modal_add_branch_shop_scroll" data-kt-scroll-offset="300px">
                        
                        <!-- Basic Information -->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.code') }}</label>
                            <input type="text" name="code" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('branch_shop.enter_code') }}" />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.name') }}</label>
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('branch_shop.enter_name') }}" />
                        </div>

                        <!-- Address Information -->
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.address') }}</label>
                            <textarea name="address" class="form-control form-control-solid" rows="3" placeholder="{{ __('branch_shop.enter_address') }}"></textarea>
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-4 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.province') }}</label>
                                <input type="text" name="province" class="form-control form-control-solid" placeholder="{{ __('branch_shop.select_province') }}" />
                            </div>
                            <div class="col-md-4 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.district') }}</label>
                                <input type="text" name="district" class="form-control form-control-solid" placeholder="{{ __('branch_shop.select_district') }}" />
                            </div>
                            <div class="col-md-4 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.ward') }}</label>
                                <input type="text" name="ward" class="form-control form-control-solid" placeholder="{{ __('branch_shop.select_ward') }}" />
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.phone') }}</label>
                                <input type="text" name="phone" class="form-control form-control-solid" placeholder="{{ __('branch_shop.enter_phone') }}" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.email') }}</label>
                                <input type="email" name="email" class="form-control form-control-solid" placeholder="{{ __('branch_shop.enter_email') }}" />
                            </div>
                        </div>

                        <!-- Manager -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.manager') }}</label>
                            <select name="manager_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('branch_shop.select_manager') }}" data-allow-clear="true">
                                <option></option>
                            </select>
                        </div>

                        <!-- Shop Type and Status -->
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.shop_type') }}</label>
                                <select name="shop_type" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('branch_shop.select_shop_type') }}">
                                    <option value="flagship">{{ __('branch_shop.flagship') }}</option>
                                    <option value="standard" selected>{{ __('branch_shop.standard') }}</option>
                                    <option value="mini">{{ __('branch_shop.mini') }}</option>
                                    <option value="kiosk">{{ __('branch_shop.kiosk') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('branch_shop.status') }}</label>
                                <select name="status" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('branch_shop.select_status') }}">
                                    <option value="active" selected>{{ __('branch_shop.active') }}</option>
                                    <option value="inactive">{{ __('branch_shop.inactive') }}</option>
                                    <option value="maintenance">{{ __('branch_shop.maintenance') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Operating Hours -->
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.opening_time') }}</label>
                                <input type="time" name="opening_time" class="form-control form-control-solid" value="08:00" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.closing_time') }}</label>
                                <input type="time" name="closing_time" class="form-control form-control-solid" value="22:00" />
                            </div>
                        </div>

                        <!-- Working Days -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.working_days') }}</label>
                            <div class="d-flex flex-wrap">
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Monday" checked />
                                    <span class="form-check-label">{{ __('branch_shop.monday') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Tuesday" checked />
                                    <span class="form-check-label">{{ __('branch_shop.tuesday') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Wednesday" checked />
                                    <span class="form-check-label">{{ __('branch_shop.wednesday') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Thursday" checked />
                                    <span class="form-check-label">{{ __('branch_shop.thursday') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Friday" checked />
                                    <span class="form-check-label">{{ __('branch_shop.friday') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Saturday" checked />
                                    <span class="form-check-label">{{ __('branch_shop.saturday') }}</span>
                                </label>
                                <label class="form-check form-check-custom form-check-solid me-6 mb-2">
                                    <input class="form-check-input" type="checkbox" name="working_days[]" value="Sunday" />
                                    <span class="form-check-label">{{ __('branch_shop.sunday') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Store Details -->
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.area') }}</label>
                                <input type="number" name="area" class="form-control form-control-solid" placeholder="0" step="0.01" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.staff_count') }}</label>
                                <input type="number" name="staff_count" class="form-control form-control-solid" placeholder="0" min="0" />
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="fv-row mb-7">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="has_delivery" value="1" />
                                <span class="form-check-label fw-semibold">{{ __('branch_shop.has_delivery') }}</span>
                            </label>
                        </div>

                        <div class="row g-9 mb-7" id="delivery_fields" style="display: none;">
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.delivery_radius') }}</label>
                                <input type="number" name="delivery_radius" class="form-control form-control-solid" placeholder="0" step="0.01" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.delivery_fee') }}</label>
                                <input type="number" name="delivery_fee" class="form-control form-control-solid" placeholder="0" step="0.01" />
                            </div>
                        </div>

                        <!-- GPS Coordinates -->
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.latitude') }}</label>
                                <input type="number" name="latitude" class="form-control form-control-solid" placeholder="0" step="0.00000001" />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.longitude') }}</label>
                                <input type="number" name="longitude" class="form-control form-control-solid" placeholder="0" step="0.00000001" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.description') }}</label>
                            <textarea name="description" class="form-control form-control-solid" rows="3" placeholder="Mô tả chi nhánh..."></textarea>
                        </div>

                        <!-- Sort Order -->
                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">{{ __('branch_shop.sort_order') }}</label>
                            <input type="number" name="sort_order" class="form-control form-control-solid" placeholder="0" min="0" value="0" />
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-kt-branch-shops-modal-action="cancel">{{ __('branch_shop.cancel') }}</button>
                <button type="button" class="btn btn-primary" data-kt-branch-shops-modal-action="submit">
                    <span class="indicator-label">{{ __('branch_shop.save') }}</span>
                    <span class="indicator-progress">{{ __('admin.please_wait') }}...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Add Branch Shop-->
