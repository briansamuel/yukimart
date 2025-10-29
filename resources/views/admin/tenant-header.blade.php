<div id="kt_app_header" style="" class="app-header d-flex flex-column">
    <!--begin::Header Top-->
    <div class="header-top app-container d-flex align-items-center" id="kt_app_header_top_container">
        
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
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1 tenant-container container-xxl  " id="kt_app_header_top_wrapper">
          <div class="header-logo me-5 me-md-10 flex-grow-1 flex-lg-grow-0" bis_skin_checked="1">
			<a href="{{ route('admin.dashboard') }}" bis_skin_checked="1">
                    <img alt="Logo" src="{{ asset('admin-assets/assets/media/logos/default.svg') }}" class="logo-default h-25px">
                
                </a>
            </div>
            <!--begin::Navbar-->
            <div class="d-flex align-items-stretch flex-shrink-0">
                <!--begin::Branch switcher-->
                @include('components.branch-switcher')
                <!--end::Branch switcher-->
                @include('admin.elements.notifications')
                @include('admin.elements.app_account_menu')
            </div>
            <!--end::Navbar-->
        </div>
        <!--end::Header Wrapper-->
    </div>
    <!--end::Header Top-->
    <!--begin::Header Top-->
    <div class="header-nav app-container d-flex align-items-center bg-primary" id="kt_app_header_container">
       

        <!--begin::Header wrapper-->
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1 tenant-container container-xxl" id="kt_app_header_wrapper">
            <!--begin::Menu wrapper-->
            <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                <!--begin::Menu-->
                <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                    <!--begin:Menu item-->
                    <div data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <a href="{{ route('admin.dashboard') }}" class="menu-link">
                            <span class="menu-title text-white">Tổng Quan</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </a>
                        <!--end:Menu link-->
                        
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item - Hàng hóa-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.products.*', 'admin.category.*', 'admin.inventory.*', 'admin.pricing.*', 'admin.transfer.*', 'admin.stock-check.*', 'admin.disposal.*', 'admin.supplier.*', 'admin.purchase.*', 'admin.purchase-return.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title text-white">Hàng hóa</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub - Mega Menu-->
                        <div class="menu-sub menu-sub-lg-dropdown menu-sub-lg-down-accordion p-0 w-100 w-lg-850px">
                            <div class="menu-active-bg p-6">
                                <div class="row">
                                    <!--begin:Column 1 - Hàng hóa-->
                                    <div class="col-lg-4 border-end border-gray-300 pe-4">
                                        <h4 class="text-gray-800 fw-bold mb-4">Hàng hóa</h4>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.products.index') }}">
                                                <span class="menu-title">Danh sách hàng hóa</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.pricing.index') }}">
                                                <span class="menu-title">Thiết lập giá</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!--end:Column 1-->

                                    <!--begin:Column 2 - Kho hàng-->
                                    <div class="col-lg-4 border-end border-gray-300 px-4">
                                        <h4 class="text-gray-800 fw-bold mb-4">Kho hàng</h4>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.transfer.index') }}">
                                                <span class="menu-title">Chuyển hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.stock-check.index') }}">
                                                <span class="menu-title">Kiểm kho</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.disposal.index') }}">
                                                <span class="menu-title">Xuất hủy</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!--end:Column 2-->

                                    <!--begin:Column 3 - Nhập hàng-->
                                    <div class="col-lg-4 ps-4">
                                        <h4 class="text-gray-800 fw-bold mb-4">Nhập hàng</h4>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.supplier.list') }}">
                                                <span class="menu-title">Nhà cung cấp</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.purchase.index') }}">
                                                <span class="menu-title">Nhập hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.purchase-return.index') }}">
                                                <span class="menu-title">Trả hàng nhập</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!--end:Column 3-->
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item - Đơn hàng-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.order.*', 'admin.invoice.*', 'admin.return.*', 'admin.shipping-partner.*', 'admin.waybill.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title text-white">Đơn hàng</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-600px">
                            <div class="menu-active-bg p-6">
                                <div class="row">
                                    <!--begin:Column - Đơn hàng-->
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="menu-item">
                                                    <a class="menu-link py-2" href="{{ route('admin.order.list') }}">
                                                        <span class="menu-title">Đặt hàng</span>
                                                    </a>
                                                </div>
                                                <div class="menu-item">
                                                    <a class="menu-link py-2" href="{{ route('admin.invoice.list') }}">
                                                        <span class="menu-title">Hóa đơn</span>
                                                    </a>
                                                </div>
                                                <div class="menu-item">
                                                    <a class="menu-link py-2" href="{{ route('admin.return.list') }}">
                                                        <span class="menu-title">Trả hàng</span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="menu-item">
                                                    <a class="menu-link py-2" href="{{ route('admin.shipping-partner.index') }}">
                                                        <span class="menu-title">Đối tác giao hàng</span>
                                                    </a>
                                                </div>
                                                <div class="menu-item">
                                                    <a class="menu-link py-2" href="{{ route('admin.waybill.index') }}">
                                                        <span class="menu-title">Vận đơn</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end:Column-->
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item - Khách hàng-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.customers.*', 'admin.promotion.*', 'admin.voucher.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title text-white">Khách hàng</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-400px">
                            <div class="menu-active-bg p-6">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.customers.index') }}">
                                                <span class="menu-title">Khách hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.promotion.index') }}">
                                                <span class="menu-title">Khuyến mãi</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.voucher.index') }}">
                                                <span class="menu-title">Voucher</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Nhân viên-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.employees.*', 'admin.schedule.*', 'admin.attendance.*', 'admin.payroll.*', 'admin.commission.*', 'admin.employee-settings.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title text-white">Nhân viên</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-500px">
                            <div class="menu-active-bg p-6">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.employees.index') }}">
                                                <span class="menu-title">Danh sách nhân viên</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.schedule.index') }}">
                                                <span class="menu-title">Lịch làm việc</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.attendance.index') }}">
                                                <span class="menu-title">Bảng chấm công</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.payroll.index') }}">
                                                <span class="menu-title">Bảng lương</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.commission.index') }}">
                                                <span class="menu-title">Bảng hoa hồng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.employee-settings.index') }}">
                                                <span class="menu-title">Thiết lập nhân viên</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Sổ quỹ-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.payment.*', 'admin.fund-transfer.*', 'admin.fund-report.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title text-white">Sổ quỹ</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-400px">
                            <div class="menu-active-bg p-6">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.payment.list') }}">
                                                <span class="menu-title">Thu chi</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.fund-transfer.index') }}">
                                                <span class="menu-title">Chuyển quỹ</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.fund-report.index') }}">
                                                <span class="menu-title">Báo cáo quỹ</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Phân tích-->
                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start" class="menu-item {{ request()->routeIs('admin.analytics.*', 'admin.report.*') ? 'here show menu-here-bg' : '' }} me-0 me-lg-2" data-kt-menu-offset="0,0">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-title text-white">Phân tích</span>
                            <span class="menu-arrow d-lg-none"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub - Mega Menu-->
                        <div class="menu-sub menu-sub-lg-dropdown p-0 w-100 w-lg-850px">
                            <div class="menu-active-bg p-6">
                                <div class="row">
                                    <!--begin:Column 1 - Phân tích kinh doanh-->
                                    <div class="col-lg-4 border-end border-gray-300 pe-4">
                                        <h4 class="text-gray-800 fw-bold mb-4">Phân tích kinh doanh</h4>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.analytics.business') }}">
                                                <span class="menu-title">Kinh doanh</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.analytics.products') }}">
                                                <span class="menu-title">Hàng hóa</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.analytics.customers') }}">
                                                <span class="menu-title">Khách hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.analytics.performance') }}">
                                                <span class="menu-title">Hiệu quả</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!--end:Column 1-->

                                    <!--begin:Column 2 - Báo cáo-->
                                    <div class="col-lg-4 border-end border-gray-300 px-4">
                                        <h4 class="text-gray-800 fw-bold mb-4">Báo cáo</h4>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.end-of-day') }}">
                                                <span class="menu-title">Cuối ngày</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.sales') }}">
                                                <span class="menu-title">Bán hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.orders') }}">
                                                <span class="menu-title">Đặt hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.products') }}">
                                                <span class="menu-title">Hàng hóa</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!--end:Column 2-->

                                    <!--begin:Column 3 - Báo cáo (tiếp)-->
                                    <div class="col-lg-4 ps-4">
                                        <h4 class="text-gray-800 fw-bold mb-4">&nbsp;</h4>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.customers') }}">
                                                <span class="menu-title">Khách hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.suppliers') }}">
                                                <span class="menu-title">Nhà cung cấp</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.employees') }}">
                                                <span class="menu-title">Nhân viên</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.channels') }}">
                                                <span class="menu-title">Kênh bán hàng</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link py-2" href="{{ route('admin.report.finance') }}">
                                                <span class="menu-title">Tài chính</span>
                                            </a>
                                        </div>
                                    </div>
                                    <!--end:Column 3-->
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Menu wrapper-->
          
        </div>
        <!--end::Header Wrapper-->
    </div>
    <!--end::Header Top-->
</div>
<!-- end:: Header -->
