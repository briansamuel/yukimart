<!--begin::Modal - Chọn giá trị thuộc tính-->
<div class="modal fade" id="kt_modal_select_attribute_values" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h3 class="fw-bold" id="attribute_values_modal_title">Chọn giá trị thuộc tính</h3>
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
                <!--begin::Search and filter-->
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <!--begin::Search-->
                    <div class="position-relative w-50">
                        <i class="fas fa-search fs-6 position-absolute ms-3 mt-3 text-gray-500"></i>
                        <input type="text" class="form-control form-control-sm ps-10" id="attribute_values_search" placeholder="Tìm kiếm" />
                    </div>
                    <!--end::Search-->

                    <!--begin::Filter and select all-->
                    <div class="d-flex align-items-center gap-3">
                        <!--begin::Sort dropdown-->
                        <select class="form-select form-select-sm w-auto" id="attribute_values_sort">
                            <option value="asc">A → Z</option>
                            <option value="desc">Z → A</option>
                        </select>
                        <!--end::Sort dropdown-->

                        <!--begin::Select all link-->
                        <a href="#" class="text-primary fw-bold fs-7 text-nowrap" id="select_all_values_link">
                            Chọn tất cả
                        </a>
                        <!--end::Select all link-->
                    </div>
                </div>
                <!--end::Search and filter-->

                <!--begin::Values container-->
                <div id="attribute_values_container" class="d-flex flex-wrap gap-2 mb-5" style="min-height: 200px;">
                    <!-- Attribute value pills will be dynamically added here -->
                    <div class="text-center text-muted w-100 py-10">
                        <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                        Đang tải...
                    </div>
                </div>
                <!--end::Values container-->

                <!--begin::Actions-->
                <div class="text-end pt-5">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                    <button type="button" class="btn btn-primary" id="confirm_attribute_values_btn">
                        <span class="indicator-label">Xong</span>
                    </button>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Chọn giá trị thuộc tính-->

