<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

class FixAllAdminRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:admin-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix all admin route references after adding admin. prefix';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing all admin route references...');

        // Step 1: Clear all caches
        $this->clearCaches();

        // Step 2: Check current route structure
        $this->checkRoutes();

        // Step 3: List routes that need to be updated in views
        $this->listRoutesToUpdate();

        $this->info('');
        $this->info('🎉 Admin route fix completed!');
        $this->info('');
        $this->info('💡 Next steps:');
        $this->line('   1. Update any remaining route references in blade files');
        $this->line('   2. Test the application in browser');
        $this->line('   3. Check for any JavaScript route references');

        return 0;
    }

    /**
     * Clear all Laravel caches
     */
    private function clearCaches()
    {
        $this->info('1. Clearing Laravel caches...');

        $caches = [
            'view:clear' => 'View cache',
            'route:clear' => 'Route cache',
            'config:clear' => 'Config cache',
            'cache:clear' => 'Application cache',
        ];

        foreach ($caches as $command => $description) {
            try {
                Artisan::call($command);
                $this->info("   ✅ {$description} cleared");
            } catch (\Exception $e) {
                $this->warn("   ⚠️  Could not clear {$description}: " . $e->getMessage());
            }
        }
    }

    /**
     * Check current route structure
     */
    private function checkRoutes()
    {
        $this->info('2. Checking current route structure...');

        $routes = Route::getRoutes();
        $adminRoutes = [];
        $oldRoutes = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name) {
                if (str_starts_with($name, 'admin.')) {
                    $adminRoutes[] = $name;
                } elseif (in_array($name, $this->getOldRouteNames())) {
                    $oldRoutes[] = $name;
                }
            }
        }

        $this->info("   ✅ Found " . count($adminRoutes) . " routes with admin. prefix");
        
        if (!empty($oldRoutes)) {
            $this->warn("   ⚠️  Found " . count($oldRoutes) . " routes without admin. prefix:");
            foreach (array_slice($oldRoutes, 0, 10) as $route) {
                $this->line("      - {$route}");
            }
            if (count($oldRoutes) > 10) {
                $this->line("      ... and " . (count($oldRoutes) - 10) . " more");
            }
        } else {
            $this->info("   ✅ No old route names found");
        }
    }

    /**
     * List routes that need to be updated
     */
    private function listRoutesToUpdate()
    {
        $this->info('3. Routes that need to be updated in views:');

        $routesToUpdate = [
            'page.list' => 'admin.page.list',
            'page.add' => 'admin.page.add',
            'page.edit' => 'admin.page.edit',
            'page.delete' => 'admin.page.delete',
            'news.list' => 'admin.news.list',
            'news.add' => 'admin.news.add',
            'project.list' => 'admin.project.list',
            'project.add' => 'admin.project.add',
            'products.list' => 'admin.products.list',
            'products.add' => 'admin.products.add',
            'comment.list' => 'admin.comment.list',
            'inventory.dashboard' => 'admin.inventory.dashboard',
            'inventory.transactions' => 'admin.inventory.transactions',
            'inventory.import' => 'admin.inventory.import',
            'inventory.export' => 'admin.inventory.export',
            'inventory.adjustment' => 'admin.inventory.adjustment',
            'supplier.list' => 'admin.supplier.list',
            'order.list' => 'admin.order.list',
            'order.add' => 'admin.order.add',
            'order.statistics' => 'admin.order.statistics',
            'category.list' => 'admin.category.list',
            'user.list' => 'admin.user.list',
            'user.add' => 'admin.user.add',
        ];

        $this->table(['Old Route', 'New Route'], array_map(function($old, $new) {
            return [$old, $new];
        }, array_keys($routesToUpdate), array_values($routesToUpdate)));

        $this->info('');
        $this->info('📝 To update these routes in blade files, replace:');
        $this->line('   {{ route("page.list") }} → {{ route("admin.page.list") }}');
        $this->line('   {{ route("news.list") }} → {{ route("admin.news.list") }}');
        $this->line('   etc...');
    }

    /**
     * Get list of old route names that should now have admin. prefix
     */
    private function getOldRouteNames(): array
    {
        return [
            'page.list', 'page.add', 'page.edit', 'page.delete',
            'news.list', 'news.add', 'news.edit', 'news.delete',
            'project.list', 'project.add', 'project.edit', 'project.delete',
            'products.list', 'products.add', 'products.edit', 'products.delete',
            'comment.list', 'comment.add', 'comment.edit', 'comment.delete',
            'inventory.dashboard', 'inventory.transactions', 'inventory.import', 'inventory.export',
            'supplier.list', 'supplier.add', 'supplier.edit', 'supplier.delete',
            'order.list', 'order.add', 'order.edit', 'order.delete',
            'category.list', 'category.add', 'category.edit', 'category.delete',
            'user.list', 'user.add', 'user.edit', 'user.delete',
        ];
    }
}
