<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Dashboard - YukiMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        .platform-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease;
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
        }
        .icon-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .icon-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); }
        .icon-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .icon-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: transform 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-crown"></i> YukiMart Platform
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> {{ $user->full_name ?? $user->email }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('platform.admin.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Platform Header -->
    <div class="platform-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">🏢 Platform Management Dashboard</h1>
                    <p class="mb-0">Welcome to YukiMart Multi-Tenant Management Platform</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-light text-dark p-2">
                        <i class="fas fa-shield-alt"></i> Platform User
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-primary">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">6</h3>
                            <p class="text-muted mb-0">Total Tenants</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">26</h3>
                            <p class="text-muted mb-0">Total Users</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-warning">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">98%</h3>
                            <p class="text-muted mb-0">System Health</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-info">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0">5</h3>
                            <p class="text-muted mb-0">Active Services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-8">
                <div class="quick-actions">
                    <h4 class="mb-3"><i class="fas fa-bolt"></i> Quick Actions</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Tenant Management</h6>
                            <a href="#" class="action-btn">
                                <i class="fas fa-plus"></i> Create New Tenant
                            </a>
                            <a href="#" class="action-btn">
                                <i class="fas fa-list"></i> View All Tenants
                            </a>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>User Management</h6>
                            <a href="#" class="action-btn">
                                <i class="fas fa-user-plus"></i> Add Platform User
                            </a>
                            <a href="#" class="action-btn">
                                <i class="fas fa-users-cog"></i> Manage Users
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>System</h6>
                            <a href="#" class="action-btn">
                                <i class="fas fa-cog"></i> System Settings
                            </a>
                            <a href="#" class="action-btn">
                                <i class="fas fa-chart-bar"></i> Analytics
                            </a>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Monitoring</h6>
                            <a href="#" class="action-btn">
                                <i class="fas fa-heartbeat"></i> System Health
                            </a>
                            <a href="#" class="action-btn">
                                <i class="fas fa-file-alt"></i> View Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="quick-actions">
                    <h4 class="mb-3"><i class="fas fa-info-circle"></i> System Info</h4>
                    
                    <div class="mb-3">
                        <strong>Platform Version:</strong><br>
                        <span class="text-muted">YukiMart v2.0.0</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Laravel Version:</strong><br>
                        <span class="text-muted">{{ app()->version() }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>PHP Version:</strong><br>
                        <span class="text-muted">{{ PHP_VERSION }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Environment:</strong><br>
                        <span class="badge bg-{{ app()->environment() === 'production' ? 'success' : 'warning' }}">
                            {{ strtoupper(app()->environment()) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
