<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\TenantContextService;
use App\Models\TenantUser;

class TenantAuthMiddleware
{
    /**
     * The tenant context service
     */
    protected TenantContextService $tenantContext;

    /**
     * Create a new middleware instance
     */
    public function __construct(TenantContextService $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Handle an incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = Auth::user();
        $tenant = $this->tenantContext->getCurrentTenant();

        // Check if tenant context exists
        if (!$tenant) {
            return $this->handleMissingTenantContext($request);
        }

        // Check if user belongs to current tenant
        if (!$this->userBelongsToTenant($user, $tenant)) {
            return $this->handleUnauthorizedTenant($request, $user, $tenant);
        }

        // Check if user is active in tenant
        if (!$this->userIsActiveInTenant($user, $tenant)) {
            return $this->handleInactiveUser($request, $user, $tenant);
        }

        // Check specific permission if provided
        if ($permission && !$this->userHasPermission($user, $tenant, $permission)) {
            return $this->handleInsufficientPermissions($request, $user, $permission);
        }

        // Update user's last access
        $this->updateUserLastAccess($user, $tenant);

        // Add tenant user info to request
        $request->attributes->set('tenant_user', $this->getTenantUser($user, $tenant));

        return $next($request);
    }

    /**
     * Check if user belongs to tenant
     */
    protected function userBelongsToTenant($user, $tenant): bool
    {
        // Platform users (superadmin, admin, dev, manager) can access any tenant
        $platformRoles = ['superadmin', 'admin', 'dev', 'manager', 'support'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));

        if ($hasPlatformRole) {
            return true;
        }

        // Check tenant-specific access
        return $user->tenants()->where('tenants.id', $tenant->id)->exists();
    }

    /**
     * Check if user is active in tenant
     */
    protected function userIsActiveInTenant($user, $tenant): bool
    {
        // Platform users are always active
        $platformRoles = ['superadmin', 'admin', 'dev', 'manager', 'support'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));

        if ($hasPlatformRole) {
            return true;
        }

        // Check tenant user relationship
        $tenantUser = TenantUser::where('user_id', $user->id)
                               ->where('tenant_id', $tenant->id)
                               ->first();

        if (!$tenantUser) {
            return false;
        }

        // Check if user is active
        if (!$tenantUser->is_active) {
            return false;
        }

        // Check invitation status
        if ($tenantUser->invitation_status !== 'accepted') {
            return false;
        }

        // Check access expiry
        if ($tenantUser->access_expires_at && now()->gt($tenantUser->access_expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has specific permission
     */
    protected function userHasPermission($user, $tenant, string $permission): bool
    {
        // Platform users have all permissions
        $platformRoles = ['superadmin', 'admin', 'dev', 'manager', 'support'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));

        if ($hasPlatformRole) {
            return true;
        }

        // Check tenant user permissions
        $tenantUser = TenantUser::where('user_id', $user->id)
                               ->where('tenant_id', $tenant->id)
                               ->first();

        if (!$tenantUser) {
            return false;
        }

        $permissions = $tenantUser->permissions ? json_decode($tenantUser->permissions, true) : [];

        // Check for wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }

        // Check for specific permission
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check role-based permissions
        return $this->checkRoleBasedPermission($tenantUser->role, $permission);
    }

    /**
     * Check role-based permissions
     */
    protected function checkRoleBasedPermission(string $role, string $permission): bool
    {
        $rolePermissions = [
            TenantUser::ROLE_OWNER => ['*'], // Owner has all permissions
            TenantUser::ROLE_ADMIN => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'products.view', 'products.create', 'products.edit', 'products.delete',
                'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
                'reports.view', 'reports.export',
                'settings.view', 'settings.edit'
            ],
            TenantUser::ROLE_MANAGER => [
                'products.view', 'products.create', 'products.edit',
                'orders.view', 'orders.create', 'orders.edit',
                'invoices.view', 'invoices.create', 'invoices.edit',
                'reports.view'
            ],
            TenantUser::ROLE_STAFF => [
                'products.view',
                'orders.view', 'orders.create',
                'invoices.view', 'invoices.create'
            ],
            TenantUser::ROLE_VIEWER => [
                'products.view',
                'orders.view',
                'invoices.view'
            ]
        ];

        $permissions = $rolePermissions[$role] ?? [];

        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    /**
     * Get tenant user relationship
     */
    protected function getTenantUser($user, $tenant)
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->first();
    }

    /**
     * Update user's last access timestamp
     */
    protected function updateUserLastAccess($user, $tenant): void
    {
        try {
            $user->tenants()->updateExistingPivot($tenant->id, [
                'last_access_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update user last access', [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle unauthenticated request
     */
    protected function handleUnauthenticated(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Authentication required'
            ], 401);
        }

        return redirect()->guest(route('login'))
                        ->with('error', 'Vui lòng đăng nhập để tiếp tục');
    }

    /**
     * Handle missing tenant context
     */
    protected function handleMissingTenantContext(Request $request)
    {
        Log::warning('Missing tenant context for authenticated user', [
            'user_id' => Auth::id(),
            'request_url' => $request->url()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Missing tenant context',
                'message' => 'Tenant context is required'
            ], 400);
        }

        return redirect()->route('tenant.select')
                        ->with('error', 'Vui lòng chọn cửa hàng để tiếp tục');
    }

    /**
     * Handle unauthorized tenant access
     */
    protected function handleUnauthorizedTenant(Request $request, $user, $tenant)
    {
        Log::warning('User attempted to access unauthorized tenant', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'request_url' => $request->url()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized tenant access',
                'message' => 'You do not have access to this tenant'
            ], 403);
        }

        return redirect()->route('tenant.select')
                        ->with('error', 'Bạn không có quyền truy cập cửa hàng này');
    }

    /**
     * Handle inactive user
     */
    protected function handleInactiveUser(Request $request, $user, $tenant)
    {
        Log::warning('Inactive user attempted access', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'request_url' => $request->url()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Account inactive',
                'message' => 'Your account is inactive in this tenant'
            ], 403);
        }

        return redirect()->route('tenant.inactive')
                        ->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa');
    }

    /**
     * Handle insufficient permissions
     */
    protected function handleInsufficientPermissions(Request $request, $user, string $permission)
    {
        Log::warning('User attempted action without permission', [
            'user_id' => $user->id,
            'permission' => $permission,
            'request_url' => $request->url()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Insufficient permissions',
                'message' => 'You do not have permission to perform this action',
                'required_permission' => $permission
            ], 403);
        }

        return redirect()->back()
                        ->with('error', 'Bạn không có quyền thực hiện hành động này');
    }
}
