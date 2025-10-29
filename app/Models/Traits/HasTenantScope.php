<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Services\TenantContextService;

trait HasTenantScope
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTenantScope()
    {
        static::addGlobalScope(new TenantScope);
        
        // Automatically set tenant_id when creating new models
        static::creating(function ($model) {
            if (!$model->tenant_id) {
                $tenantContextService = app(TenantContextService::class);
                $tenantId = $tenantContextService->getCurrentTenantId();
                
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope a query to only include models for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope a query to only include models for the current tenant.
     */
    public function scopeForCurrentTenant($query)
    {
        $tenantContextService = app(TenantContextService::class);
        $tenantId = $tenantContextService->getCurrentTenantId();
        
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }
        
        return $query;
    }

    /**
     * Scope a query to exclude tenant filtering.
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Check if the model belongs to the current tenant.
     */
    public function belongsToCurrentTenant()
    {
        $tenantContextService = app(TenantContextService::class);
        $currentTenantId = $tenantContextService->getCurrentTenantId();
        
        return $this->tenant_id === $currentTenantId;
    }

    /**
     * Check if the model belongs to a specific tenant.
     */
    public function belongsToTenant($tenantId)
    {
        return $this->tenant_id === $tenantId;
    }

    /**
     * Get all models without tenant scope.
     */
    public static function allWithoutTenant()
    {
        return static::withoutGlobalScope(TenantScope::class)->get();
    }

    /**
     * Find a model by ID without tenant scope.
     */
    public static function findWithoutTenant($id)
    {
        return static::withoutGlobalScope(TenantScope::class)->find($id);
    }

    /**
     * Create a new model instance for the current tenant.
     */
    public static function createForCurrentTenant(array $attributes = [])
    {
        $tenantContextService = app(TenantContextService::class);
        $tenantId = $tenantContextService->getCurrentTenantId();
        
        if ($tenantId) {
            $attributes['tenant_id'] = $tenantId;
        }
        
        return static::create($attributes);
    }

    /**
     * Create a new model instance for a specific tenant.
     */
    public static function createForTenant($tenantId, array $attributes = [])
    {
        $attributes['tenant_id'] = $tenantId;
        return static::create($attributes);
    }

    /**
     * Update the model for the current tenant only.
     */
    public function updateForCurrentTenant(array $attributes = [])
    {
        if (!$this->belongsToCurrentTenant()) {
            throw new \Exception('Cannot update model that does not belong to current tenant.');
        }
        
        return $this->update($attributes);
    }

    /**
     * Delete the model for the current tenant only.
     */
    public function deleteForCurrentTenant()
    {
        if (!$this->belongsToCurrentTenant()) {
            throw new \Exception('Cannot delete model that does not belong to current tenant.');
        }
        
        return $this->delete();
    }

    /**
     * Get tenant statistics for this model.
     */
    public static function getTenantStats($tenantId = null)
    {
        if (!$tenantId) {
            $tenantContextService = app(TenantContextService::class);
            $tenantId = $tenantContextService->getCurrentTenantId();
        }
        
        if (!$tenantId) {
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
            ];
        }
        
        $query = static::withoutGlobalScope(TenantScope::class)
                      ->where('tenant_id', $tenantId);
        
        $total = $query->count();
        
        // Try to get active/inactive counts if status column exists
        $active = 0;
        $inactive = 0;
        
        try {
            $active = $query->where('status', 'active')->count();
            $inactive = $total - $active;
        } catch (\Exception $e) {
            // Status column might not exist, that's okay
        }
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];
    }
}
