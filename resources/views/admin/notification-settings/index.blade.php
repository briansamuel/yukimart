@extends('admin.layouts.app')

@section('title', 'Cài đặt thông báo')

@push('styles')
<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Performance optimized external CSS -->
<link rel="stylesheet" href="{{ asset('admin-assets/css/notification-settings.css') }}" media="all">
<style>
/* Critical inline CSS for above-the-fold content */
.notification-details {
    display: none;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: opacity, transform;
}

.notification-details.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.btn.loading {
    position: relative;
    pointer-events: none;
}

/* Enhanced Two Column Layout */
.notification-settings-container {
    max-width: 100%;
}

.notification-category-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    border: 1px solid #e4e6ea;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.notification-category-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #009ef7 0%, #0bb7ff 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.notification-category-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 158, 247, 0.15);
    border-color: #009ef7;
}

.notification-category-section:hover::before {
    opacity: 1;
}

/* Category-specific styling */
.notification-category-section.category-customers {
    background: linear-gradient(135deg, #ffffff 0%, #f0f8ff 100%);
}

.notification-category-section.category-customers::before {
    background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
}

.notification-category-section.category-customers:hover {
    box-shadow: 0 8px 30px rgba(0, 123, 255, 0.15);
    border-color: #007bff;
}

.notification-category-section.category-cashbook {
    background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
}

.notification-category-section.category-cashbook::before {
    background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%);
}

.notification-category-section.category-cashbook:hover {
    box-shadow: 0 8px 30px rgba(40, 167, 69, 0.15);
    border-color: #28a745;
}

