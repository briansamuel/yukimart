<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Services\PostmanCollectionService;
use App\Services\ApiDocumentationService;
use Carbon\Carbon;

class AutoSyncApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:auto-sync 
                            {--force : Force sync even if no changes detected}
                            {--watch : Watch for changes and auto-sync}
                            {--interval=60 : Watch interval in seconds}
                            {--postman : Only sync to Postman}
                            {--docs : Only update documentation}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-discover new API routes and sync to Postman + Documentation';

    private $postmanService;
    private $docService;
    private $lastRouteHash;

    public function __construct(PostmanCollectionService $postmanService)
    {
        parent::__construct();
        $this->postmanService = $postmanService;
        $this->docService = new ApiDocumentationService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 YukiMart API Auto-Sync Tool');
        $this->info('================================');

        if ($this->option('watch')) {
            return $this->watchMode();
        }

        return $this->singleRun();
    }

    /**
     * Single run mode
     */
    private function singleRun()
    {
        $this->info('🔍 Scanning for API changes...');
        
        $currentRoutes = $this->getApiRoutes();
        $routeHash = $this->generateRouteHash($currentRoutes);
        $lastHash = Cache::get('api_routes_hash');

        if (!$this->option('force') && $routeHash === $lastHash) {
            $this->info('✅ No changes detected. API is up to date.');
            return 0;
        }

        $this->info('📊 Changes detected! Starting sync...');
        $this->displayRouteChanges($currentRoutes);

        $results = [];

        // Sync to Postman
        if (!$this->option('docs')) {
            $this->info('📮 Syncing to Postman...');
            $results['postman'] = $this->syncToPostman($currentRoutes);
        }

        // Update Documentation
        if (!$this->option('postman')) {
            $this->info('📚 Updating documentation...');
            $results['documentation'] = $this->updateDocumentation($currentRoutes);
        }

        // Save current hash
        Cache::put('api_routes_hash', $routeHash, now()->addDays(30));
        Cache::put('api_last_sync', now(), now()->addDays(30));

        $this->displayResults($results);
        return 0;
    }

    /**
     * Watch mode - continuously monitor for changes
     */
    private function watchMode()
    {
        $interval = (int) $this->option('interval');
        $this->info("👀 Watching for API changes (checking every {$interval} seconds)...");
        $this->info('Press Ctrl+C to stop watching');

        while (true) {
            $currentRoutes = $this->getApiRoutes();
            $routeHash = $this->generateRouteHash($currentRoutes);
            $lastHash = Cache::get('api_routes_hash');

            if ($routeHash !== $lastHash) {
                $this->info('🔄 Changes detected at ' . now()->format('H:i:s'));
                $this->singleRun();
            } else {
                $this->line('⏰ ' . now()->format('H:i:s') . ' - No changes');
            }

            sleep($interval);
        }
    }

    /**
     * Get all API routes
     */
    private function getApiRoutes(): array
    {
        $routes = [];
        
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            
            // Filter API v1 routes only
            if (str_starts_with($uri, 'api/v1/')) {
                $routes[] = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $uri,
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware(),
                    'parameters' => $route->parameterNames(),
                    'controller' => $this->getControllerInfo($route->getActionName())
                ];
            }
        }

        return $routes;
    }

    /**
     * Generate hash for routes to detect changes
     */
    private function generateRouteHash(array $routes): string
    {
        $routeSignature = collect($routes)->map(function ($route) {
            return $route['method'] . '|' . $route['uri'] . '|' . $route['action'];
        })->sort()->implode('|');

        return md5($routeSignature);
    }

    /**
     * Display route changes
     */
    private function displayRouteChanges(array $currentRoutes)
    {
        $this->info('📋 Current API Routes:');
        
        $grouped = collect($currentRoutes)->groupBy(function ($route) {
            $parts = explode('/', $route['uri']);
            return $parts[2] ?? 'other'; // api/v1/{group}
        });

        foreach ($grouped as $group => $routes) {
            $this->line("  📁 {$group} ({$routes->count()} routes)");
            foreach ($routes as $route) {
                $methods = str_replace('|', ', ', $route['method']);
                $this->line("    {$methods} /{$route['uri']}");
            }
        }
    }

    /**
     * Sync to Postman
     */
    private function syncToPostman(array $routes): array
    {
        try {
            $collection = $this->postmanService->generateCollection();
            $result = $this->postmanService->syncToPostman($collection);
            
            return [
                'status' => 'success',
                'routes_count' => count($routes),
                'collection_id' => config('postman.collection_id'),
                'synced_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            $this->error('❌ Postman sync failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update documentation
     */
    private function updateDocumentation(array $routes): array
    {
        try {
            $result = $this->docService->generateDocumentation();
            
            // Generate route summary
            $this->generateRouteSummary($routes);
            
            return [
                'status' => 'success',
                'routes_documented' => count($routes),
                'files_generated' => $result['files_generated'] ?? [],
                'updated_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            $this->error('❌ Documentation update failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate route summary file
     */
    private function generateRouteSummary(array $routes)
    {
        $summary = "# YukiMart API Routes Summary\n\n";
        $summary .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $summary .= "Total Routes: " . count($routes) . "\n\n";

        $grouped = collect($routes)->groupBy(function ($route) {
            $parts = explode('/', $route['uri']);
            return $parts[2] ?? 'other';
        });

        foreach ($grouped as $group => $groupRoutes) {
            $summary .= "## " . ucfirst($group) . " ({$groupRoutes->count()} routes)\n\n";
            
            foreach ($groupRoutes as $route) {
                $methods = str_replace('|', ', ', $route['method']);
                $summary .= "- **{$methods}** `/{$route['uri']}`";
                if ($route['name']) {
                    $summary .= " - {$route['name']}";
                }
                $summary .= "\n";
            }
            $summary .= "\n";
        }

        File::put(storage_path('app/api-routes-summary.md'), $summary);
    }

    /**
     * Display sync results
     */
    private function displayResults(array $results)
    {
        $this->info('🎉 Auto-sync completed!');
        $this->info('====================');

        if (isset($results['postman'])) {
            if ($results['postman']['status'] === 'success') {
                $this->info('✅ Postman: Synced ' . $results['postman']['routes_count'] . ' routes');
            } else {
                $this->error('❌ Postman: ' . $results['postman']['error']);
            }
        }

        if (isset($results['documentation'])) {
            if ($results['documentation']['status'] === 'success') {
                $this->info('✅ Documentation: Updated ' . $results['documentation']['routes_documented'] . ' routes');
            } else {
                $this->error('❌ Documentation: ' . $results['documentation']['error']);
            }
        }

        $this->info('📅 Last sync: ' . Cache::get('api_last_sync', 'Never'));
    }

    /**
     * Get controller info from action name
     */
    private function getControllerInfo(string $action): array
    {
        if (str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            return [
                'class' => $controller,
                'method' => $method
            ];
        }

        return [
            'class' => $action,
            'method' => '__invoke'
        ];
    }
}
