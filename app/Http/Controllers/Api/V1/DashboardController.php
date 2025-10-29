<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Http\Traits\ApiOptimizationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Helpers\PeriodHelper;

class DashboardController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Get comprehensive dashboard statistics with aggressive caching
     */
    public function index(Request $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for dashboard data
            $cacheKey = 'dashboard_overview_' . date('Y-m-d-H');

            // Try to get cached dashboard data (15 minutes cache)
            $data = Cache::remember($cacheKey, 900, function() {
                return $this->generateDashboardData();
            });

            // Create response with optimization headers
            $response = response()->json([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => $data,
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'cache_key' => $cacheKey,
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ], 200);

            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response, 120, 1); // 120 requests per minute

            return $response;

        } catch (\Exception $e) {
            Log::error('Dashboard data retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics with period filter and smart caching
     */
    public function getStats(Request $request)
    {
        $startTime = microtime(true);

        try {
            $period = $request->get('period', 'today');

            // Validate period using PeriodHelper
            if (!PeriodHelper::isValidPeriod($period)) {
                return $this->errorResponse(
                    'Invalid period. Valid periods: ' . implode(', ', PeriodHelper::getValidPeriods()),
                    400
                );
            }

            // Generate cache key based on period and date
            $cacheKey = "dashboard_stats_{$period}_" . date('Y-m-d-H');

            // Smart caching based on period
            $cacheMinutes = $this->getCacheMinutesForPeriod($period);

            // Get cached or calculate statistics
            $stats = Cache::remember($cacheKey, $cacheMinutes * 60, function() use ($period) {
                return $this->calculateStatsForPeriod($period);
            });

            // Create response with optimization headers
            $response = response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => $stats,
                'meta' => [
                    'period' => $period,
                    'cached' => Cache::has($cacheKey),
                    'cache_minutes' => $cacheMinutes,
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ], 200);

            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Dashboard statistics failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $orders = Order::with(['customer', 'branchShop'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer ? $order->customer->name : ($order->customer_name ?? 'Khách lẻ'),
                        'customer_phone' => $order->customer ? $order->customer->phone : ($order->customer_phone ?? null),
                        'total_amount' => (float) $order->final_amount,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'branch_shop' => $order->branchShop ? $order->branchShop->name : null,
                        'created_at' => $order->created_at,
                        'formatted_date' => $order->created_at->format('d/m/Y H:i'),
                        'formatted_amount' => number_format($order->final_amount, 0, ',', '.') . '₫'
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Recent orders retrieved successfully',
                'data' => $orders
            ], 200);

        } catch (\Exception $e) {
            Log::error('Recent orders retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve recent orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top selling products with caching
     */
    public function getTopProducts(Request $request)
    {
        $startTime = microtime(true);

        try {
            $limit = min($request->get('limit', 10), 50); // Max 50 products
            $type = $request->get('type', 'quantity');
            $period = $request->get('period', 'month');

            // Validate inputs
            $validTypes = ['quantity', 'revenue'];
            if (!in_array($type, $validTypes)) {
                return $this->errorResponse(
                    'Invalid type. Valid types: ' . implode(', ', $validTypes),
                    400
                );
            }

            if (!PeriodHelper::isValidPeriod($period)) {
                return $this->errorResponse(
                    'Invalid period. Valid periods: ' . implode(', ', PeriodHelper::getValidPeriods()),
                    400
                );
            }

            // Generate cache key
            $cacheKey = "dashboard_top_products_{$type}_{$period}_{$limit}_" . date('Y-m-d-H');

            // Cache for 10 minutes
            $products = Cache::remember($cacheKey, 600, function() use ($type, $period, $limit) {
                return $this->getTopProductsDataOptimized($type, $period, $limit);
            });





            return response()->json([
                'status' => 'success',
                'message' => 'Top products retrieved successfully',
                'data' => $products,
                'meta' => [
                    'type' => $type,
                    'period' => $period,
                    'limit' => $limit,
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Top products retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve top products',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get revenue chart data
     */
    public function getRevenueData(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // today, yesterday, month, last_month, year
            $chartData = DashboardService::getRevenueChartData($period);

            return response()->json([
                'status' => 'success',
                'message' => 'Revenue chart data retrieved successfully',
                'data' => $chartData,
                'meta' => [
                    'period' => $period
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Revenue chart data retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve revenue chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top products chart data
     */
    public function getTopProductsData(Request $request)
    {
        try {
            $type = $request->get('type', 'revenue'); // revenue or quantity
            $period = $request->get('period', 'month'); // today, yesterday, month, last_month, year
            $chartData = DashboardService::getTopProductsChartData($type, $period);

            return response()->json([
                'status' => 'success',
                'message' => 'Top products chart data retrieved successfully',
                'data' => $chartData,
                'meta' => [
                    'type' => $type
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Top products chart data retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve top products chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(Request $request)
    {
        try {
            $limit = $request->get('limit', 15);
            $activities = DashboardService::getRecentActivities($limit);

            return response()->json([
                'status' => 'success',
                'message' => 'Recent activities retrieved successfully',
                'data' => $activities,
                'meta' => [
                    'limit' => $limit
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Recent activities retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve recent activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $products = Product::with(['inventory', 'category'])
                ->where('reorder_point', '>', 0)
                ->whereHas('inventory', function($query) {
                    $query->whereRaw('quantity <= reorder_point');
                })
                ->orderByRaw('(SELECT quantity FROM inventories WHERE product_id = products.id LIMIT 1) ASC')
                ->limit($limit)
                ->get()
                ->map(function($product) {
                    $inventory = $product->inventory;
                    return [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'sku' => $product->sku,
                        'current_stock' => $inventory ? (int) $inventory->quantity : 0,
                        'reorder_point' => (int) $product->reorder_point,
                        'category' => $product->category ? $product->category->name : null,
                        'status' => $product->product_status,
                        'image' => $product->product_image ? asset('storage/' . $product->product_image) : null
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Low stock products retrieved successfully',
                'data' => $products,
                'meta' => [
                    'limit' => $limit,
                    'total_low_stock' => $products->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Low stock products retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve low stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate period-specific statistics
     */
    private function calculatePeriodStats($dateRange, $period)
    {
        // Get orders for the period
        $orders = Order::whereIn('status', ['processing', 'completed'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        // Get invoices for the period
        $invoices = Invoice::whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        // Get return orders for the period
        $returnOrders = \App\Models\ReturnOrder::whereIn('status', ['approved', 'completed'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        // Get payments for the period (receipts and disbursements)
        $receipts = \App\Models\Payment::where('payment_type', 'receipt')
            ->whereIn('status', ['completed'])
            ->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']])
            ->get();

        $payments = \App\Models\Payment::where('payment_type', 'payment')
            ->whereIn('status', ['completed'])
            ->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']])
            ->get();

        // Calculate statistics
        $ordersRevenue = $orders->sum('final_amount');
        $invoicesRevenue = $invoices->sum('total_amount');
        $totalRevenue = $ordersRevenue + $invoicesRevenue;

        // Calculate invoice subtotal and discount statistics
        $invoicesSubtotal = $invoices->sum(function($invoice) {
            return $invoice->subtotal + $invoice->tax_amount;
        });
        $invoicesDiscount = $invoices->sum('discount_amount');

        // Calculate return statistics
        $returnOrdersCount = $returnOrders->count();
        $returnRevenue = $returnOrders->sum('total_amount');

        // Calculate payment statistics
        $receiptsCount = $receipts->count();
        $paymentsCount = $payments->count();

        $ordersCount = $orders->count();
        $invoicesCount = $invoices->count();
        $totalTransactions = $ordersCount + $invoicesCount;

        $uniqueCustomers = $orders->pluck('customer_id')
            ->merge($invoices->pluck('customer_id'))
            ->merge($returnOrders->pluck('customer_id'))
            ->filter()
            ->unique()
            ->count();

        $avgTransactionValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        return [
            'period_revenue' => (float) $totalRevenue,
            'period_orders' => $ordersCount,
            'period_invoices' => $invoicesCount,
            'period_returns' => $returnOrdersCount,
            'return_revenue' => (float) $returnRevenue,
            'invoices_period_subtotal' => (float) $invoicesSubtotal,
            'invoices_period_discount' => (float) $invoicesDiscount,
            'receipts_period_count' => $receiptsCount,
            'payments_period_count' => $paymentsCount,
            'period_transactions' => $totalTransactions,
            'period_customers' => $uniqueCustomers,
            'avg_transaction_value' => (float) $avgTransactionValue,
            'orders_revenue' => (float) $ordersRevenue,
            'invoices_revenue' => (float) $invoicesRevenue,
            'total_orders' => Order::count(), // Overall total
            'total_invoices' => Invoice::count(), // Overall total
            'total_returns' => \App\Models\ReturnOrder::count(), // Overall total
        ];
    }

    /**
     * Calculate inventory statistics
     */
    private function calculateInventoryStats()
    {
        // Get total inventory quantity from inventories table
        $totalInventoryQuantity = \App\Models\Inventory::sum('quantity');

        // Calculate total inventory value using cost_price
        $totalInventoryValue = \App\Models\Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity * products.cost_price) as total_value')
            ->value('total_value') ?? 0;

        // Get products with positive inventory
        $productsInStock = \App\Models\Inventory::where('quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');

        // Get products with zero or negative inventory
        $productsOutOfStock = \App\Models\Inventory::where('quantity', '<=', 0)
            ->distinct('product_id')
            ->count('product_id');

        return [
            'total_inventory_quantity' => (int) $totalInventoryQuantity,
            'total_inventory_value' => (float) $totalInventoryValue,
            'products_in_stock' => $productsInStock,
            'products_out_of_stock' => $productsOutOfStock,
        ];
    }

    /**
     * Generate dashboard data with optimized queries
     */
    private function generateDashboardData()
    {
        return [
            // Basic statistics (cached for 15 minutes)
            'statistics' => Cache::remember('dashboard_basic_stats', 900, function() {
                return [
                    'total_products' => Product::count(),
                    'active_products' => Product::where('product_status', 'publish')->count(),
                    'total_orders' => Order::count(),
                    'total_customers' => Customer::count(),
                    'total_invoices' => Invoice::count(),
                    'low_stock_products' => Product::where('reorder_point', '>', 0)
                        ->whereHas('inventory', function($query) {
                            $query->whereRaw('quantity <= reorder_point');
                        })->count(),
                ];
            }),

            // Today's sales (cached for 5 minutes)
            'today_sales' => Cache::remember('dashboard_today_sales', 300, function() {
                return DashboardService::getTodaySalesStats();
            }),

            // Recent content (cached for 2 minutes)
            'recent_products' => Cache::remember('dashboard_recent_products', 120, function() {
                return DashboardService::takeNewProducts(10);
            }),
            'recent_activities' => Cache::remember('dashboard_recent_activities', 120, function() {
                return DashboardService::getRecentActivities(15);
            }),

            // Chart data (cached for 10 minutes)
            'revenue_chart' => Cache::remember('dashboard_revenue_chart', 600, function() {
                return DashboardService::getRevenueChartData();
            }),
            'top_products_chart' => Cache::remember('dashboard_top_products_chart', 600, function() {
                return DashboardService::getTopProductsChartData();
            }),
        ];
    }

    /**
     * Calculate statistics for specific period with caching
     */
    private function calculateStatsForPeriod($period)
    {
        $dateRange = PeriodHelper::getDateRangeForPeriod($period);

        // Period-specific statistics
        $periodStats = $this->calculatePeriodStats($dateRange, $period);

        // Overall statistics (cached separately)
        $overallStats = Cache::remember('dashboard_overall_stats', 900, function() {
            return [
                'total_products' => Product::count(),
                'active_products' => Product::where('product_status', 'publish')->count(),
                'total_customers' => Customer::count(),
                'low_stock_products' => Product::where('reorder_point', '>', 0)
                    ->whereHas('inventory', function($query) {
                        $query->whereRaw('quantity <= reorder_point');
                    })->count(),
            ];
        });

        // Inventory statistics (cached for 10 minutes)
        $inventoryStats = Cache::remember('dashboard_inventory_stats', 600, function() {
            return $this->calculateInventoryStats();
        });

        // Period info
        $periodInfo = PeriodHelper::getPeriodInfo($period);

        return array_merge($overallStats, $periodStats, $inventoryStats, $periodInfo);
    }

    /**
     * Get cache minutes based on period type
     */
    private function getCacheMinutesForPeriod($period)
    {
        $cacheMinutes = [
            'today' => 2,        // 2 minutes for today (most dynamic)
            'yesterday' => 10,   // 10 minutes for yesterday
            'month' => 10,       // 10 minutes for current month
            'last_month' => 15,  // 15 minutes for last month (more static)
            'year' => 15,        // 15 minutes for current year
            'last_year' => 30,   // 30 minutes for last year (very static)
        ];

        return $cacheMinutes[$period] ?? 10; // Default 10 minutes
    }

    /**
     * Get optimized top products data
     */
    private function getTopProductsDataOptimized($type, $period, $limit)
    {
        $dateRange = PeriodHelper::getDateRangeForPeriod($period);
        $orderByField = $type === 'revenue' ? 'total_revenue' : 'sold_quantity';

        // Optimized query with proper joins and indexing
        $query = \App\Models\InvoiceItem::select('invoice_items.product_id')
            ->selectRaw('products.product_name as name')
            ->selectRaw('products.sku')
            ->selectRaw('products.product_thumbnail as image')
            ->selectRaw('COALESCE(SUM(invoice_items.line_total), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(invoice_items.quantity), 0) as sold_quantity')
            ->join('invoices', function($join) use ($dateRange) {
                $join->on('invoice_items.invoice_id', '=', 'invoices.id')
                     ->whereIn('invoices.status', ['paid', 'completed']);

                if ($dateRange['start'] && $dateRange['end']) {
                    $join->whereBetween('invoices.created_at', [$dateRange['start'], $dateRange['end']]);
                } elseif ($dateRange['start']) {
                    $join->where('invoices.created_at', '>=', $dateRange['start']);
                }
            })
            ->leftJoin('products', 'invoice_items.product_id', '=', 'products.id')
            ->groupBy('invoice_items.product_id', 'products.product_name', 'products.sku', 'products.product_thumbnail')
            ->orderBy($orderByField, 'desc')
            ->limit($limit);

        return $query->get()->map(function($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->name ?? 'Unknown Product',
                'sku' => $item->sku ?? 'N/A',
                'total_revenue' => (float) ($item->total_revenue ?? 0),
                'image' => $item->image ? asset('storage/' . $item->image) : null,
                'sold_quantity' => (int) ($item->sold_quantity ?? 0),
                'formatted_revenue' => number_format($item->total_revenue ?? 0, 0, ',', '.') . '₫'
            ];
        });
    }
}
