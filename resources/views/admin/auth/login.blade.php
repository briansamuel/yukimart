<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Admin Login' }} - {{ $tenant->name ?? 'YukiMart' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .tenant-info {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .tenant-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .tenant-url {
            font-size: 0.9rem;
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .loading-spinner {
            display: none;
        }
        
        .demo-credentials {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .demo-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .demo-item {
            font-size: 0.8rem;
            color: #856404;
            margin-bottom: 0.25rem;
            font-family: 'Courier New', monospace;
        }
        
        .quick-login-btn {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.5);
            color: #856404;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
            margin-left: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .quick-login-btn:hover {
            background: rgba(255, 193, 7, 0.3);
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Platform/Tenant Info -->
        @if(isset($isPlatform) && $isPlatform)
        <div class="tenant-info" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white;">
            <div class="tenant-name">
                <i class="fas fa-crown me-2"></i>Platform Management
            </div>
            <div class="tenant-url" style="color: rgba(255,255,255,0.8);">{{ request()->getHost() }}</div>
        </div>
        @elseif($tenant ?? false)
        <div class="tenant-info">
            <div class="tenant-name">
                <i class="fas fa-store me-2"></i>{{ $tenant->name }}
            </div>
            <div class="tenant-url">{{ request()->getHost() }}</div>
        </div>
        @endif
        
        <!-- Login Header -->
        <div class="login-header">
            <h1 class="login-title">
                <i class="fas fa-user-shield me-2"></i>Admin Login
            </h1>
            <p class="login-subtitle">Đăng nhập vào bảng điều khiển quản trị</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ isset($isPlatform) && $isPlatform ? route('platform.login') : '/login' }}" id="loginForm">
            @csrf
            
            <!-- Email Field -->
            <div class="form-floating">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com"
                       value="{{ old('email') }}" 
                       required 
                       autofocus>
                <label for="email">
                    <i class="fas fa-envelope me-2"></i>Email Address
                </label>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="form-floating">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       placeholder="Password"
                       required>
                <label for="password">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="remember-me">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-login" id="loginBtn">
                <span class="login-text">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                </span>
                <span class="loading-spinner">
                    <i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...
                </span>
            </button>
        </form>

        <!-- Demo Credentials -->
        @if($tenant)
        <div class="demo-credentials">
            <div class="demo-title">
                <i class="fas fa-key me-2"></i>Demo Credentials (Password: 123456)
            </div>
            <div class="demo-item">
                Owner: owner@{{ $tenant->slug }}.local
                <button type="button" class="quick-login-btn" onclick="quickLogin('owner@{{ $tenant->slug }}.local', '123456')">
                    Quick Login
                </button>
            </div>
            <div class="demo-item">
                Admin: admin@{{ $tenant->slug }}.local
                <button type="button" class="quick-login-btn" onclick="quickLogin('admin@{{ $tenant->slug }}.local', '123456')">
                    Quick Login
                </button>
            </div>
            <div class="demo-item">
                Manager: manager@{{ $tenant->slug }}.local
                <button type="button" class="quick-login-btn" onclick="quickLogin('manager@{{ $tenant->slug }}.local', '123456')">
                    Quick Login
                </button>
            </div>
            <div class="demo-item">
                Staff: staff@{{ $tenant->slug }}.local
                <button type="button" class="quick-login-btn" onclick="quickLogin('staff@{{ $tenant->slug }}.local', '123456')">
                    Quick Login
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Quick login function
        function quickLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }

        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function() {
            const loginBtn = document.getElementById('loginBtn');
            const loginText = loginBtn.querySelector('.login-text');
            const loadingSpinner = loginBtn.querySelector('.loading-spinner');
            
            loginBtn.disabled = true;
            loginText.style.display = 'none';
            loadingSpinner.style.display = 'inline';
        });

        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>
