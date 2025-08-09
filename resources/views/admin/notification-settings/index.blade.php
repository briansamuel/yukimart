@extends('admin.layouts.app')

@section('title', 'Cài đặt thông báo')

@push('styles')
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

                        <!-- Hiển thị theo thứ tự ưu tiên: 4 sections mới trước -->
                        @php
                            $priorityCategories = ['customers', 'cashbook', 'inventory', 'transactions'];
                            $otherCategories = array_diff(array_keys($settingsByCategory), $priorityCategories);
                            $orderedCategories = array_merge($priorityCategories, $otherCategories);
                        @endphp

                        @foreach($orderedCategories as $categoryKey)
                            @if(isset($settingsByCategory[$categoryKey]))
                                @php $category = $settingsByCategory[$categoryKey]; @endphp
                                <!--begin::Category Section-->
                                <div class="mb-12">
                                    <div class="d-flex align-items-center mb-6">
                                        <div class="symbol symbol-40px me-4">
                                            <div class="symbol-label bg-light-{{ in_array($categoryKey, $priorityCategories) ? 'primary' : 'secondary' }}">
                                                @if($categoryKey === 'customers')
                                                    <i class="ki-duotone ki-profile-user fs-2 text-primary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                    </i>
                                                @elseif($categoryKey === 'cashbook')
                                                    <i class="ki-duotone ki-wallet fs-2 text-primary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                    </i>
                                                @elseif($categoryKey === 'inventory')
                                                    <i class="ki-duotone ki-package fs-2 text-primary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                @elseif($categoryKey === 'transactions')
                                                    <i class="ki-duotone ki-handcart fs-2 text-primary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                @else
                                                    <i class="ki-duotone ki-notification-bing fs-2 text-secondary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
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

                                    @foreach($category['types'] as $typeKey => $typeData)
                                    <!--begin::Notification Item-->
                                    <div class="d-flex align-items-center justify-content-between p-6 mb-4 bg-light-{{ in_array($categoryKey, $priorityCategories) ? 'primary' : 'secondary' }} rounded">
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
                                </div>
                                <!--end::Category Section-->
                            @endif
                        @endforeach
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
