@inject('AuthPermission', 'App\Services\Auth\AuthPermissionService')
<!--begin::Aside-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Brand-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo-->
        <a href="">
            <img alt="Logo"
                src="{{ $setting->get('general::admin_appearance::admin-logo', asset('/admin-assets/assets/media/logos/default-dark.svg')) }}"
                class="h-25px logo">
        </a>
        <!--end::Logo-->
        <!--begin::Aside toggler-->
        <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="aside-minimize">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
            <span class="svg-icon svg-icon-1 rotate-180">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none">
                    <path opacity="0.5"
                        d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                        fill="black"></path>
                    <path
                        d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                        fill="black"></path>
                </svg>
            </span>
            <!--end::Svg Icon-->
        </div>
        <!--end::Aside toggler-->
    </div>
    <!--end::Brand-->

    <!-- begin:: Aside Menu -->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Aside Menu-->

        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
                <!--begin:Menu item-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="2" width="9" height="9" rx="2"
                                        fill="currentColor"></rect>
                                    <rect opacity="0.3" x="13" y="2" width="9" height="9"
                                        rx="2" fill="currentColor"></rect>
                                    <rect opacity="0.3" x="13" y="13" width="9" height="9"
                                        rx="2" fill="currentColor"></rect>
                                    <rect opacity="0.3" x="2" y="13" width="9" height="9"
                                        rx="2" fill="currentColor"></rect>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">{{ __('menu.dashboard') }}</span>
                    </span>
                    <!--end:Menu link-->

                </div>
                <!--end:Menu item-->

                @if ($AuthPermission->checkHeader('PageController') ||
                    $AuthPermission->checkHeader('PageController/index') ||
                    $AuthPermission->checkHeader('PageController/add') ||
                    $AuthPermission->checkHeader('PageController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('admin/page*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/kt-products/docs/metronic/html/releases/2022-10-09-043348/core/html/src/media/icons/duotune/general/gen009.svg-->
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M21 22H14C13.4 22 13 21.6 13 21V3C13 2.4 13.4 2 14 2H21C21.6 2 22 2.4 22 3V21C22 21.6 21.6 22 21 22Z"
                                            fill="currentColor" />
                                        <path
                                            d="M10 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H10C10.6 2 11 2.4 11 3V21C11 21.6 10.6 22 10 22Z"
                                            fill="currentColor" />
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('menu.pages') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ Request::is('admin/page') ? 'active' : '' }}"
                                    href="{{ route('admin.page.list') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('menu.page_list') }}</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ Request::is('admin/page/add') ? 'active' : '' }}"
                                    href="{{ route('admin.page.add') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('menu.add_page') }}</span>
                                </a>
                            </div>

                        </div>
                    </div>
                @endif
                @if ($AuthPermission->checkHeader('NewsController') ||
                    $AuthPermission->checkHeader('NewsController/index') ||
                    $AuthPermission->checkHeader('NewsController/add') ||
                    $AuthPermission->checkHeader('NewsController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/news*') || Request()->query('type') == 'category_of_news' ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/kt-products/docs/metronic/html/releases/2022-10-09-043348/core/html/src/media/icons/duotune/layouts/lay002.svg-->
                                <span class="svg-icon svg-icon-muted svg-icon-2"><svg width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M6 7H3C2.4 7 2 6.6 2 6V3C2 2.4 2.4 2 3 2H6C6.6 2 7 2.4 7 3V6C7 6.6 6.6 7 6 7Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M13 7H10C9.4 7 9 6.6 9 6V3C9 2.4 9.4 2 10 2H13C13.6 2 14 2.4 14 3V6C14 6.6 13.6 7 13 7ZM21 6V3C21 2.4 20.6 2 20 2H17C16.4 2 16 2.4 16 3V6C16 6.6 16.4 7 17 7H20C20.6 7 21 6.6 21 6ZM7 13V10C7 9.4 6.6 9 6 9H3C2.4 9 2 9.4 2 10V13C2 13.6 2.4 14 3 14H6C6.6 14 7 13.6 7 13ZM14 13V10C14 9.4 13.6 9 13 9H10C9.4 9 9 9.4 9 10V13C9 13.6 9.4 14 10 14H13C13.6 14 14 13.6 14 13ZM21 13V10C21 9.4 20.6 9 20 9H17C16.4 9 16 9.4 16 10V13C16 13.6 16.4 14 17 14H20C20.6 14 21 13.6 21 13ZM7 20V17C7 16.4 6.6 16 6 16H3C2.4 16 2 16.4 2 17V20C2 20.6 2.4 21 3 21H6C6.6 21 7 20.6 7 20ZM14 20V17C14 16.4 13.6 16 13 16H10C9.4 16 9 16.4 9 17V20C9 20.6 9.4 21 10 21H13C13.6 21 14 20.6 14 20ZM21 20V17C21 16.4 20.6 16 20 16H17C16.4 16 16 16.4 16 17V20C16 20.6 16.4 21 17 21H20C20.6 21 21 20.6 21 20Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('menu.news') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ Request::is('news') ? 'active' : '' }}"
                                    href="{{ route('admin.news.list') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('menu.news_list') }}</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ Request::is('news/add') ? 'active' : '' }}"
                                    href="{{ route('admin.news.add') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('menu.add_news') }}</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ Request()->query('type') == 'category_of_news' ? 'active' : '' }}"
                                    href="{{ route('admin.category.list') }}?type=category_of_news">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('menu.news_categories') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($AuthPermission->checkHeader('ProjectController') ||
                    $AuthPermission->checkHeader('ProjectController/index') ||
                    $AuthPermission->checkHeader('ProjectController/add') ||
                    $AuthPermission->checkHeader('ProjectController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*project*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M18 21.6C16.6 20.4 9.1 20.3 6.3 21.2C5.7 21.4 5.1 21.2 4.7 20.8L2 18C4.2 15.8 10.8 15.1 15.8 15.8C16.2 18.3 17 20.5 18 21.6ZM18.8 2.8C18.4 2.4 17.8 2.20001 17.2 2.40001C14.4 3.30001 6.9 3.2 5.5 2C6.8 3.3 7.4 5.5 7.7 7.7C9 7.9 10.3 8 11.7 8C15.8 8 19.8 7.2 21.5 5.5L18.8 2.8Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.3"
                                            d="M21.2 17.3C21.4 17.9 21.2 18.5 20.8 18.9L18 21.6C15.8 19.4 15.1 12.8 15.8 7.8C18.3 7.4 20.4 6.70001 21.5 5.60001C20.4 7.00001 20.2 14.5 21.2 17.3ZM8 11.7C8 9 7.7 4.2 5.5 2L2.8 4.8C2.4 5.2 2.2 5.80001 2.4 6.40001C2.7 7.40001 3.00001 9.2 3.10001 11.7C3.10001 15.5 2.40001 17.6 2.10001 18C3.20001 16.9 5.3 16.2 7.8 15.8C8 14.2 8 12.7 8 11.7Z"
                                            fill="currentColor"></path>
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Dự Án</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('ProjectController') || $AuthPermission->isHeader('ProjectController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('project') ? 'active' : '' }}"
                                        href="{{ route('admin.project.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Danh sách dự án</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('ProjectController') || $AuthPermission->isHeader('ProjectController/add'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('project/add') ? 'active' : '' }}"
                                        href="{{ route('admin.project.add') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Thêm dự án</span>
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>
                @endif


                {{-- Products Menu --}}
                @if ($AuthPermission->checkHeader('ProductController') ||
                    $AuthPermission->checkHeader('ProductController/index') ||
                    $AuthPermission->checkHeader('ProductController/add') ||
                    $AuthPermission->checkHeader('ProductController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/products*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm002.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M21 10H13V11C13 11.6 12.6 12 12 12C11.4 12 11 11.6 11 11V10H3C2.4 10 2 10.4 2 11V13C2 13.6 2.4 14 3 14H21C21.6 14 22 13.6 22 13V11C22 10.4 21.6 10 21 10Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M12 12C11.4 12 11 11.6 11 11V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V11C13 11.6 12.6 12 12 12Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M18.1 21H5.9C5.4 21 4.9 20.6 4.8 20.1L3 13H21L19.2 20.1C19.1 20.6 18.6 21 18.1 21ZM13 18V15C13 14.4 12.6 14 12 14C11.4 14 11 14.4 11 15V18C11 18.6 11.4 19 12 19C12.6 19 13 18.6 13 18ZM17 18V15C17 14.4 16.6 14 16 14C15.4 14 15 14.4 15 15V18C15 18.6 15.4 19 16 19C16.6 19 17 18.6 17 18ZM9 18V15C9 14.4 8.6 14 8 14C7.4 14 7 14.4 7 15V18C7 18.6 7.4 19 8 19C8.6 19 9 18.6 9 18Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('product.products') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('ProductController') || $AuthPermission->isHeader('ProductController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/products') ? 'active' : '' }}"
                                        href="{{ route('admin.products.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('product.product_list') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('ProductController') || $AuthPermission->isHeader('ProductController/add'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/products/add') ? 'active' : '' }}"
                                        href="{{ route('admin.products.add') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('product.add_product') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('ProductController') || $AuthPermission->isHeader('ProductController/add'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/products/import*') ? 'active' : '' }}"
                                        href="{{ route('admin.products.import.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('product.import_products') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('ProductCategoryController') || $AuthPermission->isHeader('ProductCategoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/product-categories*') ? 'active' : '' }}"
                                        href="{{ route('admin.product-categories.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('product_category.product_categories') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($AuthPermission->checkHeader('CommentController') ||
                    $AuthPermission->checkHeader('CommentController/index') ||
                    $AuthPermission->checkHeader('CommentController/add') ||
                    $AuthPermission->checkHeader('CommentController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('comment*') ? 'here show' : '' }}">
                        <a href="{{ route('admin.comment.list') }}" class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M20 3H4C2.89543 3 2 3.89543 2 5V16C2 17.1046 2.89543 18 4 18H4.5C5.05228 18 5.5 18.4477 5.5 19V21.5052C5.5 22.1441 6.21212 22.5253 6.74376 22.1708L11.4885 19.0077C12.4741 18.3506 13.6321 18 14.8167 18H20C21.1046 18 22 17.1046 22 16V5C22 3.89543 21.1046 3 20 3Z"
                                            fill="currentColor" />
                                        <rect x="6" y="12" width="7" height="2"
                                            rx="1" fill="currentColor" />
                                        <rect x="6" y="7" width="12" height="2"
                                            rx="1" fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Bình luận</span>

                        </a>
                    </div>
                @endif

                {{-- Inventory Management Menu --}}
                @if ($AuthPermission->checkHeader('InventoryController') ||
                    $AuthPermission->checkHeader('InventoryController/index') ||
                    $AuthPermission->checkHeader('SupplierController') ||
                    $AuthPermission->checkHeader('SupplierController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/inventory*', '*/supplier*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm008.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.19996C3.40001 1.99996 3 2.20001 3 2.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z"
                                            fill="currentColor" />
                                        <path
                                            d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998ZM13 7.59998C13 6.99998 12.6 6.59998 12 6.59998H11C10.4 6.59998 10 6.99998 10 7.59998C10 8.19998 10.4 8.59998 11 8.59998H12C12.6 8.59998 13 8.19998 13 7.59998ZM13 15.6C13 15 12.6 14.6 12 14.6H11C10.4 14.6 10 15 10 15.6C10 16.2 10.4 16.6 11 16.6H12C12.6 16.6 13 16.2 13 15.6ZM9 15.6C9 15 8.6 14.6 8 14.6H6C5.4 14.6 5 15 5 15.6C5 16.2 5.4 16.6 6 16.6H8C8.6 16.6 9 16.2 9 15.6Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Quản lý kho hàng</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('InventoryController') || $AuthPermission->isHeader('InventoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/inventory') ? 'active' : '' }}"
                                        href="{{ route('admin.inventory.dashboard') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Tổng quan tồn kho</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('InventoryController') || $AuthPermission->isHeader('InventoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/inventory/transactions') ? 'active' : '' }}"
                                        href="{{ route('admin.inventory.transactions') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Lịch sử giao dịch</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('InventoryController') || $AuthPermission->isHeader('InventoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/inventory/import') ? 'active' : '' }}"
                                        href="{{ route('admin.inventory.import') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Nhập hàng</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('InventoryController') || $AuthPermission->isHeader('InventoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/inventory/export') ? 'active' : '' }}"
                                        href="{{ route('admin.inventory.export') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Xuất hàng</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('InventoryController') || $AuthPermission->isHeader('InventoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/inventory/adjustment') ? 'active' : '' }}"
                                        href="{{ route('admin.inventory.adjustment') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Điều chỉnh tồn kho</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('InventoryController') || $AuthPermission->isHeader('InventoryController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/inventory/import-export') ? 'active' : '' }}"
                                        href="{{ route('admin.inventory.import-export.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('inventory_import_export.inventory_import_export') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('SupplierController') || $AuthPermission->isHeader('SupplierController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/supplier*') ? 'active' : '' }}"
                                        href="{{ route('admin.supplier.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Nhà cung cấp</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Quick Order Menu --}}
                @if ($AuthPermission->checkHeader('OrderController/add'))
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('*/quick-order*') ? 'active' : '' }}"
                            href="{{ route('admin.quick-order.index') }}">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm008.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.19996C3.40001 1.99996 3 2.20001 3 2.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z"
                                            fill="currentColor" />
                                        <path
                                            d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998ZM13 7.59998C13 6.99998 12.6 6.59998 12 6.59998H11C10.4 6.59998 10 6.99998 10 7.59998C10 8.19998 10.4 8.59998 11 8.59998H12C12.6 8.59998 13 8.19998 13 7.59998ZM13 15.6C13 15 12.6 14.6 12 14.6H11C10.4 14.6 10 15 10 15.6C10 16.2 10.4 16.6 11 16.6H12C12.6 16.6 13 16.2 13 15.6ZM9 15.6C9 15 8.6 14.6 8 14.6H6C5.4 14.6 5 15 5 15.6C5 16.2 5.4 16.6 6 16.6H8C8.6 16.6 9 16.2 9 15.6Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('order.quick_order') }}</span>
                            <span class="badge badge-light-success ms-auto">{{ __('order.pos') }}</span>
                        </a>
                    </div>
                @endif

                {{-- Orders Management Menu --}}
                @if ($AuthPermission->checkHeader('OrderController') ||
                    $AuthPermission->checkHeader('OrderController/index') ||
                    $AuthPermission->checkHeader('OrderController/add') ||
                    $AuthPermission->checkHeader('OrderController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/order*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm001.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M18.041 22.041C18.5932 22.041 19.041 21.5932 19.041 21.041C19.041 20.4887 18.5932 20.041 18.041 20.041C17.4887 20.041 17.041 20.4887 17.041 21.041C17.041 21.5932 17.4887 22.041 18.041 22.041Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M6.04095 22.041C6.59324 22.041 7.04095 21.5932 7.04095 21.041C7.04095 20.4887 6.59324 20.041 6.04095 20.041C5.48867 20.041 5.04095 20.4887 5.04095 21.041C5.04095 21.5932 5.48867 22.041 6.04095 22.041Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M7.04095 16.041L19.1409 15.1409C19.7409 15.1409 20.141 14.7409 20.341 14.1409L21.7409 8.34094C21.9409 7.64094 21.4409 7.04095 20.7409 7.04095H5.44095L5.24095 5.941C5.14095 5.341 4.54095 4.941 3.94095 4.941H2.04095C1.54095 4.941 1.04095 5.341 1.04095 5.941C1.04095 6.441 1.44095 6.941 2.04095 6.941H3.14095L4.04095 14.041C4.14095 14.641 4.74095 15.041 5.34095 15.041H6.04095V16.041Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Quản lý đơn hàng</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('OrderController') || $AuthPermission->isHeader('OrderController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/order') ? 'active' : '' }}"
                                        href="{{ route('admin.order.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Danh sách đơn hàng</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('OrderController') || $AuthPermission->isHeader('OrderController/add'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/order/add') ? 'active' : '' }}"
                                        href="{{ route('admin.order.add') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Tạo đơn hàng</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('OrderController') || $AuthPermission->isHeader('OrderController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/order/statistics') ? 'active' : '' }}"
                                        href="{{ route('admin.order.statistics') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Thống kê đơn hàng</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Customers Menu --}}
                @if ($AuthPermission->checkHeader('CustomerController') ||
                    $AuthPermission->checkHeader('CustomerController/index'))
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('*/customers*') ? 'active' : '' }}"
                            href="{{ route('admin.customers.index') }}">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/communication/com006.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M22 12C22 17.5 17.5 22 12 22S2 17.5 2 12S6.5 2 12 2S22 6.5 22 12ZM12 7C10.3 7 9 8.3 9 10C9 11.7 10.3 13 12 13C13.7 13 15 11.7 15 10C15 8.3 13.7 7 12 7Z"
                                            fill="currentColor" />
                                        <path
                                            d="M12 22C14.6 22 17 21 18.7 19.4C17.9 16.9 15.2 15 12 15C8.8 15 6.1 16.9 5.3 19.4C7 21 9.4 22 12 22Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('customer.customers') }}</span>
                        </a>
                    </div>
                @endif

                {{-- Transaction Management Menu --}}
                @if ($AuthPermission->checkHeader('InvoiceController') ||
                    $AuthPermission->checkHeader('InvoiceController/index') ||
                    $AuthPermission->checkHeader('ReturnController') ||
                    $AuthPermission->checkHeader('ReturnController/index') ||
                    $AuthPermission->checkHeader('PaymentController') ||
                    $AuthPermission->checkHeader('PaymentController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/invoice*', '*/returns*', '*/payment*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm002.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.19996C3.40001 1.99996 3 2.20001 3 2.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z"
                                            fill="currentColor" />
                                        <path
                                            d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998ZM13 7.59998C13 6.99998 12.6 6.59998 12 6.59998H11C10.4 6.59998 10 6.99998 10 7.59998C10 8.19998 10.4 8.59998 11 8.59998H12C12.6 8.59998 13 8.19998 13 7.59998ZM13 15.6C13 15 12.6 14.6 12 14.6H11C10.4 14.6 10 15 10 15.6C10 16.2 10.4 16.6 11 16.6H12C12.6 16.6 13 16.2 13 15.6ZM9 15.6C9 15 8.6 14.6 8 14.6H6C5.4 14.6 5 15 5 15.6C5 16.2 5.4 16.6 6 16.6H8C8.6 16.6 9 16.2 9 15.6Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Quản lý giao dịch</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('InvoiceController') || $AuthPermission->isHeader('InvoiceController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/invoice') ? 'active' : '' }}"
                                        href="{{ route('admin.invoice.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Hóa đơn</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('ReturnController') || $AuthPermission->isHeader('ReturnController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/returns') ? 'active' : '' }}"
                                        href="{{ route('admin.return.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Trả hàng</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('PaymentController') || $AuthPermission->isHeader('PaymentController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/payment') ? 'active' : '' }}"
                                        href="{{ route('admin.payment.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Thu chi</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Branch Shops Management Menu --}}
                @if ($AuthPermission->checkHeader('BranchShopController') ||
                    $AuthPermission->checkHeader('BranchShopController/index') ||
                    $AuthPermission->checkHeader('BranchShopController/create') ||
                    $AuthPermission->checkHeader('BranchShopController/edit'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/branch-shops*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/maps/map001.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M6 22H4V3C4 2.4 4.4 2 5 2C5.6 2 6 2.4 6 3V22Z"
                                            fill="currentColor" />
                                        <path
                                            d="M18 14H4V4H18C18.8 4 19.2 4.9 18.7 5.5L16 9L18.8 12.5C19.3 13.1 18.8 14 18 14Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('branch_shop.branch_shop_management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('BranchShopController') || $AuthPermission->isHeader('BranchShopController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/branch-shops') ? 'active' : '' }}"
                                        href="{{ route('admin.branch-shops.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('branch_shop.branch_shop_list') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('BranchShopController') || $AuthPermission->isHeader('BranchShopController/create'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/branch-shops/create') ? 'active' : '' }}"
                                        href="{{ route('admin.branch-shops.create') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('branch_shop.add_branch_shop') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('BranchShopController') || $AuthPermission->isHeader('BranchShopController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/branch-shops/statistics') ? 'active' : '' }}"
                                        href="{{ route('admin.branch-shops.statistics') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('branch_shop.statistics') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Reports and Analytics Menu --}}
                @if ($AuthPermission->checkHeader('ReportsController') ||
                    $AuthPermission->checkHeader('ReportsController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/reports*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <rect x="2" y="6" width="20" height="12" rx="2" fill="currentColor"/>
                                        <rect x="4" y="8" width="16" height="8" rx="1" fill="white"/>
                                        <path d="M7 12L10 9L13 12L17 8" stroke="currentColor" stroke-width="2"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('reports.reports_analytics') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('ReportsController') || $AuthPermission->isHeader('ReportsController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/reports') ? 'active' : '' }}"
                                        href="{{ route('admin.reports.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('reports.dashboard') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Shopee Integration Menu --}}
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ Request::is('*/shopee*') ? 'here show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm009.svg-->
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3"
                                        d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.19996C3.40001 1.99996 3 2.20001 3 2.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z"
                                        fill="currentColor" />
                                    <path
                                        d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998ZM13 7.59998C13 6.99998 12.6 6.59998 12 6.59998H11C10.4 6.59998 10 6.99998 10 7.59998C10 8.19998 10.4 8.59998 11 8.59998H12C12.6 8.59998 13 8.19998 13 7.59998ZM13 15.6C13 15 12.6 14.6 12 14.6H11C10.4 14.6 10 15 10 15.6C10 16.2 10.4 16.6 11 16.6H12C12.6 16.6 13 16.2 13 15.6ZM9 15.6C9 15 8.6 14.6 8 14.6H6C5.4 14.6 5 15 5 15.6C5 16.2 5.4 16.6 6 16.6H8C8.6 16.6 9 16.2 9 15.6Z"
                                        fill="currentColor" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">{{ __('Shopee Integration') }}</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('*/shopee/dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.shopee.dashboard') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ __('Dashboard') }}</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ Request::is('*/shopee/connect') ? 'active' : '' }}"
                                href="{{ route('admin.shopee.connect') }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ __('Connect Shop') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Backup and Restore Menu --}}
                @if ($AuthPermission->checkHeader('BackupController') ||
                    $AuthPermission->checkHeader('BackupController/index'))
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('*/backup*') ? 'active' : '' }}"
                            href="{{ route('admin.backup.index') }}">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen006.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M22 5V19C22 19.6 21.6 20 21 20H19.5L11.9 12.4C11.5 12 10.9 12 10.5 12.4L3 20C2.5 20 2 19.5 2 19V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5ZM7.5 7C6.7 7 6 7.7 6 8.5C6 9.3 6.7 10 7.5 10C8.3 10 9 9.3 9 8.5C9 7.7 8.3 7 7.5 7Z"
                                            fill="currentColor" />
                                        <path
                                            d="M19.5 20L11.9 12.4C11.5 12 10.9 12 10.5 12.4L3 20H19.5Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('backup.backup_restore') }}</span>
                        </a>
                    </div>
                @endif

                {{-- Audit Logs Menu --}}
                @if ($AuthPermission->checkHeader('AuditLogController') ||
                    $AuthPermission->checkHeader('AuditLogController/index'))
                    <div class="menu-item">
                        <a class="menu-link {{ Request::is('*/audit-logs*') ? 'active' : '' }}"
                            href="{{ route('admin.audit-logs.index') }}">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M12 22C13.6569 22 15 20.6569 15 19C15 17.3431 13.6569 16 12 16C10.3431 16 9 17.3431 9 19C9 20.6569 10.3431 22 12 22Z"
                                            fill="currentColor" />
                                        <path
                                            d="M19 15V18C19 18.6 18.6 19 18 19H6C5.4 19 5 18.6 5 18V15C6.1 15 7 14.1 7 13V10C7 7.6 8.7 5.6 11 5.1V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V5.1C15.3 5.6 17 7.6 17 10V13C17 14.1 17.9 15 19 15Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">{{ __('audit_log.audit_logs') }}</span>
                        </a>
                    </div>
                @endif

                @if ($AuthPermission->checkHeader('UsersController') ||
                    $AuthPermission->checkHeader('UsersController/index') ||
                    $AuthPermission->checkHeader('MyProfileController') ||
                    $AuthPermission->checkHeader('MyProfileController/index') ||
                    $AuthPermission->checkHeader('AgentsController') ||
                    $AuthPermission->checkHeader('AgentsController/index') ||
                    $AuthPermission->checkHeader('GuestsController') ||
                    $AuthPermission->checkHeader('GuestsController/index') ||
                    $AuthPermission->checkHeader('RoleController') ||
                    $AuthPermission->checkHeader('RoleController/index') ||
                    $AuthPermission->checkHeader('PermissionController') ||
                    $AuthPermission->checkHeader('PermissionController/index') ||
                    $AuthPermission->checkHeader('LogsUserController') ||
                    $AuthPermission->checkHeader('LogsUserController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('*/user*', '*/agent*', '*/guest*', '*/logs-user*', '*/roles*', '*/permissions*', '*/my-profile', '*/change-password') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span
                                    class="svg-icon svg-icon-2 {{ Request::is('*/user*', '*/agent*', '*/guest*', '*/logs-user*', '*/roles*', '*/permissions*', '*/my-profile', '*/change-password') ? 'svg-icon-info' : '' }}">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M16.0173 9H15.3945C14.2833 9 13.263 9.61425 12.7431 10.5963L12.154 11.7091C12.0645 11.8781 12.1072 12.0868 12.2559 12.2071L12.6402 12.5183C13.2631 13.0225 13.7556 13.6691 14.0764 14.4035L14.2321 14.7601C14.2957 14.9058 14.4396 15 14.5987 15H18.6747C19.7297 15 20.4057 13.8774 19.912 12.945L18.6686 10.5963C18.1487 9.61425 17.1285 9 16.0173 9Z"
                                            fill="currentColor" />
                                        <rect opacity="0.3" x="14" y="4" width="4"
                                            height="4" rx="2" fill="currentColor" />
                                        <path
                                            d="M4.65486 14.8559C5.40389 13.1224 7.11161 12 9 12C10.8884 12 12.5961 13.1224 13.3451 14.8559L14.793 18.2067C15.3636 19.5271 14.3955 21 12.9571 21H5.04292C3.60453 21 2.63644 19.5271 3.20698 18.2067L4.65486 14.8559Z"
                                            fill="currentColor" />
                                        <rect opacity="0.3" x="6" y="5" width="6"
                                            height="6" rx="3" fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Tài khoản</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('MyProfileController') || $AuthPermission->isHeader('MyProfileController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/my-profile', '*/change-password') ? 'active' : '' }}"
                                        href="{{ route('admin.profile') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Thông tin tài khoản</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('UsersController') || $AuthPermission->isHeader('UsersController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/user', '*/user/add', '*/user/edit', '*/user/detail*') ? 'active' : '' }}"
                                        href="{{ route('admin.user.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Tài khoản hệ thống</span>
                                    </a>
                                </div>
                            @endif
                           
                            {{-- @if ($AuthPermission->isHeader('GuestsController') || $AuthPermission->isHeader('GuestsController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*/guest*') ? 'active' : '' }}"
                                        href="{{ route('guest.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Tài khoản người dùng</span>
                                    </a>
                                </div>
                            @endif --}}
                            @if ($AuthPermission->isHeader('RoleController') || $AuthPermission->isHeader('RoleController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*roles*') ? 'active' : '' }}"
                                        href="{{ route('admin.roles.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('roles.title') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('PermissionController') || $AuthPermission->isHeader('PermissionController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('*permissions*') ? 'active' : '' }}"
                                        href="{{ route('admin.permissions.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('permissions.title') }}</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('LogsUserController') || $AuthPermission->isHeader('LogsUserController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('logs-user*') ? 'active' : '' }}"
                                        href="{{ route('admin.logs_user.list') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Logs User</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($AuthPermission->checkHeader('SubcribeEmailsController') ||
                    $AuthPermission->checkHeader('SubcribeEmailsController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('subcribe-emails*') ? 'here show' : '' }}">
                        <a href="{{ route('admin.subcribe_email.list') }}" class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M20 14H18V10H20C20.6 10 21 10.4 21 11V13C21 13.6 20.6 14 20 14ZM21 19V17C21 16.4 20.6 16 20 16H18V20H20C20.6 20 21 19.6 21 19ZM21 7V5C21 4.4 20.6 4 20 4H18V8H20C20.6 8 21 7.6 21 7Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M17 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H17C17.6 2 18 2.4 18 3V21C18 21.6 17.6 22 17 22ZM10 7C8.9 7 8 7.9 8 9C8 10.1 8.9 11 10 11C11.1 11 12 10.1 12 9C12 7.9 11.1 7 10 7ZM13.3 16C14 16 14.5 15.3 14.3 14.7C13.7 13.2 12 12 10.1 12C8.10001 12 6.49999 13.1 5.89999 14.7C5.59999 15.3 6.19999 16 7.39999 16H13.3Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Subcribe Emails</span>
                        </a>
                    </div>
                @endif
                @if ($AuthPermission->checkHeader('ContactController') || $AuthPermission->checkHeader('ContactController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('contact*') ? 'here show' : '' }}">
                        <a href="{{ route('admin.contact.index') }}" class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M6 8.725C6 8.125 6.4 7.725 7 7.725H14L18 11.725V12.925L22 9.725L12.6 2.225C12.2 1.925 11.7 1.925 11.4 2.225L2 9.725L6 12.925V8.725Z"
                                            fill="currentColor" />
                                        <path opacity="0.3"
                                            d="M22 9.72498V20.725C22 21.325 21.6 21.725 21 21.725H3C2.4 21.725 2 21.325 2 20.725V9.72498L11.4 17.225C11.8 17.525 12.3 17.525 12.6 17.225L22 9.72498ZM15 11.725H18L14 7.72498V10.725C14 11.325 14.4 11.725 15 11.725Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Liên hệ</span>
                        </a>
                    </div>
                @endif
                @if ($AuthPermission->checkHeader('MenuController') ||
                    $AuthPermission->checkHeader('MenuController/index') ||
                    $AuthPermission->checkHeader('ThemeOptionsController') ||
                    $AuthPermission->checkHeader('ThemeOptionsController/option') ||
                    $AuthPermission->checkHeader('CustomCssController') ||
                    $AuthPermission->checkHeader('CustomCssController/index'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('menus*', 'custom-css*', 'theme-option*', 'template*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M20.859 12.596L17.736 13.596L10.388 20.944C10.2915 21.0406 10.1769 21.1172 10.0508 21.1695C9.9247 21.2218 9.78953 21.2486 9.65302 21.2486C9.5165 21.2486 9.3813 21.2218 9.25519 21.1695C9.12907 21.1172 9.01449 21.0406 8.918 20.944L2.29999 14.3229C2.10543 14.1278 1.99619 13.8635 1.99619 13.588C1.99619 13.3124 2.10543 13.0481 2.29999 12.853L11.853 3.29999C11.9495 3.20341 12.0641 3.12679 12.1902 3.07452C12.3163 3.02225 12.4515 2.9953 12.588 2.9953C12.7245 2.9953 12.8597 3.02225 12.9858 3.07452C13.1119 3.12679 13.2265 3.20341 13.323 3.29999L21.199 11.176C21.3036 11.2791 21.3797 11.4075 21.4201 11.5486C21.4605 11.6898 21.4637 11.8391 21.4295 11.9819C21.3953 12.1247 21.3249 12.2562 21.2249 12.3638C21.125 12.4714 20.9989 12.5514 20.859 12.596Z"
                                            fill="currentColor" />
                                        <path
                                            d="M14.8 10.184C14.7447 10.1843 14.6895 10.1796 14.635 10.1699L5.816 8.69997C5.55436 8.65634 5.32077 8.51055 5.16661 8.29469C5.01246 8.07884 4.95035 7.8106 4.99397 7.54897C5.0376 7.28733 5.18339 7.05371 5.39925 6.89955C5.6151 6.7454 5.88334 6.68332 6.14498 6.72694L14.963 8.19692C15.2112 8.23733 15.435 8.36982 15.59 8.56789C15.7449 8.76596 15.8195 9.01502 15.7989 9.26564C15.7784 9.51626 15.6642 9.75001 15.479 9.92018C15.2939 10.0904 15.0514 10.1846 14.8 10.184ZM17 18.6229C17 19.0281 17.0985 19.4272 17.287 19.7859C17.4755 20.1446 17.7484 20.4521 18.0821 20.6819C18.4158 20.9117 18.8004 21.0571 19.2027 21.1052C19.605 21.1534 20.0131 21.103 20.3916 20.9585C20.7702 20.814 21.1079 20.5797 21.3758 20.2757C21.6437 19.9716 21.8336 19.607 21.9293 19.2133C22.025 18.8195 22.0235 18.4085 21.925 18.0154C21.8266 17.6223 21.634 17.259 21.364 16.9569L19.843 15.257C19.7999 15.2085 19.7471 15.1697 19.688 15.1432C19.6289 15.1167 19.5648 15.1029 19.5 15.1029C19.4352 15.1029 19.3711 15.1167 19.312 15.1432C19.2529 15.1697 19.2001 15.2085 19.157 15.257L17.636 16.9569C17.2254 17.4146 16.9988 18.0081 17 18.6229ZM10.388 20.9409L17.736 13.5929H1.99999C1.99921 13.7291 2.02532 13.8643 2.0768 13.9904C2.12828 14.1165 2.2041 14.2311 2.29997 14.3279L8.91399 20.9409C9.01055 21.0381 9.12539 21.1152 9.25188 21.1679C9.37836 21.2205 9.51399 21.2476 9.65099 21.2476C9.78798 21.2476 9.92361 21.2205 10.0501 21.1679C10.1766 21.1152 10.2914 21.0381 10.388 20.9409Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Giao diện</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            @if ($AuthPermission->isHeader('MenusController') || $AuthPermission->isHeader('MenusController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('my-profile', 'change-password') ? 'active' : '' }}"
                                        href="{{ route('admin.menu.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Quản lý Menu</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('ThemeOptionsController') ||
                                $AuthPermission->isHeader('ThemeOptionsController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('theme-option') ? 'active' : '' }}"
                                        href="{{ route('admin.theme_option.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Tuỳ biến</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('CustomCssController') || $AuthPermission->isHeader('CustomCssController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('custom-css') ? 'active' : '' }}"
                                        href="{{ route('admin.custom_css.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Tuỳ chỉnh CSS</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('TemplateController') || $AuthPermission->isHeader('TemplateController/index'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('template') ? 'active' : '' }}"
                                        href="{{ route('admin.template.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Template</span>
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>
                @endif
                {{-- @if ($AuthPermission->checkHeader('MultiLanguageController'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('language*') ? 'here show' : '' }}">
                        <a href="{{ route('language.index') }}" class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M18.4 5.59998C21.9 9.09998 21.9 14.8 18.4 18.3C14.9 21.8 9.2 21.8 5.7 18.3L18.4 5.59998Z"
                                            fill="currentColor" />
                                        <path
                                            d="M12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2ZM19.9 11H13V8.8999C14.9 8.6999 16.7 8.00005 18.1 6.80005C19.1 8.00005 19.7 9.4 19.9 11ZM11 19.8999C9.7 19.6999 8.39999 19.2 7.39999 18.5C8.49999 17.7 9.7 17.2001 11 17.1001V19.8999ZM5.89999 6.90002C7.39999 8.10002 9.2 8.8 11 9V11.1001H4.10001C4.30001 9.4001 4.89999 8.00002 5.89999 6.90002ZM7.39999 5.5C8.49999 4.7 9.7 4.19998 11 4.09998V7C9.7 6.8 8.39999 6.3 7.39999 5.5ZM13 17.1001C14.3 17.3001 15.6 17.8 16.6 18.5C15.5 19.3 14.3 19.7999 13 19.8999V17.1001ZM13 4.09998C14.3 4.29998 15.6 4.8 16.6 5.5C15.5 6.3 14.3 6.80002 13 6.90002V4.09998ZM4.10001 13H11V15.1001C9.1 15.3001 7.29999 16 5.89999 17.2C4.89999 16 4.30001 14.6 4.10001 13ZM18.1 17.1001C16.6 15.9001 14.8 15.2 13 15V12.8999H19.9C19.7 14.5999 19.1 16.0001 18.1 17.1001Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Đa ngôn ngữ</span>
                        </a>
                    </div>
                @endif --}}
                @if ($AuthPermission->checkHeader('SettingController'))
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ Request::is('settings*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <!--begin::Svg Icon | path: icons/duotune/ecommerce/ecm007.svg-->
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3"
                                            d="M22.1 11.5V12.6C22.1 13.2 21.7 13.6 21.2 13.7L19.9 13.9C19.7 14.7 19.4 15.5 18.9 16.2L19.7 17.2999C20 17.6999 20 18.3999 19.6 18.7999L18.8 19.6C18.4 20 17.8 20 17.3 19.7L16.2 18.9C15.5 19.3 14.7 19.7 13.9 19.9L13.7 21.2C13.6 21.7 13.1 22.1 12.6 22.1H11.5C10.9 22.1 10.5 21.7 10.4 21.2L10.2 19.9C9.4 19.7 8.6 19.4 7.9 18.9L6.8 19.7C6.4 20 5.7 20 5.3 19.6L4.5 18.7999C4.1 18.3999 4.1 17.7999 4.4 17.2999L5.2 16.2C4.8 15.5 4.4 14.7 4.2 13.9L2.9 13.7C2.4 13.6 2 13.1 2 12.6V11.5C2 10.9 2.4 10.5 2.9 10.4L4.2 10.2C4.4 9.39995 4.7 8.60002 5.2 7.90002L4.4 6.79993C4.1 6.39993 4.1 5.69993 4.5 5.29993L5.3 4.5C5.7 4.1 6.3 4.10002 6.8 4.40002L7.9 5.19995C8.6 4.79995 9.4 4.39995 10.2 4.19995L10.4 2.90002C10.5 2.40002 11 2 11.5 2H12.6C13.2 2 13.6 2.40002 13.7 2.90002L13.9 4.19995C14.7 4.39995 15.5 4.69995 16.2 5.19995L17.3 4.40002C17.7 4.10002 18.4 4.1 18.8 4.5L19.6 5.29993C20 5.69993 20 6.29993 19.7 6.79993L18.9 7.90002C19.3 8.60002 19.7 9.39995 19.9 10.2L21.2 10.4C21.7 10.5 22.1 11 22.1 11.5ZM12.1 8.59998C10.2 8.59998 8.6 10.2 8.6 12.1C8.6 14 10.2 15.6 12.1 15.6C14 15.6 15.6 14 15.6 12.1C15.6 10.2 14 8.59998 12.1 8.59998Z"
                                            fill="currentColor" />
                                        <path
                                            d="M17.1 12.1C17.1 14.9 14.9 17.1 12.1 17.1C9.30001 17.1 7.10001 14.9 7.10001 12.1C7.10001 9.29998 9.30001 7.09998 12.1 7.09998C14.9 7.09998 17.1 9.29998 17.1 12.1ZM12.1 10.1C11 10.1 10.1 11 10.1 12.1C10.1 13.2 11 14.1 12.1 14.1C13.2 14.1 14.1 13.2 14.1 12.1C14.1 11 13.2 10.1 12.1 10.1Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                            </span>
                            <span class="menu-title">Cài đặt</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ Request::is('admin/settings') ? 'active' : '' }}"
                                    href="{{ route('admin.settings.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('user_settings.user_settings') }}</span>
                                </a>
                            </div>
                            @if ($AuthPermission->isHeader('SettingController/general'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('settings-general') ? 'active' : '' }}"
                                        href="{{ route('admin.setting.general') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Cài đặt chung</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('SettingController/email'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('settings-email') ? 'active' : '' }}"
                                        href="{{ route('admin.setting.email') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Cài đặt Email</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('SettingController/loginSocial'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('settings-social-login') ? 'active' : '' }}"
                                        href="{{ route('admin.setting.login_social') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Login Social</span>
                                    </a>
                                </div>
                            @endif
                            @if ($AuthPermission->isHeader('SettingController/notification'))
                                <div class="menu-item">
                                    <a class="menu-link {{ Request::is('settings-notification') ? 'active' : '' }}"
                                        href="{{ route('admin.setting.notification') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Thông báo</span>
                                    </a>
                                </div>
                            @endif
                            <div class="menu-item">
                                <a class="menu-link {{ Request::is('notification-settings*') ? 'active' : '' }}"
                                    href="{{ route('admin.notification-settings.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Cài đặt thông báo cá nhân</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>
<!-- end:: Aside Menu -->
