<!--begin::Sidebar-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-300px order-1 order-lg-1 mb-10 mb-lg-0">
    <!--begin::Form-->
    <form id="kt_return_filter_form" class="filter-form">
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
                            <input class="form-check-input" type="radio" value="this_month" id="time_this_month" name="time_filter_display" checked/>
                            <label class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100" for="time_this_month" id="time_filter_trigger" style="cursor: pointer;">
                                <span>Tháng này</span>
                                <i class="fas fa-chevron-down fs-3 text-muted" id="time_dropdown_icon"></i>
                            </label>
                        </div>

                        <!-- Time Options Panel (Popup) -->
                        <div id="time_options_panel"  class="time-options-panel" style="display: none;">
                            <div class="card-body">
                                <!-- Close button -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0">Chọn khoảng thời gian</h6>
                                    <button type="button" class="btn btn-sm btn-icon btn-light" id="close_time_panel">
                                        <i class="fas fa-times fs-2"></i>
                                    </button>
                                </div>
                                <div class="row g-2">
                                <!-- Cột 1: Theo ngày -->
                                <div class="col">
                                    <h6 class="fw-bold text-dark mb-2 fs-7">Theo ngày</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="today">Hôm nay</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="yesterday">Hôm qua</button>
                                    </div>
                                </div>

                                <!-- Cột 2: Theo tuần -->
                                <div class="col">
                                    <h6 class="fw-bold text-dark mb-2 fs-7">Theo tuần</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="this_week">Tuần này</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="last_week">Tuần trước</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="7_days">7 ngày qua</button>
                                    </div>
                                </div>

                                <!-- Cột 3: Theo tháng -->
                                <div class="col">
                                    <h6 class="fw-bold text-dark mb-2 fs-7">Theo tháng</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-primary time-option active" data-value="this_month">Tháng này</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="last_month">Tháng trước</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="30_days">30 ngày qua</button>
                                    </div>
                                </div>

                                <!-- Cột 4: Theo quý -->
                                <div class="col">
                                    <h6 class="fw-bold text-dark mb-2 fs-7">Theo quý</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="this_quarter">Quý này</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="last_quarter">Quý trước</button>
                                    </div>
                                </div>

                                <!-- Cột 5: Theo năm -->
                                <div class="col">
                                    <h6 class="fw-bold text-dark mb-2 fs-7">Theo năm</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="this_year">Năm này</button>
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="last_year">Năm trước</button>
                                    </div>
                                </div>

                              
                            </div>
                        </div>
                        </div>
                    </div>
                    <!-- End Time Filter Container -->

                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="custom" id="time_custom" name="time_filter_display"/>
                        <label class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100" for="time_custom">
                            <span>Tùy chỉnh</span>
                            <i class="fas fa-calendar-alt fs-3 text-muted"></i>
                        </label>
                    </div>

                    <!-- Custom Date Range Picker -->
                    <div id="custom_date_range" class="mt-3" style="display: none;">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold text-muted">Từ ngày</label>
                                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" />
                            </div>
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold text-muted">Đến ngày</label>
                                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Time Filter Block-->

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
            <div  class="card-body pt-0">
                
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="completed" id="status_completed" checked/>
                        <label class="form-check-label fw-semibold" for="status_completed">
                            Hoàn thành
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="cancelled" id="status_cancelled" checked/>
                        <label class="form-check-label fw-semibold" for="status_cancelled">
                            Đã hủy
                        </label>
                    </div>

                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status Filter Block-->

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
                <select class="form-select form-select-solid" multiple="multiple" data-kt-select2="true" data-placeholder="Chọn người tạo" data-allow-clear="true" name="creator_id" id="creator_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Creator Filter Block-->

        <!--begin::Seller Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Người nhận trả</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" multiple="multiple" data-kt-select2="true" data-placeholder="Chọn người nhận trả" data-allow-clear="true" name="approver_id" id="approver_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Seller Filter Block-->


        <!--begin::Sales Channel Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Kênh bán</h3>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-store fs-3 text-primary me-2"></i>
                        <i class="fas fa-chevron-up fs-3 text-muted"></i>
                    </div>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" multiple="multiple" data-kt-select2="true" data-placeholder="Chọn kênh bán" data-allow-clear="true" name="sales_channel" id="sales_channel_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Sales Channel Filter Block-->
        <!--end::Additional Filter Blocks-->
    </form>
    <!--end::Form-->
</div>
<!--end::Sidebar-->
