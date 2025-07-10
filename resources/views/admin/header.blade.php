<div id="kt_app_header" style="" class="app-header">
    <!--begin::Container-->
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
        <!--begin::Aside mobile toggle-->
        <div class="d-flex align-items-center d-lg-none ms-n3 me-1" title="Show aside menu">
            <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px"
                id="kt_aside_mobile_toggle">
                <!--begin::Svg Icon | path: icons/duotune/abstract/abs015.svg-->
                <span class="svg-icon svg-icon-2x mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none">
                        <path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z"
                            fill="black"></path>
                        <path opacity="0.3"
                            d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z"
                            fill="black"></path>
                    </svg>
                </span>
                <!--end::Svg Icon-->
            </div>
        </div>
        <!--end::Aside mobile toggle-->
        <!--begin::Mobile logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="../../demo1/dist/index.html" class="d-lg-none">
                <img alt="Logo" src="assets/media/logos/default-small.svg" class="h-30px">
            </a>
        </div>
        <!--end::Mobile logo-->
        <!--begin::Header wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            <!--begin::Menu wrapper-->
            <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                <!--begin::Menu-->
                <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <a href="{{ route('admin.dashboard') }}" class="menu-link">
                            <span class="menu-title">Tổng Quan</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                            <!--begin:Dashboards menu-->
                            <div class="menu-active-bg px-4 px-lg-0">
                                <!--begin:Menu items-->
                                <div class="d-flex flex-column">
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.dashboard') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-element-11 fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Dashboard</span>
                                        </a>
                                    </div>
                                </div>
                                <!--end:Menu items-->
                            </div>
                            <!--end:Dashboards menu-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.products.*', 'admin.category.*', 'admin.inventory.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title">Hàng Hoá</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                            <!--begin:Products menu-->
                            <div class="menu-active-bg px-4 px-lg-0">
                                <!--begin:Menu items-->
                                <div class="d-flex flex-column">
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.products.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-package fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Danh sách</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.products.add') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-plus fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Thêm mới</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.category.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-category fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Danh mục</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.inventory.dashboard') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-chart-simple fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Tồn kho</span>
                                        </a>
                                    </div>
                                </div>
                                <!--end:Menu items-->
                            </div>
                            <!--end:Products menu-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.order.*', 'admin.invoice.*', 'admin.quick-order.*', 'admin.return-order.*', 'admin.payment.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title">Giao dịch</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                            <!--begin:Transactions menu-->
                            <div class="menu-active-bg px-4 px-lg-0">
                                <!--begin:Menu items-->
                                <div class="d-flex flex-column">
                                    <div class="menu-item">
                                        <a class="menu-link py-3 {{ request()->routeIs('admin.order.*') ? 'active' : '' }}" href="{{ route('admin.order.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-basket fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Đặt hàng</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3 {{ request()->routeIs('admin.invoice.*') ? 'active' : '' }}" href="{{ route('admin.invoice.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-bill fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                    <span class="path6"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Hoá đơn</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.quick-order.index') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-flash fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Bán hàng nhanh</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3 {{ request()->routeIs('admin.return-order.*') ? 'active' : '' }}" href="{{ route('admin.return-order.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-arrow-left fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Trả hàng</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3 {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}" href="{{ route('admin.payment.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-wallet fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Phiếu thu/chi</span>
                                        </a>
                                    </div>
                                </div>
                                <!--end:Menu items-->
                            </div>
                            <!--end:Transactions menu-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.customers.*', 'admin.supplier.*', 'admin.branch-shops.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title">Đối tác</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-300px">
                            <!--begin:Partners menu-->
                            <div class="menu-active-bg px-4 px-lg-0">
                                <!--begin:Menu items-->
                                <div class="d-flex flex-column">
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.customers.index') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-profile-user fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Khách hàng</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.supplier.list') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-truck fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Nhà cung cấp</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link py-3" href="{{ route('admin.branch-shops.index') }}">
                                            <span class="menu-icon">
                                                <i class="ki-duotone ki-shop fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Cửa hàng</span>
                                        </a>
                                    </div>
                                </div>
                                <!--end:Menu items-->
                            </div>
                            <!--end:Partners menu-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Menu wrapper-->
            <!--begin::Navbar-->

            <div class="d-flex align-items-stretch flex-shrink-0">
                @include('admin.elements.notifications')
                @include('admin.elements.app_account_menu')

            </div>
            <!--begin::Navbar-->
        </div>
        <!--end::Header Wrapper-->
    </div>
    <!--end::Container-->
</div>
<!-- end:: Header -->
