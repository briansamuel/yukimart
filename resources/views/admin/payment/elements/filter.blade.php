<!--begin::Sidebar-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-300px order-1 order-lg-1 mb-10 mb-lg-0">
    <!--begin::Form-->
    <form id="kt_payment_filter_form" class="filter-form">
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
                        <div id="time_options_panel" class="time-options-panel" style="display: none;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h6>Theo ngày</h6>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="#" class="time-option" data-value="today">Hôm nay</a>
                                            <a href="#" class="time-option" data-value="yesterday">Hôm qua</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6>Theo tuần</h6>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="#" class="time-option" data-value="this_week">Tuần này</a>
                                            <a href="#" class="time-option" data-value="last_week">Tuần trước</a>
                                            <a href="#" class="time-option" data-value="last_7_days">7 ngày qua</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6>Theo tháng</h6>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="#" class="time-option active" data-value="this_month">Tháng này</a>
                                            <a href="#" class="time-option" data-value="last_month">Tháng trước</a>
                                            <a href="#" class="time-option" data-value="last_30_days">30 ngày qua</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6>Theo quý</h6>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="#" class="time-option" data-value="this_quarter">Quý này</a>
                                            <a href="#" class="time-option" data-value="last_quarter">Quý trước</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6>Theo năm</h6>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="#" class="time-option" data-value="this_year">Năm này</a>
                                            <a href="#" class="time-option" data-value="last_year">Năm trước</a>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6>Tùy chọn</h6>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="#" class="time-option" data-value="custom" id="custom_range_trigger">
                                                <i class="fas fa-calendar-alt me-2"></i>Lựa chọn khác
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-light mt-3" id="time_panel_close">Đóng</button>
                            </div>
                        </div>

                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="custom" id="time_custom" name="time_filter_display"/>
                            <label class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100" for="time_custom" style="cursor: pointer;">
                                <span>Tùy chỉnh</span>
                                <i class="fas fa-chevron-down fs-3 text-muted"></i>
                            </label>
                        </div>

                        <!-- Hidden inputs for time filter -->
                        <input type="hidden" name="time_filter" id="time_filter" value="this_month">
                        <input type="hidden" name="date_from" id="date_from" value="">
                        <input type="hidden" name="date_to" id="date_to" value="">
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Time Filter Block-->

        <!--begin::Status Filter Block-->
        <div class="card card-flush mb-5 filter-block">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center">
                    <h3 class="fw-bold text-dark me-2">Trạng thái</h3>
                    <i class="fas fa-check-circle fs-2 text-primary"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="paid" id="status_paid" checked/>
                        <label class="form-check-label fw-semibold" for="status_paid">
                            Đã thanh toán
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="pending" id="status_pending" checked/>
                        <label class="form-check-label fw-semibold" for="status_pending">
                            Chờ xử lý
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="cancelled" id="status_cancelled"/>
                        <label class="form-check-label fw-semibold" for="status_cancelled">
                            Đã hủy
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status Filter Block-->

        <!--begin::Payment Method Filter Block-->
        <div class="card card-flush mb-5 filter-block">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center">
                    <h3 class="fw-bold text-dark me-2">Phương thức thanh toán</h3>
                    <i class="fas fa-credit-card fs-2 text-primary"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <h6 class="fw-bold text-dark mb-3">Lọc theo phương thức</h6>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="" id="payment_method_all" name="payment_method" checked/>
                        <label class="form-check-label fw-semibold" for="payment_method_all">
                            <i class="fas fa-list me-2"></i>Tất cả
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="cash" id="payment_method_cash" name="payment_method"/>
                        <label class="form-check-label fw-semibold" for="payment_method_cash">
                            <i class="fas fa-money-bill me-2"></i>Tiền mặt
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="card" id="payment_method_card" name="payment_method"/>
                        <label class="form-check-label fw-semibold" for="payment_method_card">
                            <i class="fas fa-credit-card me-2"></i>Thẻ
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="transfer" id="payment_method_transfer" name="payment_method"/>
                        <label class="form-check-label fw-semibold" for="payment_method_transfer">
                            <i class="fas fa-university me-2"></i>Chuyển khoản
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="check" id="payment_method_check" name="payment_method"/>
                        <label class="form-check-label fw-semibold" for="payment_method_check">
                            <i class="fas fa-file-invoice me-2"></i>Séc
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="points" id="payment_method_points" name="payment_method"/>
                        <label class="form-check-label fw-semibold" for="payment_method_points">
                            <i class="fas fa-star me-2"></i>Điểm thưởng
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="other" id="payment_method_other" name="payment_method"/>
                        <label class="form-check-label fw-semibold" for="payment_method_other">
                            <i class="fas fa-ellipsis-h me-2"></i>Khác
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Payment Method Filter Block-->

        <!--begin::Document Type Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Loại chứng từ</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="receipt" id="doc_receipt" checked/>
                        <label class="form-check-label fw-semibold" for="doc_receipt">
                            Phiếu thu
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="disbursement" id="doc_disbursement" checked/>
                        <label class="form-check-label fw-semibold" for="doc_disbursement">
                            Phiếu chi
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Document Type Filter Block-->

        <!--begin::Income Type Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Loại thu chi</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn loại thu chi" data-allow-clear="true" name="income_type" id="income_type_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Income Type Filter Block-->

        <!--begin::Status Filter Block-->
        <div class="card card-flush mb-5">
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
                        <input class="form-check-input" type="checkbox" value="completed" id="status_completed" checked/>
                        <label class="form-check-label fw-semibold" for="status_completed">
                            Đã thanh toán
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="cancelled" id="status_cancelled"/>
                        <label class="form-check-label fw-semibold" for="status_cancelled">
                            Đã hủy
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status Filter Block-->

        <!--begin::Business Result Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Hạch toán kết quả kinh doanh</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" id="business_result_all">Tất cả</button>
                    <button type="button" class="btn btn-light btn-sm" id="business_result_yes">Có</button>
                    <button type="button" class="btn btn-light btn-sm" id="business_result_no">Không</button>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Business Result Filter Block-->

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
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn người tạo" data-allow-clear="true" name="creator_id" id="creator_filter">
                    <option></option>
                    @foreach($creators as $creator)
                        <option value="{{ $creator->id }}">{{ $creator->name }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Creator Filter Block-->

        <!--begin::Staff Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Nhân viên</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn nhân viên" data-allow-clear="true" name="staff_id" id="staff_filter">
                    <option></option>
                    @foreach($staff as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Staff Filter Block-->

        <!--begin::Recipient Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Người nộp/nhận</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Tất cả" data-allow-clear="true" name="recipient_id" id="recipient_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Recipient Filter Block-->
    </form>
    <!--end::Form-->
</div>
<!--end::Sidebar-->
