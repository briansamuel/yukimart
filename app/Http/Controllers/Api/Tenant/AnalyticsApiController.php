<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Analytics\SalesDailySummary;
use App\Models\Analytics\CustomerDailyStats;
use App\Models\Analytics\InventoryDailyStats;
use App\Models\Analytics\StaffDailyPerformance;
use App\Models\Analytics\AccountsReceivable;
use App\Models\Analytics\SlowMovingInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsApiController extends Controller
{
    /**
     * Get business overview data
     */
    public function businessOverview(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        $query = SalesDailySummary::where('tenant_id', $tenantId)
            ->whereBetween('summary_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $data = $query->get();

        $metrics = [
            'total_revenue' => $data->sum('total_revenue'),
            'total_profit' => $data->sum('total_profit'),
            'total_orders' => $data->sum('total_orders'),
            'unique_customers' => $data->sum('unique_customers'),
            'profit_margin' => $data->sum('total_revenue') > 0 
                ? ($data->sum('total_profit') / $data->sum('total_revenue')) * 100 
                : 0,
        ];

        $chartData = $data->map(function ($item) {
            return [
                'date' => $item->summary_date->format('Y-m-d'),
                'revenue' => (float) $item->total_revenue,
                'profit' => (float) $item->total_profit,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
                'chart_data' => $chartData,
            ],
        ]);
    }

    /**
     * Get customer overview data
     */
    public function customerOverview(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        $query = CustomerDailyStats::where('tenant_id', $tenantId)
            ->whereBetween('stats_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $data = $query->get();

        $metrics = [
            'total_customers' => $data->sum('total_customers'),
            'new_customers' => $data->sum('new_customers'),
            'returning_customers' => $data->sum('returning_customers'),
            'vip_customers' => $data->sum('vip_customers'),
            'total_revenue' => $data->sum('new_customer_revenue') + 
                              $data->sum('returning_customer_revenue') + 
                              $data->sum('walkin_revenue') + 
                              $data->sum('vip_revenue'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
            ],
        ]);
    }

    /**
     * Get inventory overview data
     */
    public function inventoryOverview(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        $query = InventoryDailyStats::where('tenant_id', $tenantId)
            ->whereBetween('stats_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $data = $query->get();

        $metrics = [
            'total_inventory_value' => $data->sum('total_inventory_value'),
            'total_skus' => $data->sum('total_skus'),
            'low_stock_items' => $data->sum('low_stock_items'),
            'out_of_stock_items' => $data->sum('out_of_stock_items'),
            'avg_turnover_rate' => $data->count() > 0 ? $data->avg('inventory_turnover_rate') : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
            ],
        ]);
    }

    /**
     * Get staff performance data
     */
    public function staffPerformance(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        $query = StaffDailyPerformance::where('tenant_id', $tenantId)
            ->whereBetween('performance_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $data = $query->get();

        $topPerformers = $data->groupBy('staff_id')
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
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'top_performers' => $topPerformers,
            ],
        ]);
    }

    /**
     * Get accounts receivable data
     */
    public function accountsReceivable(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $statusFilter = $request->input('status');

        $query = AccountsReceivable::where('tenant_id', $tenantId);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $data = $query->get();

        $metrics = [
            'total_invoice_amount' => $data->sum('invoice_amount'),
            'total_paid_amount' => $data->sum('paid_amount'),
            'total_outstanding' => $data->sum('outstanding_amount'),
            'overdue_count' => $data->where('status', 'overdue')->count(),
            'overdue_amount' => $data->where('status', 'overdue')->sum('outstanding_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
            ],
        ]);
    }

    /**
     * Get slow moving inventory
     */
    public function slowMovingInventory(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $branchShopId = $request->input('branch_shop_id');
        $limit = $request->input('limit', 20);

        $query = SlowMovingInventory::where('tenant_id', $tenantId)
            ->where('aging_category', 'slow_moving');

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        $items = $query->orderByDesc('days_without_sale')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
            ],
        ]);
    }
}

