<!--begin::Sidebar-->
<div id="products_filter_sidebar"
    class="filter-sidebar flex-column flex-lg-row-auto w-100 w-lg-300px order-1 order-lg-1 mb-10 mb-lg-0">
    <!--begin::Form-->
    <form id="kt_products_filter_form" class="filter-form">
        <!--begin::Time Filter Block-->
        <div id="time_filter_block" class="card card-flush mb-5 filter-block">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center">
                    <h3 class="fw-bold text-dark me-2">Thời gian</h3>
                    <i class="fas fa-calendar fs-2 text-primary"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <!-- Time Filter Container -->
                    <div class="time-filter-container">
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="radio" value="this_month" id="time_this_month"
                                name="time_filter_display" checked />
                            <label
                                class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100"
                                for="time_this_month" id="time_filter_trigger" style="cursor: pointer;">
                                <span>Tháng này</span>
                                <i class="fas fa-chevron-down fs-3 text-muted" id="time_dropdown_icon"></i>
                            </label>
                        </div>

                        <!-- Time Options Panel (Popup) -->
                      
                    </div>
                    <!-- End Time Filter Container -->

                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="custom" id="time_custom"
                            name="time_filter_display" />
                        <label
                            class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100"
                            for="time_custom">
                            <span>Tùy chỉnh</span>
                            <i class="fas fa-calendar-alt fs-3 text-muted"></i>
                        </label>
                    </div>

                    <!-- Custom Date Range Picker -->
                    <div id="custom_date_range" class="mt-3" style="display: none;">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold text-muted">Từ ngày</label>
                                <input type="date" class="form-control form-control-sm" id="date_from"
                                    name="date_from" />
                            </div>
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold text-muted">Đến ngày</label>
                                <input type="date" class="form-control form-control-sm" id="date_to"
                                    name="date_to" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Time Filter Block-->

        <!--begin::Branch Shop Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Chi nhánh</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" multiple="multiple" data-kt-select2="true"
                    data-placeholder="Chọn chi nhánh" data-allow-clear="true" name="branch_shop_ids[]" id="branch_shop_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Branch Shop Filter Block-->

        <!--begin::Category Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Danh mục</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" multiple="multiple" data-kt-select2="true"
                    data-placeholder="Chọn danh mục" data-allow-clear="true" name="category_ids[]" id="category_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Category Filter Block-->

        <!--begin::Status Filter Block-->
        <div id="status_filter_block" class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Trạng thái</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="publish"
                            id="status_publish" checked />
                        <label class="form-check-label fw-semibold" for="status_publish">
                            Đã xuất bản
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="draft"
                            id="status_draft" />
                        <label class="form-check-label fw-semibold" for="status_draft">
                            Bản nháp
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="pending"
                            id="status_pending" />
                        <label class="form-check-label fw-semibold" for="status_pending">
                            Chờ duyệt
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="trash"
                            id="status_trash" />
                        <label class="form-check-label fw-semibold" for="status_trash">
                            Thùng rác
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status Filter Block-->

        <!--begin::Stock Status Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Tồn kho</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="stock_status[]" value="in_stock"
                            id="stock_in_stock" checked />
                        <label class="form-check-label fw-semibold" for="stock_in_stock">
                            Còn hàng
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="stock_status[]" value="low_stock"
                            id="stock_low_stock" />
                        <label class="form-check-label fw-semibold" for="stock_low_stock">
                            Sắp hết
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="stock_status[]" value="out_of_stock"
                            id="stock_out_of_stock" />
                        <label class="form-check-label fw-semibold" for="stock_out_of_stock">
                            Hết hàng
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Stock Status Filter Block-->

        <!--begin::Price Range Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Khoảng giá</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fs-7 fw-bold text-muted">Từ</label>
                        <input type="number" class="form-control form-control-sm" id="price_from"
                            name="price_from" placeholder="0" />
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-7 fw-bold text-muted">Đến</label>
                        <input type="number" class="form-control form-control-sm" id="price_to"
                            name="price_to" placeholder="0" />
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Price Range Filter Block-->

        <!--begin::Creator Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Người tạo</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" multiple="multiple" data-kt-select2="true"
                    data-placeholder="Chọn người tạo" data-allow-clear="true" name="created_by" id="creator_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Creator Filter Block-->

    </form>
    <!--end::Form-->
</div>
<!--end::Sidebar-->
