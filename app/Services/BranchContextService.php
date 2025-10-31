<?php

namespace App\Services;

use App\Models\BranchShop;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BranchContextService
{
    protected $currentBranchShop = null;

    /**
     * Set the current branch shop
     */
    public function setCurrentBranchShop(?BranchShop $branchShop): void
    {
        $this->currentBranchShop = $branchShop;

        if ($branchShop) {
            // Bind to service container
            app()->instance('current_branch_shop', $branchShop);
            
            // Store in session
            Session::put('current_branch_shop_id', $branchShop->id);
            
            Log::info('Branch shop context set', [
                'branch_shop_id' => $branchShop->id,
                'branch_shop_code' => $branchShop->code,
                'branch_shop_name' => $branchShop->name
            ]);
        } else {
            // Clear context
            app()->forgetInstance('current_branch_shop');
            Session::forget('current_branch_shop_id');
            
            Log::info('Branch shop context cleared');
        }
    }

    /**
     * Get the current branch shop
     */
    public function getCurrentBranchShop(): ?BranchShop
    {
        if ($this->currentBranchShop) {
            return $this->currentBranchShop;
        }

        // Try to get from service container
        if (app()->bound('current_branch_shop')) {
            $branchShop = app('current_branch_shop');
            if ($branchShop instanceof BranchShop) {
                $this->currentBranchShop = $branchShop;
                return $branchShop;
            }
        }

        // Try to get from session
        $branchShopId = Session::get('current_branch_shop_id');
        if ($branchShopId) {
            $branchShop = BranchShop::find($branchShopId);
            if ($branchShop && $branchShop->status === 'active') {
                $this->setCurrentBranchShop($branchShop);
                return $branchShop;
            }
        }

        // Try to get user's primary branch shop
        $user = Auth::user();
        if ($user) {
            $primaryBranchShop = $user->branchShops()
                ->wherePivot('is_primary', true)
                ->wherePivot('is_active', true)
                ->where('status', 'active')
                ->first();
                
            if ($primaryBranchShop) {
                $this->setCurrentBranchShop($primaryBranchShop);
                return $primaryBranchShop;
            }

            // If no primary, get first active branch shop
            $firstBranchShop = $user->branchShops()
                ->wherePivot('is_active', true)
                ->where('status', 'active')
                ->first();
                
            if ($firstBranchShop) {
                $this->setCurrentBranchShop($firstBranchShop);
                return $firstBranchShop;
            }
        }

        return null;
    }

    /**
     * Get current branch shop ID
     */
    public function getCurrentBranchShopId(): ?int
    {
        $branchShop = $this->getCurrentBranchShop();
        return $branchShop?->id;
    }

    /**
     * Get available branch shops for current user
     */
    public function getAvailableBranchShops()
    {
        $user = Auth::user();
        if (!$user) {
            return collect();
        }

        // Get current tenant ID
        $tenantContextService = app(\App\Services\TenantContextService::class);
        $currentTenantId = $tenantContextService->getCurrentTenantId();

        // Get user's branch shops filtered by current tenant
        $query = $user->branchShops()
            ->wherePivot('is_active', true)
            ->where('status', 'active');

        // Filter by tenant if available
        if ($currentTenantId) {
            $query->where('branch_shops.tenant_id', $currentTenantId);
        }

        return $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Switch to a specific branch shop
     * Updates is_primary in database AND saves to session
     * Sets selected branch as is_primary = true, all other branches = false
     */
    public function switchToBranchShop(int $branchShopId): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Get current tenant ID
        $tenantContextService = app(\App\Services\TenantContextService::class);
        $currentTenantId = $tenantContextService->getCurrentTenantId();

        // Check if user has access to this branch shop
        $query = $user->branchShops()
            ->wherePivot('is_active', true)
            ->where('branch_shops.id', $branchShopId)
            ->where('branch_shops.status', 'active');

        // Filter by tenant if available
        if ($currentTenantId) {
            $query->where('branch_shops.tenant_id', $currentTenantId);
        }

        $branchShop = $query->first();

        if (!$branchShop) {
            Log::warning('User attempted to switch to unauthorized branch shop', [
                'user_id' => $user->id,
                'branch_shop_id' => $branchShopId,
                'tenant_id' => $currentTenantId
            ]);
            return false;
        }

        // Update is_primary in database
        // Step 1: Set all branches to is_primary = false for this user
        $user->branchShops()->updateExistingPivot(
            $user->branchShops()->pluck('branch_shops.id')->toArray(),
            ['is_primary' => false]
        );

        // Step 2: Set selected branch to is_primary = true
        $user->branchShops()->updateExistingPivot($branchShopId, ['is_primary' => true]);

        // Step 3: Save to session
        $this->setCurrentBranchShop($branchShop);

        Log::info('Branch shop switched successfully (DB + session)', [
            'user_id' => $user->id,
            'branch_shop_id' => $branchShopId,
            'branch_shop_name' => $branchShop->name,
            'tenant_id' => $currentTenantId,
            'is_primary_updated' => true
        ]);

        return true;
    }

    /**
     * Clear current branch shop context
     */
    public function clearBranchShopContext(): void
    {
        $this->setCurrentBranchShop(null);
    }

    /**
     * Check if user has access to branch shop
     */
    public function hasAccessToBranchShop(int $branchShopId): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->branchShops()
            ->wherePivot('is_active', true)
            ->where('branch_shops.id', $branchShopId)
            ->where('branch_shops.status', 'active')
            ->exists();
    }

    /**
     * Get user's role in current branch shop
     */
    public function getCurrentBranchShopRole(): ?string
    {
        $user = Auth::user();
        $branchShop = $this->getCurrentBranchShop();
        
        if (!$user || !$branchShop) {
            return null;
        }

        $pivot = $user->branchShops()
            ->where('branch_shops.id', $branchShop->id)
            ->first()?->pivot;

        return $pivot?->role_in_shop;
    }

    /**
     * Check if user is manager in current branch shop
     */
    public function isManagerInCurrentBranchShop(): bool
    {
        return $this->getCurrentBranchShopRole() === 'manager';
    }

    /**
     * Get branch shop statistics for current user
     */
    public function getBranchShopStatistics(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $totalBranchShops = $user->branchShops()
            ->wherePivot('is_active', true)
            ->where('status', 'active')
            ->count();

        $primaryBranchShop = $user->branchShops()
            ->wherePivot('is_primary', true)
            ->wherePivot('is_active', true)
            ->where('status', 'active')
            ->first();

        return [
            'total_branch_shops' => $totalBranchShops,
            'has_primary' => $primaryBranchShop !== null,
            'primary_branch_shop' => $primaryBranchShop,
            'current_branch_shop' => $this->getCurrentBranchShop()
        ];
    }
}
