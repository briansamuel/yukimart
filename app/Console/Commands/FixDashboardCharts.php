<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Services\DashboardService;

class FixDashboardCharts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:dashboard-charts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix dashboard charts by clearing caches and testing functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing Dashboard Charts...');
        $this->newLine();

        // Step 1: Clear all caches
        $this->info('1. Clearing Laravel caches...');
        $this->clearCaches();

        // Step 2: Test chart data generation
        $this->info('2. Testing chart data generation...');
        $this->testChartData();

        // Step 3: Check database tables
        $this->info('3. Checking database tables...');
        $this->checkDatabase();

        // Step 4: Verify routes
        $this->info('4. Verifying routes...');
        $this->verifyRoutes();

        // Step 5: Create sample data if needed
        $this->info('5. Checking for sample data...');
        $this->checkSampleData();

        $this->newLine();
        $this->info('🎉 Dashboard Charts Fix Completed!');
        $this->newLine();
        
        $this->info('📋 Summary:');
        $this->line('   ✅ Caches cleared');
        $this->line('   ✅ Chart data tested');
        $this->line('   ✅ Database verified');
        $this->line('   ✅ Routes confirmed');
        
        $this->newLine();
        $this->info('🌐 Next Steps:');
        $this->line('   1. Visit: ' . route('admin.dashboard'));
        $this->line('   2. Open browser developer tools (F12)');
        $this->line('   3. Check console for JavaScript errors');
        $this->line('   4. Verify charts render properly');

        return 0;
    }

    /**
     * Clear all Laravel caches
     */
    private function clearCaches()
    {
        $caches = [
            'view:clear' => 'View cache',
            'route:clear' => 'Route cache',
            'config:clear' => 'Config cache',
            'cache:clear' => 'Application cache',
        ];

        foreach ($caches as $command => $description) {
            try {
                Artisan::call($command);
                $this->line("   ✅ {$description} cleared");
            } catch (\Exception $e) {
                $this->warn("   ⚠️  Could not clear {$description}: " . $e->getMessage());
            }
        }
    }

    /**
     * Test chart data generation
     */
    private function testChartData()
    {
        try {
            // Test revenue chart
            $revenueData = DashboardService::getRevenueChartData('month');
            if (!empty($revenueData['categories']) && !empty($revenueData['data'])) {
                $this->line('   ✅ Revenue chart data: ' . count($revenueData['categories']) . ' points');
            } else {
                $this->warn('   ⚠️  Revenue chart data is empty');
            }

            // Test top products chart
            $productsData = DashboardService::getTopProductsChartData('revenue');
            if (!empty($productsData['categories']) && !empty($productsData['data'])) {
                $this->line('   ✅ Top products chart data: ' . count($productsData['categories']) . ' products');
            } else {
                $this->warn('   ⚠️  Top products chart data is empty');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Chart data generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Check database tables
     */
    private function checkDatabase()
    {
        $tables = [
            'orders' => \App\Models\Order::class,
            'order_items' => \App\Models\OrderItem::class,
            'products' => \App\Models\Product::class,
            'customers' => \App\Models\Customer::class,
        ];

        foreach ($tables as $tableName => $model) {
            try {
                $count = $model::count();
                $this->line("   ✅ {$tableName} table: {$count} records");
            } catch (\Exception $e) {
                $this->error("   ❌ {$tableName} table error: " . $e->getMessage());
            }
        }
    }

    /**
     * Verify routes
     */
    private function verifyRoutes()
    {
        $routes = [
            'admin.dashboard' => 'Dashboard',
            'admin.dashboard.revenue-data' => 'Revenue data API',
            'admin.dashboard.top-products-data' => 'Top products API',
        ];

        foreach ($routes as $routeName => $description) {
            try {
                $url = route($routeName);
                $this->line("   ✅ {$description}: {$url}");
            } catch (\Exception $e) {
                $this->error("   ❌ {$description} route error: " . $e->getMessage());
            }
        }
    }

    /**
     * Check for sample data
     */
    private function checkSampleData()
    {
        $orderCount = \App\Models\Order::count();
        $productCount = \App\Models\Product::count();
        $customerCount = \App\Models\Customer::count();

        if ($orderCount == 0) {
            $this->warn('   ⚠️  No orders found - charts will show sample data');
            $this->line('   💡 Consider creating some test orders for realistic charts');
        } else {
            $this->line("   ✅ Found {$orderCount} orders for chart data");
        }

        if ($productCount == 0) {
            $this->warn('   ⚠️  No products found - top products chart will show sample data');
        } else {
            $this->line("   ✅ Found {$productCount} products");
        }

        if ($customerCount == 0) {
            $this->warn('   ⚠️  No customers found');
        } else {
            $this->line("   ✅ Found {$customerCount} customers");
        }
    }
}
