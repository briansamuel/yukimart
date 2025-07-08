<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

class FixUserSettingsRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-settings-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user settings route issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing user settings route issues...');

        // Step 1: Clear route cache
        $this->info('1. Clearing route cache...');
        try {
            Artisan::call('route:clear');
            $this->info('✅ Route cache cleared');
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear route cache: ' . $e->getMessage());
        }

        // Step 2: Clear config cache
        $this->info('2. Clearing config cache...');
        try {
            Artisan::call('config:clear');
            $this->info('✅ Config cache cleared');
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear config cache: ' . $e->getMessage());
        }

        // Step 3: Check if route exists
        $this->info('3. Checking if user-settings routes exist...');
        
        $routes = Route::getRoutes();
        $userSettingsRoutes = [];
        
        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name && str_contains($name, 'user-settings')) {
                $userSettingsRoutes[] = [
                    'name' => $name,
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                ];
            }
        }

        if (empty($userSettingsRoutes)) {
            $this->error('❌ No user-settings routes found!');
            
            // Check if admin routes are loaded
            $adminRoutes = [];
            foreach ($routes as $route) {
                $name = $route->getName();
                if ($name && str_starts_with($name, 'admin.')) {
                    $adminRoutes[] = $name;
                }
            }
            
            if (empty($adminRoutes)) {
                $this->error('❌ No admin routes found! Admin routes may not be loading properly.');
                $this->info('💡 Check RouteServiceProvider and admin.php route file.');
            } else {
                $this->info('✅ Found ' . count($adminRoutes) . ' admin routes');
                $this->info('💡 User-settings routes may not be defined correctly in admin.php');
            }
            
            return 1;
        }

        $this->info('✅ Found ' . count($userSettingsRoutes) . ' user-settings routes:');
        
        $this->table(['Route Name', 'URI', 'Methods'], $userSettingsRoutes);

        // Step 4: Check specifically for the store route
        $storeRoute = collect($userSettingsRoutes)->firstWhere('name', 'admin.user-settings.store');
        
        if ($storeRoute) {
            $this->info('✅ admin.user-settings.store route found!');
            $this->line('   URI: ' . $storeRoute['uri']);
            $this->line('   Methods: ' . $storeRoute['methods']);
            
            // Test the route URL generation
            try {
                $url = route('admin.user-settings.store');
                $this->info('✅ Route URL generation works: ' . $url);
            } catch (\Exception $e) {
                $this->error('❌ Route URL generation failed: ' . $e->getMessage());
                return 1;
            }
            
        } else {
            $this->error('❌ admin.user-settings.store route NOT found!');
            return 1;
        }

        // Step 5: Check controller exists
        $this->info('4. Checking UserSettingsController...');
        
        $controllerClass = 'App\Http\Controllers\Admin\CMS\UserSettingsController';
        if (class_exists($controllerClass)) {
            $this->info('✅ UserSettingsController exists');
            
            if (method_exists($controllerClass, 'store')) {
                $this->info('✅ store method exists');
            } else {
                $this->error('❌ store method not found in UserSettingsController');
                return 1;
            }
        } else {
            $this->error('❌ UserSettingsController not found');
            return 1;
        }

        // Step 6: Check UserSetting model
        $this->info('5. Checking UserSetting model...');
        
        $modelClass = 'App\Models\UserSetting';
        if (class_exists($modelClass)) {
            $this->info('✅ UserSetting model exists');
        } else {
            $this->error('❌ UserSetting model not found');
            return 1;
        }

        $this->info('');
        $this->info('🎉 All checks passed! User settings route should be working now.');
        $this->info('');
        $this->info('💡 If you\'re still having issues, try:');
        $this->line('   1. Restart your web server');
        $this->line('   2. Clear browser cache');
        $this->line('   3. Check browser console for JavaScript errors');

        return 0;
    }
}
