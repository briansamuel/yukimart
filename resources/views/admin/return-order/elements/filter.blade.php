<!--begin::Sidebar-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-300px order-1 order-lg-1 mb-10 mb-lg-0">
    <!--begin::Form-->
    <form id="kt_return_order_filter_form">
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
                    <div class="time-filter-container" style="position: relative;">
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="radio" value="this_month" id="time_shortcut" name="time_filter" checked/>
                            <label class="form-check-label fw-semibold d-flex align-items-center justify-content-between w-100" for="time_shortcut" style="cursor: pointer;">
                                <span>Tháng này</span>
                                <i class="fas fa-chevron-down fs-3 text-muted" id="time_dropdown_icon" onclick="toggleTimePanel()"></i>
                            </label>
                        </div>

                        <!-- Time Options Panel (Popup) -->
                        <div id="time_options_panel" style="display: none; position: absolute; top: 0; left: 100%; margin-left: 15px; width: 600px; z-index: 1000; background: white; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);">
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
                                        <button type="button" class="btn btn-sm btn-light-primary time-option active" data-value="this_month">Tháng này</button>
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
                                        <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="all">Tất cả</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <!-- End Time Filter Container -->

                    <!-- Hidden radio for time filter value -->
                    <input type="radio" name="time_filter" value="all" id="time_this_month" checked style="display: none;">

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
                <div class="text-muted mb-3">Chọn trạng thái đơn trả hàng...</div>
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="pending" id="status_pending" checked/>
                        <label class="form-check-label fw-semibold" for="status_pending">
                            Chờ duyệt
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="approved" id="status_approved" checked/>
                        <label class="form-check-label fw-semibold" for="status_approved">
                            Đã duyệt
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="rejected" id="status_rejected"/>
                        <label class="form-check-label fw-semibold" for="status_rejected">
                            Từ chối
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="status[]" value="completed" id="status_completed"/>
                        <label class="form-check-label fw-semibold" for="status_completed">
                            Hoàn thành
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status Filter Block-->

        <!--begin::Return Reason Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Lý do trả hàng</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="reason[]" value="defective" id="reason_defective"/>
                        <label class="form-check-label fw-semibold" for="reason_defective">
                            Hàng lỗi
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="reason[]" value="wrong_item" id="reason_wrong_item"/>
                        <label class="form-check-label fw-semibold" for="reason_wrong_item">
                            Giao sai hàng
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="reason[]" value="customer_request" id="reason_customer_request"/>
                        <label class="form-check-label fw-semibold" for="reason_customer_request">
                            Khách hàng yêu cầu
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="reason[]" value="damaged" id="reason_damaged"/>
                        <label class="form-check-label fw-semibold" for="reason_damaged">
                            Hàng bị hỏng
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="reason[]" value="expired" id="reason_expired"/>
                        <label class="form-check-label fw-semibold" for="reason_expired">
                            Hết hạn
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="reason[]" value="other" id="reason_other"/>
                        <label class="form-check-label fw-semibold" for="reason_other">
                            Khác
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Return Reason Filter Block-->

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

        <!--begin::Customer Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Khách hàng</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Chọn khách hàng" data-allow-clear="true" name="customer_id" id="customer_filter">
                    <option></option>
                </select>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Customer Filter Block-->

        <!--begin::Refund Method Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Phương thức hoàn tiền</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="d-flex flex-column">
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="refund_method[]" value="cash" id="refund_cash"/>
                        <label class="form-check-label fw-semibold" for="refund_cash">
                            Tiền mặt
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="refund_method[]" value="transfer" id="refund_transfer"/>
                        <label class="form-check-label fw-semibold" for="refund_transfer">
                            Chuyển khoản
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="refund_method[]" value="card" id="refund_card"/>
                        <label class="form-check-label fw-semibold" for="refund_card">
                            Thẻ
                        </label>
                    </div>
                    <div class="form-check form-check-custom form-check-solid mb-3">
                        <input class="form-check-input" type="checkbox" name="refund_method[]" value="store_credit" id="refund_store_credit"/>
                        <label class="form-check-label fw-semibold" for="refund_store_credit">
                            Tín dụng cửa hàng
                        </label>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Refund Method Filter Block-->

        <!--begin::Amount Range Filter Block-->
        <div class="card card-flush mb-5">
            <!--begin::Card header-->
            <div class="card-header pt-5">
                <!--begin::Card title-->
                <div class="card-title d-flex align-items-center justify-content-between w-100">
                    <h3 class="fw-bold text-dark">Khoảng tiền</h3>
                    <i class="fas fa-chevron-up fs-3 text-muted"></i>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-6">
                        <label class="form-label fs-6 fw-semibold">Từ:</label>
                        <input type="number" class="form-control form-control-solid" name="amount_from" placeholder="0" />
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-6 fw-semibold">Đến:</label>
                        <input type="number" class="form-control form-control-solid" name="amount_to" placeholder="0" />
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Amount Range Filter Block-->
    </form>
    <!--end::Form-->
