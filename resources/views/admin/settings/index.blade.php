@extends('admin.main-content')

@section('title', __('User Settings'))

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ __('User Settings') }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ __('Settings') }}</li>
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
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="fas fa-cog fs-1 position-absolute ms-6"></i>
                            <h3 class="fw-bold ms-15 mb-0">{{ __('Personal Settings') }}</h3>
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                            <button type="button" class="btn btn-light-primary me-3" id="reset-settings-btn">
                                <i class="fas fa-sync-alt fs-2"></i>
                                {{ __('Reset to Default') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="save-settings-btn">
                                <i class="fas fa-check fs-2"></i>
                                {{ __('Save Settings') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    
                    <form id="settings-form">
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Col-->
                            <div class="col-lg-6">
                                <!--begin::UI Preferences-->
                                <div class="card card-flush mb-6">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h3 class="fw-bold">{{ __('Interface Preferences') }}</h3>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!--begin::Theme-->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('Theme') }}</label>
                                            <div class="col-lg-8">
                                                <select name="theme" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('Select theme') }}">
                                                    <option value="light" {{ ($uiPreferences['theme'] ?? 'light') == 'light' ? 'selected' : '' }}>{{ __('Light') }}</option>
                                                    <option value="dark" {{ ($uiPreferences['theme'] ?? 'light') == 'dark' ? 'selected' : '' }}>{{ __('Dark') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--end::Theme-->

                                        <!--begin::Language-->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('Language') }}</label>
                                            <div class="col-lg-8">
                                                <select name="language" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('Select language') }}">
                                                    <option value="vi" {{ ($uiPreferences['language'] ?? 'vi') == 'vi' ? 'selected' : '' }}>{{ __('Vietnamese') }}</option>
                                                    <option value="en" {{ ($uiPreferences['language'] ?? 'vi') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--end::Language-->

                                        <!--begin::Items per page-->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('Items per page') }}</label>
                                            <div class="col-lg-8">
                                                <select name="items_per_page" class="form-select form-select-solid" data-control="select2">
                                                    <option value="10" {{ ($uiPreferences['items_per_page'] ?? 25) == 10 ? 'selected' : '' }}>10</option>
                                                    <option value="25" {{ ($uiPreferences['items_per_page'] ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                                    <option value="50" {{ ($uiPreferences['items_per_page'] ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ ($uiPreferences['items_per_page'] ?? 25) == 100 ? 'selected' : '' }}>100</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!--end::Items per page-->

                                        <!--begin::Sidebar collapsed-->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('Sidebar') }}</label>
                                            <div class="col-lg-8">
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="sidebar_collapsed" value="1" 
                                                           {{ ($uiPreferences['sidebar_collapsed'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ __('Keep sidebar collapsed') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Sidebar collapsed-->
                                    </div>
                                </div>
                                <!--end::UI Preferences-->
                            </div>
                            <!--end::Col-->

                            <!--begin::Col-->
                            <div class="col-lg-6">
                                <!--begin::Notification Preferences-->
                                <div class="card card-flush mb-6">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h3 class="fw-bold">{{ __('Notification Preferences') }}</h3>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!--begin::Notifications enabled-->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('user_settings.notifications.title') }}</label>
                                            <div class="col-lg-8">
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="notifications_enabled" value="1" 
                                                           {{ ($notificationPreferences['notifications_enabled'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ __('user_settings.notifications.enable') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Notifications enabled-->

                                        <!--begin::Email notifications-->
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('user_settings.notifications.email') }}</label>
                                            <div class="col-lg-8">
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="email_notifications" value="1" 
                                                           {{ ($notificationPreferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ __('user_settings.notifications.enable_email') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Email notifications-->
                                    </div>
                                </div>
                                <!--end::Notification Preferences-->

                                <!--begin::Actions-->
                                <div class="card card-flush">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h3 class="fw-bold">{{ __('Actions') }}</h3>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-column gap-3">
                                            <button type="button" class="btn btn-light-info" id="export-settings-btn">
                                                <i class="fas fa-download fs-2"></i>
                                                {{ __('Export Settings') }}
                                            </button>
                                            <button type="button" class="btn btn-light-warning" id="import-settings-btn">
                                                <i class="fas fa-upload fs-2"></i>
                                                {{ __('Import Settings') }}
                                            </button>
                                            <button type="button" class="btn btn-light-danger" id="clear-cache-btn">
                                                <i class="fas fa-trash fs-2"></i>
                                                {{ __('Clear Cache') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->
                    </form>

                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

        </div>
    </div>
    <!--end::Content-->
</div>

<!-- Import Settings Modal -->
<div class="modal fade" id="import-settings-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('Import Settings') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="import-settings-form">
                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">{{ __('Settings File') }}</label>
                        <input type="file" class="form-control form-control-solid" name="settings_file" accept=".json" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="import-settings-submit">{{ __('Import') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Save settings
    $('#save-settings-btn').click(function() {
        const formData = new FormData($('#settings-form')[0]);
        const settings = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                key = key.slice(0, -2);
                if (!settings[key]) settings[key] = [];
                settings[key].push(value);
            } else {
                settings[key] = value;
            }
        }
        
        // Handle checkboxes
        $('input[type="checkbox"]').each(function() {
            const name = $(this).attr('name');
            if (name && !settings[name]) {
                settings[name] = false;
            } else if (name && settings[name]) {
                settings[name] = true;
            }
        });

        $.ajax({
            url: '{{ route("admin.settings.update") }}',
            method: 'POST',
            data: settings,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "{{ __('OK') }}",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    
                    // Apply theme change immediately if changed
                    if (settings.theme) {
                        $('html').attr('data-bs-theme', settings.theme);
                    }
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    text: response.message || "{{ __('An error occurred') }}",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "{{ __('OK') }}",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }
        });
    });

    // Reset settings
    $('#reset-settings-btn').click(function() {
        Swal.fire({
            text: "{{ __('Are you sure you want to reset all settings to default?') }}",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "{{ __('Yes, reset!') }}",
            cancelButtonText: "{{ __('Cancel') }}",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light"
            }
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: '{{ route("admin.settings.reset") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "{{ __('OK') }}",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            }).then(function() {
                                location.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    // Export settings
    $('#export-settings-btn').click(function() {
        window.location.href = '{{ route("admin.settings.export") }}';
    });

    // Import settings
    $('#import-settings-btn').click(function() {
        $('#import-settings-modal').modal('show');
    });

    $('#import-settings-submit').click(function() {
        const fileInput = $('input[name="settings_file"]')[0];
        if (!fileInput.files[0]) {
            Swal.fire({
                text: "{{ __('Please select a file') }}",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "{{ __('OK') }}",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const settings = JSON.parse(e.target.result);
                
                $.ajax({
                    url: '{{ route("admin.settings.import") }}',
                    method: 'POST',
                    data: { settings: settings },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#import-settings-modal').modal('hide');
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "{{ __('OK') }}",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            }).then(function() {
                                location.reload();
                            });
                        }
                    }
                });
            } catch (error) {
                Swal.fire({
                    text: "{{ __('Invalid file format') }}",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "{{ __('OK') }}",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }
        };
        reader.readAsText(fileInput.files[0]);
    });

    // Clear cache
    $('#clear-cache-btn').click(function() {
        $.ajax({
            url: '{{ route("admin.settings.clear-cache") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "{{ __('OK') }}",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            }
        });
    });
});
</script>
@endpush
