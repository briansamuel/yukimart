<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\Business;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\SalesDailySummary;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseProfitController extends BaseTenantController
{
    /**
     * Display expense and profit analysis
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

        // Calculate expense and profit metrics
        $metrics = $this->calculateMetrics($summaryData);

        // Get branch-wise breakdown
        $branchBreakdown = $this->getBranchBreakdown($tenantId, $fromDate, $toDate);

        // Get daily data for chart
        $chartData = $summaryData->map(function ($item) {
            return [
                'date' => $item->summary_date->format('Y-m-d'),
                'cogs' => (float) $item->total_cogs,
                'profit' => (float) $item->total_profit,
                'profit_margin' => $item->net_revenue > 0 ? ($item->total_profit / $item->net_revenue) * 100 : 0,
            ];
        })->toArray();

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.business.expense_profit', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'branchBreakdown' => $branchBreakdown,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Calculate expense and profit metrics
     */
    protected function calculateMetrics($summaryData)
    {
        $totalRevenue = $summaryData->sum('total_revenue');
        $totalReturn = $summaryData->sum('total_return_amount');
        $netRevenue = $totalRevenue - $totalReturn;
        $totalCogs = $summaryData->sum('total_cogs');
        $totalProfit = $summaryData->sum('total_profit');
        $dayCount = $summaryData->count();

        return [
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalProfit,
            'profit_margin' => $netRevenue > 0 ? ($totalProfit / $netRevenue) * 100 : 0,
            'cogs_percentage' => $totalRevenue > 0 ? ($totalCogs / $totalRevenue) * 100 : 0,
            'avg_cogs_per_day' => $dayCount > 0 ? $totalCogs / $dayCount : 0,
            'avg_profit_per_day' => $dayCount > 0 ? $totalProfit / $dayCount : 0,
        ];
    }

    /**
     * Get branch-wise expense and profit breakdown
     */
    protected function getBranchBreakdown($tenantId, $fromDate, $toDate)
    {
        return SalesDailySummary::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate)
            ->selectRaw('branch_shop_id, SUM(total_revenue) as revenue, SUM(total_cogs) as cogs, SUM(total_profit) as profit')
            ->groupBy('branch_shop_id')
            ->with('branch:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'branch_id' => $item->branch_shop_id,
                    'branch_name' => $item->branch ? $item->branch->name : 'Unknown',
                    'revenue' => (float) $item->revenue,
                    'cogs' => (float) $item->cogs,
                    'profit' => (float) $item->profit,
                    'profit_margin' => $item->revenue > 0 ? ($item->profit / $item->revenue) * 100 : 0,
                ];
            })
            ->toArray();
    }
}

