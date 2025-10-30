<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\Product;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\InventoryDailyStats;
use App\Models\Analytics\SlowMovingInventory;
use App\Models\BranchShop;
use Illuminate\Http\Request;

class InventoryController extends BaseTenantController
{
    /**
     * Display inventory analytics
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        // Get inventory stats
        $query = InventoryDailyStats::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $inventoryData = $query->get();

        // Calculate aggregated metrics
        $metrics = $this->calculateMetrics($inventoryData);

        // Get low stock items
        $lowStockQuery = SlowMovingInventory::byTenant($tenantId)
            ->where('current_stock', '>', 0)
            ->where('aging_category', '!=', 'dead_stock');

        if ($branchShopId) {
            $lowStockQuery->where('branch_shop_id', $branchShopId);
        }

        $lowStockItems = $lowStockQuery->limit(20)->get();

        // Get overstock items
        $overstockQuery = SlowMovingInventory::byTenant($tenantId)
            ->where('aging_category', 'slow_moving');

        if ($branchShopId) {
            $overstockQuery->where('branch_shop_id', $branchShopId);
        }

        $overstockItems = $overstockQuery->limit(20)->get();

        // Get daily chart data
        $chartData = $inventoryData->map(function ($item) {
            return [
                'date' => $item->stats_date->format('Y-m-d'),
                'total_value' => (float) $item->total_inventory_value,
                'low_stock_value' => (float) $item->low_stock_value,
                'turnover_rate' => (float) $item->inventory_turnover_rate,
            ];
        })->toArray();

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.product.inventory', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'lowStockItems' => $lowStockItems,
            'overstockItems' => $overstockItems,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Calculate inventory metrics
     */
    protected function calculateMetrics($inventoryData)
    {
        $totalValue = $inventoryData->sum('total_inventory_value');
        $lowStockValue = $inventoryData->sum('low_stock_value');
        $overstockValue = $inventoryData->sum('overstock_value');
        $totalSkus = $inventoryData->sum('total_skus');
        $lowStockItems = $inventoryData->sum('low_stock_items');
        $outOfStockItems = $inventoryData->sum('out_of_stock_items');
        $avgTurnover = $inventoryData->count() > 0 
            ? $inventoryData->avg('inventory_turnover_rate') 
            : 0;

        return [
            'total_inventory_value' => $totalValue,
            'low_stock_value' => $lowStockValue,
            'overstock_value' => $overstockValue,
            'total_skus' => $totalSkus,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'avg_turnover_rate' => $avgTurnover,
            'low_stock_percentage' => $totalSkus > 0 ? ($lowStockItems / $totalSkus) * 100 : 0,
        ];
    }
}

