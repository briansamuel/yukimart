<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContextService;

class BaseTenantController extends Controller
{
    protected $tenantContextService;

    public function __construct()
    {
        // Apply tenant middleware to all tenant controllers
        // Note: Routes already have 'auth:tenant' and 'tenant.resolve' middleware
        // So we don't duplicate them here
        
        // Initialize tenant context service
        $this->tenantContextService = app(TenantContextService::class);
    }

    /**
     * Get current tenant ID
     */
    protected function getCurrentTenantId()
    {
        return $this->tenantContextService->getCurrentTenantId();
    }

    /**
     * Get current tenant
     */
    protected function getCurrentTenant()
    {
        return $this->tenantContextService->getCurrentTenant();
    }

    /**
     * Check if user has permission in current tenant
     */
    protected function userCan($permission, $user = null)
    {
        $user = $user ?? auth('tenant')->user();
        return $this->tenantContextService->userCanPerformAction($permission, $user);
    }

    /**
     * Get user role in current tenant
     */
    protected function getUserRole($user = null)
    {
        $user = $user ?? auth('tenant')->user();
        return $this->tenantContextService->getUserRoleInCurrentTenant($user);
    }

    /**
     * Apply tenant scope to query builder
     */
    protected function applyTenantScope($query)
    {
        $tenantId = $this->getCurrentTenantId();
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get tenant setting
     */
    protected function getTenantSetting($key, $default = null)
    {
        return $this->tenantContextService->getTenantSetting($key, $default);
    }

    /**
     * Set tenant setting
     */
    protected function setTenantSetting($key, $value)
    {
        return $this->tenantContextService->setTenantSetting($key, $value);
    }

    /**
     * Get tenant statistics
     */
    protected function getTenantStatistics()
    {
        return $this->tenantContextService->getTenantStatistics();
    }

    /**
     * Check if tenant has feature enabled
     */
    protected function tenantHasFeature($feature)
    {
        $tenant = $this->getCurrentTenant();
        if (!$tenant) {
            return false;
        }

        $features = $tenant->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Check tenant limits
     */
    protected function checkTenantLimit($type)
    {
        $tenant = $this->getCurrentTenant();
        if (!$tenant) {
            return false;
        }

        switch ($type) {
            case 'users':
                return $tenant->current_users < $tenant->max_users;
            case 'products':
                return $tenant->current_products < $tenant->max_products;
            case 'branch_shops':
                return $tenant->current_branch_shops < $tenant->max_branch_shops;
            case 'storage':
                return $tenant->current_storage_used < $tenant->storage_limit;
            default:
                return true;
        }
    }

    /**
     * Get available tenants for current user
     */
    protected function getAvailableTenants($user = null)
    {
        $user = $user ?? auth('tenant')->user();
        return $this->tenantContextService->getAvailableTenantsForUser($user);
    }

    /**
     * Switch to different tenant
     */
    protected function switchTenant($tenantId, $user = null)
    {
        $user = $user ?? auth('tenant')->user();
        return $this->tenantContextService->switchToTenant($tenantId, $user);
    }

    /**
     * Log tenant activity
     */
    protected function logTenantActivity($action, $description = null, $metadata = [])
    {
        $tenant = $this->getCurrentTenant();
        $user = auth('tenant')->user();

        if ($tenant && $user) {
            \App\Models\TenantActivityLog::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'action' => $action,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Return tenant-aware JSON response
     */
    protected function tenantResponse($data = [], $message = null, $status = 200)
    {
        $tenant = $this->getCurrentTenant();
        
        return response()->json([
            'success' => $status >= 200 && $status < 300,
            'message' => $message,
            'data' => $data,
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ] : null,
            'timestamp' => now()->toISOString(),
        ], $status);
    }

    /**
     * Return tenant-aware error response
     */
    protected function tenantErrorResponse($message, $status = 400, $errors = [])
    {
        return $this->tenantResponse([
            'errors' => $errors
        ], $message, $status);
    }

    /**
     * Return tenant-aware success response
     */
    protected function tenantSuccessResponse($data = [], $message = 'Success')
    {
        return $this->tenantResponse($data, $message, 200);
    }
}

