@extends('admin.main-content')

@section('title', 'Quản lý Sao lưu')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Quản lý Sao lưu
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Sao lưu</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!-- Statistics Cards -->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="total-backups">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Tổng số backup</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #7239EA;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="completed-backups">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Hoàn thành</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #17C653;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="active-schedules">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Lịch hoạt động</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #FFC700;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="total-size">0 MB</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Tổng dung lượng</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Tabs -->
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#manual-backup-tab">
                                    <i class="fas fa-download me-2"></i>Sao lưu thủ công
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#schedule-backup-tab">
                                    <i class="fas fa-clock me-2"></i>Lịch tự động
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#backup-history-tab">
                                    <i class="fas fa-history me-2"></i>Lịch sử
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body py-4">
                    <div class="tab-content" id="backup-tabs">
                        <!-- Manual Backup Tab -->
                        <div class="tab-pane fade show active" id="manual-backup-tab" role="tabpanel">
                            @include('admin.backup.partials.manual-backup')
                        </div>

                        <!-- Schedule Backup Tab -->
                        <div class="tab-pane fade" id="schedule-backup-tab" role="tabpanel">
                            @include('admin.backup.partials.schedule-backup')
                        </div>

                        <!-- Backup History Tab -->
                        <div class="tab-pane fade" id="backup-history-tab" role="tabpanel">
                            @include('admin.backup.partials.backup-history')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>

<!-- Progress Modal -->
<div class="modal fade" id="backup-progress-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Đang thực hiện sao lưu</h3>
            </div>
            <div class="modal-body text-center">
                <div class="mb-5">
                    <div class="progress h-15px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="backup-progress-bar"></div>
                    </div>
                </div>
                <p class="text-muted mb-0" id="backup-progress-message">Đang chuẩn bị...</p>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restore-confirmation-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-danger">Xác nhận khôi phục</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Việc khôi phục sẽ ghi đè toàn bộ dữ liệu hiện tại. 
                    Bạn có chắc chắn muốn tiếp tục?
                </div>
                <p class="mb-0">Backup: <strong id="restore-backup-name"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirm-restore-btn">Khôi phục</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.table-responsive {
    border-radius: 0.475rem;
}

.backup-item {
    transition: all 0.3s ease;
}

.backup-item:hover {
    background-color: #f8f9fa;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% {
        background-position: 1rem 0;
    }
    100% {
        background-position: 0 0;
    }
}
</style>
@endsection

@section('scripts')
<script src="{{ asset('admin-assets/js/backup.js') }}"></script>
@endsection
