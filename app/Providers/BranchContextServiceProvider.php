<?php

namespace App\Providers;

use App\Services\BranchContextService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class BranchContextServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BranchContextService::class, function ($app) {
            return new BranchContextService();
        });

        $this->app->alias(BranchContextService::class, 'branch.context');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share branch context data with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $branchContextService = app(BranchContextService::class);
                
                $view->with([
                    'currentBranchShop' => $branchContextService->getCurrentBranchShop(),
                    'availableBranchShops' => $branchContextService->getAvailableBranchShops(),
                    'branchShopStatistics' => $branchContextService->getBranchShopStatistics()
                ]);
            }
        });

        // Specific composer for branch-switcher component
        View::composer('components.branch-switcher', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $branchContextService = app(BranchContextService::class);
                $tenantContextService = app(\App\Services\TenantContextService::class);

                // Get current tenant ID
                $currentTenantId = $tenantContextService->getCurrentTenantId();

                // Check if user has permission to see branch switcher
                // Only Owner, Admin, Manager can see branch switcher
                $canSeeBranchSwitcher = false;
                $userRole = null;

                if ($currentTenantId) {
                    $userRole = $user->getRoleInTenant($currentTenantId);
                    $canSeeBranchSwitcher = in_array($userRole, [
                        \App\Models\TenantUser::ROLE_OWNER,
                        \App\Models\TenantUser::ROLE_ADMIN,
                        \App\Models\TenantUser::ROLE_MANAGER
                    ]);
                }

                // Get current branch shop (from session or is_primary)
                $currentBranchShop = $branchContextService->getCurrentBranchShop();

                // Get available branch shops based on user role
                // Owner: All branch shops in tenant
                // Admin/Manager: Only branch shops they are assigned to (from user_branch_shops)
                $availableBranchShops = collect();

                if ($canSeeBranchSwitcher && $currentTenantId) {
                    if ($userRole === \App\Models\TenantUser::ROLE_OWNER) {
                        // Owner can see all branch shops in tenant
                        $availableBranchShops = \App\Models\BranchShop::where('tenant_id', $currentTenantId)
                            ->where('status', 'active')
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->get();
                    } else {
                        // Admin/Manager can only see their assigned branch shops
                        $availableBranchShops = $branchContextService->getAvailableBranchShops();
                    }
                }

                $view->with([
                    'currentBranchShop' => $currentBranchShop,
                    'availableBranchShops' => $availableBranchShops,
                    'canSeeBranchSwitcher' => $canSeeBranchSwitcher,
                    'userRole' => $userRole
                ]);
            } else {
                $view->with([
                    'currentBranchShop' => null,
                    'availableBranchShops' => collect(),
                    'canSeeBranchSwitcher' => false,
                    'userRole' => null
                ]);
            }
        });
    }
}
