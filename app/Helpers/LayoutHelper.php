<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class LayoutHelper
{
    /**
     * Determine which layout to use based on user context
     *
     * @return string
     */
    public static function getLayout(): string
    {
        // Check if user is authenticated
        if (!Auth::guard('admin')->check()) {
            return 'admin.layouts.app'; // Default layout for non-authenticated
        }

        $user = Auth::guard('admin')->user();
        
        // Check if user has platform roles
        $platformRoles = ['admin', 'superadmin', 'dev', 'manager', 'shop_manager'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));
        
        // Check current context
        $isInTenantMode = session('current_tenant_id') !== null;
        $isPlatformRoute = request()->is('admin/platform*');
        
        // Platform users logic
        if ($hasPlatformRole) {
            // If accessing platform routes, use platform layout
            if ($isPlatformRoute) {
                return 'admin.layouts.platform-app';
            }
            
            // If in tenant mode, use tenant layout
            if ($isInTenantMode) {
                return 'admin.layouts.tenant-app';
            }
            
            // Default to platform layout for platform users
            return 'admin.layouts.platform-app';
        }
        
        // Regular tenant users always use tenant layout
        return 'admin.layouts.tenant-app';
    }
    
    /**
     * Check if current user is a platform user
     *
     * @return bool
     */
    public static function isPlatformUser(): bool
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }

        $user = Auth::guard('admin')->user();
        $platformRoles = ['admin', 'superadmin', 'dev', 'manager', 'shop_manager'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        return !empty(array_intersect($platformRoles, $userRoles));
    }
    
    /**
     * Check if current user is in tenant mode
     *
     * @return bool
     */
    public static function isInTenantMode(): bool
    {
        return session('current_tenant_id') !== null;
    }
    
    /**
     * Get current tenant information
     *
     * @return array|null
     */
    public static function getCurrentTenant(): ?array
    {
        return session('current_tenant');
    }
    
    /**
     * Get current tenant role
     *
     * @return string|null
     */
    public static function getCurrentTenantRole(): ?string
    {
        return session('current_tenant_role');
    }
    
    /**
     * Check if current route is platform route
     *
     * @return bool
     */
    public static function isPlatformRoute(): bool
    {
        return request()->is('admin/platform*');
    }
    
    /**
     * Get layout mode for JavaScript
     *
     * @return string
     */
    public static function getLayoutMode(): string
    {
        if (self::isPlatformRoute() || (self::isPlatformUser() && !self::isInTenantMode())) {
            return 'platform';
        }
        
        return 'tenant';
    }
    
    /**
     * Get navigation items based on user context
     *
     * @return array
     */
    public static function getNavigationItems(): array
    {
        $layoutMode = self::getLayoutMode();
        
        if ($layoutMode === 'platform') {
            return self::getPlatformNavigationItems();
        }
        
        return self::getTenantNavigationItems();
    }
    
    /**
     * Get platform navigation items
     *
     * @return array
     */
    private static function getPlatformNavigationItems(): array
    {
        return [
            [
                'title' => 'Platform Dashboard',
                'route' => 'platform.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'active' => request()->routeIs('platform.dashboard*')
            ],
            [
                'title' => 'Tenant Management',
                'icon' => 'fas fa-store',
                'children' => [
                    [
                        'title' => 'All Tenants',
                        'route' => 'platform.tenants.index',
                        'active' => request()->routeIs('platform.tenants.index')
                    ],
                    [
                        'title' => 'Create Tenant',
                        'route' => 'platform.tenants.create',
                        'active' => request()->routeIs('platform.tenants.create')
                    ],
                    [
                        'title' => 'Switch to Tenant',
                        'route' => 'platform.tenants.switch',
                        'active' => request()->routeIs('platform.tenants.switch')
                    ]
                ]
            ],
            [
                'title' => 'Platform Users',
                'icon' => 'fas fa-users-cog',
                'children' => [
                    [
                        'title' => 'All Users',
                        'route' => 'platform.users.index',
                        'active' => request()->routeIs('platform.users.index')
                    ],
                    [
                        'title' => 'Create User',
                        'route' => 'platform.users.create',
                        'active' => request()->routeIs('platform.users.create')
                    ],
                    [
                        'title' => 'Roles',
                        'route' => 'platform.roles.index',
                        'active' => request()->routeIs('platform.roles.*')
                    ]
                ]
            ],
            [
                'title' => 'System Management',
                'icon' => 'fas fa-cogs',
                'children' => [
                    [
                        'title' => 'Settings',
                        'route' => 'platform.system.settings',
                        'active' => request()->routeIs('platform.system.settings')
                    ],
                    [
                        'title' => 'Logs',
                        'route' => 'platform.system.logs',
                        'active' => request()->routeIs('platform.system.logs*')
                    ],
                    [
                        'title' => 'Performance',
                        'route' => 'platform.system.performance',
                        'active' => request()->routeIs('platform.system.performance*')
                    ],
                    [
                        'title' => 'Backup',
                        'route' => 'platform.system.backup',
                        'active' => request()->routeIs('platform.system.backup*')
                    ]
                ]
            ],
            [
                'title' => 'Analytics',
                'icon' => 'fas fa-chart-bar',
                'children' => [
                    [
                        'title' => 'Overview',
                        'route' => 'platform.analytics.overview',
                        'active' => request()->routeIs('platform.analytics.overview')
                    ],
                    [
                        'title' => 'Tenant Analytics',
                        'route' => 'platform.analytics.tenants',
                        'active' => request()->routeIs('platform.analytics.tenants')
                    ],
                    [
                        'title' => 'Performance Reports',
                        'route' => 'platform.analytics.performance',
                        'active' => request()->routeIs('platform.analytics.performance')
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get tenant navigation items
     *
     * @return array
     */
    private static function getTenantNavigationItems(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'active' => request()->routeIs('admin.dashboard*')
            ],
            [
                'title' => 'Sản phẩm',
                'icon' => 'fas fa-box',
                'children' => [
                    [
                        'title' => 'Danh sách sản phẩm',
                        'route' => 'admin.products.index',
                        'active' => request()->routeIs('admin.products.index')
                    ],
                    [
                        'title' => 'Thêm sản phẩm',
                        'route' => 'admin.products.create',
                        'active' => request()->routeIs('admin.products.create')
                    ],
                    [
                        'title' => 'Danh mục',
                        'route' => 'admin.categories.index',
                        'active' => request()->routeIs('admin.categories.*')
                    ]
                ]
            ],
            [
                'title' => 'Đơn hàng',
                'icon' => 'fas fa-shopping-cart',
                'children' => [
                    [
                        'title' => 'Danh sách đơn hàng',
                        'route' => 'admin.orders.index',
                        'active' => request()->routeIs('admin.orders.index')
                    ],
                    [
                        'title' => 'Bán hàng nhanh',
                        'route' => 'admin.quick-order.index',
                        'active' => request()->routeIs('admin.quick-order.*')
                    ]
                ]
            ],
            [
                'title' => 'Khách hàng',
                'icon' => 'fas fa-users',
                'children' => [
                    [
                        'title' => 'Danh sách khách hàng',
                        'route' => 'admin.customers.index',
                        'active' => request()->routeIs('admin.customers.*')
                    ]
                ]
            ],
            [
                'title' => 'Giao dịch',
                'icon' => 'fas fa-file-invoice',
                'children' => [
                    [
                        'title' => 'Hóa đơn',
                        'route' => 'admin.invoices.index',
                        'active' => request()->routeIs('admin.invoices.*')
                    ],
                    [
                        'title' => 'Trả hàng',
                        'route' => 'admin.returns.index',
                        'active' => request()->routeIs('admin.returns.*')
                    ],
                    [
                        'title' => 'Thanh toán',
                        'route' => 'admin.payments.index',
                        'active' => request()->routeIs('admin.payments.*')
                    ]
                ]
            ]
        ];
    }
}
