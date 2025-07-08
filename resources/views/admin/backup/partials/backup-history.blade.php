<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history text-primary me-2"></i>Lịch sử sao lưu
        </h3>
        <div class="card-toolbar">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <select class="form-select form-select-sm" id="status-filter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="failed">Thất bại</option>
                        <option value="running">Đang chạy</option>
                    </select>
                </div>
                <div class="me-3">
                    <select class="form-select form-select-sm" id="type-filter">
                        <option value="">Tất cả loại</option>
                        <option value="manual">Thủ công</option>
                        <option value="auto">Tự động</option>
                    </select>
                </div>
                <button class="btn btn-sm btn-light-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($backups->count() > 0)
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3" id="backup-history-table">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-200px">Tên backup</th>
                        <th class="min-w-100px">Loại</th>
                        <th class="min-w-100px">Kích thước</th>
                        <th class="min-w-120px">Thời gian</th>
                        <th class="min-w-100px">Trạng thái</th>
                        <th class="min-w-120px">Người tạo</th>
                        <th class="min-w-150px text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr class="backup-item" data-status="{{ $backup->status }}" data-type="{{ $backup->type }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <div class="symbol-label bg-light-{{ $backup->status === 'completed' ? 'success' : ($backup->status === 'failed' ? 'danger' : 'warning') }}">
                                        <i class="fas fa-{{ $backup->status === 'completed' ? 'check' : ($backup->status === 'failed' ? 'times' : 'clock') }} text-{{ $backup->status === 'completed' ? 'success' : ($backup->status === 'failed' ? 'danger' : 'warning') }} fs-2"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <span class="text-dark fw-bold text-hover-primary fs-6">{{ $backup->name }}</span>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">
                                        {{ $backup->filename }}
                                    </span>
                                    @if($backup->description)
                                    <span class="text-muted fw-semibold d-block fs-8">
                                        {{ Str::limit($backup->description, 50) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light-{{ $backup->type === 'manual' ? 'primary' : 'info' }}">
                                {{ $backup->type_label }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted fw-semibold">{{ $backup->formatted_file_size }}</span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-dark fw-semibold">{{ $backup->created_at->format('d/m/Y') }}</span>
                                <span class="text-muted fw-semibold fs-7">{{ $backup->created_at->format('H:i:s') }}</span>
                                @if($backup->duration && $backup->status === 'completed')
                                <span class="text-muted fw-semibold fs-8">{{ $backup->duration }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $backup->status_badge_class }}">{{ $backup->status_label }}</span>
                            @if($backup->status === 'failed' && $backup->error_message)
                            <i class="fas fa-exclamation-triangle text-warning ms-2" 
                               data-bs-toggle="tooltip" 
                               title="{{ $backup->error_message }}"></i>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-30px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <span class="text-primary fw-bold fs-7">
                                            {{ strtoupper(substr($backup->creator->full_name ?? 'U', 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-dark fw-semibold">{{ $backup->creator->full_name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="text-end">
                            @if($backup->status === 'completed')
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-icon btn-light btn-active-light-primary" 
                                        onclick="window.open('{{ route('admin.backup.download', $backup->id) }}', '_blank')"
                                        data-bs-toggle="tooltip" title="Tải về">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-light btn-active-light-success restore-backup" 
                                        data-id="{{ $backup->id }}" 
                                        data-name="{{ $backup->name }}"
                                        data-bs-toggle="tooltip" title="Khôi phục">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-light btn-active-light-danger delete-backup" 
                                        data-id="{{ $backup->id }}"
                                        data-bs-toggle="tooltip" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            @elseif($backup->status === 'running')
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                <span class="text-muted fs-7">Đang chạy...</span>
                            </div>
                            @elseif($backup->status === 'failed')
                            <button class="btn btn-sm btn-icon btn-light btn-active-light-danger delete-backup" 
                                    data-id="{{ $backup->id }}"
                                    data-bs-toggle="tooltip" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-muted">
                Hiển thị {{ $backups->firstItem() ?? 0 }} - {{ $backups->lastItem() ?? 0 }} 
                trong tổng số {{ $backups->total() }} backup
            </div>
            <div>
                {{ $backups->links() }}
            </div>
        </div>
        @else
        <div class="text-center py-10">
            <i class="fas fa-history text-muted fs-3x mb-3"></i>
            <p class="text-muted fs-5">Chưa có backup nào được tạo</p>
            <a href="#manual-backup-tab" class="btn btn-primary" data-bs-toggle="tab">
                <i class="fas fa-plus me-2"></i>Tạo backup đầu tiên
            </a>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Filter functionality
    const statusFilter = document.getElementById('status-filter');
    const typeFilter = document.getElementById('type-filter');
    const backupItems = document.querySelectorAll('.backup-item');

    function filterBackups() {
        const statusValue = statusFilter.value;
        const typeValue = typeFilter.value;

        backupItems.forEach(item => {
            const itemStatus = item.dataset.status;
            const itemType = item.dataset.type;

            const statusMatch = !statusValue || itemStatus === statusValue;
            const typeMatch = !typeValue || itemType === typeValue;

            if (statusMatch && typeMatch) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });

        // Update visible count
        const visibleItems = Array.from(backupItems).filter(item => item.style.display !== 'none');
        updateVisibleCount(visibleItems.length);
    }

    function updateVisibleCount(count) {
        // You can add logic here to update a counter if needed
        console.log(`Showing ${count} backups`);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterBackups);
    }

    if (typeFilter) {
        typeFilter.addEventListener('change', filterBackups);
    }

    // Auto-refresh for running backups
    const runningBackups = document.querySelectorAll('.backup-item[data-status="running"]');
    if (runningBackups.length > 0) {
        // Refresh page every 30 seconds if there are running backups
        setTimeout(() => {
            location.reload();
        }, 30000);
    }
});
</script>
