@extends('admin.main-content')

@section('title', 'Quản lý Thông báo')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Quản lý Thông báo
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Thông báo</li>
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
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="total-notifications">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Tổng thông báo</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #7239EA;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="unread-notifications">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Chưa đọc</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #17C653;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="today-notifications">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Hôm nay</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end  mb-5 mb-xl-10" style="background-color: #FFC700;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="urgent-notifications">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Khẩn cấp</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="fas fa-search fs-3 position-absolute ms-5"></i>
                            <input type="text" data-kt-notification-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Tìm kiếm thông báo..." />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-notification-table-toolbar="base">
                            <div class="me-3">
                                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Lọc theo loại" data-allow-clear="true" id="type-filter">
                                    <option></option>
                                    <option value="order">Đơn hàng</option>
                                    <option value="invoice">Hóa đơn</option>
                                    <option value="inventory">Tồn kho</option>
                                    <option value="system">Hệ thống</option>
                                    <option value="user">Người dùng</option>
                                </select>
                            </div>
                            <div class="me-3">
                                <select class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Lọc theo trạng thái" data-allow-clear="true" id="status-filter">
                                    <option></option>
                                    <option value="unread">Chưa đọc</option>
                                    <option value="read">Đã đọc</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-light-primary me-3" id="mark-all-read-btn">
                                <i class="fas fa-check-double"></i>
                                Đánh dấu tất cả đã đọc
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_create_notification">
                                <i class="fas fa-plus"></i>
                                Tạo thông báo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body py-4">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_notifications_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_notifications_table .form-check-input" value="1" />
                                    </div>
                                </th>
                                <th class="min-w-125px">Loại</th>
                                <th class="min-w-200px">Tiêu đề</th>
                                <th class="min-w-150px">Nội dung</th>
                                <th class="min-w-100px">Mức độ</th>
                                <th class="min-w-100px">Trạng thái</th>
                                <th class="min-w-125px">Thời gian</th>
                                <th class="text-end min-w-100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="kt_modal_create_notification" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_create_notification_header">
                <h2 class="fw-bold">Tạo thông báo mới</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form id="kt_modal_create_notification_form" class="form" action="#">
                    <div class="fv-row mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Loại thông báo</label>
                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Chọn loại thông báo" name="type" id="notification_type">
                            <option></option>
                            <option value="order">Đơn hàng</option>
                            <option value="invoice">Hóa đơn</option>
                            <option value="inventory">Tồn kho</option>
                            <option value="system">Hệ thống</option>
                            <option value="user">Người dùng</option>
                            <option value="general">Thông báo chung</option>
                        </select>
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Tiêu đề</label>
                        <input type="text" class="form-control form-control-solid" placeholder="Nhập tiêu đề thông báo" name="title" />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Nội dung</label>
                        <textarea class="form-control form-control-solid" rows="4" placeholder="Nhập nội dung thông báo" name="message"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 mb-2">Mức độ ưu tiên</label>
                                <select class="form-select form-select-solid fw-bold" data-kt-select2="true" name="priority">
                                    <option value="low">Thấp</option>
                                    <option value="normal" selected>Bình thường</option>
                                    <option value="high">Cao</option>
                                    <option value="urgent">Khẩn cấp</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">Hết hạn</label>
                                <input class="form-control form-control-solid" placeholder="Chọn thời gian hết hạn" name="expires_at" id="expires_at" />
                            </div>
                        </div>
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Người nhận</label>
                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Chọn người nhận" data-allow-clear="true" multiple="multiple" name="recipients[]" id="recipients">
                            <option></option>
                        </select>
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" data-kt-notifications-modal-action="submit">
                            <span class="indicator-label">Gửi thông báo</span>
                            <span class="indicator-progress">Đang gửi...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.notification-item {
    transition: all 0.3s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f1f8ff;
    border-left: 3px solid #009ef7;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/apps/notifications/list.js') }}"></script>
@endpush
