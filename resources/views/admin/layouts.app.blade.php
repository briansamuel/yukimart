@inject('setting', 'App\Services\SettingService')
<!DOCTYPE html>

<html lang="en">

<!-- begin::Head -->

<head>
    <base href="{{ url('/admin/') }}">
    <meta charset="utf-8" />
    <title>{{ $setting->get('general::admin_appearance::site-name', 'ERMSystem') }} | @yield('page-header', 'Trang Quản Trị') -
        @yield('page-sub_header', 'DashBoard')
    </title>
    <meta name="description" content="Updates and statistics">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--begin::Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700|Roboto:300,400,500,600,700">

    <!--end::Fonts -->

    @yield('style')
    <!--end::Page Vendors Styles -->

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="{{ asset('') }}admin-assets/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet"
        type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <!--end::Global Stylesheets Bundle-->

    <link rel="shortcut icon"
        href="{{ $setting->get('general::admin_appearance::admin-favicon', 'admin-assets/assets/media/logos/favicon.ico') }}" />
</head>

<!-- end::Head -->

<!-- begin::Body -->

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        var skinTinyMCE = 'oxide';
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
            if (themeMode === "dark") {
                skinTinyMCE = 'oxide-dark';
            }

            document.documentElement.setAttribute("data-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!-- begin:: Header -->
            @include('admin.header')
            <!-- end:: Header -->

            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                <!-- BEGIN: Left Aside -->
                @include('admin.left-aside')
                <!-- END: Left Aside -->

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
    </script>
    <script src="{{ asset('admin-assets/assets/js/language/en.js') }}" type="text/javascript"></script>
    <!-- end::Global Config -->
    <!--begin::Javascript-->
    <!--begin::Global Theme Bundle(used by all pages) -->

    <script src="{{ asset('') }}admin-assets/assets/plugins/global/plugins.bundle.js" type="text/javascript"></script>
    <script src="{{ asset('') }}admin-assets/assets/js/scripts.bundle.js" type="text/javascript"></script>
    <script src="{{ asset('') }}admin-assets/src/js/layout/aside.js" type="text/javascript"></script>
    <script src="{{ asset('') }}admin-assets/src/js/layout/toolbar.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    {{-- <script src="{{ asset('') }}admin/js/main.js" type="text/javascript"></script> --}}
    <!--end::Global Theme Bundle -->
    <!--begin::Page Vendors(used by this page) -->
    @yield('vendor-script', '')
    <!--end::Page Vendors -->
    <!--begin::Notifications Script -->
    <script src="{{ asset('admin-assets/assets/js/custom/notifications.js') }}"></script>
    <!--end::Notifications Script -->
    <!--begin::Page Scripts(used by this page) -->
    @yield('scripts')
    <!--end::Javascript-->
</body>

<!-- end::Body -->

</html>
