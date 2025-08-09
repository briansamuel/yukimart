<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class DashboardService
{

    public static function takeNewGuest($quantity) {
        $result = GuestService::takeNew($quantity);
        return $result;
    }

    public static function takeNewContact($quantity) {
        $result = ContactService::takeNew($quantity);
        return $result;
    }



    public static function totalGuest() {
        $result = GuestService::totalRows();
        return $result;
    }

    public static function totalContact() {
        $result = ContactService::totalRows();
        return $result;
    }

    

    // Product statistics
    public static function totalProducts() {
        return Product::count();
    }

    public static function activeProducts() {
        return Product::where('product_status', 'publish')->count();
    }

    public static function takeNewProducts($quantity) {
        return Product::with('inventory')
            ->orderBy('created_at', 'desc')
            ->limit($quantity)
            ->get();
    }

    // Category statistics
 

    // User statistics
    public static function totalUsers() {
        return User::count();
    }

    public static function activeUsers() {
        return User::where('status', 'active')->count();
    }

    // Order statistics
    public static function totalOrders() {
        return \App\Models\Order::count();
    }

    // Customer statistics
    public static function totalCustomers() {
        return \App\Models\Customer::count();
    }

    // Today's sales statistics
    public static function getTodaySalesStats() {
        $today = \Carbon\Carbon::today();

        return [
            'orders_count' => \App\Models\Order::whereDate('created_at', $today)->count(),
            // Revenue lấy từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            'revenue' => \App\Models\Invoice::whereDate('created_at', $today)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount'),
            'customers_count' => \App\Models\Order::whereDate('created_at', $today)->distinct('customer_id')->count(),
            // Average order value vẫn lấy từ orders vì đây là thống kê đơn hàng
            'avg_order_value' => \App\Models\Order::whereDate('created_at', $today)->avg('final_amount') ?? 0,
        ];
    }

    // Recent activities - Get order creation notifications
    public static function getRecentActivities($limit = 15) {
        // Get recent order creation notifications
        $orderNotifications = \App\Models\Notification::ofType(['order_created', 'inventory_import'])
            ->with(['creator', 'notifiable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($notification) {
                $orderData = $notification->data ?? [];

                $orderCode = $orderData['order_code'] ?? 'N/A';
                $customerName = $orderData['customer_name'] ?? 'Khách hàng';
                $totalAmount = $orderData['total_amount'] ?? 0;
                $orderId = $orderData['order_id'] ?? null;

                // Get seller information from order if order_id exists
                $sellerName = null;
                $sellerInfo = null;
                if ($orderId) {
                    try {
                        $order = \App\Models\Order::with('seller')->find($orderId);
                        if ($order && $order->seller) {
                            $sellerName = $order->seller->full_name;
                            $sellerInfo = [
                                'id' => $order->seller->id,
                                'name' => $order->seller->full_name,
                                'email' => $order->seller->email ?? null,
                            ];
                        }
                    } catch (\Exception $e) {
                        // If order not found or error, use fallback
                        $sellerName = $orderData['sold_by_name'] ?? null;
                    }
                }

                // Fallback to notification data if no seller found
                if (!$sellerName) {
                    $sellerName = $orderData['sold_by_name'] ?? $orderData['created_by'] ?? 'Không xác định';
                }

                return [
                    'id' => $notification->id,
                    'user_name' => $notification->creator ? $notification->creator->name : 'Hệ thống',
                    'action' => 'Tạo đơn hàng',
                    'description' => "Đơn hàng {$orderCode} cho {$customerName}",
                    'model_display' => 'Đơn hàng',
                    'created_at' => $notification->created_at,
                    'time_ago' => $notification->time_ago,
                    'icon' => 'ki-basket text-primary',
                    'type' => 'order_created',
                    'order_code' => $orderCode,
                    'customer_name' => $customerName,
                    'seller_name' => $sellerName,
                    'seller_info' => $sellerInfo,
                    'total_amount' => $totalAmount,
                    'formatted_amount' => number_format($totalAmount, 0, ',', '.') . '₫',
                    'is_read' => $notification->is_read,
                    'priority' => $notification->priority,
                    'priority_badge' => $notification->priority_badge,
                    'action_url' => $notification->action_url ?? null,
                    'action_text' => 'bán đơn hàng',
                ];
            });

        // If no order notifications found, get other recent notifications as fallback
        if ($orderNotifications->isEmpty()) {
            $fallbackNotifications = \App\Models\Notification::with(['creator', 'notifiable'])
                
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'user_name' => $notification->creator ? $notification->creator->name : 'Hệ thống',
                        'action' => $notification->type_display,
                        'description' => $notification->message,
                        'model_display' => $notification->type_display,
                        'created_at' => $notification->created_at,
                        'time_ago' => $notification->time_ago,
                        'icon' => $notification->type_icon,
                        'type' => $notification->type,
                        'is_read' => $notification->is_read,
                        'priority' => $notification->priority,
                        'priority_badge' => $notification->priority_badge,
                        'action_url' => $notification->data['action_url'] ?? null,
                        'action_text' => 'bán đơn hàng',
                    ];
                });

            return $fallbackNotifications;
        }

      
        return $orderNotifications;
    }

    // Chart data for revenue with period support
    public static function getRevenueChartData($period = 'month') {
        switch ($period) {
            case 'today':
                return self::getTodayRevenueChart();
            case 'yesterday':
                return self::getYesterdayRevenueChart();
            case 'this_week':
                return self::getThisWeekRevenueChart();
            case 'last_week':
                return self::getLastWeekRevenueChart();
            case 'last_month':
                return self::getLastMonthRevenueChart();
            case 'year':
                return self::getYearRevenueChart();
            default: // month
                return self::getMonthRevenueChart();
        }
    }

    // Helper method to get period name
    private static function getPeriodName($period) {
        switch ($period) {
            case 'today':
                return 'hôm nay';
            case 'yesterday':
                return 'hôm qua';
            case 'month':
                return 'tháng này';
            case 'last_month':
                return 'tháng trước';
            case 'year':
                return 'năm nay';
            default:
                return 'tháng này';
        }
    }

    // Helper method to get date range for period
    private static function getDateRangeForPeriod($period) {
        switch ($period) {
            case 'today':
                return [
                    'start' => \Carbon\Carbon::today(),
                    'end' => \Carbon\Carbon::today()->endOfDay()
                ];
            case 'yesterday':
                return [
                    'start' => \Carbon\Carbon::yesterday(),
                    'end' => \Carbon\Carbon::yesterday()->endOfDay()
                ];
            case 'month':
                return [
                    'start' => \Carbon\Carbon::now()->startOfMonth(),
                    'end' => \Carbon\Carbon::now()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => \Carbon\Carbon::now()->subMonth()->startOfMonth(),
                    'end' => \Carbon\Carbon::now()->subMonth()->endOfMonth()
                ];
            case 'year':
                return [
                    'start' => \Carbon\Carbon::now()->startOfYear(),
                    'end' => \Carbon\Carbon::now()->endOfYear()
                ];
            default:
                return [
                    'start' => \Carbon\Carbon::now()->startOfMonth(),
                    'end' => \Carbon\Carbon::now()->endOfMonth()
                ];
        }
    }

    // Top products chart data
    public static function getTopProductsChartData($type = 'revenue', $period = 'month') {
        // Get date range based on period
        $dateRange = self::getDateRangeForPeriod($period);

        if ($type === 'quantity') {
            // Top 10 products by quantity sold
            $query = \App\Models\OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                ->with('product')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['processing', 'completed']); // Chỉ lấy orders đã confirmed

            // Apply date filter
            if ($dateRange['start'] && $dateRange['end']) {
                $query->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']]);
            } elseif ($dateRange['start']) {
                $query->where('orders.created_at', '>=', $dateRange['start']);
            }

            $products = $query->groupBy('product_id')
                ->orderBy('total_quantity', 'desc')
                ->limit(10)
                ->get();

            $categories = $products->pluck('product.product_name')->toArray();
            $data = $products->pluck('total_quantity')->toArray();

            // Nếu không có dữ liệu thì để rỗng (không dùng sample data)
            if (empty($categories) || empty($data)) {
                $categories = [];
                $data = [];
            }

            // Get period name for series
            $periodName = self::getPeriodName($period);

            return [
                'categories' => $categories,
                'data' => $data,
                'series_name' => 'Số lượng bán ' . $periodName,
                'type' => 'quantity',
                'period' => $period
            ];
        } else {
            // Top 10 products by revenue - Lấy từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $query = \App\Models\InvoiceItem::select('product_id', DB::raw('SUM(line_total) as total_revenue'))
                ->with('product')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereIn('invoices.status', ['paid', 'completed']); // Chỉ lấy invoices đã hoàn thành

            // Apply date filter
            if ($dateRange['start'] && $dateRange['end']) {
                $query->whereBetween('invoices.created_at', [$dateRange['start'], $dateRange['end']]);
            } elseif ($dateRange['start']) {
                $query->where('invoices.created_at', '>=', $dateRange['start']);
            }

            $products = $query->groupBy('product_id')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();

            $categories = $products->pluck('product.product_name')->toArray();
            $data = $products->pluck('total_revenue')->map(function($revenue) {
                return $revenue / 1000000; // Convert to millions
            })->toArray();

            // Nếu không có dữ liệu thì để rỗng (không dùng sample data)
            if (empty($categories) || empty($data)) {
                $categories = [];
                $data = [];
            }

            // Get period name for series
            $periodName = self::getPeriodName($period);

            return [
                'categories' => $categories,
                'data' => $data,
                'series_name' => 'Doanh thu ' . $periodName,
                'type' => 'revenue',
                'period' => $period
            ];
        }
    }

    // Helper methods for different chart periods
    private static function getTodayRevenueChart() {
        $today = \Carbon\Carbon::today();
        $hours = [];
        $data = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = $today->copy()->addHours($i);
            $hours[] = $hour->format('H:i');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereBetween('created_at', [
                $hour,
                $hour->copy()->addHour()
            ])->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $hours,
            'data' => $data,
            'series_name' => 'Doanh thu hôm nay (triệu VNĐ)'
        ];
    }

    private static function getYesterdayRevenueChart() {
        $yesterday = \Carbon\Carbon::yesterday();
        $hours = [];
        $data = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = $yesterday->copy()->addHours($i);
            $hours[] = $hour->format('H:i');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereBetween('created_at', [
                $hour,
                $hour->copy()->addHour()
            ])->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $hours,
            'data' => $data,
            'series_name' => 'Doanh thu hôm qua (triệu VNĐ)'
        ];
    }

    private static function getThisWeekRevenueChart() {
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
        $endOfWeek = \Carbon\Carbon::now()->endOfWeek();
        $days = [];
        $data = [];

        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $days[] = $date->format('d/m');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');
            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $days,
            'data' => $data,
            'series_name' => 'Doanh thu tuần này (triệu VNĐ)'
        ];
    }

    private static function getLastWeekRevenueChart() {
        $startOfLastWeek = \Carbon\Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = \Carbon\Carbon::now()->subWeek()->endOfWeek();
        $days = [];
        $data = [];

        for ($date = $startOfLastWeek->copy(); $date->lte($endOfLastWeek); $date->addDay()) {
            $days[] = $date->format('d/m');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');
            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $days,
            'data' => $data,
            'series_name' => 'Doanh thu tuần trước (triệu VNĐ)'
        ];
    }

    private static function getMonthRevenueChart() {
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $today = \Carbon\Carbon::now(); // Chỉ hiển thị đến ngày hiện tại
        $days = [];
        $data = [];

        // Loop từ đầu tháng đến ngày hiện tại (không phải cuối tháng)
        for ($date = $startOfMonth->copy(); $date->lte($today); $date->addDay()) {
            $days[] = $date->format('d/m');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');
            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $days,
            'data' => $data,
            'series_name' => 'Doanh thu tháng này (triệu VNĐ)'
        ];
    }

    private static function getLastMonthRevenueChart() {
        $startOfLastMonth = \Carbon\Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = \Carbon\Carbon::now()->subMonth()->endOfMonth();
        $days = [];
        $data = [];

        for ($date = $startOfLastMonth->copy(); $date->lte($endOfLastMonth); $date->addDay()) {
            $days[] = $date->format('d/m');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');
            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $days,
            'data' => $data,
            'series_name' => 'Doanh thu tháng trước (triệu VNĐ)'
        ];
    }

    private static function getYearRevenueChart() {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = \Carbon\Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            // Lấy revenue từ hóa đơn đã hoàn thành, không phải từ đơn hàng
            $revenue = \App\Models\Invoice::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');

            $data[] = $revenue / 1000000; // Convert to millions
        }

        return [
            'categories' => $months,
            'data' => $data,
            'series_name' => 'Doanh thu theo năm (triệu VNĐ)'
        ];
    }

}

