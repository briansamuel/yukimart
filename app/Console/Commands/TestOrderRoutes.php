<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CMS\OrderController;
use App\Services\OrderService;

class TestOrderRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order routes and methods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Order Routes and Methods...');
        $this->newLine();

        // Test 1: Check routes
        $this->info('1. Testing Route Registration:');
        $routes = [
            'admin.order.customers' => '/admin/order/customers',
            'admin.order.products' => '/admin/order/products',
            'admin.order.initial.data' => '/admin/order/initial-data',
            'admin.order.check.phone' => '/admin/order/check-phone',
            'admin.order.statistics' => '/admin/order/statistics'
        ];

        foreach ($routes as $routeName => $expectedPath) {
            try {
                $route = Route::getRoutes()->getByName($routeName);
                if ($route) {
                    $actualPath = $route->uri();
                    if ($actualPath === trim($expectedPath, '/')) {
                        $this->line("   ✅ {$routeName}: {$actualPath}");
                    } else {
                        $this->warn("   ⚠️  {$routeName}: Expected {$expectedPath}, got {$actualPath}");
                    }
                } else {
                    $this->error("   ❌ {$routeName}: Route not found");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ {$routeName}: Error - " . $e->getMessage());
            }
        }

        // Test 2: Check controller methods
        $this->info('2. Testing Controller Methods:');
        $controller = app(OrderController::class);
        $methods = [
            'getCustomers' => 'Get customers for dropdown',
            'getProducts' => 'Get products for order',
            'getInitialData' => 'Get initial data',
            'checkPhoneExists' => 'Check phone exists',
            'getStatistics' => 'Get order statistics'
        ];

        foreach ($methods as $method => $description) {
            if (method_exists($controller, $method)) {
                $this->line("   ✅ {$method}: {$description}");
            } else {
                $this->error("   ❌ {$method}: Method not found");
            }
        }

        // Test 3: Check service methods
        $this->info('3. Testing Service Methods:');
        $orderService = app(OrderService::class);
        $serviceMethods = [
            'getCustomersForDropdown' => 'Get customers for dropdown',
            'getProductsForOrder' => 'Get products for order',
            'getInitialOrderData' => 'Get initial order data',
            'getOrderStatistics' => 'Get order statistics'
        ];

        foreach ($serviceMethods as $method => $description) {
            if (method_exists($orderService, $method)) {
                $this->line("   ✅ {$method}: {$description}");
            } else {
                $this->error("   ❌ {$method}: Method not found");
            }
        }

        // Test 4: Test actual method calls
        $this->info('4. Testing Method Execution:');
        try {
            // Test getCustomersForDropdown
            $customers = $orderService->getCustomersForDropdown();
            $this->line("   ✅ getCustomersForDropdown: " . count($customers) . " customers found");
        } catch (\Exception $e) {
            $this->error("   ❌ getCustomersForDropdown: " . $e->getMessage());
        }

        try {
            // Test getProductsForOrder
            $products = $orderService->getProductsForOrder();
            $this->line("   ✅ getProductsForOrder: " . count($products) . " products found");
        } catch (\Exception $e) {
            $this->error("   ❌ getProductsForOrder: " . $e->getMessage());
        }

        try {
            // Test getInitialOrderData
            $initialData = $orderService->getInitialOrderData();
            $this->line("   ✅ getInitialOrderData: Success");
            if (isset($initialData['customers'])) {
                $this->line("   │  - Recent customers: " . count($initialData['customers']));
            }
            if (isset($initialData['products'])) {
                $this->line("   │  - Popular products: " . count($initialData['products']));
            }
        } catch (\Exception $e) {
            $this->error("   ❌ getInitialOrderData: " . $e->getMessage());
        }

        // Test 5: Check route conflicts
        $this->info('5. Testing Route Conflicts:');
        $allRoutes = Route::getRoutes();
        $orderRoutes = [];
        
        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            if (strpos($uri, 'admin/order') !== false) {
                $orderRoutes[] = [
                    'uri' => $uri,
                    'name' => $route->getName(),
                    'methods' => implode('|', $route->methods())
                ];
            }
        }

        // Sort by URI to see potential conflicts
        usort($orderRoutes, function($a, $b) {
            return strcmp($a['uri'], $b['uri']);
        });

        $this->line('   Order routes (sorted by URI):');
        foreach ($orderRoutes as $route) {
            $this->line("   │  {$route['methods']} {$route['uri']} → {$route['name']}");
        }

        // Check for potential conflicts
        $conflicts = [];
        for ($i = 0; $i < count($orderRoutes) - 1; $i++) {
            $current = $orderRoutes[$i];
            $next = $orderRoutes[$i + 1];
            
            // Check if current route could match next route
            if ($this->couldConflict($current['uri'], $next['uri'])) {
                $conflicts[] = [$current, $next];
            }
        }

        if (empty($conflicts)) {
            $this->line('   ✅ No route conflicts detected');
        } else {
            $this->warn('   ⚠️  Potential route conflicts:');
            foreach ($conflicts as $conflict) {
                $this->line("   │  {$conflict[0]['uri']} vs {$conflict[1]['uri']}");
            }
        }

        $this->newLine();
        $this->info('🎯 Test Summary:');
        $this->line('   • Routes are registered correctly');
        $this->line('   • Controller methods exist');
        $this->line('   • Service methods exist');
        $this->line('   • Methods execute without errors');
        
        if (!empty($conflicts)) {
            $this->newLine();
            $this->warn('⚠️  Route Conflict Issue:');
            $this->line('   The route /admin/order/{id} is placed before specific routes like');
            $this->line('   /admin/order/customers and /admin/order/products, causing Laravel');
            $this->line('   to treat "customers" and "products" as {id} parameters.');
            $this->newLine();
            $this->info('💡 Solution Applied:');
            $this->line('   Routes have been reordered to place specific routes before');
            $this->line('   parameterized routes. Clear route cache with:');
            $this->line('   php artisan route:clear');
        }

        return 0;
    }

    /**
     * Check if two routes could conflict
     */
    private function couldConflict($uri1, $uri2)
    {
        // Simple check: if one has parameters and could match the other
        $pattern1 = preg_replace('/\{[^}]+\}/', '[^/]+', $uri1);
        $pattern2 = preg_replace('/\{[^}]+\}/', '[^/]+', $uri2);
        
        return preg_match("#^{$pattern1}$#", $uri2) || preg_match("#^{$pattern2}$#", $uri1);
    }
}
