<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\Product;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\SalesDailySummary;
use App\Models\Analytics\SlowMovingInventory;
use App\Models\Analytics\ProductBundleSuggestion;
use App\Models\BranchShop;
use Illuminate\Http\Request;

class OverviewController extends BaseTenantController
{
    /**
     * Display product analytics overview
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        // Get sales summary for product metrics
        $query = SalesDailySummary::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate);

        if ($branchShopId) {
            $query->byBranch($branchShopId);
        }

        $summaryData = $query->get();

        // Get slow moving inventory
        $slowMovingQuery = SlowMovingInventory::byTenant($tenantId);
        if ($branchShopId) {
            $slowMovingQuery->where('branch_shop_id', $branchShopId);
        }
        $slowMovingItems = $slowMovingQuery->slowMoving()->limit(10)->get();

        // Get dead stock
        $deadStockQuery = SlowMovingInventory::byTenant($tenantId);
        if ($branchShopId) {
            $deadStockQuery->where('branch_shop_id', $branchShopId);
        }
        $deadStockItems = $deadStockQuery->deadStock()->limit(10)->get();

        // Get bundle suggestions
        $bundleSuggestions = ProductBundleSuggestion::byTenant($tenantId)
            ->topRecommendations(10)
            ->get();

        // Calculate metrics
        $metrics = $this->calculateMetrics($summaryData);

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.product.overview', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'slowMovingItems' => $slowMovingItems,
            'deadStockItems' => $deadStockItems,
            'bundleSuggestions' => $bundleSuggestions,
        ]);
    }

    /**
     * Calculate product metrics
     */
    protected function calculateMetrics($summaryData)
    {
        $totalRevenue = $summaryData->sum('total_revenue');
        $totalOrders = $summaryData->sum('total_orders');
        $uniqueCustomers = $summaryData->sum('unique_customers');

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'unique_customers' => $uniqueCustomers,
            'avg_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
            'revenue_per_customer' => $uniqueCustomers > 0 ? $totalRevenue / $uniqueCustomers : 0,
        ];
    }
}

