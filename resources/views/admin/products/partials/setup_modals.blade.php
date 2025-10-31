<!--begin::Modal - Thiết lập giá-->
<div class="modal fade" id="kt_modal_setup_price" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h3 class="fw-bold">Thiết lập giá</h3>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                <!--begin::Form-->
                <form id="kt_modal_setup_price_form" class="form" action="#">
                    <div class="mb-5">
                        <label class="form-label">Giá vốn</label>
                        <input type="number" class="form-control text-end" name="setup_cost_price" placeholder="0" value="0" min="0" step="0.01" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Giá bán</label>
                        <input type="number" class="form-control text-end" name="setup_sale_price" placeholder="0" value="0" min="0" step="0.01" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" class="form-control text-end" name="setup_promo_price" placeholder="0" value="0" min="0" step="0.01" />
                    </div>

                    <!--begin::Actions-->
                    <div class="text-end pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Áp dụng</button>
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
<!--end::Modal - Thiết lập giá-->

@include('admin.products.partials.setup_unit_attribute_modal')
@include('admin.products.partials.add_unit_modal')
@include('admin.products.partials.select_attribute_values_modal')

<!--begin::Modal - Thiết lập hoa hồng nhân viên-->
<div class="modal fade" id="kt_modal_setup_commission" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h3 class="fw-bold">Thiết lập hoa hồng nhân viên</h3>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->

            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-10 pt-0 pb-10">
                <!--begin::Form-->
                <form id="kt_modal_setup_commission_form" class="form" action="#">
                    <div class="mb-5">
                        <label class="form-label">Loại hoa hồng</label>
                        <select class="form-select" name="commission_type">
                            <option value="percentage">Theo % doanh thu</option>
                            <option value="fixed">Giá trị cố định</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Giá trị</label>
                        <input type="number" class="form-control text-end" name="commission_value" placeholder="0" value="0" min="0" step="0.01" />
                    </div>

                    <!--begin::Actions-->
                    <div class="text-end pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Áp dụng</button>
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
<!--end::Modal - Thiết lập hoa hồng nhân viên-->

