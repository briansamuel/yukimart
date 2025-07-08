<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class CheckUserSettingsRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:check-user-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if user settings routes exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking user settings routes...');

        // Get all routes
        $routes = Route::getRoutes();
        
        $userSettingsRoutes = [];
        
        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name && str_contains($name, 'user-settings')) {
                $userSettingsRoutes[] = [
                    'name' => $name,
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                    'action' => $route->getActionName(),
                ];
            }
        }

        if (empty($userSettingsRoutes)) {
            $this->error('❌ No user-settings routes found!');
            
            // Check for similar routes
            $this->info('Looking for similar routes...');
            $similarRoutes = [];
            
            foreach ($routes as $route) {
                $name = $route->getName();
                if ($name && (str_contains($name, 'settings') || str_contains($name, 'user'))) {
                    $similarRoutes[] = [
                        'name' => $name,
                        'uri' => $route->uri(),
                        'methods' => implode('|', $route->methods()),
                    ];
                }
            }
            
            if (!empty($similarRoutes)) {
                $this->table(['Route Name', 'URI', 'Methods'], array_map(function($route) {
                    return [$route['name'], $route['uri'], $route['methods']];
                }, array_slice($similarRoutes, 0, 10)));
            }
            
            return 1;
        }

        $this->info('✅ Found ' . count($userSettingsRoutes) . ' user-settings routes:');
        
        $this->table(['Route Name', 'URI', 'Methods', 'Action'], array_map(function($route) {
            return [$route['name'], $route['uri'], $route['methods'], $route['action']];
        }, $userSettingsRoutes));

        // Check specifically for the store route
        $storeRoute = collect($userSettingsRoutes)->firstWhere('name', 'admin.user-settings.store');
        
        if ($storeRoute) {
            $this->info('✅ admin.user-settings.store route found!');
            $this->line('URI: ' . $storeRoute['uri']);
            $this->line('Methods: ' . $storeRoute['methods']);
            $this->line('Action: ' . $storeRoute['action']);
        } else {
            $this->error('❌ admin.user-settings.store route NOT found!');
            
            // Check what store routes exist
            $storeRoutes = collect($userSettingsRoutes)->filter(function($route) {
                return str_contains($route['name'], 'store');
            });
            
            if ($storeRoutes->isNotEmpty()) {
                $this->info('Found these store routes:');
                foreach ($storeRoutes as $route) {
                    $this->line('- ' . $route['name']);
                }
            }
        }

        return 0;
    }
}
