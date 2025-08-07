<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;

class DebugDashboardCommand extends Command
{
    protected $signature = 'debug:dashboard';
    protected $description = 'Debug dashboard data and statistics';

    public function handle()
    {
        $this->info('=== DASHBOARD DEBUG REPORT ===');
        $this->newLine();

        // Current date info
        $this->info('📅 DATE INFORMATION:');
        $this->line('Current time: ' . now());
        $this->line('Today: ' . Carbon::today());
        $this->line('Timezone: ' . config('app.timezone'));
        $this->newLine();

        // Database counts
        $this->info('📊 DATABASE COUNTS:');
        $this->line('Total Orders: ' . Order::count());
        $this->line('Total Customers: ' . Customer::count());
        $this->line('Total Products: ' . Product::count());
        $this->newLine();

        // Today's data
        $this->info('🎯 TODAY\'S DATA:');
        $today = Carbon::today();
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $revenueToday = Order::whereDate('created_at', $today)->sum('final_amount');
        $customersToday = Order::whereDate('created_at', $today)->distinct('customer_id')->count();
        $avgOrderValue = Order::whereDate('created_at', $today)->avg('final_amount') ?? 0;

        $this->line('Orders today: ' . $ordersToday);
        $this->line('Revenue today: ' . number_format($revenueToday, 0, ',', '.') . ' VNĐ');
        $this->line('Customers today: ' . $customersToday);
        $this->line('Avg order value: ' . number_format($avgOrderValue, 0, ',', '.') . ' VNĐ');
        $this->newLine();

        // Recent orders
        $this->info('📋 RECENT ORDERS (Last 5):');
        $recentOrders = Order::orderBy('created_at', 'desc')->limit(5)->get();
        if ($recentOrders->count() > 0) {
            foreach ($recentOrders as $order) {
                $this->line('- ' . $order->order_number . ' | ' . number_format($order->final_amount, 0, ',', '.') . ' VNĐ | ' . $order->created_at->format('d/m/Y H:i'));
            }
        } else {
            $this->line('No orders found');
        }
        $this->newLine();

        // DashboardService test
        $this->info('🔧 DASHBOARD SERVICE TEST:');
        try {
            $todayStats = DashboardService::getTodaySalesStats();
            $this->line('Service - Orders: ' . ($todayStats['orders_count'] ?? 'N/A'));
            $this->line('Service - Revenue: ' . number_format($todayStats['revenue'] ?? 0, 0, ',', '.') . ' VNĐ');
            $this->line('Service - Customers: ' . ($todayStats['customers_count'] ?? 'N/A'));
            $this->line('Service - Avg Order: ' . number_format($todayStats['avg_order_value'] ?? 0, 0, ',', '.') . ' VNĐ');
        } catch (\Exception $e) {
            $this->error('DashboardService error: ' . $e->getMessage());
        }
        $this->newLine();

        // Orders by date (last 7 days)
        $this->info('📈 ORDERS BY DATE (Last 7 days):');
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Order::whereDate('created_at', $date)->count();
            $revenue = Order::whereDate('created_at', $date)->sum('final_amount');
            $this->line($date->format('d/m/Y') . ': ' . $count . ' orders, ' . number_format($revenue, 0, ',', '.') . ' VNĐ');
        }
        $this->newLine();

        // Check for data issues
        $this->info('🔍 DATA VALIDATION:');
        
        // Check for orders with null amounts
        $nullAmountOrders = Order::whereNull('final_amount')->count();
        if ($nullAmountOrders > 0) {
            $this->warn('Found ' . $nullAmountOrders . ' orders with null final_amount');
        }

        // Check for orders with zero amounts
        $zeroAmountOrders = Order::where('final_amount', 0)->count();
        if ($zeroAmountOrders > 0) {
            $this->warn('Found ' . $zeroAmountOrders . ' orders with zero final_amount');
        }

        // Check for future dated orders
        $futureOrders = Order::where('created_at', '>', now())->count();
        if ($futureOrders > 0) {
            $this->warn('Found ' . $futureOrders . ' orders with future dates');
        }

        $this->info('✅ Dashboard debug completed!');
    }
}
