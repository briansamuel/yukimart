@inject('setting', 'App\Services\SettingService')
<!DOCTYPE html>

<html lang="en">

<!-- begin::Head -->

<head>
    <base href="{{ url('/admin/') }}">
    <meta charset="utf-8" />
    <title>{{ $setting->get('general::admin_appearance::site-name', 'YukiMart Platform') }} | @yield('page-header', 'Platform Management') -
        @yield('page-sub_header', 'Dashboard')
    </title>
    <meta name="description" content="Platform Management Dashboard">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--begin::Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Roboto:300,400,500,600,700">
    <!--end::Fonts -->
    <!--begin::Page Vendor Stylesheets(used by this page) -->
    @yield('vendor-style')
    <!--end::Page Vendor Stylesheets -->
    <!--begin::Global Stylesheets Bundle(used by all pages) -->
    <link href="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle -->
    <!--begin::Page Custom Stylesheets(used by this page) -->
    @yield('custom-style')
    <!--end::Page Custom Stylesheets -->
    <link rel="shortcut icon" href="{{ asset('admin-assets/assets/media/logos/favicon.ico') }}" />
    
    <!-- Platform-specific styles -->
    <style>
        .platform-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .platform-sidebar {
            background: linear-gradient(180deg, #1e1e2d 0%, #2a2a3a 100%);
        }
        
        .platform-header {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .tenant-switch-indicator {
            background: #ffc107;
            color: #000;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
    </style>
</head>
<!--end::Head -->
<!--begin::Body -->

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-theme-mode");
            } else {
                if (localStorage.getItem("data-theme") !== null) {
                    themeMode = localStorage.getItem("data-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            <!-- BEGIN: Platform Header -->
            @include('admin.platform-header')
            <!-- END: Platform Header -->

            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                <!-- BEGIN: Platform Sidebar -->
                @include('admin.platform-sidebar')
                <!-- END: Platform Sidebar -->

                <!--begin::Container-->
                  @yield('content')
                <!--end::Container-->
            </div>
            <!--end::Wrapper-->
            @include('admin.footer')
        </div>
    </div>
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
        <span class="svg-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1"
                    transform="rotate(90 13 6)" fill="black"></rect>
                <path
                    d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z"
                    fill="black"></path>
            </svg>
        </span>
        <!--end::Svg Icon-->
    </div>
    @yield('modal')
    <script>
        var hostUrl = "assets/";
        var base_url = "{{ url('/') }}";
        var admin_url = "{{ url('/admin') }}/";
        var is_platform_mode = true;
        var current_tenant_id = {{ session('current_tenant_id', 'null') }};
    </script>
    <script src="{{ asset('admin-assets/assets/js/language/en.js') }}" type="text/javascript"></script>
    <!-- end::Global Config -->
    <!--begin::Javascript-->
    <!--begin::Global Theme Bundle(used by all pages) -->
    <script src="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Theme Bundle -->
    <!--begin::Page Vendors Javascript(used by this page) -->
    @yield('vendor-script')
    <!--end::Page Vendors Javascript -->
    <!--begin::Page Custom Javascript(used by this page) -->
    @yield('custom-script')
    <!--end::Page Custom Javascript -->
    
    <!-- Platform-specific scripts -->
    <script>
        $(document).ready(function() {
            // Platform mode indicator
            console.log('Platform Management Mode Active');
            
            // Add platform badge to body
            $('body').addClass('platform-mode');
            
            // Tenant switching functionality for platform users
            window.switchToTenant = function(tenantId) {
                if (!tenantId) {
                    toastr.error('Please select a tenant');
                    return;
                }
                
                $.ajax({
                    url: '{{ route("platform.switch-to-tenant") }}',
                    method: 'POST',
                    data: {
                        tenant_id: tenantId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Switched to tenant mode');
                            window.location.href = response.redirect_url || '{{ route("admin.dashboard") }}';
                        } else {
                            toastr.error(response.message || 'Failed to switch to tenant');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error switching to tenant mode');
                    }
                });
            };
            
            // Return to platform mode
            window.returnToPlatform = function() {
                $.ajax({
                    url: '{{ route("platform.return-to-platform") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Returned to platform mode');
                            window.location.href = '{{ route("platform.dashboard") }}';
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error returning to platform mode');
                    }
                });
            };
        });
    </script>
</body>
<!--end::Body -->

</html>
