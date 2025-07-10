<!--begin::Sidebar-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-400px mb-10 order-1 order-lg-1">

    <!--begin::Card-->
    <div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title">
                <h2 class="mb-0">Bộ lọc</h2>
            </div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Form-->
            <form id="kt_payment_filter_form" class="form" action="#">
                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Thời gian</label>
                    <div class="d-flex flex-column flex-wrap">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="today" />
                            <span class="form-check-label text-gray-600 fw-semibold">Hôm nay</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="yesterday" />
                            <span class="form-check-label text-gray-600 fw-semibold">Hôm qua</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="this_week" />
                            <span class="form-check-label text-gray-600 fw-semibold">Tuần này</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="last_week" />
                            <span class="form-check-label text-gray-600 fw-semibold">Tuần trước</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="this_month" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Tháng này</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="last_month" />
                            <span class="form-check-label text-gray-600 fw-semibold">Tháng trước</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="this_year" />
                            <span class="form-check-label text-gray-600 fw-semibold">Năm này</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="all" />
                            <span class="form-check-label text-gray-600 fw-semibold">Tất cả</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input time-option" type="radio" name="time_filter" value="custom" />
                            <span class="form-check-label text-gray-600 fw-semibold">Lựa chọn khác</span>
                        </label>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Loại phiếu</label>
                    <div class="d-flex flex-column flex-wrap">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_type[]" value="receipt" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Phiếu thu</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_type[]" value="payment" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Phiếu chi</span>
                        </label>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Trạng thái</label>
                    <div class="d-flex flex-column flex-wrap">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="status[]" value="pending" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Chờ xử lý</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="status[]" value="completed" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Hoàn thành</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="status[]" value="cancelled" />
                            <span class="form-check-label text-gray-600 fw-semibold">Đã hủy</span>
                        </label>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Phương thức</label>
                    <div class="d-flex flex-column flex-wrap">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_method[]" value="cash" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Tiền mặt</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_method[]" value="card" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Thẻ</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_method[]" value="transfer" checked />
                            <span class="form-check-label text-gray-600 fw-semibold">Chuyển khoản</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_method[]" value="check" />
                            <span class="form-check-label text-gray-600 fw-semibold">Séc</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_method[]" value="points" />
                            <span class="form-check-label text-gray-600 fw-semibold">Điểm tích lũy</span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="payment_method[]" value="other" />
                            <span class="form-check-label text-gray-600 fw-semibold">Khác</span>
                        </label>
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Người tạo</label>
                    <select class="form-select form-select-solid" id="creator_filter" name="creator_id">
                        <option value="">Tất cả</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}">{{ $creator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Khách hàng</label>
                    <select class="form-select form-select-solid" id="customer_filter" name="customer_id">
                        <option value="">Tất cả</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="mb-5">
                    <label class="form-label fw-semibold text-dark">Khoảng số tiền</label>
                    <div class="d-flex">
                        <input type="number" class="form-control form-control-solid me-2" name="amount_from" placeholder="Từ" />
                        <input type="number" class="form-control form-control-solid" name="amount_to" placeholder="Đến" />
                    </div>
                </div>
                <!--end::Input group-->

                <!--begin::Actions-->
                <div class="d-flex justify-content-end">
                    <button type="reset" id="kt_payment_filter_reset" class="btn btn-light btn-active-light-primary me-2">Đặt lại bộ lọc</button>
                    <button type="submit" id="kt_payment_filter_search" class="btn btn-primary">Tìm kiếm</button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

</div>
<!--end::Sidebar-->
