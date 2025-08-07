<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    /**
     * API Health Check
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $health = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'version' => '1.0.0',
                'environment' => app()->environment(),
                'checks' => []
            ];

            // Database check
            try {
                DB::connection()->getPdo();
                $health['checks']['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection successful'
                ];
            } catch (\Exception $e) {
                $health['checks']['database'] = [
                    'status' => 'unhealthy',
                    'message' => 'Database connection failed: ' . $e->getMessage()
                ];
                $health['status'] = 'unhealthy';
            }

            // Cache check
            try {
                Cache::put('health_check', 'test', 60);
                $value = Cache::get('health_check');
                Cache::forget('health_check');
                
                $health['checks']['cache'] = [
                    'status' => $value === 'test' ? 'healthy' : 'unhealthy',
                    'message' => $value === 'test' ? 'Cache working properly' : 'Cache not working'
                ];
                
                if ($value !== 'test') {
                    $health['status'] = 'degraded';
                }
            } catch (\Exception $e) {
                $health['checks']['cache'] = [
                    'status' => 'unhealthy',
                    'message' => 'Cache error: ' . $e->getMessage()
                ];
                $health['status'] = 'degraded';
            }

            // Storage check
            try {
                $testFile = storage_path('logs/health_check.tmp');
                file_put_contents($testFile, 'test');
                $content = file_get_contents($testFile);
                unlink($testFile);
                
                $health['checks']['storage'] = [
                    'status' => $content === 'test' ? 'healthy' : 'unhealthy',
                    'message' => $content === 'test' ? 'Storage writable' : 'Storage not writable'
                ];
                
                if ($content !== 'test') {
                    $health['status'] = 'degraded';
                }
            } catch (\Exception $e) {
                $health['checks']['storage'] = [
                    'status' => 'unhealthy',
                    'message' => 'Storage error: ' . $e->getMessage()
                ];
                $health['status'] = 'degraded';
            }

            // Memory check
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $memoryLimitBytes = $this->convertToBytes($memoryLimit);
            $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;

            $health['checks']['memory'] = [
                'status' => $memoryPercent < 80 ? 'healthy' : ($memoryPercent < 95 ? 'degraded' : 'unhealthy'),
                'usage' => $this->formatBytes($memoryUsage),
                'limit' => $memoryLimit,
                'percentage' => round($memoryPercent, 2)
            ];

            if ($memoryPercent >= 95) {
                $health['status'] = 'unhealthy';
            } elseif ($memoryPercent >= 80) {
                $health['status'] = 'degraded';
            }

            $statusCode = $health['status'] === 'healthy' ? 200 : ($health['status'] === 'degraded' ? 200 : 503);

            return response()->json($health, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage(),
                'checks' => []
            ], 503);
        }
    }

    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
