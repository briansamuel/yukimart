<!--begin::Sidebar-->
<div class="flex-column flex-lg-row-auto w-100 w-lg-250px w-xl-250px mb-10 order-1 order-lg-1">
    <!--begin::Card-->
    <div class="card card-flush">
        <!--begin::Card body-->
        <div class="card-body p-0">
            <!--begin::Menu wrapper-->
            <div class="menu-wrapper hover-scroll-overlay-y my-5 my-lg-5" data-kt-scroll="true"
                data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers=".menu-wrapper"
                data-kt-scroll-offset="5px" style="max-height: 600px;">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold px-5"
                    id="kt_settings_sidebar_menu" data-kt-menu="true">

                    <!--begin::Menu section - QUẢN LÝ-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Quản lý</span>
                        </div>
                    </div>
                    <!--end::Menu section-->

                    <!--begin::Menu item - Hàng hóa-->
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('admin/settings/products*') ? 'active' : '' }}"
                            href="{{ route('admin.settings.products.index') }}">
                            <span class="menu-icon">
                                <i class="fas fa-box fs-3"></i>
                            </span>
                            <span class="menu-title">Hàng hóa</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item - Đơn hàng (Placeholder)-->
                    <div class="menu-item">
                        <a class="menu-link" href="#" style="opacity: 0.5; cursor: not-allowed;">
                            <span class="menu-icon">
                                <i class="fas fa-shopping-cart fs-3"></i>
                            </span>
                            <span class="menu-title">Đơn hàng</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item - Khách hàng (Placeholder)-->
                    <div class="menu-item">
                        <a class="menu-link" href="#" style="opacity: 0.5; cursor: not-allowed;">
                            <span class="menu-icon">
                                <i class="fas fa-users fs-3"></i>
                            </span>
                            <span class="menu-title">Khách hàng</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item - Sổ quỹ (Placeholder)-->
                    <div class="menu-item">
                        <a class="menu-link" href="#" style="opacity: 0.5; cursor: not-allowed;">
                            <span class="menu-icon">
                                <i class="fas fa-wallet fs-3"></i>
                            </span>
                            <span class="menu-title">Sổ quỹ</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu section - CỬA HÀNG-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Cửa hàng</span>
                        </div>
                    </div>
                    <!--end::Menu section-->
                    <!--begin::Menu item - Thông tin cửa hàng-->
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('admin/settings/retailer-info*') ? 'active' : '' }}"
                            href="{{ route('admin.settings.retailer-info') }}">
                            <span class="menu-icon">
                                <i class="fas fa-store fs-2"></i>
                            </span>
                            <span class="menu-title">Thông tin cửa hàng</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item - Quản lý người dùng-->
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('admin/settings/user-manager*') ? 'active' : '' }}"
                            href="{{ route('admin.settings.user-manager') }}">
                            <span class="menu-icon">
                                <i class="fas fa-users fs-3"></i>
                            </span>
                            <span class="menu-title">Quản lý người dùng</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item - Quản lý chi nhánh-->
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('admin/settings/branch-manager*') ? 'active' : '' }}"
                            href="{{ route('admin.settings.branch-manager') }}">
                            <span class="menu-icon">
                                <i class="fas fa-map-marker-alt fs-3"></i>
                            </span>
                            <span class="menu-title">Quản lý chi nhánh</span>
                        </a>
                    </div>
                    <!--end::Menu item-->

                    {{-- Placeholder for future categories --}}
                    {{-- 
                    <!--begin::Menu item - Category: Dữ liệu-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fas fa-database fs-2"></i>
                            </span>
                            <span class="menu-title">Dữ liệu</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Import/Export</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Backup/Restore</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Menu item-->
                    
                    <!--begin::Menu item - Category: Thiết bị-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fas fa-laptop fs-2"></i>
                            </span>
                            <span class="menu-title">Thiết bị</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">POS Devices</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Printers</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Menu item-->
                    
                    <!--begin::Menu item - Category: Tích hợp-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fas fa-plug fs-2"></i>
                            </span>
                            <span class="menu-title">Tích hợp</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Payment Gateways</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="#">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Shipping Providers</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Menu item-->
                    --}}

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Menu wrapper-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>
<!--end::Sidebar-->