</div>
<!--end::Sidebar-->

<style>
.time-option {
    transition: all 0.2s ease;
}

.time-option:hover {
    background-color: #009ef7 !important;
    color: white !important;
    border-color: #009ef7 !important;
}

.time-option.active {
    background-color: #009ef7 !important;
    color: white !important;
    border-color: #009ef7 !important;
}

#time_options_panel {
    max-height: 400px;
    overflow-y: auto;
}

/* Responsive positioning */
@media (max-width: 1200px) {
    #time_options_panel {
        left: 0 !important;
        top: 100% !important;
        margin-left: 0 !important;
        width: 100% !important;
    }
}
</style>

<script>
function toggleTimePanel() {
    const panel = document.getElementById('time_options_panel');
    const icon = document.getElementById('time_dropdown_icon');

    if (panel) {
        if (panel.style.display === 'block') {
            panel.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        } else {
            panel.style.display = 'block';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    }
}

// Load saved time filter state
function loadTimeFilterState() {
    try {
        const savedState = localStorage.getItem('return_order_time_filter');
        if (savedState) {
            const state = JSON.parse(savedState);

            // Update the main label
            const mainLabel = document.querySelector('#time_shortcut + label span');
            if (mainLabel) {
                mainLabel.textContent = state.text;
            }

            // Update hidden radio value and check it
            const hiddenRadio = document.getElementById('time_shortcut');
            if (hiddenRadio) {
                hiddenRadio.value = state.value;
                hiddenRadio.checked = true; // Ensure radio is checked
            }

            // Update active state for time options
            document.querySelectorAll('.time-option').forEach(option => {
                option.classList.remove('active', 'btn-primary');
                option.classList.add('btn-light-primary');

                if (option.getAttribute('data-value') === state.value) {
                    option.classList.add('active', 'btn-primary');
                    option.classList.remove('btn-light-primary');
                }
            });

            console.log('Time filter state loaded:', state);
        } else {
            // Default to "Tháng này" if no saved state
            setDefaultTimeFilter();
        }
    } catch (error) {
        console.log('Error loading time filter state:', error);
        // Default to "Tháng này" if error
        setDefaultTimeFilter();
    }
}

// Set default time filter to "Tháng này"
function setDefaultTimeFilter() {
    const mainLabel = document.querySelector('#time_shortcut + label span');
    if (mainLabel) {
        mainLabel.textContent = 'Tháng này';
    }

    const hiddenRadio = document.getElementById('time_shortcut');
    if (hiddenRadio) {
        hiddenRadio.value = 'this_month';
        hiddenRadio.checked = true; // Ensure radio is checked
    }

    // Set active state for "Tháng này" button
    document.querySelectorAll('.time-option').forEach(option => {
        option.classList.remove('active', 'btn-primary');
        option.classList.add('btn-light-primary');

        if (option.getAttribute('data-value') === 'this_month') {
            option.classList.add('active', 'btn-primary');
            option.classList.remove('btn-light-primary');
        }
    });

    console.log('Default time filter set to: this_month');
}

// Handle time option clicks
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('time-option')) {
        const value = event.target.getAttribute('data-value');
        const text = event.target.textContent;

        // Remove active class from all time options
        document.querySelectorAll('.time-option').forEach(option => {
            option.classList.remove('active');
            option.classList.remove('btn-primary');
            option.classList.add('btn-light-primary');
        });

        // Add active class to clicked option
        event.target.classList.add('active');
        event.target.classList.remove('btn-light-primary');
        event.target.classList.add('btn-primary');

        // Update the main label
        const mainLabel = document.querySelector('#time_shortcut + label span');
        if (mainLabel) {
            mainLabel.textContent = text;
        }

        // Update hidden radio value and ensure it's checked
        const hiddenRadio = document.getElementById('time_shortcut');
        if (hiddenRadio) {
            hiddenRadio.value = value;
            hiddenRadio.checked = true; // Ensure radio stays checked
        }

        // Save state to localStorage
        localStorage.setItem('return_order_time_filter', JSON.stringify({
            value: value,
            text: text
        }));

        console.log('Time filter updated:', { value, text });

        // Close panel
        const panel = document.getElementById('time_options_panel');
        if (panel) {
            panel.style.display = 'none';
        }

        // Update icon
        const icon = document.getElementById('time_dropdown_icon');
        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }

        // Trigger filter update (if DataTable exists)
        if (typeof window.KTReturnOrdersList !== 'undefined' && window.KTReturnOrdersList.reload) {
            window.KTReturnOrdersList.reload();
        }
    }
});

// Close panel when clicking outside
document.addEventListener('click', function(event) {
    const panel = document.getElementById('time_options_panel');
    const container = document.querySelector('.time-filter-container');

    if (panel && container && !container.contains(event.target)) {
        panel.style.display = 'none';
        const icon = document.getElementById('time_dropdown_icon');
        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
});

// Load time filter state when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    loadTimeFilterState();
});
</script>
