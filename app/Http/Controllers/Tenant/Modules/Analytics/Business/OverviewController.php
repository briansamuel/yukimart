<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\Business;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\SalesDailySummary;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OverviewController extends BaseTenantController
{
    /**
     * Display business overview analytics
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        // Get filter parameters
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        // Get sales summary data
        $query = SalesDailySummary::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate);

        if ($branchShopId) {
            $query->byBranch($branchShopId);
        }

        $summaryData = $query->get();

        // Calculate aggregated metrics
        $metrics = $this->calculateMetrics($summaryData);

        // Get daily data for chart
        $chartData = $summaryData->map(function ($item) {
            return [
                'date' => $item->summary_date->format('Y-m-d'),
                'revenue' => (float) $item->total_revenue,
                'return_amount' => (float) $item->total_return_amount,
                'net_revenue' => (float) $item->net_revenue,
                'profit' => (float) $item->total_profit,
                'orders' => (int) $item->total_orders,
            ];
        })->toArray();

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.business.overview', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Calculate aggregated metrics from summary data
     */
    protected function calculateMetrics($summaryData)
    {
        $totalRevenue = $summaryData->sum('total_revenue');
        $totalReturn = $summaryData->sum('total_return_amount');
        $netRevenue = $summaryData->sum('net_revenue');
        $totalCogs = $summaryData->sum('total_cogs');
        $totalProfit = $summaryData->sum('total_profit');
        $totalOrders = $summaryData->sum('total_orders');
        $uniqueCustomers = $summaryData->sum('unique_customers');
        $dayCount = $summaryData->count();

        return [
            'total_revenue' => $totalRevenue,
            'total_return' => $totalReturn,
            'net_revenue' => $netRevenue,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalProfit,
            'total_orders' => $totalOrders,
            'unique_customers' => $uniqueCustomers,
            'avg_revenue_per_day' => $dayCount > 0 ? $totalRevenue / $dayCount : 0,
            'avg_profit_per_day' => $dayCount > 0 ? $totalProfit / $dayCount : 0,
            'profit_margin' => $netRevenue > 0 ? ($totalProfit / $netRevenue) * 100 : 0,
            'avg_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
        ];
    }
}

