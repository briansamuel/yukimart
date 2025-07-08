<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsService
{
    /**
     * Get sales report data
     */
    public function getSalesReport($filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();
        $groupBy = $filters['group_by'] ?? 'day'; // day, week, month, year

        $query = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('order_status', '!=', 'cancelled');

        // Apply additional filters
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['branch_shop_id'])) {
            $query->where('branch_shop_id', $filters['branch_shop_id']);
        }

        // Group by time period
        switch ($groupBy) {
            case 'day':
                $selectRaw = "DATE(created_at) as period, 
                             COUNT(*) as total_orders,
                             SUM(final_amount) as total_revenue,
                             AVG(final_amount) as avg_order_value";
                $groupByRaw = "DATE(created_at)";
                break;
            case 'week':
                $selectRaw = "YEARWEEK(created_at) as period,
                             COUNT(*) as total_orders,
                             SUM(final_amount) as total_revenue,
                             AVG(final_amount) as avg_order_value";
                $groupByRaw = "YEARWEEK(created_at)";
                break;
            case 'month':
                $selectRaw = "DATE_FORMAT(created_at, '%Y-%m') as period,
                             COUNT(*) as total_orders,
                             SUM(final_amount) as total_revenue,
                             AVG(final_amount) as avg_order_value";
                $groupByRaw = "DATE_FORMAT(created_at, '%Y-%m')";
                break;
            case 'year':
                $selectRaw = "YEAR(created_at) as period,
                             COUNT(*) as total_orders,
                             SUM(final_amount) as total_revenue,
                             AVG(final_amount) as avg_order_value";
                $groupByRaw = "YEAR(created_at)";
                break;
        }

        $data = $query->selectRaw($selectRaw)
            ->groupByRaw($groupByRaw)
            ->orderBy('period')
            ->get();

        // Calculate summary statistics
        $summary = [
            'total_orders' => $data->sum('total_orders'),
            'total_revenue' => $data->sum('total_revenue'),
            'avg_order_value' => $data->avg('avg_order_value'),
            'growth_rate' => $this->calculateGrowthRate($data, 'total_revenue'),
        ];

        return [
            'data' => $data,
            'summary' => $summary,
            'period' => $groupBy,
            'date_range' => [$dateFrom, $dateTo]
        ];
    }

    /**
     * Get inventory report data
     */
    public function getInventoryReport($filters = [])
    {
        $query = Inventory::with(['product', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->select([
                'inventories.*',
                'products.product_name',
                'products.sku',
                'products.cost_price',
                'products.sale_price',
                'products.reorder_point',
                'products.product_category',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code'
            ]);

        // Apply filters
        if (!empty($filters['warehouse_id'])) {
            $query->where('inventories.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['product_category'])) {
            $query->where('products.product_category', $filters['product_category']);
        }

        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low_stock':
                    $query->whereRaw('inventories.quantity <= products.reorder_point AND inventories.quantity > 0');
                    break;
                case 'out_of_stock':
                    $query->where('inventories.quantity', '<=', 0);
                    break;
                case 'in_stock':
                    $query->whereRaw('inventories.quantity > products.reorder_point');
                    break;
            }
        }

        $data = $query->get();

        // Calculate inventory statistics
        $summary = [
            'total_products' => $data->count(),
            'total_value' => $data->sum(function($item) {
                return $item->quantity * $item->cost_price;
            }),
            'low_stock_count' => $data->filter(function($item) {
                return $item->quantity <= $item->reorder_point && $item->quantity > 0;
            })->count(),
            'out_of_stock_count' => $data->filter(function($item) {
                return $item->quantity <= 0;
            })->count(),
            'in_stock_count' => $data->filter(function($item) {
                return $item->quantity > $item->reorder_point;
            })->count(),
        ];

        return [
            'data' => $data,
            'summary' => $summary
        ];
    }

    /**
     * Get product performance report
     */
    public function getProductPerformanceReport($filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->where('orders.order_status', '!=', 'cancelled')
            ->select([
                'products.id',
                'products.product_name',
                'products.sku',
                'products.cost_price',
                'products.sale_price',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('SUM(order_items.quantity * products.cost_price) as total_cost'),
                DB::raw('SUM(order_items.total_price) - SUM(order_items.quantity * products.cost_price) as profit'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            ])
            ->groupBy([
                'products.id', 
                'products.product_name', 
                'products.sku', 
                'products.cost_price', 
                'products.sale_price'
            ]);

        // Apply filters
        if (!empty($filters['product_category'])) {
            $query->where('products.product_category', $filters['product_category']);
        }

        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        // Order by performance metric
        $orderBy = $filters['order_by'] ?? 'total_revenue';
        $query->orderBy($orderBy, 'desc');

        $data = $query->get();

        // Calculate profit margins
        $data = $data->map(function($item) {
            $item->profit_margin = $item->total_revenue > 0 ? 
                ($item->profit / $item->total_revenue) * 100 : 0;
            return $item;
        });

        return [
            'data' => $data,
            'summary' => [
                'total_products' => $data->count(),
                'total_revenue' => $data->sum('total_revenue'),
                'total_profit' => $data->sum('profit'),
                'avg_profit_margin' => $data->avg('profit_margin'),
            ]
        ];
    }

    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics($filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();

        // Customer purchase statistics
        $customerStats = DB::table('customers')
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->where('orders.order_status', '!=', 'cancelled')
            ->select([
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.phone',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.final_amount) as total_spent'),
                DB::raw('AVG(orders.final_amount) as avg_order_value'),
                DB::raw('MAX(orders.created_at) as last_order_date')
            ])
            ->groupBy(['customers.id', 'customers.name', 'customers.email', 'customers.phone'])
            ->having('total_orders', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->get();

        // Customer segmentation
        $segments = [
            'vip' => $customerStats->filter(function($customer) {
                return $customer->total_spent >= 10000000; // 10M VND
            })->count(),
            'loyal' => $customerStats->filter(function($customer) {
                return $customer->total_orders >= 5 && $customer->total_spent >= 5000000; // 5M VND
            })->count(),
            'regular' => $customerStats->filter(function($customer) {
                return $customer->total_orders >= 2 && $customer->total_spent >= 1000000; // 1M VND
            })->count(),
            'new' => $customerStats->filter(function($customer) {
                return $customer->total_orders == 1;
            })->count(),
        ];

        return [
            'customers' => $customerStats,
            'segments' => $segments,
            'summary' => [
                'total_customers' => $customerStats->count(),
                'total_revenue' => $customerStats->sum('total_spent'),
                'avg_customer_value' => $customerStats->avg('total_spent'),
                'avg_orders_per_customer' => $customerStats->avg('total_orders'),
            ]
        ];
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary($filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();

        // Revenue and costs
        $revenue = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('order_status', '!=', 'cancelled')
            ->sum('final_amount');

        $costs = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->where('orders.order_status', '!=', 'cancelled')
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        $profit = $revenue - $costs;
        $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        // Inventory value
        $inventoryValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->sum(DB::raw('inventories.quantity * products.cost_price'));

        return [
            'revenue' => $revenue,
            'costs' => $costs,
            'profit' => $profit,
            'profit_margin' => $profitMargin,
            'inventory_value' => $inventoryValue,
            'date_range' => [$dateFrom, $dateTo]
        ];
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate($data, $field)
    {
        if ($data->count() < 2) {
            return 0;
        }

        $first = $data->first()->{$field};
        $last = $data->last()->{$field};

        if ($first == 0) {
            return 0;
        }

        return (($last - $first) / $first) * 100;
    }

    /**
     * Get dashboard analytics
     */
    public function getDashboardAnalytics($period = 'month')
    {
        $dateFrom = Carbon::now()->startOf($period);
        $dateTo = Carbon::now()->endOf($period);

        return [
            'sales' => $this->getSalesReport(['date_from' => $dateFrom, 'date_to' => $dateTo]),
            'inventory' => $this->getInventoryReport(),
            'financial' => $this->getFinancialSummary(['date_from' => $dateFrom, 'date_to' => $dateTo]),
            'top_products' => $this->getProductPerformanceReport([
                'date_from' => $dateFrom, 
                'date_to' => $dateTo, 
                'limit' => 10
            ]),
            'customer_stats' => $this->getCustomerAnalytics(['date_from' => $dateFrom, 'date_to' => $dateTo]),
        ];
    }
}
