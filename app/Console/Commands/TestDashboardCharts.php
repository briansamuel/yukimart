<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;

class TestDashboardCharts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dashboard-charts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test dashboard chart data generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Dashboard Charts...');
        $this->newLine();

        // Test Revenue Chart Data
        $this->info('1. Testing Revenue Chart Data:');
        $this->line('   ├─ Testing month period...');
        
        try {
            $monthData = DashboardService::getRevenueChartData('month');
            $this->info('   ├─ ✅ Month data: ' . count($monthData['categories']) . ' categories, ' . count($monthData['data']) . ' data points');
            $this->line('   ├─ Series: ' . $monthData['series_name']);
            $this->line('   ├─ Sample categories: ' . implode(', ', array_slice($monthData['categories'], 0, 5)));
            $this->line('   ├─ Sample data: ' . implode(', ', array_slice($monthData['data'], 0, 5)));
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Month data failed: ' . $e->getMessage());
        }

        $this->line('   ├─ Testing today period...');
        try {
            $todayData = DashboardService::getRevenueChartData('today');
            $this->info('   ├─ ✅ Today data: ' . count($todayData['categories']) . ' categories, ' . count($todayData['data']) . ' data points');
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Today data failed: ' . $e->getMessage());
        }

        $this->line('   ├─ Testing year period...');
        try {
            $yearData = DashboardService::getRevenueChartData('year');
            $this->info('   └─ ✅ Year data: ' . count($yearData['categories']) . ' categories, ' . count($yearData['data']) . ' data points');
        } catch (\Exception $e) {
            $this->error('   └─ ❌ Year data failed: ' . $e->getMessage());
        }

        $this->newLine();

        // Test Top Products Chart Data
        $this->info('2. Testing Top Products Chart Data:');
        $this->line('   ├─ Testing revenue type...');
        
        try {
            $revenueProducts = DashboardService::getTopProductsChartData('revenue');
            $this->info('   ├─ ✅ Revenue products: ' . count($revenueProducts['categories']) . ' products');
            $this->line('   ├─ Series: ' . $revenueProducts['series_name']);
            $this->line('   ├─ Type: ' . $revenueProducts['type']);
            $this->line('   ├─ Sample products: ' . implode(', ', array_slice($revenueProducts['categories'], 0, 3)));
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Revenue products failed: ' . $e->getMessage());
        }

        $this->line('   └─ Testing quantity type...');
        try {
            $quantityProducts = DashboardService::getTopProductsChartData('quantity');
            $this->info('   └─ ✅ Quantity products: ' . count($quantityProducts['categories']) . ' products');
            $this->line('       ├─ Series: ' . $quantityProducts['series_name']);
            $this->line('       └─ Type: ' . $quantityProducts['type']);
        } catch (\Exception $e) {
            $this->error('   └─ ❌ Quantity products failed: ' . $e->getMessage());
        }

        $this->newLine();

        // Test Dashboard Statistics
        $this->info('3. Testing Dashboard Statistics:');
        
        try {
            $totalProducts = DashboardService::totalProducts();
            $this->info('   ├─ ✅ Total Products: ' . $totalProducts);
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Total Products failed: ' . $e->getMessage());
        }

        try {
            $totalOrders = DashboardService::totalOrders();
            $this->info('   ├─ ✅ Total Orders: ' . $totalOrders);
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Total Orders failed: ' . $e->getMessage());
        }

        try {
            $totalCustomers = DashboardService::totalCustomers();
            $this->info('   ├─ ✅ Total Customers: ' . $totalCustomers);
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Total Customers failed: ' . $e->getMessage());
        }

        try {
            $todaySales = DashboardService::getTodaySalesStats();
            $this->info('   └─ ✅ Today Sales Stats:');
            $this->line('       ├─ Orders: ' . $todaySales['orders_count']);
            $this->line('       ├─ Revenue: ' . number_format($todaySales['revenue'], 0, ',', '.') . ' VNĐ');
            $this->line('       ├─ Customers: ' . $todaySales['customers_count']);
            $this->line('       └─ Avg Order: ' . number_format($todaySales['avg_order_value'], 0, ',', '.') . ' VNĐ');
        } catch (\Exception $e) {
            $this->error('   └─ ❌ Today Sales Stats failed: ' . $e->getMessage());
        }

        $this->newLine();

        // Test Database Tables
        $this->info('4. Testing Database Tables:');
        
        try {
            $orderCount = \App\Models\Order::count();
            $this->info('   ├─ ✅ Orders table: ' . $orderCount . ' records');
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Orders table failed: ' . $e->getMessage());
        }

        try {
            $orderItemCount = \App\Models\OrderItem::count();
            $this->info('   ├─ ✅ OrderItems table: ' . $orderItemCount . ' records');
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ OrderItems table failed: ' . $e->getMessage());
        }

        try {
            $productCount = \App\Models\Product::count();
            $this->info('   ├─ ✅ Products table: ' . $productCount . ' records');
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Products table failed: ' . $e->getMessage());
        }

        try {
            $customerCount = \App\Models\Customer::count();
            $this->info('   └─ ✅ Customers table: ' . $customerCount . ' records');
        } catch (\Exception $e) {
            $this->error('   └─ ❌ Customers table failed: ' . $e->getMessage());
        }

        $this->newLine();

        // Test Routes
        $this->info('5. Testing Routes:');
        
        try {
            $dashboardRoute = route('admin.dashboard');
            $this->info('   ├─ ✅ Dashboard route: ' . $dashboardRoute);
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Dashboard route failed: ' . $e->getMessage());
        }

        try {
            $revenueDataRoute = route('admin.dashboard.revenue-data');
            $this->info('   ├─ ✅ Revenue data route: ' . $revenueDataRoute);
        } catch (\Exception $e) {
            $this->error('   ├─ ❌ Revenue data route failed: ' . $e->getMessage());
        }

        try {
            $topProductsRoute = route('admin.dashboard.top-products-data');
            $this->info('   └─ ✅ Top products route: ' . $topProductsRoute);
        } catch (\Exception $e) {
            $this->error('   └─ ❌ Top products route failed: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Dashboard Charts Test Completed!');
        $this->newLine();
        
        $this->info('💡 Next Steps:');
        $this->line('   1. Visit the dashboard: ' . route('admin.dashboard'));
        $this->line('   2. Open browser developer tools (F12)');
        $this->line('   3. Check console for any JavaScript errors');
        $this->line('   4. Verify charts are rendering properly');
        $this->line('   5. Test dropdown filters for chart updates');

        return 0;
    }
}
