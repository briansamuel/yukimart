<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\SecurityService;

class ApiPerformanceMiddleware
{
    protected $securityService;
    protected $cachePrefix = 'api_perf:';

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $queryCount = 0;

        // Start monitoring database queries
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        // Security checks
        $this->performSecurityChecks($request);

        // Rate limiting
        if (!$this->checkRateLimit($request)) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.'
            ], 429);
        }

        // Check for cached response
        $cacheKey = $this->generateCacheKey($request);
        if ($this->shouldCache($request)) {
            $cachedResponse = Cache::get($cacheKey);
            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                $response->headers->set('X-Cache', 'HIT');
                return $response;
            }
        }

        // Process request
        $response = $next($request);

        // Calculate performance metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $metrics = [
            'execution_time' => round(($endTime - $startTime) * 1000, 2), // milliseconds
            'memory_usage' => round(($endMemory - $startMemory) / 1024 / 1024, 2), // MB
            'query_count' => $queryCount,
            'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) // MB
        ];

        // Add performance headers
        $response->headers->set('X-Response-Time', $metrics['execution_time'] . 'ms');
        $response->headers->set('X-Memory-Usage', $metrics['memory_usage'] . 'MB');
        $response->headers->set('X-Query-Count', $metrics['query_count']);

        // Log slow requests
        if ($metrics['execution_time'] > 1000) { // > 1 second
            Log::warning('Slow API request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'metrics' => $metrics,
                'user_id' => auth()->id()
            ]);
        }

        // Cache successful responses
        if ($this->shouldCache($request) && $response->isSuccessful()) {
            $responseData = json_decode($response->getContent(), true);
            if ($responseData) {
                Cache::put($cacheKey, $responseData, $this->getCacheDuration($request));
                $response->headers->set('X-Cache', 'MISS');
            }
        }

        // Store metrics for analysis
        $this->storeMetrics($request, $metrics);

        return $response;
    }

    /**
     * Perform security checks
     */
    protected function performSecurityChecks(Request $request): void
    {
        $suspiciousActivity = $this->securityService->detectSuspiciousActivity($request);
        
        if (!empty($suspiciousActivity)) {
            $this->securityService->logSecurityEvent('Suspicious API activity detected', [
                'patterns' => $suspiciousActivity,
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
        }
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit(Request $request): bool
    {
        $key = 'rate_limit:' . $request->ip();
        $maxRequests = 60; // requests per minute
        $window = 60; // seconds

        $current = Cache::get($key, 0);
        
        if ($current >= $maxRequests) {
            return false;
        }

        Cache::put($key, $current + 1, now()->addSeconds($window));
        return true;
    }

    /**
     * Generate cache key for request
     */
    protected function generateCacheKey(Request $request): string
    {
        $key = $request->method() . ':' . $request->path();
        
        // Include query parameters for GET requests
        if ($request->isMethod('GET')) {
            $params = $request->query();
            ksort($params);
            $key .= ':' . md5(serialize($params));
        }

        // Include user context for authenticated requests
        if (auth()->check()) {
            $key .= ':user:' . auth()->id();
        }

        return $this->cachePrefix . md5($key);
    }

    /**
     * Determine if request should be cached
     */
    protected function shouldCache(Request $request): bool
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }

        // Don't cache admin routes
        if (str_contains($request->path(), 'admin')) {
            return false;
        }

        // Cache API routes
        if (str_contains($request->path(), 'api/')) {
            return true;
        }

        // Cache specific endpoints
        $cacheableEndpoints = [
            'products',
            'categories',
            'customers',
            'suppliers'
        ];

        foreach ($cacheableEndpoints as $endpoint) {
            if (str_contains($request->path(), $endpoint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cache duration based on request type
     */
    protected function getCacheDuration(Request $request): int
    {
        // Short cache for frequently changing data
        if (str_contains($request->path(), 'orders') || 
            str_contains($request->path(), 'inventory')) {
            return 300; // 5 minutes
        }

        // Medium cache for semi-static data
        if (str_contains($request->path(), 'products') || 
            str_contains($request->path(), 'customers')) {
            return 900; // 15 minutes
        }

        // Long cache for static data
        if (str_contains($request->path(), 'categories') || 
            str_contains($request->path(), 'suppliers')) {
            return 3600; // 1 hour
        }

        return 600; // Default 10 minutes
    }

    /**
     * Store performance metrics
     */
    protected function storeMetrics(Request $request, array $metrics): void
    {
        $key = $this->cachePrefix . 'metrics:' . date('Y-m-d-H');
        
        $existing = Cache::get($key, [
            'total_requests' => 0,
            'total_time' => 0,
            'total_memory' => 0,
            'total_queries' => 0,
            'slow_requests' => 0,
            'endpoints' => []
        ]);

        $existing['total_requests']++;
        $existing['total_time'] += $metrics['execution_time'];
        $existing['total_memory'] += $metrics['memory_usage'];
        $existing['total_queries'] += $metrics['query_count'];

        if ($metrics['execution_time'] > 1000) {
            $existing['slow_requests']++;
        }

        // Track per-endpoint metrics
        $endpoint = $request->method() . ' ' . $request->path();
        if (!isset($existing['endpoints'][$endpoint])) {
            $existing['endpoints'][$endpoint] = [
                'count' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'max_time' => 0
            ];
        }

        $existing['endpoints'][$endpoint]['count']++;
        $existing['endpoints'][$endpoint]['total_time'] += $metrics['execution_time'];
        $existing['endpoints'][$endpoint]['avg_time'] = 
            $existing['endpoints'][$endpoint]['total_time'] / $existing['endpoints'][$endpoint]['count'];
        $existing['endpoints'][$endpoint]['max_time'] = 
            max($existing['endpoints'][$endpoint]['max_time'], $metrics['execution_time']);

        Cache::put($key, $existing, now()->addHours(25));
    }

    /**
     * Get performance statistics
     */
    public static function getPerformanceStats(int $hours = 24): array
    {
        $stats = [
            'total_requests' => 0,
            'avg_response_time' => 0,
            'avg_memory_usage' => 0,
            'avg_query_count' => 0,
            'slow_request_percentage' => 0,
            'hourly_breakdown' => [],
            'top_endpoints' => []
        ];

        $cachePrefix = 'api_perf:metrics:';
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $key = $cachePrefix . $hour;
            $hourlyData = Cache::get($key, []);

            if (!empty($hourlyData)) {
                $stats['total_requests'] += $hourlyData['total_requests'] ?? 0;
                $stats['hourly_breakdown'][$hour] = $hourlyData;
            }
        }

        if ($stats['total_requests'] > 0) {
            $totalTime = array_sum(array_column($stats['hourly_breakdown'], 'total_time'));
            $totalMemory = array_sum(array_column($stats['hourly_breakdown'], 'total_memory'));
            $totalQueries = array_sum(array_column($stats['hourly_breakdown'], 'total_queries'));
            $totalSlowRequests = array_sum(array_column($stats['hourly_breakdown'], 'slow_requests'));

            $stats['avg_response_time'] = round($totalTime / $stats['total_requests'], 2);
            $stats['avg_memory_usage'] = round($totalMemory / $stats['total_requests'], 2);
            $stats['avg_query_count'] = round($totalQueries / $stats['total_requests'], 2);
            $stats['slow_request_percentage'] = round(($totalSlowRequests / $stats['total_requests']) * 100, 2);
        }

        // Aggregate endpoint statistics
        $allEndpoints = [];
        foreach ($stats['hourly_breakdown'] as $hourlyData) {
            if (isset($hourlyData['endpoints'])) {
                foreach ($hourlyData['endpoints'] as $endpoint => $data) {
                    if (!isset($allEndpoints[$endpoint])) {
                        $allEndpoints[$endpoint] = [
                            'count' => 0,
                            'total_time' => 0,
                            'max_time' => 0
                        ];
                    }
                    $allEndpoints[$endpoint]['count'] += $data['count'];
                    $allEndpoints[$endpoint]['total_time'] += $data['total_time'];
                    $allEndpoints[$endpoint]['max_time'] = max($allEndpoints[$endpoint]['max_time'], $data['max_time']);
                }
            }
        }

        // Calculate average times and sort by slowest
        foreach ($allEndpoints as $endpoint => &$data) {
            $data['avg_time'] = round($data['total_time'] / $data['count'], 2);
        }

        uasort($allEndpoints, function($a, $b) {
            return $b['avg_time'] <=> $a['avg_time'];
        });

        $stats['top_endpoints'] = array_slice($allEndpoints, 0, 10, true);

        return $stats;
    }
}
