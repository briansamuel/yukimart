<!--begin::Modal - Tạo thương hiệu-->
<div class="modal fade" id="kt_modal_create_brand" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <!--begin::Modal content-->
        <div class="modal-content shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h3 class="fw-bold">Tạo thương hiệu mới</h3>
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
                <form id="kt_modal_create_brand_form" class="form" action="#">
                    <!--begin::Input group-->
                    <div class="mb-5">
                        <label class="required form-label">Tên thương hiệu</label>
                        <input type="text" class="form-control" name="brand_name" placeholder="Nhập tên thương hiệu" required />
                    </div>
                    <!--end::Input group-->

                    <!--begin::Actions-->
                    <div class="text-end pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="save_brand_btn">
                            <span class="indicator-label">Lưu</span>
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
<!--end::Modal - Tạo thương hiệu-->

