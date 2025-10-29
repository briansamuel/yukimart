<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Services\TenantContextService;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Only apply tenant scope if the model has a tenant_id column
        if ($this->modelHasTenantColumn($model)) {
            $tenantContextService = app(TenantContextService::class);
            $tenantId = $tenantContextService->getCurrentTenantId();
            
            if ($tenantId) {
                $builder->where($model->getTable() . '.tenant_id', $tenantId);
            }
        }
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder)
    {
        $this->addWithoutTenant($builder);
        $this->addWithTenant($builder);
        $this->addOnlyTenant($builder);
    }

    /**
     * Add the without-tenant extension to the builder.
     */
    protected function addWithoutTenant(Builder $builder)
    {
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the with-tenant extension to the builder.
     */
    protected function addWithTenant(Builder $builder)
    {
        $builder->macro('withTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutGlobalScope($this)
                          ->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });
    }

    /**
     * Add the only-tenant extension to the builder.
     */
    protected function addOnlyTenant(Builder $builder)
    {
        $builder->macro('onlyTenant', function (Builder $builder, $tenantId) {
            return $builder->withoutGlobalScope($this)
                          ->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });
    }

    /**
     * Check if the model has a tenant_id column
     */
    protected function modelHasTenantColumn(Model $model)
    {
        // Check if the model has tenant_id in fillable or if it exists in the table
        $fillable = $model->getFillable();
        $guarded = $model->getGuarded();
        
        // If tenant_id is in fillable, it exists
        if (in_array('tenant_id', $fillable)) {
            return true;
        }
        
        // If guarded is not ['*'] and tenant_id is not in guarded, it might exist
        if ($guarded !== ['*'] && !in_array('tenant_id', $guarded)) {
            return true;
        }
        
        // For models that don't specify fillable/guarded clearly, 
        // we'll assume they have tenant_id if they're tenant-aware models
        $tenantAwareModels = [
            'App\Models\Product',
            'App\Models\Category', 
            'App\Models\Customer',
            'App\Models\Supplier',
            'App\Models\Order',
            'App\Models\OrderItem',
            'App\Models\Invoice',
            'App\Models\InvoiceItem',
            'App\Models\ReturnOrder',
            'App\Models\ReturnOrderItem',
            'App\Models\Payment',
            'App\Models\BankAccount',
            'App\Models\Inventory',
            'App\Models\InventoryTransaction',
            'App\Models\ProductAttribute',
            'App\Models\ProductVariant',
            'App\Models\Warehouse',
            'App\Models\BranchShop',
            'App\Models\Notification',
            'App\Models\ShopeeToken',
            'App\Models\MarketplaceIntegration',
            'App\Models\LoyaltyTransaction',
            'App\Models\NotificationTemplate',
        ];
        
        return in_array(get_class($model), $tenantAwareModels);
    }
}
