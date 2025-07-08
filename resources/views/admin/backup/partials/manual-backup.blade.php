<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-download text-primary me-2"></i>Tạo sao lưu thủ công
                </h3>
            </div>
            <div class="card-body">
                <form id="manual-backup-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label required">Tên backup</label>
                                <input type="text" class="form-control" name="name" placeholder="Nhập tên backup" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label">Loại backup</label>
                                <select class="form-select" name="backup_type" id="backup-type-select">
                                    <option value="full">Toàn bộ database</option>
                                    <option value="selective">Chọn bảng cụ thể</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Mô tả backup (tùy chọn)"></textarea>
                    </div>

                    <!-- Table Selection (Hidden by default) -->
                    <div class="mb-5" id="table-selection" style="display: none;">
                        <label class="form-label">Chọn bảng cần sao lưu</label>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="select-all-tables">
                                            <label class="form-check-label fw-bold" for="select-all-tables">
                                                Chọn tất cả
                                            </label>
                                        </div>
                                        <hr>
                                    </div>
                                    @foreach($tables as $table)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input table-checkbox" type="checkbox" 
                                                   name="tables[]" value="{{ $table }}" id="table-{{ $table }}">
                                            <label class="form-check-label" for="table-{{ $table }}">
                                                {{ $table }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Tạo backup ngay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle text-info me-2"></i>Hướng dẫn
                </h3>
            </div>
            <div class="card-body">
                <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Sao lưu thủ công</h4>
                            <div class="fs-6 text-gray-700">
                                <p class="mb-2">• <strong>Toàn bộ database:</strong> Sao lưu tất cả bảng và dữ liệu</p>
                                <p class="mb-2">• <strong>Chọn bảng:</strong> Chỉ sao lưu các bảng được chọn</p>
                                <p class="mb-2">• File backup sẽ được lưu dưới định dạng SQL</p>
                                <p class="mb-0">• Có thể tải về hoặc khôi phục sau này</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="separator my-6"></div>

                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Lưu ý quan trọng</h4>
                            <div class="fs-6 text-gray-700">
                                <p class="mb-2">• Backup lớn có thể mất nhiều thời gian</p>
                                <p class="mb-2">• Không đóng trình duyệt khi đang backup</p>
                                <p class="mb-0">• Kiểm tra dung lượng ổ cứng trước khi backup</p>
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
    // Toggle table selection based on backup type
    const backupTypeSelect = document.getElementById('backup-type-select');
    const tableSelection = document.getElementById('table-selection');
    
    backupTypeSelect.addEventListener('change', function() {
        if (this.value === 'selective') {
            tableSelection.style.display = 'block';
        } else {
            tableSelection.style.display = 'none';
        }
    });

    // Select all tables functionality
    const selectAllCheckbox = document.getElementById('select-all-tables');
    const tableCheckboxes = document.querySelectorAll('.table-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        tableCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update select all checkbox when individual checkboxes change
    tableCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.table-checkbox:checked').length;
            const totalCount = tableCheckboxes.length;
            
            selectAllCheckbox.checked = checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        });
    });
});
</script>
