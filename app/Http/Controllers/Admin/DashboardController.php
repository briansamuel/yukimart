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
        $data['topProductsChart'] = DashboardService::getTopProductsChartData('revenue', 'month');

        return view('admin.dash-board', $data);
    }

    /**
     * Get dashboard statistics with period filter
     */
    public function getStats(Request $request)
    {
        try {
            $period = $request->get('period', 'today'); // today, yesterday, month, last_month, year

            // Get date range for period filter
            $dateRange = $this->getDateRangeForPeriod($period);

            // Calculate period-specific statistics
            $periodStats = $this->getPeriodStats($dateRange);

            // Calculate invoice and return order totals for the period
            $totalInvoiceAmount = \App\Models\Invoice::whereIn('status', ['paid', 'completed'])
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('total_amount');

            $totalReturnAmount = \App\Models\ReturnOrder::whereIn('status', ['approved', 'completed'])
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('total_amount');

            $stats = [
                // Overall totals (not filtered by period)
                'total_orders' => DashboardService::totalOrders(),
                'total_invoices' => \App\Models\Invoice::count(),
                'total_products' => DashboardService::totalProducts(),
                'total_customers' => DashboardService::totalCustomers(),
                'total_users' => DashboardService::totalUsers(),
                'active_users' => DashboardService::activeUsers(),
                'low_stock_products' => \App\Models\Product::where('reorder_point', '>', 0)
                    ->whereHas('inventory', function($query) {
                        $query->whereRaw('quantity <= reorder_point');
                    })->count(),

                // Period-specific statistics
                'period_revenue' => $periodStats['revenue'],
                'period_orders' => $periodStats['orders_count'],
                'period_customers' => $periodStats['customers_count'],
                'period_avg_order_value' => $periodStats['avg_order_value'],
                'period_invoice_amount' => (float) $totalInvoiceAmount,
                'period_return_amount' => (float) $totalReturnAmount,
                'period_net_revenue' => (float) ($periodStats['revenue'] + $totalInvoiceAmount - $totalReturnAmount),

                // Formatted values
                'formatted_period_revenue' => number_format($periodStats['revenue'], 0, ',', '.') . '₫',
                'formatted_period_invoice_amount' => number_format($totalInvoiceAmount, 0, ',', '.') . '₫',
                'formatted_period_return_amount' => number_format($totalReturnAmount, 0, ',', '.') . '₫',
                'formatted_period_net_revenue' => number_format($periodStats['revenue'] + $totalInvoiceAmount - $totalReturnAmount, 0, ',', '.') . '₫',
                'formatted_avg_order_value' => number_format($periodStats['avg_order_value'], 0, ',', '.') . '₫',
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => $stats,
                'meta' => [
                    'period' => $period,
                    'period_name' => $this->getPeriodName($period),
                    'date_range' => [
                        'start' => $dateRange['start'] ? $dateRange['start']->format('Y-m-d H:i:s') : null,
                        'end' => $dateRange['end'] ? $dateRange['end']->format('Y-m-d H:i:s') : null
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue chart data API
     */
    public function getRevenueChart(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // today, yesterday, month, last_month, year

            // Get date range for period filter
            $dateRange = $this->getDateRangeForPeriod($period);

            // Generate chart data based on period
            $chartData = $this->generateRevenueChartData($dateRange, $period);

            return response()->json([
                'status' => 'success',
                'message' => 'Revenue chart data retrieved successfully',
                'data' => [
                    'revenue_chart' => $chartData
                ],
                'meta' => [
                    'period' => $period,
                    'period_name' => $this->getPeriodName($period),
                    'date_range' => [
                        'start' => $dateRange['start'] ? $dateRange['start']->format('Y-m-d H:i:s') : null,
                        'end' => $dateRange['end'] ? $dateRange['end']->format('Y-m-d H:i:s') : null
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve revenue chart data',
                'error' => $e->getMessage()
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
        $period = $request->get('period', 'month'); // today, yesterday, month, last_month, year
        $chartData = DashboardService::getTopProductsChartData($type, $period);

        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }

    /**
     * Get date range for period filter
     */
    private function getDateRangeForPeriod($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];

            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay()
                ];

            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];

            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];

            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];

            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }

    /**
     * Get period name in Vietnamese
     */
    private function getPeriodName($period)
    {
        $periodNames = [
            'today' => 'hôm nay',
            'yesterday' => 'hôm qua',
            'month' => 'tháng này',
            'last_month' => 'tháng trước',
            'year' => 'năm nay'
        ];

        return $periodNames[$period] ?? 'tháng này';
    }

    /**
     * Get period statistics
     */
    private function getPeriodStats($dateRange)
    {
        // Get orders for the period
        $orders = \App\Models\Order::whereIn('status', ['processing', 'completed'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        $revenue = $orders->sum('final_amount');
        $ordersCount = $orders->count();
        $customersCount = $orders->pluck('customer_id')->unique()->count();
        $avgOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0;

        return [
            'revenue' => (float) $revenue,
            'orders_count' => $ordersCount,
            'customers_count' => $customersCount,
            'avg_order_value' => (float) $avgOrderValue
        ];
    }

    /**
     * Generate revenue chart data based on period
     */
    private function generateRevenueChartData($dateRange, $period)
    {
        $categories = [];
        $data = [];
        $seriesName = '';

        switch ($period) {
            case 'today':
            case 'yesterday':
                // Hourly data for single day
                $seriesName = $period === 'today' ? 'Doanh thu hôm nay (triệu VNĐ)' : 'Doanh thu hôm qua (triệu VNĐ)';

                for ($hour = 0; $hour < 24; $hour += 3) {
                    $categories[] = sprintf('%02d:00', $hour);

                    $hourStart = $dateRange['start']->copy()->addHours($hour);
                    $hourEnd = $dateRange['start']->copy()->addHours($hour + 3);

                    $revenue = \App\Models\Order::whereIn('status', ['processing', 'completed'])
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->sum('final_amount');

                    $data[] = round($revenue / 1000000, 6); // Convert to millions
                }
                break;

            case 'month':
            case 'last_month':
                // Daily data for month
                $seriesName = $period === 'month' ? 'Doanh thu tháng này (triệu VNĐ)' : 'Doanh thu tháng trước (triệu VNĐ)';

                $startDate = $dateRange['start']->copy();
                $endDate = $dateRange['end']->copy();

                while ($startDate <= $endDate) {
                    $categories[] = $startDate->format('d/m');

                    $dayStart = $startDate->copy()->startOfDay();
                    $dayEnd = $startDate->copy()->endOfDay();

                    $revenue = \App\Models\Order::whereIn('status', ['processing', 'completed'])
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->sum('final_amount');

                    $data[] = round($revenue / 1000000, 6); // Convert to millions

                    $startDate->addDay();
                }
                break;

            case 'year':
                // Monthly data for year
                $seriesName = 'Doanh thu năm nay (triệu VNĐ)';

                for ($month = 1; $month <= 12; $month++) {
                    $categories[] = sprintf('T%d', $month);

                    $monthStart = Carbon::create($dateRange['start']->year, $month, 1)->startOfMonth();
                    $monthEnd = Carbon::create($dateRange['start']->year, $month, 1)->endOfMonth();

                    $revenue = \App\Models\Order::whereIn('status', ['processing', 'completed'])
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('final_amount');

                    $data[] = round($revenue / 1000000, 6); // Convert to millions
                }
                break;
        }

        return [
            'categories' => $categories,
            'data' => $data,
            'series_name' => $seriesName
        ];
    }
}
