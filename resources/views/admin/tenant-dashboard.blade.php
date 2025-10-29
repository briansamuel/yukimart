<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Dashboard' }} - {{ $tenant->name ?? 'YukiMart' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #667eea !important;
        }
        
        .tenant-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stats-icon.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-icon.success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        }
        
        .stats-icon.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-icon.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .welcome-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .action-btn {
            background: rgba(102, 126, 234, 0.1);
            border: 2px solid rgba(102, 126, 234, 0.2);
            color: #667eea;
            padding: 1rem;
            border-radius: 10px;
            text-decoration: none;
            display: block;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            transform: translateX(5px);
        }
        
        .tenant-info-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
        
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-owner {
            background: #dc3545;
            color: white;
        }
        
        .role-admin {
            background: #fd7e14;
            color: white;
        }
        
        .role-manager {
            background: #20c997;
            color: white;
        }
        
        .role-staff {
            background: #6f42c1;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-store me-2"></i>{{ $tenant->name ?? 'YukiMart' }}
            </a>
            
            <div class="d-flex align-items-center">
                <span class="tenant-badge me-3">
                    <i class="fas fa-globe me-2"></i>{{ request()->getHost() }}
                </span>
                
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-2"></i>{{ $user->full_name ?? 'User' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <h1 class="welcome-title">
                <i class="fas fa-rocket me-3"></i>Chào mừng đến với {{ $tenant->name ?? 'YukiMart' }}!
            </h1>
            <p class="welcome-subtitle">
                Bảng điều khiển quản trị - Quản lý toàn bộ hoạt động kinh doanh của bạn
            </p>
            <div class="row">
                <div class="col-md-4">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <div>Theo dõi doanh thu</div>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <div>Quản lý khách hàng</div>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <div>Quản lý kho hàng</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- User Info -->
            <div class="col-md-4">
                <div class="tenant-info-card">
                    <h5 class="mb-3">
                        <i class="fas fa-user-circle me-2"></i>Thông tin người dùng
                    </h5>
                    
                    <div class="info-item">
                        <span class="info-label">Họ tên:</span>
                        <span class="info-value">{{ $user->full_name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $user->email ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Vai trò:</span>
                        <span class="role-badge role-{{ $tenantUser->role ?? 'staff' }}">
                            {{ ucfirst($tenantUser->role ?? 'Staff') }}
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Trạng thái:</span>
                        <span class="badge bg-success">{{ ucfirst($user->status ?? 'Active') }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Tham gia:</span>
                        <span class="info-value">{{ $tenantUser->joined_at ? $tenantUser->joined_at->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>

                <!-- Tenant Info -->
                <div class="tenant-info-card">
                    <h5 class="mb-3">
                        <i class="fas fa-store me-2"></i>Thông tin cửa hàng
                    </h5>
                    
                    <div class="info-item">
                        <span class="info-label">Tên cửa hàng:</span>
                        <span class="info-value">{{ $tenant->name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Mã cửa hàng:</span>
                        <span class="info-value">{{ $tenant->slug ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Subdomain:</span>
                        <span class="info-value">{{ $tenant->subdomain ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Trạng thái:</span>
                        <span class="badge bg-success">{{ ucfirst($tenant->status ?? 'Active') }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Ngày tạo:</span>
                        <span class="info-value">{{ $tenant->created_at ? $tenant->created_at->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number">{{ $tenant->current_users ?? 0 }}</div>
                    <div class="stats-label">Người dùng</div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon success">
                        <i class="fas fa-store-alt"></i>
                    </div>
                    <div class="stats-number">{{ $tenant->current_branch_shops ?? 0 }}</div>
                    <div class="stats-label">Chi nhánh</div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon warning">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stats-number">{{ $tenant->current_products ?? 0 }}</div>
                    <div class="stats-label">Sản phẩm</div>
                </div>

                <div class="stats-card">
                    <div class="stats-icon info">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Đơn hàng hôm nay</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="quick-actions">
                    <h5 class="mb-3">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                    
                    <a href="#" class="action-btn">
                        <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
                    </a>
                    
                    <a href="#" class="action-btn">
                        <i class="fas fa-shopping-cart me-2"></i>Tạo đơn hàng
                    </a>
                    
                    <a href="#" class="action-btn">
                        <i class="fas fa-users me-2"></i>Quản lý khách hàng
                    </a>
                    
                    <a href="#" class="action-btn">
                        <i class="fas fa-chart-bar me-2"></i>Xem báo cáo
                    </a>
                    
                    <a href="#" class="action-btn">
                        <i class="fas fa-cog me-2"></i>Cài đặt hệ thống
                    </a>
                    
                    <a href="{{ route('admin.auth.check') }}" class="action-btn">
                        <i class="fas fa-info-circle me-2"></i>Kiểm tra trạng thái
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can add AJAX calls here to refresh stats
            console.log('Stats refresh interval');
        }, 30000);

        // Welcome animation
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeCard = document.querySelector('.welcome-card');
            welcomeCard.style.opacity = '0';
            welcomeCard.style.transform = 'translateY(20px)';
            
            setTimeout(function() {
                welcomeCard.style.transition = 'all 0.6s ease';
                welcomeCard.style.opacity = '1';
                welcomeCard.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
