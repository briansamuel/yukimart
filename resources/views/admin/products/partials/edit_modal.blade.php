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
                                        <label class="required form-label">Tên hàng</label>
                                        <input type="text" class="form-control" name="product_name" id="edit_product_name" placeholder="Bắt buộc" required />
                                    </div>
                                    <!--end::Tên hàng-->

                                    <!--begin::Nhóm hàng & Thương hiệu-->
                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">Nhóm hàng</label>
                                                <a href="#" class="text-primary text-nowrap fw-bold fs-7" id="edit_create_category_link">Tạo mới</a>
                                            </div>
                                            <select class="form-select" name="category_id" id="edit_category_select">
                                                <option value="">Chọn nhóm hàng (Bắt buộc)</option>
                                                @if(isset($categories))
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">Thương hiệu</label>
                                                <a href="#" class="text-primary text-nowrap fw-bold fs-7" id="edit_create_brand_link">Tạo mới</a>
                                            </div>
                                            <select class="form-select" name="edit_brand_select" id="edit_brand_select">
                                                <option value="">Chọn thương hiệu</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!--end::Nhóm hàng & Thương hiệu-->
                                </div>
                                <!--end::Left column-->

                                <!--begin::Right column - Thêm ảnh-->
                                <div class="col-md-4">
                                    <div class="mb-5">
                                        <label class="form-label">Thêm ảnh</label>
                                        <!--begin::Image upload area-->
                                        <div class="d-flex gap-3">
                                            <!--begin::Main image (large square)-->
                                            <div style="flex: 0 0 200px;">
                                                <div class="border border-dashed border-gray-300 rounded text-center cursor-pointer position-relative"
                                                     id="edit_product_main_image_area"
                                                     style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; padding: 8px;">
                                                    <div id="edit_product_main_image_preview" class="d-none w-100 h-100 position-relative">
                                                        <img src="" alt="Main Image" id="edit_product_main_image_img" class="rounded" style="width: 100%; height: 100%; object-fit: cover;" />
                                                        <button type="button" class="btn btn-sm btn-icon btn-danger position-absolute top-0 end-0 m-1"
                                                                onclick="removeEditProductImage('main')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <div id="edit_product_main_image_placeholder" class="text-center">
                                                        <button type="button" class="btn btn-light-primary btn-sm mb-2">
                                                            <i class="fas fa-plus me-1"></i>Thêm ảnh
                                                        </button>
                                                        <div class="text-muted fs-8">Mỗi ảnh không quá 2MB</div>
                                                    </div>
                                                </div>
                                                <input type="file" name="edit_product_thumbnail_file" id="edit_product_thumbnail_file" class="d-none" accept="image/*" />
                                                <input type="hidden" name="edit_product_thumbnail" id="edit_product_thumbnail" />
                                            </div>
                                            <!--end::Main image-->

                                            <!--begin::Additional images (4 small square images vertical)-->
                                            <div style="flex: 0 0 45px;">
                                                <div class="d-flex flex-column gap-2">
                                                    @for($i = 0; $i < 4; $i++)
                                                    <div class="border border-dashed border-gray-300 rounded text-center cursor-pointer position-relative"
                                                         id="edit_product_additional_image_area_{{ $i }}"
                                                         style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; padding: 2px;">
                                                        <div id="edit_product_additional_image_preview_{{ $i }}" class="d-none w-100 h-100 position-relative">
                                                            <img src="" alt="Image {{ $i + 1 }}" id="edit_product_additional_image_img_{{ $i }}" class="rounded" style="width: 100%; height: 100%; object-fit: cover;" />
                                                            <div class="position-absolute top-0 end-0 d-flex gap-1" style="margin: -4px -4px 0 0;">
                                                                <button type="button" class="btn btn-sm btn-icon btn-primary"
                                                                        style="padding: 1px 3px; font-size: 8px; width: 16px; height: 16px;"
                                                                        onclick="pinEditProductImage({{ $i }})"
                                                                        title="Đặt làm ảnh chính">
                                                                    <i class="fas fa-thumbtack"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-icon btn-danger"
                                                                        style="padding: 1px 3px; font-size: 8px; width: 16px; height: 16px;"
                                                                        onclick="removeEditProductImage({{ $i }})">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div id="edit_product_additional_image_placeholder_{{ $i }}" class="w-100 h-100">
                                                            <img src="/admin-assets/assets/images/image-not-available.jpg" alt="Placeholder" class="rounded" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.3;" />
                                                        </div>
                                                    </div>
                                                    <input type="file" name="edit_product_images_file[]" id="edit_product_image_file_{{ $i }}" class="d-none" accept="image/*" />
                                                    <input type="hidden" name="edit_product_images[]" id="edit_product_image_{{ $i }}" />
                                                    @endfor
                                                </div>
                                            </div>
                                            <!--end::Additional images-->
                                        </div>
                                        <!--end::Image upload area-->
                                    </div>
                                </div>
                                <!--end::Right column-->
                            </div>

                            <!--begin::Full width fields (from Giá bán onwards)-->
                            <div class="row">
                                <div class="col-12">
                                    <!--begin::Giá bán-->
                                    <div class="mb-5">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0">Giá bán</label>
                                            <a href="#" class="text-primary fw-bold" id="edit_setup_price_link">
                                                <i class="fas fa-cog me-1"></i>Thiết lập giá
                                            </a>
                                        </div>
                                        <input type="number" class="form-control form-control-lg text-end fs-3"
                                               name="sale_price" id="edit_sale_price" placeholder="0" value="0" min="0" step="0.01" />
                                    </div>
                                    <!--end::Giá bán-->

                                    <!--begin::Giá vốn-->
                                    <div class="mb-5">
                                        <label class="form-label">Giá vốn</label>
                                        <input type="number" class="form-control text-end"
                                               name="cost_price" id="edit_cost_price" placeholder="0" value="0" min="0" step="0.01" />
                                    </div>
                                    <!--end::Giá vốn-->

                                    <!--begin::Tồn kho (Collapsible)-->
                                    <div class="mb-5">
                                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#edit_stock_section">
                                            <label class="form-label mb-0">Tồn kho</label>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        <div class="collapse" id="edit_stock_section">
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fs-7">Tồn kho</label>
                                                    <input type="number" class="form-control text-end" name="initial_stock" id="edit_initial_stock" placeholder="0" value="0" min="0" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-7">Định mức tồn thấp nhất</label>
                                                    <input type="number" class="form-control text-end" name="reorder_point" id="edit_reorder_point" placeholder="0" value="0" min="0" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-7">Định mức tồn cao nhất</label>
                                                    <input type="number" class="form-control text-end" name="max_stock" id="edit_max_stock" placeholder="999,999,999" value="999999999" min="0" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Tồn kho-->

                                    <!--begin::Tích điểm-->
                                    <div class="mb-5">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="form-label mb-0">Tích điểm</label>
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" name="edit_enable_points" id="edit_enable_points" />
                                            </div>
                                        </div>
                                        <div class="collapse" id="edit_points_section">
                                            <div class="mt-3">
                                                <label class="form-label fs-7">Điểm</label>
                                                <input type="number" class="form-control" name="points" id="edit_points" placeholder="0" value="0" min="0" />
                                                <div class="form-text">Tỷ lệ quy đổi 100 VNĐ = 1 điểm</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Tích điểm-->

                                    <!--begin::Vị trí, trọng lượng (Collapsible)-->
                                    <div class="mb-5">
                                        <div class="d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#edit_location_weight_section">
                                            <label class="form-label mb-0">Vị trí, trọng lượng</label>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        <div class="collapse" id="edit_location_weight_section">
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7">Vị trí</label>
                                                    <select class="form-select" name="location" id="edit_location">
                                                        <option value="">Chọn vị trí</option>
                                                    </select>
                                                    <a href="#" class="text-primary fs-7 fw-bold mt-1 d-inline-block">Tạo mới</a>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7">Trọng lượng</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control text-end" name="weight" id="edit_weight" placeholder="0" value="0" min="0" />
                                                        <span class="input-group-text">g</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Vị trí, trọng lượng-->

                                    <!--begin::Quản lý theo đơn vị tính và thuộc tính-->
                                    <div class="mb-5">
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                            <div>
                                                <div class="fw-bold">Quản lý theo đơn vị tính và thuộc tính</div>
                                                <div class="text-muted fs-7">Tạo nhiều hàng hóa khác đơn vị tính (chai, lốc, thùng) hoặc đặc điểm (hương vị, dung tích, màu sắc). Mỗi hàng hóa có 1 mã hàng riêng.</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary" id="edit_setup_unit_attribute_btn">Thiết lập</button>
                                        </div>
                                    </div>
                                    <!--end::Quản lý theo đơn vị tính và thuộc tính-->

                                    <!--begin::Hoa hồng nhân viên-->
                                    <div class="mb-5">
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                            <div>
                                                <div class="fw-bold">Hoa hồng nhân viên</div>
                                                <div class="text-muted fs-7">Thiết lập hoa hồng cho nhân viên theo % doanh thu hoặc giá trị cụ thể</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary" id="edit_setup_commission_btn">Thiết lập</button>
                                        </div>
                                    </div>
                                    <!--end::Hoa hồng nhân viên-->

                                    <!--begin::Bán trực tiếp-->
                                    <div class="mb-5">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="edit_direct_sale" id="edit_direct_sale" checked />
                                            <label class="form-check-label" for="edit_direct_sale">
                                                <span class="fw-bold">Bán trực tiếp</span>
                                                <i class="fas fa-info-circle ms-1 text-muted" data-bs-toggle="tooltip" title="Cho phép bán sản phẩm này trực tiếp"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <!--end::Bán trực tiếp-->
                                </div>
                            </div>
                            <!--end::Full width fields-->
                        </div>
                        <!--end::Tab pane - Thông tin-->

                        <!--begin::Tab pane - Mô tả-->
                        <div class="tab-pane fade" id="kt_tab_edit_product_description" role="tabpanel">
                            <div class="mb-5">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea class="form-control" name="product_description" id="edit_product_description" rows="3" placeholder="Nhập mô tả ngắn về sản phẩm"></textarea>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">Nội dung chi tiết</label>
                                <textarea class="form-control" name="product_content" id="edit_product_content" rows="8" placeholder="Nhập nội dung chi tiết về sản phẩm"></textarea>
                            </div>
                        </div>
                        <!--end::Tab pane - Mô tả-->
                    </div>
                    <!--end::Tab content-->

                    <!--begin::Actions-->
                    <div class="text-end pt-5 border-top mt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="button" class="btn btn-light-primary me-3" id="update_and_close_btn">
                            Cập nhật Lưu & Tạo thêm hàng Đóng
                        </button>
                        <button type="submit" class="btn btn-primary" id="update_product_btn">
                            <span class="indicator-label">Cập nhật</span>
                            <span class="indicator-progress d-none">
                                Đang cập nhật... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
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
<!--end::Modal - Sửa hàng hóa-->

