<!-- Time Options Panel (Popup) -->
<div id="time_options_panel" class="time-options-panel" style="display: none;">
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
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="today">Hôm
                        nay</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="yesterday">Hôm
                        qua</button>
                </div>
            </div>

            <!-- Cột 2: Theo tuần -->
            <div class="col">
                <h6 class="fw-bold text-dark mb-2 fs-7">Theo tuần</h6>
                <div class="d-flex flex-column gap-1">
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="this_week">Tuần
                        này</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="last_week">Tuần
                        trước</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="7_days">7 ngày
                        qua</button>
                </div>
            </div>

            <!-- Cột 3: Theo tháng -->
            <div class="col">
                <h6 class="fw-bold text-dark mb-2 fs-7">Theo tháng</h6>
                <div class="d-flex flex-column gap-1">
                    <button type="button" class="btn btn-sm btn-primary time-option active"
                        data-value="this_month">Tháng này</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option"
                        data-value="last_month">Tháng trước</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="30_days">30 ngày
                        qua</button>
                </div>
            </div>

            <!-- Cột 4: Theo quý -->
            <div class="col">
                <h6 class="fw-bold text-dark mb-2 fs-7">Theo quý</h6>
                <div class="d-flex flex-column gap-1">
                    <button type="button" class="btn btn-sm btn-light-primary time-option"
                        data-value="this_quarter">Quý này</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option"
                        data-value="last_quarter">Quý trước</button>
                </div>
            </div>

            <!-- Cột 5: Theo năm -->
            <div class="col">
                <h6 class="fw-bold text-dark mb-2 fs-7">Theo năm</h6>
                <div class="d-flex flex-column gap-1">
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="this_year">Năm
                        này</button>
                    <button type="button" class="btn btn-sm btn-light-primary time-option" data-value="last_year">Năm
                        trước</button>
                </div>
            </div>


        </div>
    </div>
</div>
