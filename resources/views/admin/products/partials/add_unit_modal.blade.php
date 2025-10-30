<!--begin::Modal - Thêm đơn vị cơ bản-->
<div class="modal fade" id="kt_modal_add_unit" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h3 class="fw-bold">Thêm đơn vị cơ bản</h3>
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
                <form id="kt_modal_add_unit_form" class="form" action="#">
                    <input type="hidden" name="unit_id" id="unit_id" value="">
                    
                    <!--begin::Input group - Tên đơn vị-->
                    <div class="mb-5">
                        <label class="required form-label">Tên đơn vị cơ bản</label>
                        <input type="text" class="form-control" name="unit_name" id="unit_name" placeholder="Ví dụ: Chai, Lốc, Thùng" required />
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group - Giá bán-->
                    <div class="mb-5">
                        <label class="form-label">Giá bán</label>
                        <input type="number" class="form-control" name="unit_sale_price" id="unit_sale_price" placeholder="0" value="0" min="0" step="0.01" />
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group - Bán trực tiếp-->
                    <div class="mb-5">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_direct_sale" id="is_direct_sale" value="1" checked />
                            <label class="form-check-label" for="is_direct_sale">
                                Bán trực tiếp
                            </label>
                        </div>
                        <div class="form-text">Cho phép bán đơn vị này trực tiếp cho khách hàng</div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Actions-->
                    <div class="text-end pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="button" class="btn btn-light-primary me-3" id="save_and_new_unit_btn">
                            <span class="indicator-label">Xong & Thêm mới</span>
                            <span class="indicator-progress d-none">
                                Đang lưu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                        <button type="submit" class="btn btn-primary" id="save_unit_btn">
                            <span class="indicator-label">Xong</span>
                            <span class="indicator-progress d-none">
                                Đang lưu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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
<!--end::Modal - Thêm đơn vị cơ bản-->

