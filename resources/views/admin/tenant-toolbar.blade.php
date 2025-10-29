@inject('AuthPermission', 'App\Services\Auth\AuthPermissionService')
@php
    $currentTenant = session('current_tenant');
    $tenantRole = session('current_tenant_role', 'user');
@endphp
<!--begin::Tenant Toolbar-->
<div id="kt_app_toolbar" class="app-toolbar tenant-toolbar py-3 py-lg-6">
    <!--begin::Container-->
    <div class="app-container container-fluid d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                @yield('page-header', 'Dashboard')
            </h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">@yield('page-sub_header', 'Dashboard')</li>
                <!--end::Item-->
            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--end::Page title-->

        <!--begin::Tenant Navigation Menu-->
        <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
            <!--begin::Menu-->
            <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                
                <!--begin:Dashboard-->
                <div class="menu-item {{ request()->routeIs('admin.dashboard*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2">
                    <a class="menu-link" href="{{ route('admin.dashboard') }}">
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>
                <!--end:Dashboard-->

                <!--begin:Products-->
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.brands.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Sản phẩm</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.products.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-box text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Danh sách sản phẩm</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.products.create') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-plus text-success fs-6"></i>
                                        </span>
                                        <span class="menu-title">Thêm sản phẩm</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.categories.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-tags text-info fs-6"></i>
                                        </span>
                                        <span class="menu-title">Danh mục</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.brands.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-award text-warning fs-6"></i>
                                        </span>
                                        <span class="menu-title">Thương hiệu</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:Products-->

                <!--begin:Orders-->
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.orders.*', 'admin.quick-order.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Đơn hàng</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.orders.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-shopping-cart text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Danh sách đơn hàng</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.quick-order.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-bolt text-success fs-6"></i>
                                        </span>
                                        <span class="menu-title">Bán hàng nhanh</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.orders.create') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-plus text-info fs-6"></i>
                                        </span>
                                        <span class="menu-title">Tạo đơn hàng</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:Orders-->

                <!--begin:Customers-->
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.customers.*', 'admin.supplier.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Khách hàng</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.customers.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-users text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Danh sách khách hàng</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.customers.create') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-user-plus text-success fs-6"></i>
                                        </span>
                                        <span class="menu-title">Thêm khách hàng</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.supplier.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-truck text-info fs-6"></i>
                                        </span>
                                        <span class="menu-title">Nhà cung cấp</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:Customers-->

                <!--begin:Invoices-->
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.invoices.*', 'admin.returns.*', 'admin.payments.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Giao dịch</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.invoices.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-file-invoice text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Hóa đơn</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.returns.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-undo text-warning fs-6"></i>
                                        </span>
                                        <span class="menu-title">Trả hàng</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.payments.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-credit-card text-success fs-6"></i>
                                        </span>
                                        <span class="menu-title">Thanh toán</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end:Invoices-->

                <!--begin:Inventory-->
                @if($AuthPermission->hasPermission('inventory.view'))
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.inventory.*', 'admin.branch-shops.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Kho hàng</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.inventory.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-warehouse text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Tồn kho</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.settings.branch-manager') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-store text-info fs-6"></i>
                                        </span>
                                        <span class="menu-title">Chi nhánh</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!--end:Inventory-->

                <!--begin:Reports-->
                @if($AuthPermission->hasPermission('reports.view'))
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.reports.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Báo cáo</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.reports.sales') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-chart-line text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Báo cáo bán hàng</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.reports.inventory') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-boxes text-info fs-6"></i>
                                        </span>
                                        <span class="menu-title">Báo cáo tồn kho</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.reports.financial') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-dollar-sign text-success fs-6"></i>
                                        </span>
                                        <span class="menu-title">Báo cáo tài chính</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!--end:Reports-->

                <!--begin:Settings-->
                @if($AuthPermission->hasPermission('settings.view') || in_array($tenantRole, ['owner', 'admin']))
                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.settings.*', 'admin.users.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                    <span class="menu-link">
                        <span class="menu-title">Cài đặt</span>
                        <span class="menu-arrow d-lg-none"></span>
                    </span>
                    <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                        <div class="menu-active-bg px-4 px-lg-0">
                            <div class="d-flex flex-column">
                                @if(in_array($tenantRole, ['owner', 'admin']))
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.users.index') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-users-cog text-primary fs-6"></i>
                                        </span>
                                        <span class="menu-title">Quản lý người dùng</span>
                                    </a>
                                </div>
                                @endif
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.settings.general') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-cog text-info fs-6"></i>
                                        </span>
                                        <span class="menu-title">Cài đặt chung</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link py-3" href="{{ route('admin.settings.tenant') }}">
                                        <span class="menu-icon">
                                            <i class="fas fa-store-alt text-warning fs-6"></i>
                                        </span>
                                        <span class="menu-title">Cài đặt cửa hàng</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!--end:Settings-->

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Tenant Navigation Menu-->

        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Mobile menu toggle-->
            <div class="d-lg-none btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_app_header_menu_toggle">
                <!--begin::Svg Icon | path: icons/duotune/abstract/abs015.svg-->
                <span class="svg-icon svg-icon-2x">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black"></path>
                        <path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </div>
            <!--end::Mobile menu toggle-->
            @yield('toolbar-actions')
        </div>
        <!--end::Actions-->
    </div>
    <!--end::Container-->
</div>
<!--end::Tenant Toolbar-->
