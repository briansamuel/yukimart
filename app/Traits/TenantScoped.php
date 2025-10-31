<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TenantScoped
{
    /**
     * Boot the tenant scoped trait for a model.
     */
    protected static function bootTenantScoped(): void
    {
        // Add global scope to automatically filter by tenant_id
        static::addGlobalScope(new TenantScope);

        // Automatically set tenant_id when creating new records
        static::creating(function (Model $model) {
            if (static::shouldSetTenantId($model)) {
                $model->tenant_id = static::getCurrentTenantId();
            }
        });

        // Prevent updating tenant_id after creation (security measure)
        static::updating(function (Model $model) {
            if ($model->isDirty('tenant_id') && $model->getOriginal('tenant_id')) {
                throw new \Exception('Cannot change tenant_id after record creation for security reasons');
            }
        });
    }

    /**
     * Get the current tenant ID
     */
    public static function getCurrentTenantId(): ?int
    {
        // Try to get tenant from current context
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');
            return $tenant ? $tenant->id : null;
        }

        // Try to get tenant from authenticated user
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'getCurrentTenant')) {
                $tenant = $user->getCurrentTenant();
                return $tenant ? $tenant->id : null;
            }

            // Fallback to user's tenant_id if available
            if (isset($user->tenant_id)) {
                return $user->tenant_id;
            }
        }

        // Try to get from session
        if (session()->has('current_tenant_id')) {
            return session('current_tenant_id');
        }

        // Try to get from request
        if (request()->has('tenant_id')) {
            return request('tenant_id');
        }

        return null;
    }

    /**
     * Set the current tenant for this request
     */
    public static function setCurrentTenant($tenant): void
    {
        $tenantId = is_object($tenant) ? $tenant->id : $tenant;
        
        app()->instance('current_tenant', $tenant);
        session(['current_tenant_id' => $tenantId]);
    }

    /**
     * Check if we should set tenant_id for this model
     */
    protected static function shouldSetTenantId(Model $model): bool
    {
        // Don't set if tenant_id is already set
        if (isset($model->tenant_id) && $model->tenant_id) {
            return false;
        }

        // Don't set if model doesn't have tenant_id column
        if (!$model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'tenant_id')) {
            return false;
        }

        // Don't set if we can't determine current tenant
        if (!static::getCurrentTenantId()) {
            return false;
        }

        return true;
    }

    /**
     * Create a new query without the tenant scope
     */
    public static function withoutTenantScope()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    /**
     * Create a new query for all tenants (admin use)
     */
    public static function allTenants()
    {
        return static::withoutTenantScope();
    }

    /**
     * Create a new query for specific tenant
     */
    public static function forTenant($tenantId)
    {
        return static::withoutTenantScope()->where('tenant_id', $tenantId);
    }

    /**
     * Get records for multiple tenants
     */
    public static function forTenants(array $tenantIds)
    {
        return static::withoutTenantScope()->whereIn('tenant_id', $tenantIds);
    }

    /**
     * Switch tenant context temporarily
     */
    public static function asTenant($tenant, callable $callback)
    {
        $originalTenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        
        try {
            static::setCurrentTenant($tenant);
            return $callback();
        } finally {
            if ($originalTenant) {
                static::setCurrentTenant($originalTenant);
            } else {
                app()->forgetInstance('current_tenant');
                session()->forget('current_tenant_id');
            }
        }
    }

    /**
     * Check if current user can access this tenant's data
     */
    public function canAccessTenant(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        
        // Super admin can access all tenants
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // Check if user belongs to this tenant
        if (method_exists($user, 'belongsToTenant')) {
            return $user->belongsToTenant($this->tenant_id);
        }

        // Fallback: check if user's tenant_id matches
        return isset($user->tenant_id) && $user->tenant_id === $this->tenant_id;
    }

    /**
     * Get the tenant for this model
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Check if this record belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        $currentTenantId = static::getCurrentTenantId();
        return $currentTenantId && $this->tenant_id === $currentTenantId;
    }

    /**
     * Ensure record belongs to current tenant (throws exception if not)
     */
    public function ensureBelongsToCurrentTenant(): void
    {
        if (!$this->belongsToCurrentTenant()) {
            throw new \Exception('Access denied: Record does not belong to current tenant');
        }
    }

    /**
     * Get tenant-specific cache key
     */
    public function getTenantCacheKey(string $key): string
    {
        return "tenant_{$this->tenant_id}_{$key}";
    }

    /**
     * Scope to filter by current tenant
     */
    public function scopeCurrentTenant($query)
    {
        $tenantId = static::getCurrentTenantId();
        
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        // If no current tenant, return empty result for security
        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope to filter by specific tenant
     */
    public function scopeTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to exclude specific tenant
     */
    public function scopeExceptTenant($query, $tenantId)
    {
        return $query->where('tenant_id', '!=', $tenantId);
    }

    /**
     * Get tenant-aware relationship
     */
    public function tenantAwareRelation($relation, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        return $this->hasMany($relation, $foreignKey, $localKey)
                    ->where($relation::getTableName() . '.tenant_id', $this->tenant_id);
    }

    /**
     * Create tenant-aware belongs to relationship
     */
    public function tenantAwareBelongsTo($relation, $foreignKey = null, $ownerKey = null, $relationName = null)
    {
        $foreignKey = $foreignKey ?: $this->getDefaultForeignKeyName($relationName ?: $relation);
        $ownerKey = $ownerKey ?: 'id';

        return $this->belongsTo($relation, $foreignKey, $ownerKey)
                    ->where($relation::getTableName() . '.tenant_id', $this->tenant_id);
    }

    /**
     * Validate tenant consistency in relationships
     */
    public function validateTenantConsistency(): bool
    {
        // This method can be overridden in models to add specific validation
        return true;
    }

    /**
     * Get default foreign key name
     */
    private function getDefaultForeignKeyName(string $relation): string
    {
        return strtolower(class_basename($relation)) . '_id';
    }
}
