<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TenantContextService
{
    /**
     * Current tenant instance
     */
    protected ?Tenant $currentTenant = null;

    /**
     * Cache key prefix for tenant data
     */
    protected string $cachePrefix = 'tenant_context_';

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected int $cacheTtl = 3600;

    /**
     * Set the current tenant
     */
    public function setCurrentTenant(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        if ($tenant) {
            // Bind to service container
            app()->instance('current_tenant', $tenant);
            
            // Store in session
            Session::put('current_tenant_id', $tenant->id);
            
            // Cache tenant data
            $this->cacheTenantData($tenant);
            
            Log::info('Tenant context set', [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'tenant_name' => $tenant->name
            ]);
        } else {
            // Clear context
            app()->forgetInstance('current_tenant');
            Session::forget('current_tenant_id');
            
            Log::info('Tenant context cleared');
        }
    }

    /**
     * Get the current tenant
     */
    public function getCurrentTenant(): ?Tenant
    {
        if ($this->currentTenant) {
            return $this->currentTenant;
        }

        // Try to get from service container
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');
            if ($tenant instanceof Tenant) {
                $this->currentTenant = $tenant;
                return $tenant;
            }
        }

        // Try to get from session
        $tenantId = Session::get('current_tenant_id');
        if ($tenantId) {
            $tenant = $this->getTenantFromCache($tenantId) ?? Tenant::find($tenantId);
            if ($tenant) {
                $this->setCurrentTenant($tenant);
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Get current tenant ID
     */
    public function getCurrentTenantId(): ?int
    {
        $tenant = $this->getCurrentTenant();
        return $tenant?->id;
    }

    /**
     * Check if tenant context is set
     */
    public function hasTenantContext(): bool
    {
        return $this->getCurrentTenant() !== null;
    }

    /**
     * Switch to a different tenant
     */
    public function switchToTenant(int $tenantId, ?User $user = null): bool
    {
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            Log::warning('Attempted to switch to non-existent tenant', [
                'tenant_id' => $tenantId,
                'user_id' => $user?->id
            ]);
            return false;
        }

        // Check if user has access to this tenant
        if ($user && !$this->userCanAccessTenant($user, $tenant)) {
            Log::warning('User attempted to switch to unauthorized tenant', [
                'tenant_id' => $tenantId,
                'user_id' => $user->id
            ]);
            return false;
        }

        // Validate tenant status
        if (!$this->validateTenantForSwitch($tenant)) {
            Log::warning('Attempted to switch to invalid tenant', [
                'tenant_id' => $tenantId,
                'tenant_status' => $tenant->status,
                'user_id' => $user?->id
            ]);
            return false;
        }

        // Switch context
        $this->setCurrentTenant($tenant);

        // Update user's last access if provided
        if ($user) {
            $this->updateUserTenantAccess($user, $tenant);
        }

        Log::info('Tenant switched successfully', [
            'tenant_id' => $tenantId,
            'user_id' => $user?->id
        ]);

        return true;
    }

    /**
     * Get available tenants for user
     */
    public function getAvailableTenantsForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->tenants()
                   ->wherePivot('is_active', true)
                   ->whereIn('status', [Tenant::STATUS_ACTIVE, Tenant::STATUS_TRIAL])
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Get user's role in current tenant
     */
    public function getUserRoleInCurrentTenant(?User $user = null): ?string
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user || !$this->hasTenantContext()) {
            return null;
        }

        $tenant = $this->getCurrentTenant();
        return $user->getRoleInTenant($tenant->id);
    }

    /**
     * Check if user can perform action in current tenant
     */
    public function userCanPerformAction(string $action, ?User $user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user || !$this->hasTenantContext()) {
            return false;
        }

        $tenant = $this->getCurrentTenant();

        // Platform users have all permissions
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

        if (!$tenantUser || !$tenantUser->is_active) {
            return false;
        }

        // Check explicit permissions
        $permissions = $tenantUser->permissions ? json_decode($tenantUser->permissions, true) : [];
        if (in_array($action, $permissions) || in_array('*', $permissions)) {
            return true;
        }

        // Check role-based permissions
        return $this->checkRoleBasedPermission($tenantUser->role, $action);
    }

    /**
     * Check role-based permissions
     */
    protected function checkRoleBasedPermission(string $role, string $permission): bool
    {
        $rolePermissions = [
            'owner' => ['*'], // Owner has all permissions
            'admin' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'products.view', 'products.create', 'products.edit', 'products.delete',
                'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
                'returns.view', 'returns.create', 'returns.edit', 'returns.delete',
                'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
                'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
                'reports.view', 'reports.export',
                'settings.view', 'settings.edit'
            ],
            'manager' => [
                'products.view', 'products.create', 'products.edit',
                'orders.view', 'orders.create', 'orders.edit',
                'invoices.view', 'invoices.create', 'invoices.edit',
                'returns.view', 'returns.create', 'returns.edit',
                'payments.view', 'payments.create', 'payments.edit',
                'customers.view', 'customers.create', 'customers.edit',
                'reports.view'
            ],
            'staff' => [
                'products.view',
                'orders.view', 'orders.create',
                'invoices.view', 'invoices.create',
                'customers.view', 'customers.create'
            ],
            'viewer' => [
                'products.view',
                'orders.view',
                'invoices.view',
                'customers.view'
            ]
        ];

        $permissions = $rolePermissions[$role] ?? [];

        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    /**
     * Get tenant settings
     */
    public function getTenantSetting(string $key, $default = null)
    {
        $tenant = $this->getCurrentTenant();
        
        if (!$tenant) {
            return $default;
        }

        return $tenant->getSetting($key, $default);
    }

    /**
     * Set tenant setting
     */
    public function setTenantSetting(string $key, $value): bool
    {
        $tenant = $this->getCurrentTenant();
        
        if (!$tenant) {
            return false;
        }

        $tenant->setSetting($key, $value);
        return true;
    }

    /**
     * Clear tenant context
     */
    public function clearContext(): void
    {
        $this->setCurrentTenant(null);
    }

    /**
     * Get tenant cache key
     */
    protected function getTenantCacheKey(int $tenantId): string
    {
        return $this->cachePrefix . $tenantId;
    }

    /**
     * Cache tenant data
     */
    protected function cacheTenantData(Tenant $tenant): void
    {
        $cacheKey = $this->getTenantCacheKey($tenant->id);
        
        Cache::put($cacheKey, [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'features' => $tenant->features,
            'settings' => $tenant->settings,
            'cached_at' => Carbon::now()
        ], $this->cacheTtl);
    }

    /**
     * Get tenant from cache
     */
    protected function getTenantFromCache(int $tenantId): ?Tenant
    {
        $cacheKey = $this->getTenantCacheKey($tenantId);
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return null;
        }

        // Create tenant instance from cached data
        $tenant = new Tenant();
        $tenant->id = $cachedData['id'];
        $tenant->name = $cachedData['name'];
        $tenant->slug = $cachedData['slug'];
        $tenant->status = $cachedData['status'];
        $tenant->features = $cachedData['features'];
        $tenant->settings = $cachedData['settings'];
        $tenant->exists = true;

        return $tenant;
    }

    /**
     * Check if user can access tenant
     */
    protected function userCanAccessTenant(User $user, Tenant $tenant): bool
    {
        $tenantUser = $user->tenants()->where('tenants.id', $tenant->id)->first();
        
        if (!$tenantUser) {
            return false;
        }

        return $tenantUser->pivot->is_active && 
               $tenantUser->pivot->invitation_status === TenantUser::INVITATION_ACCEPTED;
    }

    /**
     * Validate tenant for switching
     */
    protected function validateTenantForSwitch(Tenant $tenant): bool
    {
        // Check status
        if (!in_array($tenant->status, [Tenant::STATUS_ACTIVE, Tenant::STATUS_TRIAL])) {
            return false;
        }

        // Check expiry
        if ($tenant->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Update user's tenant access timestamp
     */
    protected function updateUserTenantAccess(User $user, Tenant $tenant): void
    {
        try {
            $user->tenants()->updateExistingPivot($tenant->id, [
                'last_access_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update user tenant access', [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStatistics(): array
    {
        $tenant = $this->getCurrentTenant();
        
        if (!$tenant) {
            return [];
        }

        return [
            'users' => [
                'current' => $tenant->current_users,
                'max' => $tenant->max_users,
                'remaining' => $tenant->getRemainingUsers(),
                'percentage' => $tenant->max_users > 0 ? round(($tenant->current_users / $tenant->max_users) * 100, 2) : 0
            ],
            'products' => [
                'current' => $tenant->current_products,
                'max' => $tenant->max_products,
                'remaining' => $tenant->getRemainingProducts(),
                'percentage' => $tenant->max_products > 0 ? round(($tenant->current_products / $tenant->max_products) * 100, 2) : 0
            ],
            'storage' => [
                'current' => $tenant->current_storage_used,
                'max' => $tenant->storage_limit,
                'remaining' => $tenant->getRemainingStorage(),
                'percentage' => $tenant->storage_limit > 0 ? round(($tenant->current_storage_used / $tenant->storage_limit) * 100, 2) : 0
            ]
        ];
    }
}
