<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Services\TenantContextService;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope if model has tenant_id column
        if (!$this->modelHasTenantColumn($model)) {
            return;
        }

        // Get current tenant ID
        $tenantId = $this->getCurrentTenantId();

        // Apply tenant filter if we have a current tenant
        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        } else {
            // If no tenant context, apply restrictive filter for security
            // This prevents accidental data leaks when tenant context is missing
            $this->applySecurityFilter($builder, $model);
        }
    }

    /**
     * Extend the query builder with additional methods.
     */
    public function extend(Builder $builder): void
    {
        // Add withoutTenantScope method
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        // Add allTenants method (for admin use)
        $builder->macro('allTenants', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        // Add forTenant method
        $builder->macro('forTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutGlobalScope($this)
                          ->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });

        // Add forTenants method (multiple tenants)
        $builder->macro('forTenants', function (Builder $builder, array $tenantIds) {
            return $builder->withoutGlobalScope($this)
                          ->whereIn($builder->getModel()->getTable() . '.tenant_id', $tenantIds);
        });

        // Add currentTenant method
        $builder->macro('currentTenant', function (Builder $builder) {
            $tenantId = $this->getCurrentTenantId();
            if ($tenantId) {
                return $builder->withoutGlobalScope($this)
                              ->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
            
            // Return empty result if no current tenant
            return $builder->whereRaw('1 = 0');
        });

        // Add exceptTenant method
        $builder->macro('exceptTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutGlobalScope($this)
                          ->where($builder->getModel()->getTable() . '.tenant_id', '!=', $tenantId);
        });

        // Add tenantAware method for relationships
        $builder->macro('tenantAware', function (Builder $builder) {
            $tenantId = $this->getCurrentTenantId();
            if ($tenantId) {
                return $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
            return $builder;
        });
    }

    /**
     * Get the current tenant ID from TenantContextService
     */
    protected function getCurrentTenantId(): ?int
    {
        try {
            $tenantContextService = app(TenantContextService::class);
            return $tenantContextService->getCurrentTenantId();
        } catch (\Exception $e) {
            // Fallback to legacy method if service is not available
            return $this->getLegacyTenantId();
        }
    }

    /**
     * Legacy method to get tenant ID (fallback)
     */
    protected function getLegacyTenantId(): ?int
    {
        // Priority 1: Check if tenant is bound in service container
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');
            return is_object($tenant) ? $tenant->id : $tenant;
        }

        // Priority 2: Check session
        if (session()->has('current_tenant_id')) {
            return session('current_tenant_id');
        }

        // Priority 3: Check request parameters
        if (request()->has('tenant_id')) {
            return request('tenant_id');
        }

        // Priority 4: Check route parameters
        if (request()->route() && request()->route()->hasParameter('tenant')) {
            $tenant = request()->route()->parameter('tenant');
            return is_object($tenant) ? $tenant->id : $tenant;
        }

        return null;
    }

    /**
     * Check if model has tenant_id column
     */
    protected function modelHasTenantColumn(Model $model): bool
    {
        static $cache = [];
        
        $table = $model->getTable();
        
        if (!isset($cache[$table])) {
            try {
                $cache[$table] = $model->getConnection()
                                      ->getSchemaBuilder()
                                      ->hasColumn($table, 'tenant_id');
            } catch (\Exception $e) {
                // If we can't check the column, assume it doesn't exist
                $cache[$table] = false;
            }
        }

        return $cache[$table];
    }

    /**
     * Apply security filter when no tenant context is available
     */
    protected function applySecurityFilter(Builder $builder, Model $model): void
    {
        // Check if we should allow access without tenant context
        if ($this->shouldAllowWithoutTenant($model)) {
            return;
        }

        // Apply restrictive filter to prevent data leaks
        // This will return no results when tenant context is missing
        $builder->whereRaw('1 = 0');
    }

    /**
     * Check if model should allow access without tenant context
     */
    protected function shouldAllowWithoutTenant(Model $model): bool
    {
        // Allow for certain system models or admin contexts
        $allowedModels = [
            \App\Models\Tenant::class,
            // Add other models that should be accessible without tenant context
        ];

        $modelClass = get_class($model);
        
        // Check if model is in allowed list
        if (in_array($modelClass, $allowedModels)) {
            return true;
        }

        // Check if current user is super admin
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        }

        // Check if we're in console/artisan command context
        if (app()->runningInConsole()) {
            return true;
        }

        // Check if we're in testing environment
        if (app()->environment('testing')) {
            return true;
        }

        return false;
    }

    /**
     * Try to resolve tenant from subdomain
     */
    protected function resolveTenantFromSubdomain(): ?int
    {
        if (!request()->getHost()) {
            return null;
        }

        $host = request()->getHost();
        $parts = explode('.', $host);

        // If we have at least 3 parts (subdomain.domain.tld)
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            
            // Skip common subdomains
            $skipSubdomains = ['www', 'api', 'admin', 'app'];
            if (in_array($subdomain, $skipSubdomains)) {
                return null;
            }

            // Try to find tenant by subdomain
            try {
                $tenant = \App\Models\Tenant::where('subdomain', $subdomain)->first();
                return $tenant ? $tenant->id : null;
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Check if scope should be applied based on context
     */
    protected function shouldApplyScope(Model $model): bool
    {
        // Don't apply scope for Tenant model itself
        if ($model instanceof \App\Models\Tenant) {
            return false;
        }

        // Don't apply scope if model doesn't have tenant_id column
        if (!$this->modelHasTenantColumn($model)) {
            return false;
        }

        // Don't apply scope in certain contexts
        if ($this->isExemptContext()) {
            return false;
        }

        return true;
    }

    /**
     * Check if current context is exempt from tenant scoping
     */
    protected function isExemptContext(): bool
    {
        // Exempt during migrations
        if (app()->runningInConsole() && 
            (str_contains(request()->server('argv')[1] ?? '', 'migrate') ||
             str_contains(request()->server('argv')[1] ?? '', 'seed'))) {
            return true;
        }

        // Exempt for super admin users
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log tenant scope application for debugging
     */
    protected function logScopeApplication(Builder $builder, Model $model, ?int $tenantId): void
    {
        if (config('app.debug') && config('tenant.log_scope_application', false)) {
            \Log::debug('TenantScope applied', [
                'model' => get_class($model),
                'table' => $model->getTable(),
                'tenant_id' => $tenantId,
                'query' => $builder->toSql(),
                'bindings' => $builder->getBindings()
            ]);
        }
    }
}