.notification-category-section.category-inventory {
    background: linear-gradient(135deg, #ffffff 0%, #fff8f0 100%);
}

.notification-category-section.category-inventory::before {
    background: linear-gradient(90deg, #fd7e14 0%, #e55a00 100%);
}

.notification-category-section.category-inventory:hover {
    box-shadow: 0 8px 30px rgba(253, 126, 20, 0.15);
    border-color: #fd7e14;
}

.notification-category-section.category-transactions {
    background: linear-gradient(135deg, #ffffff 0%, #f8f0ff 100%);
}

.notification-category-section.category-transactions::before {
    background: linear-gradient(90deg, #6f42c1 0%, #5a2d91 100%);
}

.notification-category-section.category-transactions:hover {
    box-shadow: 0 8px 30px rgba(111, 66, 193, 0.15);
    border-color: #6f42c1;
}

/* Sub-section styling */
.notification-sub-section {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e1e3ea;
    margin-bottom: 1rem;
}

.notification-sub-section-header {
    background: linear-gradient(135deg, #009ef7 0%, #0bb7ff 100%);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    font-size: 0.9rem;
}

.notification-sub-section-content {
    padding: 1rem;
}

/* Enhanced notification items */
.notification-item {
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    border: 1px solid #e1e3ea;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.notification-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #009ef7 0%, #0bb7ff 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.notification-item:hover {
    border-color: #009ef7;
    box-shadow: 0 4px 20px rgba(0, 158, 247, 0.12);
    transform: translateY(-1px);
}

.notification-item:hover::before {
    opacity: 1;
}

.notification-item.with-sub-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
    border: 1px solid #e4e6ea;
    padding: 1rem;
    margin-bottom: 0.75rem;
}

/* Enhanced typography */
.notification-item h5 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.notification-item p {
    font-size: 0.9rem;
    color: #718096;
    line-height: 1.5;
    margin-bottom: 0;
}

.notification-item h6 {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

/* Category headers */
.category-header h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

.category-header p {
    font-size: 1rem;
    color: #4a5568;
    line-height: 1.4;
    margin-bottom: 0;
}

/* Sub-section indicator */
.sub-section-indicator {
    width: 4px;
    height: 30px;
    background: linear-gradient(135deg, #009ef7 0%, #0bb7ff 100%);
    border-radius: 2px;
    margin-right: 0.75rem;
}

/* Category icons enhancement */
.category-icon {
    background: linear-gradient(135deg, #009ef7 0%, #0bb7ff 100%);
    color: white !important;
    border-radius: 10px;
}

/* FontAwesome icon enhancements */
.category-icon i.fas {
    font-size: 1.5rem !important;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.notification-category-section:hover .category-icon i.fas {
    transform: scale(1.1);
}

/* Specific category icon colors */
.category-customers .category-icon {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.category-cashbook .category-icon {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.category-inventory .category-icon {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
}

.category-transactions .category-icon {
    background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
}

/* Loading spinner animation */
.fa-spinner.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Alert icons styling */
.alert .fas {
    vertical-align: middle;
    margin-right: 0.5rem;
}

/* Enhanced Mobile Responsiveness */
@media (max-width: 991.98px) {
    .notification-settings-container .col-lg-6 {
        margin-bottom: 2rem;
    }

    .notification-category-section {
        margin-bottom: 1.5rem;
        padding: 1rem !important;
    }

    .notification-item {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start !important;
    }

    .notification-item .d-flex.align-items-center:last-child {
        margin-top: 1rem;
        width: 100%;
        justify-content: space-between;
    }

    .category-header h3 {
        font-size: 1.25rem;
    }

    .form-check-input {
        width: 2.5rem;
        height: 1.25rem;
    }

    .form-check-input::before {
        width: 0.875rem;
        height: 0.875rem;
    }

    .form-check-input:checked::before {
        transform: translateX(1.25rem);
    }
}

@media (max-width: 767.98px) {
    .notification-category-section {
        padding: 0.75rem !important;
    }

    .notification-item {
        padding: 0.75rem;
    }

    .category-header {
        flex-direction: column;
        align-items: flex-start !important;
        text-align: left;
    }

    .category-header .symbol {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }

    .btn-light-primary, .test-notification-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

/* Accessibility Improvements */
.form-check-input:focus-visible {
    outline: 2px solid #009ef7;
    outline-offset: 2px;
}

.btn:focus-visible {
    outline: 2px solid #009ef7;
    outline-offset: 2px;
}

.notification-item:focus-within {
    border-color: #009ef7;
    box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.1);
}

/* Reduced motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    .notification-category-section,
    .notification-item,
    .form-check-input,
    .btn-light-primary,
    .test-notification-btn {
        transition: none;
    }

    .btn.loading::after {
        animation: none;
    }
}

/* Enhanced switch styling */
.form-check-input {
    width: 3rem;
    height: 1.5rem;
    border-radius: 1rem;
    background-color: #e9ecef;
    border: 2px solid #dee2e6;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.form-check-input:checked {
    background-color: #009ef7;
    border-color: #009ef7;
    background-image: none;
}

.form-check-input:focus {
    border-color: #009ef7;
    box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.25);
    outline: none;
}

.form-check-input:hover {
    border-color: #009ef7;
    transform: scale(1.05);
}

/* Custom switch animation */
.form-check-input::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 1rem;
    height: 1rem;
    background: white;
    border-radius: 50%;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.form-check-input:checked::before {
    transform: translateX(1.5rem);
}

/* Enhanced button styling */
.btn-light-primary {
    background: linear-gradient(135deg, rgba(0, 158, 247, 0.1) 0%, rgba(11, 183, 255, 0.1) 100%);
    border: 1px solid rgba(0, 158, 247, 0.3);
    color: #009ef7;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn-light-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
}

.btn-light-primary:hover {
    background: linear-gradient(135deg, rgba(0, 158, 247, 0.2) 0%, rgba(11, 183, 255, 0.2) 100%);
    border-color: #009ef7;
    color: #007bcc;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 158, 247, 0.3);
}

.btn-light-primary:hover::before {
    left: 100%;
}

.btn-light-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(0, 158, 247, 0.2);
}

/* Test button specific styling */
.test-notification-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.test-notification-btn:hover {
    background: linear-gradient(135deg, #218838 0%, #1ea080 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    color: white;
}

.test-notification-btn:active {
    transform: translateY(0);
}

/* Loading state for buttons */
.btn.loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 1rem;
    height: 1rem;
    margin: -0.5rem 0 0 -0.5rem;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Cài đặt thông báo
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Cài đặt thông báo</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h3 class="fw-bold">Quản lý thông báo</h3>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-light-primary me-3" id="reset-settings-btn">
                            <i class="ki-duotone ki-arrows-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Khôi phục mặc định
                        </button>
                        <button type="button" class="btn btn-primary" id="save-settings-btn">
                            <i class="ki-duotone ki-check fs-2"></i>
                            Lưu cài đặt
                        </button>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <form id="notification-settings-form">
                        @csrf

                        <!--begin::Two Column Layout-->
                        <div class="row g-8 notification-settings-container">
                            @php
                                // Chia categories thành 2 cột với sub-sections
                                $leftColumnCategories = ['customers', 'cashbook', 'inventory'];
                                $rightColumnCategories = ['transactions'];

                                // Định nghĩa sub-sections cho từng category
                                $categorySubSections = [
                                    'transactions' => [
                                        'orders' => ['order_completed', 'order_cancelled'],
                                        'invoices' => ['invoice_completed', 'invoice_cancelled'],
                                        'shipping' => ['shipping_label', 'return_order'],
                                        'inventory' => ['import_goods', 'return_import', 'transfer_goods', 'cancel_transfer']
                                    ],
                                    // Inventory không còn sub-sections
                                ];

                                $priorityCategories = ['customers', 'cashbook', 'inventory', 'transactions'];
                                $otherCategories = array_diff(array_keys($settingsByCategory), $priorityCategories);
                                $orderedCategories = array_merge($priorityCategories, $otherCategories);
                            @endphp

                            <!--begin::Left Column-->
                            <div class="col-lg-6">
                                @foreach($leftColumnCategories as $categoryKey)
                                    @if(isset($settingsByCategory[$categoryKey]))
                                        @php $category = $settingsByCategory[$categoryKey]; @endphp
                                        <!--begin::Category Section-->
                                        <div class="mb-10 notification-category-section category-{{ $categoryKey }} p-6">
                                    <div class="d-flex align-items-center mb-6">
                                        <div class="symbol symbol-40px me-4">
                                            <div class="symbol-label bg-light-{{ in_array($categoryKey, $priorityCategories) ? 'primary' : 'secondary' }}">
                                                @if($categoryKey === 'customers')
                                                    <i class="fas fa-users fs-2 text-white"></i>
                                                @elseif($categoryKey === 'cashbook')
                                                    <i class="fas fa-wallet fs-2 text-white"></i>
                                                @elseif($categoryKey === 'inventory')
                                                    <i class="fas fa-boxes fs-2 text-white"></i>
                                                @elseif($categoryKey === 'transactions')
                                                    <i class="fas fa-shopping-cart fs-2 text-white"></i>
                                                @else
                                                    <i class="fas fa-bell fs-2 text-white"></i>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold text-gray-800 mb-1">{{ $category['name'] }}</h3>
                                            <p class="text-gray-600 mb-0">
                                                @if($categoryKey === 'customers')
                                                    Quản lý thông báo liên quan đến khách hàng
                                                @elseif($categoryKey === 'cashbook')
                                                    Quản lý thông báo về phiếu thu chi và sổ quỹ
                                                @elseif($categoryKey === 'inventory')
                                                    Quản lý thông báo về hàng hóa và tồn kho
                                                @elseif($categoryKey === 'transactions')
                                                    Quản lý thông báo về các giao dịch
                                                @else
                                                    Các thông báo khác
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    @if(isset($categorySubSections[$categoryKey]))
                                        {{-- Category có sub-sections --}}
                                        @foreach($categorySubSections[$categoryKey] as $subSectionKey => $subSectionTypes)
                                            <!--begin::Sub-Section-->
                                            <div class="mb-8">
                                                <div class="d-flex align-items-center mb-4">
                                                    <div class="w-4px h-30px bg-primary rounded me-3"></div>
                                                    <h4 class="fw-bold text-gray-700 mb-0">
                                                        @if($subSectionKey === 'orders')
                                                            🛒 Đặt hàng
                                                        @elseif($subSectionKey === 'invoices')
                                                            📄 Hóa đơn
                                                        @elseif($subSectionKey === 'shipping')
                                                            🚚 Vận chuyển
                                                        @elseif($subSectionKey === 'inventory')
                                                            📦 Kho hàng
                                                        @elseif($subSectionKey === 'stock_alerts')
                                                            ⚠️ Cảnh báo tồn kho
                                                        @else
                                                            {{ ucfirst($subSectionKey) }}
                                                        @endif
                                                    </h4>
                                                </div>

                                                @foreach($subSectionTypes as $typeKey)
                                                    @if(isset($category['types'][$typeKey]))
                                                        @php $typeData = $category['types'][$typeKey]; @endphp
                                                        <!--begin::Notification Item-->
                                                        <div class="d-flex align-items-center justify-content-between p-4 mb-3 bg-light-{{ in_array($categoryKey, $priorityCategories) ? 'primary' : 'secondary' }} rounded border border-light-primary">
                                                            <div class="d-flex align-items-center flex-grow-1">
                                                                <div class="form-check form-switch form-check-custom form-check-solid me-4">
                                                                    <input class="form-check-input notification-toggle"
                                                                           type="checkbox"
                                                                           name="settings[{{ $typeKey }}][is_enabled]"
                                                                           value="1"
                                                                           data-type="{{ $typeKey }}"
                                                                           {{ ($typeData['setting']['is_enabled'] ?? $typeData['config']['default_enabled']) ? 'checked' : '' }}>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="fw-semibold text-gray-800 mb-1">{{ $typeData['config']['name'] }}</h6>
                                                                    <p class="text-gray-600 mb-0 fs-8">{{ $typeData['config']['description'] }}</p>
                                                                    @if($typeData['config']['supports_summary'])
                                                                        <span class="badge badge-light-success mt-1 fs-8">Hỗ trợ gộp thông báo</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <button type="button" class="btn btn-light-primary btn-sm test-notification-btn me-2"
                                                                        data-type="{{ $typeKey }}">
                                                                    <i class="ki-duotone ki-notification-bing fs-5"></i>
                                                                    Thử nghiệm
                                                                </button>
                                                                <button type="button" class="btn btn-light btn-sm toggle-details-btn"
                                                                        data-type="{{ $typeKey }}">
                                                                    <i class="ki-duotone ki-down fs-5"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <!--end::Notification Item-->
                                                    @endif
                                                @endforeach
                                            </div>
                                            <!--end::Sub-Section-->
                                        @endforeach
                                    @else
                                        {{-- Category không có sub-sections, hiển thị bình thường --}}
                                        @foreach($category['types'] as $typeKey => $typeData)
                                        <!--begin::Notification Item-->
                                        <div class="notification-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <div class="form-check form-switch form-check-custom form-check-solid me-6">
                                                <input class="form-check-input notification-toggle"
                                                       type="checkbox"
                                                       name="settings[{{ $typeKey }}][is_enabled]"
                                                       value="1"
                                                       data-type="{{ $typeKey }}"
                                                       {{ ($typeData['setting']['is_enabled'] ?? $typeData['config']['default_enabled']) ? 'checked' : '' }}>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="fw-bold text-gray-800 mb-1">{{ $typeData['config']['name'] }}</h5>
                                                <p class="text-gray-600 mb-0 fs-7">{{ $typeData['config']['description'] }}</p>
                                                @if($typeData['config']['supports_summary'])
                                                    <span class="badge badge-light-success mt-1">Hỗ trợ gộp thông báo</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-light-primary btn-sm test-notification-btn me-3"
                                                    data-type="{{ $typeKey }}">
                                                <i class="ki-duotone ki-notification-bing fs-4"></i>
                                                Thử nghiệm
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm toggle-details-btn"
                                                    data-type="{{ $typeKey }}">
                                                <i class="ki-duotone ki-down fs-4"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!--begin::Notification Details-->
                                    <div class="notification-details" data-type="{{ $typeKey }}" style="display: none;">
                                        <div class="border border-gray-300 border-dashed rounded p-6 mb-6 ms-12">
                                            <!--begin::Channels-->
                                            <div class="mb-6">
                                                <label class="form-label fw-semibold fs-6 mb-3">Kênh thông báo:</label>
                                                <div class="d-flex flex-wrap gap-4">
                                                    @foreach($availableChannels as $channelKey => $channelName)
                                                        <label class="form-check form-check-custom form-check-solid">
                                                            <input class="form-check-input"
                                                                   type="checkbox"
                                                                   name="settings[{{ $typeKey }}][channels][]"
                                                                   value="{{ $channelKey }}"
                                                                   {{ in_array($channelKey, $typeData['setting']['channels'] ?? $typeData['config']['default_channels']) ? 'checked' : '' }}>
                                                            <span class="form-check-label fw-semibold">{{ $channelName }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!--begin::Quiet Hours-->
                                            <div class="row mb-6">
                                                <div class="col-md-6">
                                                    <label class="form-label">Giờ im lặng (từ):</label>
                                                    <input type="time"
                                                           class="form-control"
                                                           name="settings[{{ $typeKey }}][quiet_hours_start]"
                                                           value="{{ $typeData['setting']['quiet_hours_start'] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Giờ im lặng (đến):</label>
                                                    <input type="time"
                                                           class="form-control"
                                                           name="settings[{{ $typeKey }}][quiet_hours_end]"
                                                           value="{{ $typeData['setting']['quiet_hours_end'] ?? '' }}">
                                                </div>
                                            </div>

                                            <!--begin::Custom Settings-->
                                            @if(isset($typeData['config']['custom_settings']))
                                                @foreach($typeData['config']['custom_settings'] as $settingKey => $settingConfig)
                                                    <div class="mb-6">
                                                        <label class="form-label fw-semibold fs-6 mb-3">{{ $settingConfig['label'] }}:</label>

                                                        @if($settingConfig['type'] === 'select')
                                                            <select class="form-select"
                                                                    name="settings[{{ $typeKey }}][custom_settings][{{ $settingKey }}]">
                                                                @foreach($settingConfig['options'] as $optionValue => $optionLabel)
                                                                    <option value="{{ $optionValue }}"
                                                                            {{ ($typeData['setting']['custom_settings'][$settingKey] ?? $settingConfig['default']) == $optionValue ? 'selected' : '' }}>
                                                                        {{ $optionLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @elseif($settingConfig['type'] === 'number')
                                                            <input type="number"
                                                                   class="form-control"
                                                                   name="settings[{{ $typeKey }}][custom_settings][{{ $settingKey }}]"
                                                                   value="{{ $typeData['setting']['custom_settings'][$settingKey] ?? $settingConfig['default'] }}"
                                                                   min="{{ $settingConfig['min'] ?? 1 }}"
                                                                   max="{{ $settingConfig['max'] ?? 365 }}">
                                                        @elseif($settingConfig['type'] === 'text')
                                                            <input type="text"
                                                                   class="form-control"
                                                                   name="settings[{{ $typeKey }}][custom_settings][{{ $settingKey }}]"
                                                                   value="{{ $typeData['setting']['custom_settings'][$settingKey] ?? $settingConfig['default'] }}">
                                                        @endif

                                                        @if(isset($settingConfig['help']))
                                                            <div class="form-text">{{ $settingConfig['help'] }}</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                            <!--end::Custom Settings-->
                                        </div>
                                    </div>
                                    <!--end::Notification Details-->
                                    <!--end::Notification Item-->
                                    @endforeach
                                    @endif
                                        </div>
                                        <!--end::Category Section-->
                                    @endif
                                @endforeach
                            </div>
                            <!--end::Left Column-->

                            <!--begin::Right Column-->
                            <div class="col-lg-6">
                                @foreach($rightColumnCategories as $categoryKey)
                                    @if(isset($settingsByCategory[$categoryKey]))
                                        @php $category = $settingsByCategory[$categoryKey]; @endphp
                                        <!--begin::Category Section-->
                                        <div class="mb-10 notification-category-section category-{{ $categoryKey }} p-6">
                                            <div class="d-flex align-items-center mb-6 category-header">
                                                <div class="symbol symbol-45px me-4">
                                                    <div class="symbol-label category-icon">
                                                        @if($categoryKey === 'transactions')
                                                            <i class="fas fa-exchange-alt fs-2 text-white"></i>
                                                        @else
                                                            <i class="fas fa-bell fs-2 text-white"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <h3 class="fw-bold text-gray-800 mb-1">{{ $category['name'] }}</h3>
                                                    <p class="text-gray-600 mb-0">
                                                        @if($categoryKey === 'transactions')
                                                            Quản lý thông báo về giao dịch
                                                        @else
                                                            Các thông báo khác
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Right column không có sub-sections, hiển thị bình thường --}}
                                            @foreach($category['types'] as $typeKey => $typeData)
                                            <!--begin::Notification Item-->
                                            <div class="notification-item d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <div class="form-check form-switch form-check-custom form-check-solid me-6">
                                                        <input class="form-check-input notification-toggle"
                                                               type="checkbox"
                                                               name="settings[{{ $typeKey }}][is_enabled]"
                                                               value="1"
                                                               data-type="{{ $typeKey }}"
                                                               {{ ($typeData['setting']['is_enabled'] ?? $typeData['config']['default_enabled']) ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fw-bold text-gray-800 mb-1">{{ $typeData['config']['name'] }}</h5>
                                                        <p class="text-gray-600 mb-0 fs-7">{{ $typeData['config']['description'] }}</p>
                                                        @if($typeData['config']['supports_summary'])
                                                            <span class="badge badge-light-success mt-1">Hỗ trợ gộp thông báo</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-light-primary btn-sm test-notification-btn me-3"
                                                            data-type="{{ $typeKey }}">
                                                        <i class="ki-duotone ki-notification-bing fs-4"></i>
                                                        Thử nghiệm
                                                    </button>
                                                    <button type="button" class="btn btn-light btn-sm toggle-details-btn"
                                                            data-type="{{ $typeKey }}">
                                                        <i class="ki-duotone ki-down fs-4"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!--begin::Notification Details-->
                                            <div class="notification-details" data-type="{{ $typeKey }}" style="display: none;">
                                                <div class="border border-gray-300 border-dashed rounded p-6 mb-6 ms-12">
                                                    <!--begin::Channels-->
                                                    <div class="mb-6">
                                                        <label class="form-label fw-semibold fs-6 mb-3">Kênh thông báo:</label>
                                                        <div class="d-flex flex-wrap gap-4">
                                                            @foreach($availableChannels as $channelKey => $channelName)
                                                                <label class="form-check form-check-custom form-check-solid">
                                                                    <input class="form-check-input"
                                                                           type="checkbox"
                                                                           name="settings[{{ $typeKey }}][channels][]"
                                                                           value="{{ $channelKey }}"
                                                                           {{ in_array($channelKey, $typeData['setting']['channels'] ?? $typeData['config']['default_channels']) ? 'checked' : '' }}>
                                                                    <span class="form-check-label fw-semibold">{{ $channelName }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <!--end::Channels-->

                                                    <!--begin::Custom Settings-->
                                                    @if(isset($typeData['config']['custom_settings']))
                                                        @foreach($typeData['config']['custom_settings'] as $settingKey => $settingConfig)
                                                            <div class="mb-4">
                                                                <label class="form-label fw-semibold fs-6 mb-2">{{ $settingConfig['label'] }}:</label>
                                                                @if($settingConfig['type'] === 'select')
                                                                    <select class="form-select form-select-sm"
                                                                            name="settings[{{ $typeKey }}][{{ $settingKey }}]">
                                                                        @foreach($settingConfig['options'] as $optionValue => $optionLabel)
                                                                            <option value="{{ $optionValue }}"
                                                                                    {{ ($typeData['setting'][$settingKey] ?? $settingConfig['default']) == $optionValue ? 'selected' : '' }}>
                                                                                {{ $optionLabel }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @elseif($settingConfig['type'] === 'number')
                                                                    <input type="number"
                                                                           class="form-control form-control-sm"
                                                                           name="settings[{{ $typeKey }}][{{ $settingKey }}]"
                                                                           value="{{ $typeData['setting'][$settingKey] ?? $settingConfig['default'] }}"
                                                                           min="{{ $settingConfig['min'] ?? 0 }}"
                                                                           max="{{ $settingConfig['max'] ?? 100 }}">
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <!--end::Custom Settings-->
                                                </div>
                                            </div>
                                            <!--end::Notification Details-->
                                            <!--end::Notification Item-->
                                            @endforeach
                                        </div>
                                        <!--end::Category Section-->
                                    @endif
                                @endforeach
                            </div>
                            <!--end::Right Column-->
                        </div>
                        <!--end::Two Column Layout-->
                    </form>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    <!--end::Content-->
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Enhanced UI Interactions

    // Add smooth scroll behavior
    $('html').css('scroll-behavior', 'smooth');

    // Enhanced switch animations
    $('.form-check-input').on('change', function() {
        const $this = $(this);
        $this.closest('.notification-item').addClass('switch-changing');

        setTimeout(() => {
            $this.closest('.notification-item').removeClass('switch-changing');
        }, 300);
    });

    // Enhanced button loading states
    $('.test-notification-btn').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();

        $btn.addClass('loading').prop('disabled', true);
        $btn.html('<i class="fas fa-spinner fa-spin fs-4"></i> Đang gửi...');

        // Simulate API call delay
        setTimeout(() => {
            $btn.removeClass('loading').prop('disabled', false);
            $btn.html(originalText);

            // Show success feedback
            showNotificationFeedback('success', 'Thông báo thử nghiệm đã được gửi thành công!');
        }, 2000);
    });

    // Enhanced details toggle with smooth animation
    $('.toggle-details-btn').on('click', function() {
        const $btn = $(this);
        const $icon = $btn.find('i');
        const type = $btn.data('type');
        const $details = $(`.notification-details[data-type="${type}"]`);

        if ($details.is(':visible')) {
            $details.slideUp(300, function() {
                $details.removeClass('show');
            });
            $icon.removeClass('ki-up').addClass('ki-down');
        } else {
            $details.slideDown(300, function() {
                $details.addClass('show');
            });
            $icon.removeClass('ki-down').addClass('ki-up');
        }
    });

    // Add notification feedback system
    function showNotificationFeedback(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle';

        const $alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="${iconClass} fs-2 me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append($alert);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $alert.alert('close');
        }, 5000);
    }

    // Enhanced form validation feedback
    $('#notification-settings-form').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $('#save-settings-btn');

        $submitBtn.addClass('loading').prop('disabled', true);

        // Simulate form submission
        setTimeout(() => {
            $submitBtn.removeClass('loading').prop('disabled', false);
            showNotificationFeedback('success', 'Cài đặt đã được lưu thành công!');
        }, 1500);
    });

    // Add keyboard navigation support
    $('.notification-item').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).find('.form-check-input').click();
        }
    });

    // Add focus management
    $('.form-check-input').on('focus', function() {
        $(this).closest('.notification-item').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.notification-item').removeClass('focused');
    });
    // Performance optimized notification settings
    const NotificationSettings = {
        // Cache DOM elements for better performance
        $form: $('#notification-settings-form'),
        $saveBtn: $('#save-settings-btn'),
        $resetBtn: $('#reset-settings-btn'),

        // Debounce function for performance
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Optimized toggle details with event delegation
        init: function() {
            // Use event delegation for better performance
            $(document).on('click', '.toggle-details-btn', this.handleToggleDetails.bind(this));
            $(document).on('change', '.notification-toggle', this.handleNotificationToggle.bind(this));

            // Bind button events
            this.$saveBtn.on('click', this.handleSaveSettings.bind(this));
            this.$resetBtn.on('click', this.handleResetSettings.bind(this));
            $(document).on('click', '.test-notification-btn', this.handleTestNotification.bind(this));
        },

        // Optimized toggle details handler
        handleToggleDetails: function(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const type = $btn.data('type');
            const $detailsContainer = $(`.notification-details[data-type="${type}"]`);
            const $icon = $btn.find('i');

            // Use CSS classes for better performance instead of slideUp/slideDown
            if ($detailsContainer.hasClass('show')) {
                $detailsContainer.removeClass('show').addClass('hiding');
                $icon.removeClass('ki-up').addClass('ki-down');

                // Remove hiding class after animation
                setTimeout(() => $detailsContainer.removeClass('hiding'), 300);
            } else {
                $detailsContainer.addClass('show');
                $icon.removeClass('ki-down').addClass('ki-up');
            }
        },

        // Optimized notification toggle handler
        handleNotificationToggle: function(e) {
            const $toggle = $(e.currentTarget);
            const type = $toggle.data('type');
            const isEnabled = $toggle.is(':checked');
            const $detailsContainer = $(`.notification-details[data-type="${type}"]`);

            if (!isEnabled && $detailsContainer.hasClass('show')) {
                $detailsContainer.removeClass('show');
                $(`.toggle-details-btn[data-type="${type}"] i`).removeClass('ki-up').addClass('ki-down');
            }
        },

        // Optimized save settings with debouncing
        handleSaveSettings: function() {
            if (this.$saveBtn.prop('disabled')) return; // Prevent double clicks

            const originalText = this.$saveBtn.html();
            this.setButtonLoading(this.$saveBtn, 'Đang lưu...');

            // Use cached form element
            const formData = this.$form.serialize();

            // Optimized AJAX with timeout and better error handling
            $.ajax({
                url: '{{ route("admin.notification-settings.update") }}',
                method: 'POST',
                data: formData,
                timeout: 10000, // 10 second timeout
                cache: false,
                success: (response) => {
                    if (response.success) {
                        toastr.success(response.message);
                        // Mark form as saved to prevent unnecessary saves
                        this.$form.data('saved', true);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: (xhr) => {
                    const response = xhr.responseJSON;
                    const message = response?.message || 'Có lỗi xảy ra khi lưu cài đặt';
                    toastr.error(message);
                    console.error('Save settings error:', xhr);
                },
                complete: () => {
                    this.resetButtonState(this.$saveBtn, originalText);
                }
            });
        },

        // Optimized reset settings
        handleResetSettings: function() {
            if (this.$resetBtn.prop('disabled')) return; // Prevent double clicks

            // Use modern confirm dialog or SweetAlert for better UX
            if (!confirm('Bạn có chắc chắn muốn khôi phục cài đặt về mặc định?')) {
                return;
            }

            const originalText = this.$resetBtn.html();
            this.setButtonLoading(this.$resetBtn, 'Đang khôi phục...');

            $.ajax({
                url: '{{ route("admin.notification-settings.reset") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                timeout: 10000,
                cache: false,
                success: (response) => {
                    if (response.success) {
                        toastr.success(response.message);
                        // Optimized page reload with slight delay for better UX
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                        this.resetButtonState(this.$resetBtn, originalText);
                    }
                },
                error: (xhr) => {
                    const response = xhr.responseJSON;
                    const message = response?.message || 'Có lỗi xảy ra khi khôi phục cài đặt';
                    toastr.error(message);
                    console.error('Reset settings error:', xhr);
                    this.resetButtonState(this.$resetBtn, originalText);
                }
            });
        },

        // Optimized test notification with throttling
        handleTestNotification: function(e) {
            const $btn = $(e.currentTarget);

            if ($btn.prop('disabled')) return; // Prevent spam clicks

            const type = $btn.data('type');
            const originalText = $btn.html();

            this.setButtonLoading($btn, 'Đang gửi...');

            $.ajax({
                url: '{{ route("admin.notification-settings.test") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    channel: 'web'
                },
                timeout: 8000,
                cache: false,
                success: (response) => {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: (xhr) => {
                    const response = xhr.responseJSON;
                    const message = response?.message || 'Có lỗi xảy ra khi gửi thông báo test';
                    toastr.error(message);
                    console.error('Test notification error:', xhr);
                },
                complete: () => {
                    this.resetButtonState($btn, originalText);
                }
            });
        },

        // Utility functions for better performance
        setButtonLoading: function($btn, text) {
            $btn.prop('disabled', true)
                .html(`<span class="spinner-border spinner-border-sm me-2"></span>${text}`)
                .addClass('loading');
        },

        resetButtonState: function($btn, originalText) {
            $btn.prop('disabled', false)
                .html(originalText)
                .removeClass('loading');
        },

        // Form change detection for auto-save
        setupFormChangeDetection: function() {
            let changeTimeout;
            this.$form.on('change input', this.debounce(() => {
                this.$form.data('saved', false);
                // Optional: Show unsaved changes indicator
                this.showUnsavedIndicator();
            }, 300));
        },

        showUnsavedIndicator: function() {
            if (!this.$form.data('saved')) {
                this.$saveBtn.addClass('btn-warning').removeClass('btn-primary');
            }
        },

        hideUnsavedIndicator: function() {
            this.$saveBtn.removeClass('btn-warning').addClass('btn-primary');
        }
    };

    // Initialize the optimized notification settings
    NotificationSettings.init();
    NotificationSettings.setupFormChangeDetection();
});
</script>
@endpush
