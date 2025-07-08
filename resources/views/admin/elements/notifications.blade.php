<!--begin::Notifications-->
<div class="d-flex align-items-center ms-1 ms-lg-3">
    <!--begin::Menu wrapper-->
    <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px position-relative" 
         data-kt-menu-trigger="{default: 'click', lg: 'hover'}" 
         data-kt-menu-attach="parent" 
         data-kt-menu-placement="bottom-end">
        <!--begin::Icon-->
        <i class="fas fa-bell fs-2"></i>
        <!--end::Icon-->
        <!--begin::Pulse-->
        <span class="pulse-ring" id="notification_pulse" style="display: none;"></span>
        <!--end::Pulse-->
        <!--begin::Badge-->
        <span class="badge badge-light-danger badge-circle position-absolute"
              id="notification_count" style="display: none;">0</span>
        <!--end::Badge-->
    </div>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true" id="kt_menu_notifications">
        <!--begin::Heading-->
        <div class="d-flex flex-column bgi-no-repeat rounded-top" 
             style="background-image:url('{{ asset('admin-assets/assets/media/misc/menu-header-bg.jpg') }}')">
            <!--begin::Title-->
            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                Thông báo 
                <span class="fs-8 opacity-75 ps-3" id="notification_header_count">0 thông báo</span>
            </h3>
            <!--end::Title-->
            <!--begin::Tabs-->
            <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-semibold px-9">
                <li class="nav-item">
                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" 
                       data-bs-toggle="tab" href="#kt_topbar_notifications_1">Tất cả</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4" 
                       data-bs-toggle="tab" href="#kt_topbar_notifications_2">Đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4" 
                       data-bs-toggle="tab" href="#kt_topbar_notifications_3">Hệ thống</a>
                </li>
            </ul>
            <!--end::Tabs-->
        </div>
        <!--end::Heading-->
        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab panel-->
            <div class="tab-pane fade show active" id="kt_topbar_notifications_1" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8" id="all_notifications">
                    <!--begin::Loading-->
                    <div class="d-flex align-items-center justify-content-center py-10" id="notifications_loading">
                        <span class="spinner-border spinner-border-sm text-muted me-2"></span>
                        <span class="text-muted">Đang tải...</span>
                    </div>
                    <!--end::Loading-->
                    <!--begin::Empty state-->
                    <div class="d-none text-center py-10" id="notifications_empty">
                        <i class="fas fa-bell-slash fs-3x text-gray-400 mb-3"></i>
                        <div class="text-gray-600 fw-semibold fs-6">Không có thông báo nào</div>
                    </div>
                    <!--end::Empty state-->
                </div>
                <!--end::Items-->
                <!--begin::View more-->
                <div class="py-3 text-center border-top">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-color-gray-600 btn-active-color-primary">
                        Xem tất cả
                        <i class="fas fa-arrow-right fs-5 ms-1"></i>
                    </a>
                </div>
                <!--end::View more-->
            </div>
            <!--end::Tab panel-->
            <!--begin::Tab panel-->
            <div class="tab-pane fade" id="kt_topbar_notifications_2" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8" id="order_notifications">
                    <!--begin::Loading-->
                    <div class="d-flex align-items-center justify-content-center py-10">
                        <span class="spinner-border spinner-border-sm text-muted me-2"></span>
                        <span class="text-muted">Đang tải...</span>
                    </div>
                    <!--end::Loading-->
                </div>
                <!--end::Items-->
            </div>
            <!--end::Tab panel-->
            <!--begin::Tab panel-->
            <div class="tab-pane fade" id="kt_topbar_notifications_3" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8" id="system_notifications">
                    <!--begin::Loading-->
                    <div class="d-flex align-items-center justify-content-center py-10">
                        <span class="spinner-border spinner-border-sm text-muted me-2"></span>
                        <span class="text-muted">Đang tải...</span>
                    </div>
                    <!--end::Loading-->
                </div>
                <!--end::Items-->
            </div>
            <!--end::Tab panel-->
        </div>
        <!--end::Tab content-->
        <!--begin::Actions-->
        <div class="d-flex flex-center py-3 border-top">
            <button type="button" class="btn btn-sm btn-light-primary me-2" id="mark_all_read">
                <i class="fas fa-check-double me-1"></i>
                Đánh dấu đã đọc
            </button>
            <button type="button" class="btn btn-sm btn-light-danger" id="clear_all_notifications">
                <i class="fas fa-trash me-1"></i>
                Xóa tất cả
            </button>
        </div>
        <!--end::Actions-->
    </div>
    <!--end::Menu-->
</div>
<!--end::Notifications-->

<style>
/* Notification pulse animation */
.pulse-ring {
    content: '';
    width: 30px;
    height: 30px;
    border: 2px solid #f1416c;
    border-radius: 50%;
    position: absolute;
    top: -10px;
    right: -10px;
    animation: pulsate 1.5s ease-out infinite;
    opacity: 0.0;
}

@keyframes pulsate {
    0% {
        transform: scale(0.1, 0.1);
        opacity: 0.0;
    }
    50% {
        opacity: 1.0;
    }
    100% {
        transform: scale(1.2, 1.2);
        opacity: 0.0;
    }
}

/* Notification item styles */
.notification-item {
    transition: all 0.3s ease;
    border-radius: 0.475rem;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f1f8ff;
    border-left: 3px solid #009ef7;
}

.notification-item .notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-item .notification-content {
    flex: 1;
    min-width: 0;
}

.notification-item .notification-title {
    font-weight: 600;
    color: #181c32;
    margin-bottom: 2px;
}

.notification-item .notification-message {
    color: #7e8299;
    font-size: 0.875rem;
    line-height: 1.4;
}

.notification-item .notification-time {
    color: #a1a5b7;
    font-size: 0.75rem;
    white-space: nowrap;
}

/* Badge styles */
.badge-circle {
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    font-size: 0.7rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
