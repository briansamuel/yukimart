<!--begin::Sidebar-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-300px order-1 order-lg-1 mb-10 mb-lg-0">
    <!--begin::Form-->
    <form id="kt_invoice_filter_form">
        <!--begin::Time Filter Block-->
        <div class="card card-flush mb-5">
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
                            <input class="form-check-input" type="radio" value="this_month" id="time_this_month" name="time_filter" checked/>
                            <label class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100" for="time_this_month" id="time_filter_trigger" style="cursor: pointer;">
                                <span>Tháng này</span>
                                <i class="fas fa-chevron-down fs-3 text-muted" id="time_dropdown_icon"></i>
                            </label>
                        </div>

                        <!-- Time Options Panel (Popup) -->
                        <div id="time_options_panel">
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
                        <input class="form-check-input" type="radio" value="custom" id="time_custom" name="time_filter"/>
                        <label class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100" for="time_custom">
                            <span>Lựa chọn khác</span>
                            <i class="fas fa-calendar-alt fs-3 text-muted"></i>
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Time Filter Block-->

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
                <div class="text-muted mb-3">Chọn phương thức bán hàng...</div>
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="processing" id="status_processing" checked/>
                        <label class="form-check-label fw-semibold" for="status_processing">
                            Đang xử lý
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="completed" id="status_completed" checked/>
                        <label class="form-check-label fw-semibold" for="status_completed">
                            Hoàn thành
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="failed" id="status_failed"/>
                        <label class="form-check-label fw-semibold" for="status_failed">
                            Không giao được
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
                    <h3 class="fw-bold text-dark">Người bán</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn người bán" data-allow-clear="true" name="seller_id" id="seller_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Seller Filter Block-->

        <!--begin::Delivery Status Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Trạng thái giao hàng</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="pending" id="delivery_pending"/>
                        <label class="form-check-label fw-semibold" for="delivery_pending">
                            Chờ xử lý
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="pickup" id="delivery_pickup"/>
                        <label class="form-check-label fw-semibold" for="delivery_pickup">
                            Lấy hàng
                        </label>
                        <span class="badge badge-light-primary ms-auto">+</span>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="shipping" id="delivery_shipping"/>
                        <label class="form-check-label fw-semibold" for="delivery_shipping">
                            Giao hàng
                        </label>
                        <span class="badge badge-light-primary ms-auto">+</span>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="delivered" id="delivery_delivered"/>
                        <label class="form-check-label fw-semibold" for="delivery_delivered">
                            Giao thành công
                        </label>
                        <span class="badge badge-light-primary ms-auto">+</span>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="returned" id="delivery_returned"/>
                        <label class="form-check-label fw-semibold" for="delivery_returned">
                            Chuyển hoàn
                        </label>
                        <span class="badge badge-light-primary ms-auto">+</span>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="return_completed" id="delivery_return_completed"/>
                        <label class="form-check-label fw-semibold" for="delivery_return_completed">
                            Đã chuyển hoàn
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" value="cancelled" id="delivery_cancelled"/>
                        <label class="form-check-label fw-semibold" for="delivery_cancelled">
                            Đã hủy
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Delivery Status Filter Block-->

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
                <input class="form-control form-control-solid" name="sales_channel_tags" id="sales_channel_tags" placeholder="Chọn kênh bán..." />
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Sales Channel Filter Block-->

        <!--begin::Delivery Partner Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Đối tác giao hàng</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn người giao..." data-allow-clear="true" name="delivery_partner">
                    <option></option>
                    @foreach($deliveryPartners as $partner)
                        <option value="{{ $partner['value'] }}">{{ $partner['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Delivery Partner Filter Block-->

        <!--begin::Delivery Time Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center">
                    <h3 class="fw-bold text-dark me-2">Thời gian giao hàng</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="all_time" id="delivery_time_all" name="delivery_time_filter" checked/>
                        <label class="form-check-label fw-semibold" for="delivery_time_all">
                            Toàn thời gian
                        </label>
                        <i class="fas fa-chevron-down fs-3 text-muted ms-auto"></i>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="radio" value="custom_time" id="delivery_time_custom" name="delivery_time_filter"/>
                        <label class="form-check-label fw-semibold" for="delivery_time_custom">
                            Lựa chọn khác
                        </label>
                        <i class="fas fa-calendar-alt fs-3 text-muted ms-auto"></i>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Delivery Time Filter Block-->

        <!--begin::Additional Filter Blocks-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Khu vực giao hàng</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn Tỉnh/TP - Quận/Huyện" data-allow-clear="true" name="delivery_area">
                    <option></option>
                    @foreach($deliveryAreas as $area)
                        <option value="{{ $area['value'] }}">{{ $area['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>

        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Phương thức</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn phương thức thanh toán..." data-allow-clear="true" name="payment_method">
                    <option></option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method['value'] }}">{{ $method['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>



        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Bảng giá</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn bảng giá..." data-allow-clear="true" name="price_list">
                    <option></option>
                    @foreach($priceLists as $priceList)
                        <option value="{{ $priceList['value'] }}">{{ $priceList['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>

        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Loại thu khác</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn loại thu khác..." data-allow-clear="true" name="other_income_type">
                    <option></option>
                    @foreach($otherIncomeTypes as $type)
                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Additional Filter Blocks-->
    </form>
    <!--end::Form-->
</div>
<!--end::Sidebar-->
