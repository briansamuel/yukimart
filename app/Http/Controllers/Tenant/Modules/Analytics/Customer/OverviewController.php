<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\Customer;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\CustomerDailyStats;
use App\Models\Analytics\CustomerRetentionStats;
use App\Models\BranchShop;
use Illuminate\Http\Request;

class OverviewController extends BaseTenantController
{
    /**
     * Display customer analytics overview
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        // Get customer daily stats
        $query = CustomerDailyStats::byTenant($tenantId)
            ->byDateRange($fromDate, $toDate);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $customerData = $query->get();

        // Calculate metrics
        $metrics = $this->calculateMetrics($customerData);

        // Get retention stats
        $retentionStats = CustomerRetentionStats::byTenant($tenantId)
            ->recentCohorts(12)
            ->orderBy('cohort_month', 'desc')
            ->limit(12)
            ->get();

        // Get daily chart data
        $chartData = $customerData->map(function ($item) {
            return [
                'date' => $item->stats_date->format('Y-m-d'),
                'new_customers' => (int) $item->new_customers,
                'returning_customers' => (int) $item->returning_customers,
                'walkin_customers' => (int) $item->walkin_customers,
                'vip_customers' => (int) $item->vip_customers,
                'total_customers' => (int) $item->total_customers,
            ];
        })->toArray();

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.customer.overview', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'retentionStats' => $retentionStats,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Calculate customer metrics
     */
    protected function calculateMetrics($customerData)
    {
        $totalCustomers = $customerData->sum('total_customers');
        $newCustomers = $customerData->sum('new_customers');
        $returningCustomers = $customerData->sum('returning_customers');
        $walkinCustomers = $customerData->sum('walkin_customers');
        $vipCustomers = $customerData->sum('vip_customers');
        
        $newCustomerRevenue = $customerData->sum('new_customer_revenue');
        $returningCustomerRevenue = $customerData->sum('returning_customer_revenue');
        $walkinRevenue = $customerData->sum('walkin_revenue');
        $vipRevenue = $customerData->sum('vip_revenue');
        
        $totalRevenue = $newCustomerRevenue + $returningCustomerRevenue + $walkinRevenue + $vipRevenue;

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'walkin_customers' => $walkinCustomers,
            'vip_customers' => $vipCustomers,
            'new_customer_revenue' => $newCustomerRevenue,
            'returning_customer_revenue' => $returningCustomerRevenue,
            'walkin_revenue' => $walkinRevenue,
            'vip_revenue' => $vipRevenue,
            'total_revenue' => $totalRevenue,
            'new_customer_percentage' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
            'returning_customer_percentage' => $totalCustomers > 0 ? ($returningCustomers / $totalCustomers) * 100 : 0,
            'vip_customer_percentage' => $totalCustomers > 0 ? ($vipCustomers / $totalCustomers) * 100 : 0,
            'avg_customer_lifetime_value' => $customerData->count() > 0 
                ? $customerData->avg('avg_customer_lifetime_value') 
                : 0,
        ];
    }
}

