<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LayoutModeMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware determines whether to use Platform layout or Tenant layout
     * based on user roles and current context.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            
            // Determine layout mode
            $layoutMode = $this->determineLayoutMode($request, $user);
            
            // Share layout mode with all views
            View::share('layoutMode', $layoutMode);
            
            // Set layout mode in session for JavaScript access
            session(['layout_mode' => $layoutMode]);
        }
        
        return $next($request);
    }
    
    /**
     * Determine which layout mode to use
     *
     * @param Request $request
     * @param $user
     * @return string
     */
    private function determineLayoutMode(Request $request, $user): string
    {
        // Check if this is a platform route
        if ($request->is('admin/platform*')) {
            return 'platform';
        }
        
        // Check if user has platform roles
        $platformRoles = ['admin', 'superadmin', 'dev', 'manager', 'shop_manager'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));
        
        // Check if user is in tenant switching mode
        $isInTenantMode = session('current_tenant_id') !== null;
        
        // Platform users can switch between modes
        if ($hasPlatformRole) {
            // If platform user is in tenant mode, use tenant layout
            if ($isInTenantMode) {
                return 'tenant';
            }
            
            // If accessing tenant-specific routes, use tenant layout
            if ($this->isTenantRoute($request)) {
                return 'tenant';
            }
            
            // Default to platform layout for platform users
            return 'platform';
        }
        
        // Regular tenant users always use tenant layout
        return 'tenant';
    }
    
    /**
     * Check if the current route is tenant-specific
     *
     * @param Request $request
     * @return bool
     */
    private function isTenantRoute(Request $request): bool
    {
        $tenantRoutes = [
            'admin/dashboard',
            'admin/products*',
            'admin/orders*',
            'admin/customers*',
            'admin/invoices*',
            'admin/returns*',
            'admin/payments*',
            'admin/inventory*',
            'admin/branch-shops*',
            'admin/reports*',
            'admin/settings*',
            'admin/users*',
            'admin/quick-order*'
        ];
        
        foreach ($tenantRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }
        
        return false;
    }
}
