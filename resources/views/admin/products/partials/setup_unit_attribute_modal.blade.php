<!--begin::Modal - Thiết lập đơn vị tính và thuộc tính-->
<div class="modal fade" id="kt_modal_setup_unit_attribute" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content shadow-lg">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h3 class="fw-bold">Thiết lập đơn vị tính và thuộc tính</h3>
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
                <form id="kt_modal_setup_unit_attribute_form" class="form" action="#">
                    
                    <!--begin::Section - Đơn vị tính-->
                    <div class="mb-10">
                        <h4 class="fw-bold mb-3">Đơn vị tính</h4>
                        <p class="text-muted fs-7 mb-5">
                            Thêm đơn vị bán hoặc nhập như chai, lốc, thùng. Đặt công thức quy đổi để tính nhanh giá và tồn kho. 
                            Ví dụ: 1 lốc = 4 chai, 1 thùng = 20 lốc.
                        </p>
                        
                        <!--begin::Button - Thêm đơn vị cơ bản-->
                        <button type="button" class="btn btn-sm btn-light-primary mb-5" id="add_unit_btn">
                            <i class="fas fa-plus fs-7"></i>
                            Thêm đơn vị cơ bản
                        </button>
                        <!--end::Button-->

                        <!--begin::Units list-->
                        <div id="units_list" class="mb-5">
                            <!-- Units will be dynamically added here -->
                        </div>
                        <!--end::Units list-->
                    </div>
                    <!--end::Section-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Section - Thuộc tính-->
                    <div class="mb-10">
                        <h4 class="fw-bold mb-3">Thuộc tính</h4>
                        <p class="text-muted fs-7 mb-5">
                            Thêm đặc điểm như hương vị, dung tích, màu sắc
                        </p>

                        <!--begin::Attributes container-->
                        <div id="attributes_container">
                            <!-- Attributes will be dynamically added here -->
                        </div>
                        <!--end::Attributes container-->

                        <!--begin::Button - Thêm thuộc tính-->
                        <button type="button" class="btn btn-sm btn-light-primary mt-3" id="add_attribute_btn">
                            <i class="fas fa-plus fs-7"></i>
                            Thêm thuộc tính
                        </button>
                        <!--end::Button-->
                    </div>
                    <!--end::Section-->

                    <!--begin::Separator-->
                    <div class="separator separator-dashed my-10"></div>
                    <!--end::Separator-->

                    <!--begin::Section - Hàng cùng loại (Variants)-->
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">
                                <a href="#" class="text-gray-800" data-bs-toggle="collapse" data-bs-target="#variants_section">
                                    <i class="fas fa-chevron-down fs-7 me-2"></i>
                                    Hàng cùng loại
                                </a>
                            </h4>
                            <a href="#" class="text-primary fw-bold fs-7" id="setup_price_link">
                                <i class="fas fa-link fs-8 me-1"></i>
                                Thiết lập giá
                            </a>
                        </div>

                        <!--begin::Collapsible content-->
                        <div class="collapse show" id="variants_section">
                            <!--begin::Table wrapper-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th class="min-w-150px">Giá trị thuộc tính</th>
                                            <th class="min-w-100px">Quy đổi</th>
                                            <th class="min-w-100px">Mã hàng</th>
                                            <th class="min-w-100px">Mã vạch</th>
                                            <th class="min-w-100px">Giá vốn</th>
                                            <th class="min-w-100px">Giá bán</th>
                                            <th class="min-w-80px">Tồn kho</th>
                                            <th class="min-w-80px">Điểm</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody id="variants_table_body">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-10">
                                                Chọn thuộc tính để tự động tạo hàng cùng loại
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table wrapper-->
                        </div>
                        <!--end::Collapsible content-->
                    </div>
                    <!--end::Section-->

                    <!--begin::Actions-->
                    <div class="text-end pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="submit" class="btn btn-primary" id="save_setup_btn">
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
<!--end::Modal - Thiết lập đơn vị tính và thuộc tính-->

