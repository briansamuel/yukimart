<div class="row">
    <div class="col-lg-8">
        <!-- Create Schedule Form -->
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus text-success me-2"></i>Tạo lịch sao lưu tự động
                </h3>
            </div>
            <div class="card-body">
                <form id="schedule-backup-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label required">Tên lịch</label>
                                <input type="text" class="form-control" name="name" placeholder="Nhập tên lịch backup" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label required">Tần suất</label>
                                <select class="form-select" name="frequency" id="frequency-select" required>
                                    <option value="">Chọn tần suất</option>
                                    <option value="hourly">Hàng giờ</option>
                                    <option value="daily">Hàng ngày</option>
                                    <option value="weekly">Hàng tuần</option>
                                    <option value="monthly">Hàng tháng</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Hourly Options -->
                    <div class="row frequency-options" id="hourly-options" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Mỗi bao nhiêu giờ</label>
                                <select class="form-select" name="hour_interval">
                                    <option value="1">1 giờ</option>
                                    <option value="2">2 giờ</option>
                                    <option value="3">3 giờ</option>
                                    <option value="4">4 giờ</option>
                                    <option value="6">6 giờ</option>
                                    <option value="8">8 giờ</option>
                                    <option value="12">12 giờ</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Options -->
                    <div class="row frequency-options" id="daily-options" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Thời gian</label>
                                <input type="time" class="form-control" name="time" value="02:00">
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Options -->
                    <div class="row frequency-options" id="weekly-options" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Thứ trong tuần</label>
                                <select class="form-select" name="day_of_week">
                                    <option value="0">Chủ nhật</option>
                                    <option value="1" selected>Thứ hai</option>
                                    <option value="2">Thứ ba</option>
                                    <option value="3">Thứ tư</option>
                                    <option value="4">Thứ năm</option>
                                    <option value="5">Thứ sáu</option>
                                    <option value="6">Thứ bảy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Thời gian</label>
                                <input type="time" class="form-control" name="time" value="02:00">
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Options -->
                    <div class="row frequency-options" id="monthly-options" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Ngày trong tháng</label>
                                <select class="form-select" name="day_of_month">
                                    @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>Ngày {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Thời gian</label>
                                <input type="time" class="form-control" name="time" value="02:00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Loại backup</label>
                                <select class="form-select" name="backup_type" id="schedule-backup-type">
                                    <option value="full">Toàn bộ database</option>
                                    <option value="selective">Chọn bảng cụ thể</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label required">Lưu trữ (ngày)</label>
                                <input type="number" class="form-control" name="retention_days" value="30" min="1" max="365" required>
                                <div class="form-text">Tự động xóa backup sau số ngày này</div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Selection for Schedule -->
                    <div class="mb-5" id="schedule-table-selection" style="display: none;">
                        <label class="form-label">Chọn bảng cần sao lưu</label>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="schedule-select-all-tables">
                                            <label class="form-check-label fw-bold" for="schedule-select-all-tables">
                                                Chọn tất cả
                                            </label>
                                        </div>
                                        <hr>
                                    </div>
                                    @foreach($tables as $table)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input schedule-table-checkbox" type="checkbox" 
                                                   name="tables[]" value="{{ $table }}" id="schedule-table-{{ $table }}">
                                            <label class="form-check-label" for="schedule-table-{{ $table }}">
                                                {{ $table }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Mô tả lịch backup (tùy chọn)"></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Tạo lịch backup
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Schedules -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list text-primary me-2"></i>Danh sách lịch backup
                </h3>
            </div>
            <div class="card-body">
                @if($schedules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Tên lịch</th>
                                <th class="min-w-120px">Tần suất</th>
                                <th class="min-w-120px">Lần chạy cuối</th>
                                <th class="min-w-120px">Lần chạy tiếp</th>
                                <th class="min-w-100px">Trạng thái</th>
                                <th class="min-w-100px text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex justify-content-start flex-column">
                                            <span class="text-dark fw-bold text-hover-primary fs-6">{{ $schedule->name }}</span>
                                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $schedule->description }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-info">{{ $schedule->frequency_label }}</span>
                                </td>
                                <td>
                                    <span class="text-muted fw-semibold">
                                        {{ $schedule->last_run_at ? $schedule->last_run_at->format('d/m/Y H:i') : 'Chưa chạy' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted fw-semibold">
                                        {{ $schedule->next_run_at ? $schedule->next_run_at->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $schedule->status_badge_class }}">{{ $schedule->status_label }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-icon btn-light btn-active-light-primary toggle-schedule" 
                                            data-id="{{ $schedule->id }}" data-active="{{ $schedule->is_active ? 1 : 0 }}">
                                        <i class="fas {{ $schedule->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-light btn-active-light-danger delete-schedule" 
                                            data-id="{{ $schedule->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-10">
                    <i class="fas fa-clock text-muted fs-3x mb-3"></i>
                    <p class="text-muted fs-5">Chưa có lịch backup nào được tạo</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle text-info me-2"></i>Hướng dẫn lịch tự động
                </h3>
            </div>
            <div class="card-body">
                <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-6 mb-6">
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Tần suất backup</h4>
                            <div class="fs-6 text-gray-700">
                                <p class="mb-2">• <strong>Hàng giờ:</strong> Chạy theo khoảng cách giờ</p>
                                <p class="mb-2">• <strong>Hàng ngày:</strong> Chạy vào giờ cố định mỗi ngày</p>
                                <p class="mb-2">• <strong>Hàng tuần:</strong> Chạy vào thứ và giờ cố định</p>
                                <p class="mb-0">• <strong>Hàng tháng:</strong> Chạy vào ngày và giờ cố định</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Tự động dọn dẹp</h4>
                            <div class="fs-6 text-gray-700">
                                <p class="mb-2">• Backup cũ sẽ tự động bị xóa</p>
                                <p class="mb-2">• Thời gian lưu trữ có thể tùy chỉnh</p>
                                <p class="mb-0">• Giúp tiết kiệm dung lượng ổ cứng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle table selection based on backup type for schedule
    const scheduleBackupTypeSelect = document.getElementById('schedule-backup-type');
    const scheduleTableSelection = document.getElementById('schedule-table-selection');

    if (scheduleBackupTypeSelect && scheduleTableSelection) {
        scheduleBackupTypeSelect.addEventListener('change', function() {
            if (this.value === 'selective') {
                scheduleTableSelection.style.display = 'block';
            } else {
                scheduleTableSelection.style.display = 'none';
            }
        });
    }

    // Select all tables functionality for schedule
    const scheduleSelectAllCheckbox = document.getElementById('schedule-select-all-tables');
    const scheduleTableCheckboxes = document.querySelectorAll('.schedule-table-checkbox');

    if (scheduleSelectAllCheckbox && scheduleTableCheckboxes.length > 0) {
        scheduleSelectAllCheckbox.addEventListener('change', function() {
            scheduleTableCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all checkbox when individual checkboxes change
        scheduleTableCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.schedule-table-checkbox:checked').length;
                const totalCount = scheduleTableCheckboxes.length;

                scheduleSelectAllCheckbox.checked = checkedCount === totalCount;
                scheduleSelectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            });
        });
    }
});
</script>
