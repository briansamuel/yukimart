<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\Performance;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\StaffDailyPerformance;
use App\Models\BranchShop;
use Illuminate\Http\Request;

class OverviewController extends BaseTenantController
{
    /**
     * Display staff performance analytics
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        // Get staff performance data
        $query = StaffDailyPerformance::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $performanceData = $query->get();

        // Calculate metrics
        $metrics = $this->calculateMetrics($performanceData);

        // Get top performers
        $topPerformers = $this->getTopPerformers($tenantId, $fromDate, $toDate, $branchShopId);

        // Get daily chart data
        $chartData = $performanceData->groupBy('staff_id')
            ->map(function ($staffData) {
                return [
                    'staff_id' => $staffData->first()->staff_id,
                    'staff_name' => $staffData->first()->staff ? $staffData->first()->staff->full_name : 'Unknown',
                    'total_revenue' => (float) $staffData->sum('total_revenue'),
                    'total_profit' => (float) $staffData->sum('total_profit'),
                    'total_orders' => (int) $staffData->sum('total_orders'),
                ];
            })
            ->sortByDesc('total_revenue')
            ->values()
            ->toArray();

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.performance.overview', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'topPerformers' => $topPerformers,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Calculate performance metrics
     */
    protected function calculateMetrics($performanceData)
    {
        $totalRevenue = $performanceData->sum('total_revenue');
        $totalProfit = $performanceData->sum('total_profit');
        $totalOrders = $performanceData->sum('total_orders');
        $uniqueCustomers = $performanceData->sum('unique_customers');
        $newCustomers = $performanceData->sum('new_customers');
        $staffCount = $performanceData->groupBy('staff_id')->count();

        return [
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'total_orders' => $totalOrders,
            'unique_customers' => $uniqueCustomers,
            'new_customers' => $newCustomers,
            'staff_count' => $staffCount,
            'avg_revenue_per_staff' => $staffCount > 0 ? $totalRevenue / $staffCount : 0,
            'avg_profit_per_staff' => $staffCount > 0 ? $totalProfit / $staffCount : 0,
            'avg_orders_per_staff' => $staffCount > 0 ? $totalOrders / $staffCount : 0,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
        ];
    }

    /**
     * Get top performing staff
     */
    protected function getTopPerformers($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = StaffDailyPerformance::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate)
            ->selectRaw('staff_id, SUM(total_revenue) as revenue, SUM(total_profit) as profit, SUM(total_orders) as orders')
            ->groupBy('staff_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->with('staff:id,full_name,username');

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        return $query->get()
            ->map(function ($item) {
                return [
                    'staff_id' => $item->staff_id,
                    'staff_name' => $item->staff ? $item->staff->full_name : 'Unknown',
                    'revenue' => (float) $item->revenue,
                    'profit' => (float) $item->profit,
                    'orders' => (int) $item->orders,
                    'profit_margin' => $item->revenue > 0 ? ($item->profit / $item->revenue) * 100 : 0,
                ];
            })
            ->toArray();
    }
}

