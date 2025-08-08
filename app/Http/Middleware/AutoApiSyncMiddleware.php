<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoApiSyncMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only check on successful API requests
        if ($request->is('api/v1/*') && $response->getStatusCode() < 400) {
            $this->checkForRouteChanges();
        }

        return $response;
    }

    /**
     * Check if routes have changed and trigger auto-sync
     */
    private function checkForRouteChanges(): void
    {
        try {
            // Only check every 5 minutes to avoid performance impact
            $lastCheck = Cache::get('api_routes_last_check', 0);
            if (time() - $lastCheck < 300) {
                return;
            }

            Cache::put('api_routes_last_check', time(), 3600);

            $currentHash = $this->generateRoutesHash();
            $lastHash = Cache::get('api_routes_hash');

            if ($currentHash !== $lastHash) {
                Log::info('API routes changed, triggering auto-sync');
                
                // Run sync in background
                Artisan::queue('api:auto-sync', ['--force' => true]);
                
                Cache::put('api_routes_hash', $currentHash, now()->addDays(30));
            }
        } catch (\Exception $e) {
            Log::warning('Auto API sync check failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate hash of current API routes
     */
    private function generateRoutesHash(): string
    {
        $routes = [];
        
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/v1/')) {
                $routes[] = implode('|', $route->methods()) . '|' . $uri . '|' . $route->getActionName();
            }
        }

        sort($routes);
        return md5(implode('|', $routes));
    }
}
