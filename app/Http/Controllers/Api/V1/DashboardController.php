<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;

class DashboardController extends Controller
{
    /**
     * Get comprehensive dashboard statistics
     */
    public function index(Request $request)
    {
        try {
            // Get all dashboard data similar to Admin DashboardController
            $data = [
                // Basic statistics
                'statistics' => [
                    'total_products' => DashboardService::totalProducts(),
                    'active_products' => DashboardService::activeProducts(),
                    'total_orders' => DashboardService::totalOrders(),
                    'total_customers' => DashboardService::totalCustomers(),
                    'total_users' => DashboardService::totalUsers(),
                    'active_users' => DashboardService::activeUsers(),
                    'total_invoices' => Invoice::count(),
                    'low_stock_products' => Product::where('reorder_point', '>', 0)
                        ->whereHas('inventory', function($query) {
                            $query->whereRaw('quantity <= reorder_point');
                        })->count(),
                ],
                
                // Today's sales statistics
                'today_sales' => DashboardService::getTodaySalesStats(),
                
                // Recent content
                'recent_products' => DashboardService::takeNewProducts(10),
                'recent_activities' => DashboardService::getRecentActivities(15),
                
                // Chart data
                'revenue_chart' => DashboardService::getRevenueChartData(),
                'top_products_chart' => DashboardService::getTopProductsChartData(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => $data
            ], 200);

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
     * Get dashboard statistics (equivalent to getStats)
     */
    public function getStats(Request $request)
    {
        try {
            $todayStats = DashboardService::getTodaySalesStats();
            
            $stats = [
                'total_orders' => DashboardService::totalOrders(),
                'total_invoices' => Invoice::count(),
                'total_products' => DashboardService::totalProducts(),
                'active_products' => DashboardService::activeProducts(),
                'total_revenue' => $todayStats['revenue'] ?? 0,
                'orders_today' => $todayStats['orders_count'] ?? 0,
                'total_customers' => DashboardService::totalCustomers(),
                'total_users' => DashboardService::totalUsers(),
                'active_users' => DashboardService::activeUsers(),
                'low_stock_products' => Product::where('reorder_point', '>', 0)
                    ->whereHas('inventory', function($query) {
                        $query->whereRaw('quantity <= reorder_point');
                    })->count(),
                'avg_order_value' => $todayStats['avg_order_value'] ?? 0,
                'customers_today' => $todayStats['customers_count'] ?? 0,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ], 200);

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
     * Get top selling products
     */
    public function getTopProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $type = $request->get('type', 'quantity'); // quantity or revenue
            
            if ($type === 'revenue') {
                $products = Product::select('products.*')
                    ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total_revenue')
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->leftJoin('orders', function($join) {
                        $join->on('order_items.order_id', '=', 'orders.id')
                             ->whereIn('orders.status', ['processing', 'completed']);
                    })
                    ->groupBy('products.id')
                    ->orderBy('total_revenue', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->product_name,
                            'sku' => $product->sku,
                            'price' => (float) $product->sale_price,
                            'total_revenue' => (float) ($product->total_revenue ?? 0),
                            'formatted_revenue' => number_format($product->total_revenue ?? 0, 0, ',', '.') . '₫',
                            'image' => $product->product_image ? asset('storage/' . $product->product_image) : null
                        ];
                    });
            } else {
                $products = Product::select('products.*')
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as sold_quantity')
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->leftJoin('orders', function($join) {
                        $join->on('order_items.order_id', '=', 'orders.id')
                             ->whereIn('orders.status', ['processing', 'completed']);
                    })
                    ->groupBy('products.id')
                    ->orderBy('sold_quantity', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->product_name,
                            'sku' => $product->sku,
                            'price' => (float) $product->sale_price,
                            'sold_quantity' => (int) ($product->sold_quantity ?? 0),
                            'image' => $product->product_image ? asset('storage/' . $product->product_image) : null
                        ];
                    });
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Top products retrieved successfully',
                'data' => $products,
                'meta' => [
                    'type' => $type,
                    'limit' => $limit
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
            $chartData = DashboardService::getTopProductsChartData($type);

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
}
