<?php

namespace App\Http\Controllers\Admin;

use App\Services\DashboardService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class DashboardController extends Controller
{
    

    public function __construct()
    {
        
        
    }

    /** 
    * ======================
    * Method:: INDEX
    * ======================
    */

    public function index()
    {
        // Statistics for dashboard cards
        $data['totalProducts'] = DashboardService::totalProducts();
        $data['activeProducts'] = DashboardService::activeProducts();
        $data['totalOrders'] = DashboardService::totalOrders();
        $data['totalCustomers'] = DashboardService::totalCustomers();

        $data['totalUsers'] = DashboardService::totalUsers();
        $data['activeUsers'] = DashboardService::activeUsers();

        // Today's sales statistics
        $data['todaySales'] = DashboardService::getTodaySalesStats();

        // Recent content for widgets
        $data['recentProducts'] = DashboardService::takeNewProducts(10);
        $data['recentActivities'] = DashboardService::getRecentActivities(15);

        // Chart data for revenue
        $data['chartData'] = DashboardService::getRevenueChartData();
        $data['topProductsChart'] = DashboardService::getTopProductsChartData();

        return view('admin.dash-board', $data);
    }

    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_orders' => DashboardService::totalOrders(),
                'total_invoices' => \App\Models\Invoice::count(),
                'total_products' => DashboardService::totalProducts(),
                'total_revenue' => DashboardService::getTodaySalesStats()['revenue'] ?? 0,
                'orders_today' => DashboardService::getTodaySalesStats()['orders'] ?? 0,
                'total_customers' => DashboardService::totalCustomers(),
                'low_stock' => \App\Models\Product::where('reorder_point', '>', 0)
                    ->where('reorder_point', '<=', 10)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders()
    {
        try {
            $orders = \App\Models\Order::with(['customer'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->customer ? $order->customer->full_name : 'Khách lẻ',
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('d/m/Y H:i')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top selling products
     */
    public function getTopProducts()
    {
        try {
            $products = \App\Models\Product::select('products.*')
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as sold_quantity')
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->leftJoin('orders', function($join) {
                    $join->on('order_items.order_id', '=', 'orders.id')
                         ->where('orders.status', '=', 'completed');
                })
                ->groupBy('products.id')
                ->orderBy('sold_quantity', 'desc')
                ->limit(10)
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'price' => $product->price,
                        'sold_quantity' => $product->sold_quantity ?? 0,
                        'image' => $product->image_url
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue chart data via AJAX
     */
    public function getRevenueData(Request $request)
    {
        $period = $request->get('period', 'month');
        $chartData = DashboardService::getRevenueChartData($period);

        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }

    /**
     * Get top products chart data via AJAX
     */
    public function getTopProductsData(Request $request)
    {
        $type = $request->get('type', 'revenue'); // revenue or quantity
        $chartData = DashboardService::getTopProductsChartData($type);

        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }
}
