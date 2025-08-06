<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ErrorMonitoringService;
use App\Services\DatabaseOptimizationService;
use App\Http\Middleware\ApiPerformanceMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    protected $errorMonitor;
    protected $dbOptimization;

    public function __construct(
        ErrorMonitoringService $errorMonitor,
        DatabaseOptimizationService $dbOptimization
    ) {
        $this->errorMonitor = $errorMonitor;
        $this->dbOptimization = $dbOptimization;
    }

    /**
     * Display monitoring dashboard
     */
    public function dashboard()
    {
        return view('admin.monitoring.dashboard');
    }

    /**
     * Get system overview data
     */
    public function getSystemOverview()
    {
        try {
            $data = [
                'system_health' => $this->getSystemHealth(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'error_statistics' => $this->getErrorStatistics(),
                'database_status' => $this->getDatabaseStatus(),
                'cache_status' => $this->getCacheStatus(),
                'recent_activities' => $this->getRecentActivities()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get system overview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load system overview'
            ], 500);
        }
    }

    /**
     * Get system health metrics
     */
    protected function getSystemHealth(): array
    {
        $health = $this->errorMonitor->getSystemHealth();
        
        // Add additional health checks
        $health['uptime'] = $this->getSystemUptime();
        $health['memory_usage'] = $this->getMemoryUsage();
        $health['disk_usage'] = $this->getDiskUsage();
        $health['cpu_usage'] = $this->getCpuUsage();

        return $health;
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        $apiStats = ApiPerformanceMiddleware::getPerformanceStats(24);
        
        return [
            'api_performance' => $apiStats,
            'database_performance' => $this->getDatabasePerformance(),
            'cache_performance' => $this->getCachePerformance(),
            'response_times' => $this->getResponseTimes()
        ];
    }

    /**
     * Get error statistics
     */
    protected function getErrorStatistics(): array
    {
        $stats = $this->errorMonitor->getStatistics(24);
        $recentErrors = $this->errorMonitor->getRecentErrors(10);

        return [
            'statistics' => $stats,
            'recent_errors' => $recentErrors,
            'error_trends' => $this->getErrorTrends()
        ];
    }

    /**
     * Get database status
     */
    protected function getDatabaseStatus(): array
    {
        try {
            $analysis = $this->dbOptimization->analyzePerformance();
            
            return [
                'connection_status' => 'connected',
                'slow_queries' => count($analysis['slow_queries']),
                'missing_indexes' => count($analysis['missing_indexes']),
                'table_sizes' => array_slice($analysis['table_sizes'], 0, 5),
                'query_performance' => $this->getQueryPerformance()
            ];

        } catch (\Exception $e) {
            return [
                'connection_status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cache status
     */
    protected function getCacheStatus(): array
    {
        try {
            $start = microtime(true);
            Cache::put('health_check', 'test', 60);
            $value = Cache::get('health_check');
            Cache::forget('health_check');
            $responseTime = (microtime(true) - $start) * 1000;

            return [
                'status' => $value === 'test' ? 'healthy' : 'error',
                'response_time' => round($responseTime, 2),
                'hit_rate' => $this->getCacheHitRate(),
                'memory_usage' => $this->getCacheMemoryUsage()
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get recent system activities
     */
    protected function getRecentActivities(): array
    {
        try {
            return [
                'recent_orders' => $this->getRecentOrders(),
                'recent_users' => $this->getRecentUsers(),
                'recent_products' => $this->getRecentProducts(),
                'system_events' => $this->getSystemEvents()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get recent activities: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system uptime
     */
    protected function getSystemUptime(): array
    {
        $uptime = file_get_contents('/proc/uptime');
        $uptimeSeconds = floatval(explode(' ', $uptime)[0]);
        
        return [
            'seconds' => $uptimeSeconds,
            'formatted' => $this->formatUptime($uptimeSeconds)
        ];
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): array
    {
        return [
            'current' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'limit' => ini_get('memory_limit')
        ];
    }

    /**
     * Get disk usage
     */
    protected function getDiskUsage(): array
    {
        $bytes = disk_free_space('.');
        $total = disk_total_space('.');
        $used = $total - $bytes;

        return [
            'used' => round($used / 1024 / 1024 / 1024, 2),
            'free' => round($bytes / 1024 / 1024 / 1024, 2),
            'total' => round($total / 1024 / 1024 / 1024, 2),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    /**
     * Get CPU usage (simplified)
     */
    protected function getCpuUsage(): array
    {
        // This is a simplified version - in production you'd use more sophisticated monitoring
        $load = sys_getloadavg();
        
        return [
            '1min' => $load[0],
            '5min' => $load[1],
            '15min' => $load[2]
        ];
    }

    /**
     * Get database performance metrics
     */
    protected function getDatabasePerformance(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;

            return [
                'response_time' => round($responseTime, 2),
                'active_connections' => $this->getActiveConnections(),
                'slow_queries' => $this->getSlowQueryCount()
            ];

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cache performance metrics
     */
    protected function getCachePerformance(): array
    {
        // This would need Redis/Memcached specific implementation
        return [
            'hit_rate' => 85.5, // Example data
            'miss_rate' => 14.5,
            'evictions' => 0
        ];
    }

    /**
     * Get response times for different endpoints
     */
    protected function getResponseTimes(): array
    {
        // Get from ApiPerformanceMiddleware metrics
        $stats = ApiPerformanceMiddleware::getPerformanceStats(1);
        
        return [
            'average' => $stats['avg_response_time'] ?? 0,
            'endpoints' => array_slice($stats['top_endpoints'] ?? [], 0, 5, true)
        ];
    }

    /**
     * Get error trends
     */
    protected function getErrorTrends(): array
    {
        $trends = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('H:00');
            $stats = $this->errorMonitor->getStatistics(1);
            
            $trends[] = [
                'time' => $hour,
                'errors' => $stats['total_errors'] ?? 0,
                'critical' => $stats['critical_errors'] ?? 0
            ];
        }

        return $trends;
    }

    /**
     * Get query performance
     */
    protected function getQueryPerformance(): array
    {
        try {
            $queries = DB::select("
                SELECT 
                    COUNT(*) as total_queries,
                    AVG(TIMER_WAIT/1000000000) as avg_time
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE DIGEST_TEXT IS NOT NULL
            ");

            return [
                'total_queries' => $queries[0]->total_queries ?? 0,
                'average_time' => round($queries[0]->avg_time ?? 0, 3)
            ];

        } catch (\Exception $e) {
            return ['error' => 'Performance schema not available'];
        }
    }

    /**
     * Get cache hit rate
     */
    protected function getCacheHitRate(): float
    {
        // This would need specific cache driver implementation
        return 85.5; // Example data
    }

    /**
     * Get cache memory usage
     */
    protected function getCacheMemoryUsage(): array
    {
        // This would need specific cache driver implementation
        return [
            'used' => 45.2,
            'total' => 128.0,
            'percentage' => 35.3
        ];
    }

    /**
     * Get recent orders
     */
    protected function getRecentOrders(): array
    {
        return DB::table('orders')
            ->select('id', 'order_code', 'total_amount', 'order_status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get recent users
     */
    protected function getRecentUsers(): array
    {
        return DB::table('users')
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get recent products
     */
    protected function getRecentProducts(): array
    {
        return DB::table('products')
            ->select('id', 'product_name', 'sku', 'product_status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get system events
     */
    protected function getSystemEvents(): array
    {
        // This would read from system logs or events table
        return [
            ['event' => 'System started', 'time' => now()->subHours(2)->toISOString()],
            ['event' => 'Cache cleared', 'time' => now()->subHour()->toISOString()],
            ['event' => 'Database optimized', 'time' => now()->subMinutes(30)->toISOString()]
        ];
    }

    /**
     * Get active database connections
     */
    protected function getActiveConnections(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get slow query count
     */
    protected function getSlowQueryCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Slow_queries'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Format uptime seconds to human readable
     */
    protected function formatUptime(float $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }
}
