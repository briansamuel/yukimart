@inject('AuthPermission', 'App\Services\Auth\AuthPermissionService')
@inject('setting', 'App\Services\SettingService')
<!--begin::Platform Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column platform-sidebar" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Brand-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo-->
        <a href="{{ route('platform.dashboard') }}">
            <img alt="Logo"
                src="{{ $setting->get('general::admin_appearance::admin-logo', asset('/admin-assets/assets/media/logos/default-dark.svg')) }}"
                class="h-25px logo">
        </a>
        <!--end::Logo-->
        <!--begin::Sidebar toggler-->
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary body-bg h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
            <span class="svg-icon svg-icon-2 rotate-180">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path opacity="0.5"
                        d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                        fill="black" />
                    <path
                        d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                        fill="black" />
                </svg>
            </span>
            <!--end::Svg Icon-->
        </div>
        <!--end::Sidebar toggler-->
    </div>
    <!--end::Brand-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
                
                <!--begin:Platform Dashboard-->
                <div class="menu-item">
                    <a class="menu-link {{ Request::is('admin/platform/dashboard*') ? 'active' : '' }}"
                        href="{{ route('platform.dashboard') }}">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="2" width="9" height="9" rx="2" fill="currentColor"/>
                                    <rect opacity="0.3" x="13" y="2" width="9" height="9" rx="2" fill="currentColor"/>
                                    <rect opacity="0.3" x="13" y="13" width="9" height="9" rx="2" fill="currentColor"/>
                                    <rect opacity="0.3" x="2" y="13" width="9" height="9" rx="2" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">Platform Dashboard</span>
                    </a>
                </div>
                <!--end:Platform Dashboard-->

                <!--begin:Menu separator-->
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Platform Management</span>
                    </div>
                </div>
                <!--end:Menu separator-->

                <!--begin:Tenant Management-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ Request::is('admin/platform/tenants*') ? 'here show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 8H16C15.4 8 15 8.4 15 9V16H10V17C10 17.6 10.4 18 11 18H16C16.6 18 17 17.6 17 17V10H20C20.6 10 21 9.6 21 9C21 8.4 20.6 8 20 8Z" fill="currentColor"/>
                                    <path opacity="0.3" d="M17 10H20C20.6 10 21 9.6 21 9C21 8.4 20.6 8 20 8H16C15.4 8 15 8.4 15 9V16H10V17C10 17.6 10.4 18 11 18H16C16.6 18 17 17.6 17 17V10Z" fill="currentColor"/>
                                    <path opacity="0.3" d="M8 12V5C8 4.4 7.6 4 7 4C6.4 4 6 4.4 6 5V12H4C3.4 12 3 12.4 3 13C3 13.6 3.4 14 4 14H8C8.6 14 9 13.6 9 13C9 12.4 8.6 12 8 12Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">Tenant Management</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/tenants') ? 'active' : '' }}"
                                href="{{ route('platform.tenants.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">All Tenants</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/tenants/create') ? 'active' : '' }}"
                                href="{{ route('platform.tenants.create') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Create Tenant</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/tenants/switch*') ? 'active' : '' }}"
                                href="{{ route('platform.tenants.switch') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Switch to Tenant</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!--end:Tenant Management-->

                <!--begin:User Management-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ Request::is('admin/platform/users*') ? 'here show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.0173 9H15.3945C14.2833 9 13.263 9.61425 12.7431 10.5963L12.154 11.7091C12.0645 11.8781 12.1072 12.0868 12.2559 12.2071L12.6402 12.5183C13.2631 13.0225 13.7556 13.6691 14.0764 14.4035L14.2321 14.7601C14.2957 14.9058 14.4396 15 14.5987 15H18.6747C19.7297 15 20.4057 13.8774 19.912 12.945L18.6686 10.5963C18.1487 9.61425 17.1285 9 16.0173 9Z" fill="currentColor"/>
                                    <rect opacity="0.3" x="14" y="4" width="4" height="4" rx="2" fill="currentColor"/>
                                    <path d="M4.65486 14.8559C5.40389 13.1224 7.11161 12 9 12C10.8884 12 12.5961 13.1224 13.3451 14.8559L14.793 18.2067C15.3636 19.5271 14.3955 21 12.9571 21H5.04292C3.60453 21 2.63644 19.5271 3.20698 18.2067L4.65486 14.8559Z" fill="currentColor"/>
                                    <rect opacity="0.3" x="6" y="5" width="6" height="6" rx="3" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">Platform Users</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/users') ? 'active' : '' }}"
                                href="{{ route('platform.users.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">All Platform Users</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/users/create') ? 'active' : '' }}"
                                href="{{ route('platform.users.create') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Create Platform User</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/roles*') ? 'active' : '' }}"
                                href="{{ route('platform.roles.index') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Platform Roles</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!--end:User Management-->

                <!--begin:System Management-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ Request::is('admin/platform/system*') ? 'here show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.2929 2.70711C11.6834 2.31658 12.3166 2.31658 12.7071 2.70711L15.2929 5.29289C15.6834 5.68342 15.6834 6.31658 15.2929 6.70711L12.7071 9.29289C12.3166 9.68342 11.6834 9.68342 11.2929 9.29289L8.70711 6.70711C8.31658 6.31658 8.31658 5.68342 8.70711 5.29289L11.2929 2.70711Z" fill="currentColor"/>
                                    <path d="M11.2929 14.7071C11.6834 14.3166 12.3166 14.3166 12.7071 14.7071L15.2929 17.2929C15.6834 17.6834 15.6834 18.3166 15.2929 18.7071L12.7071 21.2929C12.3166 21.6834 11.6834 21.6834 11.2929 21.2929L8.70711 18.7071C8.31658 18.3166 8.31658 17.6834 8.70711 17.2929L11.2929 14.7071Z" fill="currentColor"/>
                                    <path opacity="0.3" d="M5.29289 8.70711C5.68342 8.31658 6.31658 8.31658 6.70711 8.70711L9.29289 11.2929C9.68342 11.6834 9.68342 12.3166 9.29289 12.7071L6.70711 15.2929C6.31658 15.6834 5.68342 15.6834 5.29289 15.2929L2.70711 12.7071C2.31658 12.3166 2.31658 11.6834 2.70711 11.2929L5.29289 8.70711Z" fill="currentColor"/>
                                    <path opacity="0.3" d="M17.2929 8.70711C17.6834 8.31658 18.3166 8.31658 18.7071 8.70711L21.2929 11.2929C21.6834 11.6834 21.6834 12.3166 21.2929 12.7071L18.7071 15.2929C18.3166 15.6834 17.6834 15.6834 17.2929 15.2929L14.7071 12.7071C14.3166 12.3166 14.3166 11.6834 14.7071 11.2929L17.2929 8.70711Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">System Management</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/system/settings') ? 'active' : '' }}"
                                href="{{ route('platform.system.settings') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Platform Settings</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/system/logs*') ? 'active' : '' }}"
                                href="{{ route('platform.system.logs') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">System Logs</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/system/performance*') ? 'active' : '' }}"
                                href="{{ route('platform.system.performance') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Performance Monitor</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/system/backup*') ? 'active' : '' }}"
                                href="{{ route('platform.system.backup') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Backup Management</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!--end:System Management-->

                <!--begin:Menu separator-->
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Analytics & Reports</span>
                    </div>
                </div>
                <!--end:Menu separator-->

                <!--begin:Analytics-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ Request::is('admin/platform/analytics*') ? 'here show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="2" width="9" height="9" rx="2" fill="currentColor"/>
                                    <rect opacity="0.3" x="13" y="2" width="9" height="9" rx="2" fill="currentColor"/>
                                    <rect opacity="0.3" x="13" y="13" width="9" height="9" rx="2" fill="currentColor"/>
                                    <rect opacity="0.3" x="2" y="13" width="9" height="9" rx="2" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">Platform Analytics</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/analytics/overview') ? 'active' : '' }}"
                                href="{{ route('platform.analytics.overview') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Overview</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/analytics/tenants') ? 'active' : '' }}"
                                href="{{ route('platform.analytics.tenants') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Tenant Analytics</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('admin/platform/analytics/performance') ? 'active' : '' }}"
                                href="{{ route('platform.analytics.performance') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Performance Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!--end:Analytics-->

                <!--begin:Menu separator-->
                <div class="menu-item">
                    <div class="menu-content pt-8 pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1">Quick Actions</span>
                    </div>
                </div>
                <!--end:Menu separator-->

                <!--begin:Quick Actions-->
                <div class="menu-item">
                    <a class="menu-link" href="#" onclick="showTenantSwitchModal()">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0014C14.2643 22.0014 14.4458 21.9488 14.6024 21.8499C14.759 21.751 14.8844 21.6097 14.964 21.4425L17.501 16.1315C17.5766 15.9734 17.6083 15.7981 17.593 15.6236C17.5777 15.4492 17.5159 15.282 17.414 15.1395L12.728 8.56949C12.6 8.39995 12.4065 8.29207 12.1887 8.26969C11.9709 8.24732 11.7555 8.31268 11.5859 8.45129C11.4164 8.58989 11.3085 8.78636 11.2861 9.00423C11.2637 9.2221 11.3291 9.43745 11.4677 9.607L15.43 8.56949Z" fill="currentColor"/>
                                    <path opacity="0.3" d="M8.56949 15.43L15.1395 10.744C15.282 10.6422 15.4492 10.5804 15.6236 10.5651C15.7981 10.5498 15.9734 10.5815 16.1315 10.657L21.4425 13.194C21.6097 13.2737 21.751 13.3991 21.8499 13.5557C21.9488 13.7123 22.0014 13.8938 22.0014 14.079C22.0014 14.2643 21.9488 14.4458 21.8499 14.6024C21.751 14.759 21.6097 14.8844 21.4425 14.964L16.1315 17.501C15.9734 17.5766 15.7981 17.6083 15.6236 17.593C15.4492 17.5777 15.282 17.5159 15.1395 17.414L8.56949 12.728C8.39995 12.6 8.29207 12.4065 8.26969 12.1887C8.24732 11.9709 8.31268 11.7555 8.45129 11.5859C8.58989 11.4164 8.78636 11.3085 9.00423 11.2861C9.2221 11.2637 9.43745 11.3291 9.607 11.4677L8.56949 15.43Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">Switch to Tenant</span>
                    </a>
                </div>

                @if(session('current_tenant_id'))
                    <div class="menu-item">
                        <a class="menu-link text-warning" href="#" onclick="returnToPlatform()">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9C19 9.55228 18.5523 10 18 10H6C5.44772 10 5 9.55228 5 9Z" fill="currentColor"/>
                                        <path d="M5 15C5 14.4477 5.44772 14 6 14H18C18.5523 14 19 14.4477 19 15C19 15.5523 18.5523 16 18 16H6C5.44772 16 5 15.5523 5 15Z" fill="currentColor"/>
                                        <path d="M15.3787 7.70711C15.7692 7.31658 15.7692 6.68342 15.3787 6.29289L12.7071 3.62132C12.3166 3.2308 11.6834 3.2308 11.2929 3.62132L8.62132 6.29289C8.2308 6.68342 8.2308 7.31658 8.62132 7.70711C9.01184 8.09763 9.645 8.09763 10.0355 7.70711L11 6.74264V17.2574L10.0355 16.2929C9.645 15.9024 9.01184 15.9024 8.62132 16.2929C8.2308 16.6834 8.2308 17.3166 8.62132 17.7071L11.2929 20.3787C11.6834 20.7692 12.3166 20.7692 12.7071 20.3787L15.3787 17.7071C15.7692 17.3166 15.7692 16.6834 15.3787 16.2929C14.9882 15.9024 14.355 15.9024 13.9645 16.2929L13 17.2574V6.74264L13.9645 7.70711C14.355 8.09763 14.9882 8.09763 15.3787 7.70711Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </span>
                            <span class="menu-title">Exit Tenant Mode</span>
                        </a>
                    </div>
                @endif
                <!--end:Quick Actions-->

            </div>
            <!--end::Menu-->
        </div>
    </div>
    <!--end::sidebar menu-->
</div>
<!--end::Platform Sidebar-->
