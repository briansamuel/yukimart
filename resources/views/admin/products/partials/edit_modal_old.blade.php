<!--begin::Modal - Sửa hàng hóa-->
<div class="modal fade" id="kt_modal_edit_product" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-between">
                <!--begin::Modal title-->
                <h2 class="fw-bold">Sửa hàng hóa</h2>
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
                <form id="kt_modal_edit_product_form" class="form" action="#">
                    <input type="hidden" name="product_id" id="edit_product_id" />
                    
                    <!--begin::Tabs-->
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_edit_product_info">Thông tin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_edit_product_description">Mô tả</a>
                        </li>
                    </ul>
                    <!--end::Tabs-->

                    <!--begin::Tab content-->
                    <div class="tab-content" id="kt_edit_product_tabs">
                        <!--begin::Tab pane - Thông tin-->
                        <div class="tab-pane fade show active" id="kt_tab_edit_product_info" role="tabpanel">
                            <div class="row">
                                <!--begin::Left column-->
                                <div class="col-md-8">
                                    <!--begin::Mã hàng & Mã vạch-->
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label class="form-label">Mã hàng</label>
                                            <input type="text" class="form-control" name="sku" id="edit_sku" placeholder="Tự động" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mã vạch</label>
                                            <input type="text" class="form-control" name="barcode" id="edit_barcode" placeholder="Nhập mã vạch" />
                                        </div>
                                    </div>
                                    <!--end::Mã hàng & Mã vạch-->

                                    <!--begin::Tên hàng-->
                                    <div class="mb-5">
                                        <label class="form-label required">Tên hàng</label>
                                        <input type="text" class="form-control" name="product_name" id="edit_product_name" placeholder="Nhập tên hàng" required />
                                    </div>
                                    <!--end::Tên hàng-->

                                    <!--begin::Nhóm hàng & Thương hiệu-->
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label class="form-label">Nhóm hàng</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <select class="form-select" name="category_id" id="edit_category_select">
                                                    <option value="">Chọn nhóm hàng (Bắt buộc)</option>
                                                    @if(isset($categories))
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <a href="#" class="text-primary text-nowrap fw-bold" id="edit_create_category_link">Tạo mới</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Thương hiệu</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <select class="form-select" name="brand_select" id="edit_brand_select">
                                                    <option value="">Chọn thương hiệu</option>
                                                </select>
                                                <a href="#" class="text-primary text-nowrap fw-bold" id="edit_create_brand_link">Tạo mới</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Nhóm hàng & Thương hiệu-->

                                    <!--begin::Giá vốn & Giá bán-->
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label class="form-label">Giá vốn</label>
                                            <input type="number" class="form-control" name="cost_price" id="edit_cost_price" placeholder="0" min="0" step="1000" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Giá bán</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" class="form-control" name="sale_price" id="edit_sale_price" placeholder="0" min="0" step="1000" />
                                                <a href="#" class="text-primary text-nowrap fw-bold" id="edit_setup_price_link">Thiết lập</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Giá vốn & Giá bán-->

                                    <!--begin::Tồn kho-->
                                    <div class="mb-5">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0">Tồn kho</label>
                                            <a class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" href="#edit_stock_section">
                                                <i class="fas fa-chevron-down"></i>
                                            </a>
                                        </div>
                                        <div class="collapse" id="edit_stock_section">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Tồn kho ban đầu</label>
                                                    <input type="number" class="form-control" name="initial_stock" id="edit_initial_stock" placeholder="0" min="0" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Tồn kho tối thiểu</label>
                                                    <input type="number" class="form-control" name="reorder_point" id="edit_reorder_point" placeholder="0" min="0" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Tồn kho tối đa</label>
                                                    <input type="number" class="form-control" name="max_stock" id="edit_max_stock" placeholder="0" min="0" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Tồn kho-->

                                    <!--begin::Điểm tích luỹ-->
                                    <div class="mb-5">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <label class="form-label mb-0">Điểm tích luỹ</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_enable_points" />
                                            </div>
                                        </div>
                                        <div class="collapse" id="edit_points_section">
                                            <input type="number" class="form-control mt-2" name="points" id="edit_points" placeholder="0" min="0" />
                                        </div>
                                    </div>
                                    <!--end::Điểm tích luỹ-->

                                    <!--begin::Vị trí & Trọng lượng-->
                                    <div class="mb-5">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0">Vị trí & Trọng lượng</label>
                                            <a class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" href="#edit_location_section">
                                                <i class="fas fa-chevron-down"></i>
                                            </a>
                                        </div>
                                        <div class="collapse" id="edit_location_section">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Vị trí</label>
                                                    <input type="text" class="form-control" name="location" id="edit_location" placeholder="Nhập vị trí" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Trọng lượng (gram)</label>
                                                    <input type="number" class="form-control" name="weight" id="edit_weight" placeholder="0" min="0" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Vị trí & Trọng lượng-->

                                    <!--begin::Đơn vị tính & Thuộc tính-->
                                    <div class="mb-5">
                                        <button type="button" class="btn btn-light-primary w-100" id="edit_setup_unit_attribute_btn">
                                            <i class="fas fa-cog me-2"></i>Thiết lập đơn vị tính và thuộc tính
                                        </button>
                                    </div>
                                    <!--end::Đơn vị tính & Thuộc tính-->

                                    <!--begin::Hoa hồng-->
                                    <div class="mb-5">
                                        <button type="button" class="btn btn-light-primary w-100" id="edit_setup_commission_btn">
                                            <i class="fas fa-percentage me-2"></i>Thiết lập hoa hồng nhân viên
                                        </button>
                                    </div>
                                    <!--end::Hoa hồng-->

                                    <!--begin::Bán trực tiếp-->
                                    <div class="mb-5">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="direct_sale" id="edit_direct_sale" />
                                            <label class="form-check-label" for="edit_direct_sale">
                                                Bán trực tiếp (không cần quản lý kho)
                                            </label>
                                        </div>
                                    </div>
                                    <!--end::Bán trực tiếp-->
                                </div>
                                <!--end::Left column-->

                                <!--begin::Right column - Image-->
                                <div class="col-md-4">
                                    <label class="form-label">Ảnh sản phẩm</label>
                                    <div class="border border-dashed border-gray-300 rounded p-5 text-center cursor-pointer" id="edit_product_image_upload_area" style="min-height: 200px;">
                                        <div id="edit_product_image_placeholder">
                                            <i class="fas fa-cloud-upload-alt fs-3x text-primary mb-3"></i>
                                            <p class="text-gray-600 mb-0">Click để chọn ảnh</p>
                                        </div>
                                        <div id="edit_product_image_preview" class="d-none position-relative">
                                            <img src="" alt="Product" class="img-fluid rounded mb-2" style="max-height: 200px;" />
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" id="edit_remove_product_image">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="product_thumbnail" id="edit_product_thumbnail" />
                                </div>
                                <!--end::Right column-->
                            </div>
                        </div>
                        <!--end::Tab pane - Thông tin-->

                        <!--begin::Tab pane - Mô tả-->
                        <div class="tab-pane fade" id="kt_tab_edit_product_description" role="tabpanel">
                            <div class="mb-5">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea class="form-control" name="product_description" id="edit_product_description" rows="3" placeholder="Nhập mô tả ngắn"></textarea>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control" name="product_content" id="edit_product_content" rows="6" placeholder="Nhập mô tả chi tiết"></textarea>
                            </div>
                        </div>
                        <!--end::Tab pane - Mô tả-->
                    </div>
                    <!--end::Tab content-->

                    <!--begin::Actions-->
                    <div class="d-flex justify-content-end gap-3 mt-10">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="submit" class="btn btn-primary" id="update_product_btn">
                            <span class="indicator-label">Cập nhật</span>
                            <span class="indicator-progress d-none">
                                Đang xử lý... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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
<!--end::Modal-->

