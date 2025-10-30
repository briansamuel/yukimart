<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <title>{{ $pageTitle ?? 'Login' }} - {{ $siteName ?? 'YukiMart' }}</title>
    <meta name="description" content="YukiMart Admin Login" />
    <meta name="keywords" content="yukimart, admin, login, management" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('admin-assets/assets/media/logos/favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    
    <!-- Global Stylesheets Bundle (includes bootstrap.bundle.css & style.bundle.css) -->
    <link href="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    
    <style>
        .auth-form-bg {
            background-image: url('{{ asset('admin-assets/assets/media/auth/bg10.jpeg') }}');
        }
        [data-bs-theme="dark"] .auth-form-bg {
            background-image: url('{{ asset('admin-assets/assets/media/auth/bg10-dark.jpeg') }}');
        }
        
        .platform-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .tenant-badge {
            background: linear-gradient(45deg, #009ef7, #50cd89);
            color: white;
        }
        
        .quick-login-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .quick-login-btn {
            padding: 8px 12px;
            border: 1px solid var(--kt-border-color);
            border-radius: 6px;
            background: var(--kt-body-bg);
            color: var(--kt-text-color);
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quick-login-btn:hover {
            background: var(--kt-primary);
            color: white;
            border-color: var(--kt-primary);
        }
    </style>
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
    <!-- Theme mode setup -->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <!-- Root -->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!-- Page bg image -->
        <style>
            body { 
                background-image: url('{{ asset('admin-assets/assets/media/auth/bg10.jpeg') }}'); 
            }
            [data-bs-theme="dark"] body { 
                background-image: url('{{ asset('admin-assets/assets/media/auth/bg10-dark.jpeg') }}'); 
            }
        </style>
        
        <!-- Authentication - Sign-in -->
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <!-- Aside -->
            <div class="d-flex flex-lg-row-fluid">
                <!-- Content -->
                <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                    <!-- Image -->
                    <img class="theme-light-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" 
                         src="{{ asset('admin-assets/assets/media/auth/agency.png') }}" alt="" />
                    <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-300px mb-10 mb-lg-20" 
                         src="{{ asset('admin-assets/assets/media/auth/agency-dark.png') }}" alt="" />
                    
                    <!-- Title -->
                    <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">
                        @if(isset($isPlatform) && $isPlatform)
                            Platform Management
                        @else
                            {{ $tenant->name ?? 'YukiMart' }}
                        @endif
                    </h1>
                    
                    <!-- Text -->
                    <div class="text-gray-600 fs-base text-center fw-semibold">
                        @if(isset($isPlatform) && $isPlatform)
                            Quản lý toàn bộ hệ thống và tenant
                        @else
                            Hệ thống quản lý bán hàng thông minh
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
                <!-- Wrapper -->
                <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                    <!-- Header -->
                    <div class="d-flex flex-center flex-column-auto mb-15">
                        <!-- Logo -->
                        <a href="#" class="mb-7">
                            <img alt="Logo" src="{{ asset('admin-assets/assets/media/logos/default-dark.svg') }}" class="h-60px" />
                        </a>
                        
                        <!-- Title -->
                        <h1 class="text-dark fw-bolder mb-3">
                            @if(isset($isPlatform) && $isPlatform)
                                <span class="badge platform-badge fs-7 fw-bold me-2">
                                    <i class="ki-duotone ki-crown fs-6 me-1"></i>PLATFORM
                                </span>
                            @else
                                <span class="badge tenant-badge fs-7 fw-bold me-2">
                                    <i class="ki-duotone ki-shop fs-6 me-1"></i>TENANT
                                </span>
                            @endif
                            Đăng nhập
                        </h1>
                        
                        <!-- Description -->
                        <div class="text-gray-500 fw-semibold fs-6">
                            @if(isset($isPlatform) && $isPlatform)
                                Truy cập bảng điều khiển quản trị platform
                            @else
                                Truy cập bảng điều khiển quản lý cửa hàng
                            @endif
                        </div>
                    </div>
                    
                    <!-- Form -->
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" 
                          action="{{ isset($isPlatform) && $isPlatform ? route('platform.login') : url('/login') }}" method="POST">
                        @csrf
                        
                        <!-- Alert Container -->
                        <div id="alert-container" class="mb-5"></div>
                        
                        <!-- Email -->
                        <div class="fv-row mb-8">
                            <input type="text" placeholder="Email" name="email" id="email" autocomplete="off" 
                                   class="form-control bg-transparent" value="{{ old('email') }}" />
                        </div>
                        
                        <!-- Password -->
                        <div class="fv-row mb-3">
                            <input type="password" placeholder="Mật khẩu" name="password" id="password" autocomplete="off" 
                                   class="form-control bg-transparent" />
                        </div>
                        
                        <!-- Remember me -->
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" />
                                <label class="form-check-label text-gray-700" for="remember">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                            
                            <a href="#" class="link-primary">
                                Quên mật khẩu?
                            </a>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="d-grid mb-10">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                <span class="indicator-label">Đăng nhập</span>
                                <span class="indicator-progress">Đang xử lý...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Demo Credentials -->
                    <div class="separator separator-content my-14">
                        <span class="w-125px text-gray-500 fw-semibold fs-7">Demo Credentials</span>
                    </div>

                    @if(isset($isPlatform) && $isPlatform)
                        <!-- Platform Demo Credentials -->
                        <div class="text-center">
                            <div class="text-gray-500 fw-semibold fs-6 mb-3">
                                <i class="ki-duotone ki-key fs-2 text-primary me-2"></i>
                                Platform Admin (Password: 123456)
                            </div>
                            <button type="button" class="btn btn-light-primary btn-sm quick-login-btn"
                                    onclick="quickLogin('superadmin@yukimart.local', '123456')">
                                <i class="ki-duotone ki-crown fs-4 me-1"></i>
                                Super Administrator
                            </button>
                        </div>
                    @else
                        <!-- Tenant Demo Credentials -->
                        <div class="text-center">
                            <div class="text-gray-500 fw-semibold fs-6 mb-3">
                                <i class="ki-duotone ki-key fs-2 text-primary me-2"></i>
                                Demo Accounts (Password: 123456)
                            </div>
                            <div class="quick-login-grid">
                                @php
                                    $host = request()->getHost();
                                    $emailDomain = match($host) {
                                        'tenant1.yukimart.local' => 'techmart.local',
                                        'tenant2.yukimart.local' => 'fashion.local',
                                        'tenant3.yukimart.local' => 'food.local',
                                        default => 'demo.local'
                                    };

                                    $demoUsers = [
                                        ['role' => 'Owner', 'email' => 'owner@' . $emailDomain, 'icon' => 'crown'],
                                        ['role' => 'Admin', 'email' => 'admin@' . $emailDomain, 'icon' => 'user-tick'],
                                        ['role' => 'Manager', 'email' => 'manager@' . $emailDomain, 'icon' => 'people'],
                                        ['role' => 'Staff', 'email' => 'staff@' . $emailDomain, 'icon' => 'badge']
                                    ];
                                @endphp

                                @foreach($demoUsers as $user)
                                    <button type="button" class="btn btn-light-success btn-sm quick-login-btn"
                                            onclick="quickLogin('{{ $user['email'] }}', '123456')">
                                        <i class="ki-duotone ki-{{ $user['icon'] }} fs-5 me-1"></i>
                                        {{ $user['role'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Javascript -->
    <script src="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/scripts.bundle.js') }}"></script>

    <!-- Custom Login Script -->
    <script>
        // Quick login function
        function quickLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;

            // Add visual feedback
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            emailInput.classList.add('is-valid');
            passwordInput.classList.add('is-valid');

            setTimeout(() => {
                emailInput.classList.remove('is-valid');
                passwordInput.classList.remove('is-valid');
            }, 1000);
        }

        // Enhanced Metronic Login with Ajax
        var KTSigninGeneral = function() {
            var form, submitButton, validator;

            var showMessage = function(message, type = 'error') {
                const alertContainer = document.getElementById('alert-container');
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const iconClass = type === 'success' ? 'ki-check-circle' : 'ki-cross-circle';

                alertContainer.innerHTML = `
                    <div class="alert ${alertClass} d-flex align-items-center p-5 mb-5">
                        <i class="ki-duotone ${iconClass} fs-2hx text-${type} me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">${message}</span>
                        </div>
                    </div>
                `;
            };

            var handleSubmitAjax = function(e) {
                validator.validate().then(function(status) {
                    if (status == 'Valid') {
                        e.preventDefault();

                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;

                        // Clear previous alerts
                        document.getElementById('alert-container').innerHTML = '';

                        // Get form data
                        const formData = new FormData(form);

                        // Ajax request
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success || data.status === true) {
                                showMessage('Đăng nhập thành công! Đang chuyển hướng...', 'success');

                                setTimeout(() => {
                                    window.location.href = data.redirect || data.url ||
                                        @if(isset($isPlatform) && $isPlatform)
                                            '{{ route("platform.admin.dashboard") }}'
                                        @else
                                            '/admin/dashboard'
                                        @endif;
                                }, 1500);
                            } else {
                                showMessage(data.message || data.msg || 'Đăng nhập thất bại. Vui lòng thử lại.', 'error');

                                // Hide loading indication
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Login error:', error);
                            showMessage('Có lỗi xảy ra. Vui lòng thử lại.', 'error');

                            // Hide loading indication
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        });
                    }
                });
            };

            return {
                init: function() {
                    form = document.querySelector('#kt_sign_in_form');
                    submitButton = document.querySelector('#kt_sign_in_submit');

                    validator = FormValidation.formValidation(form, {
                        fields: {
                            email: {
                                validators: {
                                    notEmpty: {
                                        message: 'Bắt buộc nhập email'
                                    },
                                    emailAddress: {
                                        message: 'Email không hợp lệ'
                                    }
                                }
                            },
                            password: {
                                validators: {
                                    notEmpty: {
                                        message: 'Bắt buộc nhập mật khẩu'
                                    }
                                }
                            }
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: '.fv-row'
                            })
                        }
                    });

                    submitButton.addEventListener('click', handleSubmitAjax);
                }
            };
        }();

        // Initialize on DOM ready
        KTUtil.onDOMContentLoaded(function() {
            KTSigninGeneral.init();
        });
    </script>
