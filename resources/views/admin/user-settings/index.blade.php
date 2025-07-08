@extends('admin.index')
@section('page-header', __('user_settings.title'))
@section('page-sub_header', __('user_settings.subtitle'))

@section('content')
<div class="row g-6 g-xl-9">
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ __('user_settings.appearance.title') }}</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Theme Mode-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.appearance.theme') }}</label>
                    <div class="d-flex flex-column">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="radio" name="theme_mode" value="light" 
                                {{ (auth()->user()->getSetting('theme_mode', 'light') === 'light') ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                <i class="ki-duotone ki-sun fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                {{ __('user_settings.appearance.light') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="radio" name="theme_mode" value="dark"
                                {{ (auth()->user()->getSetting('theme_mode', 'light') === 'dark') ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                <i class="ki-duotone ki-moon fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                {{ __('user_settings.appearance.dark') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" name="theme_mode" value="system"
                                {{ (auth()->user()->getSetting('theme_mode', 'light') === 'system') ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                <i class="ki-duotone ki-screen fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                {{ __('user_settings.appearance.system') }}
                            </span>
                        </label>
                    </div>
                </div>
                <!--end::Theme Mode-->

                <!--begin::Language-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.language.title') }}</label>
                    <select name="language" class="form-select form-select-solid" data-control="select2">
                        <option value="vi" {{ (auth()->user()->getSetting('language', 'vi') === 'vi') ? 'selected' : '' }}>
                            🇻🇳 {{ __('user_settings.language.vietnamese') }}
                        </option>
                        <option value="en" {{ (auth()->user()->getSetting('language', 'vi') === 'en') ? 'selected' : '' }}>
                            🇺🇸 {{ __('user_settings.language.english') }}
                        </option>
                    </select>
                </div>
                <!--end::Language-->

                <!--begin::Branch Shop-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.branch_shop.title') }}</label>
                    <select name="default_branch_shop" class="form-select form-select-solid" data-control="select2">
                        <option value="">{{ __('user_settings.branch_shop.select') }}</option>
                        @foreach(\App\Models\BranchShop::with('warehouse')->active()->orderBy('sort_order')->get() as $branch)
                            <option value="{{ $branch->id }}"
                                data-warehouse="{{ $branch->warehouse ? $branch->warehouse->name : 'Chưa gán kho' }}"
                                data-address="{{ $branch->full_address }}"
                                data-delivery="{{ $branch->has_delivery ? 'Có giao hàng' : 'Không giao hàng' }}"
                                {{ (auth()->user()->getSetting('default_branch_shop') == $branch->id) ? 'selected' : '' }}>
                                {{ $branch->name }} - {{ $branch->shop_type_label }}
                                @if($branch->warehouse)
                                    (Kho: {{ $branch->warehouse->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('user_settings.branch_shop.description') }}</div>

                    <!-- Branch Shop Info Display -->
                    <div id="branch-shop-info" class="mt-4" style="display: none;">
                        <div class="card card-bordered">
                            <div class="card-body p-4">
                                <h6 class="card-title">Thông tin chi nhánh</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ki-duotone ki-geolocation fs-4 text-primary me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <span id="branch-address" class="text-muted"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ki-duotone ki-delivery fs-4 text-success me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <span id="branch-delivery" class="text-muted"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-package fs-4 text-warning me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            <span id="branch-warehouse" class="text-muted"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Branch Shop-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ __('user_settings.notifications.title') }}</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Email Notifications-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.notifications.email') }}</label>
                    <div class="d-flex flex-column">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="email_order_created" value="1"
                                {{ auth()->user()->getSetting('email_order_created', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.notifications.order_created') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="email_inventory_low" value="1"
                                {{ auth()->user()->getSetting('email_inventory_low', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.notifications.inventory_low') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="email_system_updates" value="1"
                                {{ auth()->user()->getSetting('email_system_updates', false) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.notifications.system_updates') }}
                            </span>
                        </label>
                    </div>
                </div>
                <!--end::Email Notifications-->

                <!--begin::Web Notifications-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.notifications.web') }}</label>
                    <div class="d-flex flex-column">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="web_notifications" value="1"
                                {{ auth()->user()->getSetting('web_notifications', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.notifications.enable_web') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="notification_sound" value="1"
                                {{ auth()->user()->getSetting('notification_sound', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.notifications.sound') }}
                            </span>
                        </label>
                    </div>
                </div>
                <!--end::Web Notifications-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ __('user_settings.dashboard.title') }}</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Dashboard Widgets-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.dashboard.widgets') }}</label>
                    <div class="d-flex flex-column">
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="widget_sales_today" value="1"
                                {{ auth()->user()->getSetting('widget_sales_today', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.dashboard.sales_today') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="widget_revenue_chart" value="1"
                                {{ auth()->user()->getSetting('widget_revenue_chart', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.dashboard.revenue_chart') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="widget_top_products" value="1"
                                {{ auth()->user()->getSetting('widget_top_products', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.dashboard.top_products') }}
                            </span>
                        </label>
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="widget_recent_activities" value="1"
                                {{ auth()->user()->getSetting('widget_recent_activities', true) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold">
                                {{ __('user_settings.dashboard.recent_activities') }}
                            </span>
                        </label>
                    </div>
                </div>
                <!--end::Dashboard Widgets-->

                <!--begin::Data Display-->
                <div class="mb-8">
                    <label class="fs-6 fw-bold mb-3">{{ __('user_settings.dashboard.data_display') }}</label>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('user_settings.dashboard.items_per_page') }}</label>
                            <select name="items_per_page" class="form-select form-select-solid">
                                <option value="10" {{ (auth()->user()->getSetting('items_per_page', 25) == 10) ? 'selected' : '' }}>10</option>
                                <option value="25" {{ (auth()->user()->getSetting('items_per_page', 25) == 25) ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (auth()->user()->getSetting('items_per_page', 25) == 50) ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (auth()->user()->getSetting('items_per_page', 25) == 100) ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('user_settings.dashboard.date_format') }}</label>
                            <select name="date_format" class="form-select form-select-solid">
                                <option value="d/m/Y" {{ (auth()->user()->getSetting('date_format', 'd/m/Y') === 'd/m/Y') ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="m/d/Y" {{ (auth()->user()->getSetting('date_format', 'd/m/Y') === 'm/d/Y') ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="Y-m-d" {{ (auth()->user()->getSetting('date_format', 'd/m/Y') === 'Y-m-d') ? 'selected' : '' }}>YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!--end::Data Display-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>

<!--begin::Actions-->
<div class="row mt-8">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <button type="button" id="save-settings" class="btn btn-primary me-3">
                    <i class="ki-duotone ki-check fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('user_settings.save') }}
                </button>
                <button type="button" id="reset-settings" class="btn btn-light-danger">
                    <i class="ki-duotone ki-arrows-circle fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('user_settings.reset') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Actions-->
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/apps/user-settings/settings.js') }}"></script>
@endpush
