<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ErrorMonitoringService
{
    protected $cachePrefix = 'error_monitor:';
    protected $maxErrorsPerHour = 100;
    protected $criticalErrorThreshold = 10;

    /**
     * Track an error occurrence
     */
    public function trackError(\Exception $e, array $context = []): void
    {
        $errorData = $this->prepareErrorData($e, $context);
        
        // Log the error
        Log::error('Error tracked', $errorData);
        
        // Store in cache for quick access
        $this->storeInCache($errorData);
        
        // Check if this is a critical error pattern
        $this->checkCriticalPattern($errorData);
        
        // Update error statistics
        $this->updateStatistics($errorData);
    }

    /**
     * Prepare error data for tracking
     */
    protected function prepareErrorData(\Exception $e, array $context = []): array
    {
        return [
            'error_id' => $this->generateErrorId($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'class' => get_class($e),
            'trace' => $e->getTraceAsString(),
            'context' => $context,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->toISOString(),
            'severity' => $this->determineSeverity($e),
        ];
    }

    /**
     * Generate unique error ID based on error characteristics
     */
    protected function generateErrorId(\Exception $e): string
    {
        $key = sprintf(
            '%s:%s:%d',
            get_class($e),
            basename($e->getFile()),
            $e->getLine()
        );
        
        return md5($key);
    }

    /**
     * Determine error severity
     */
    protected function determineSeverity(\Exception $e): string
    {
        $criticalExceptions = [
            \Illuminate\Database\QueryException::class,
            \PDOException::class,
            \Error::class,
            \ParseError::class,
            \TypeError::class,
        ];

        $warningExceptions = [
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
        ];

        $exceptionClass = get_class($e);

        if (in_array($exceptionClass, $criticalExceptions)) {
            return 'critical';
        }

        if (in_array($exceptionClass, $warningExceptions)) {
            return 'warning';
        }

        // Check for specific error patterns
        if (str_contains($e->getMessage(), 'SQLSTATE') || 
            str_contains($e->getMessage(), 'Connection refused')) {
            return 'critical';
        }

        return 'info';
    }

    /**
     * Store error in cache for quick access
     */
    protected function storeInCache(array $errorData): void
    {
        $errorId = $errorData['error_id'];
        $cacheKey = $this->cachePrefix . $errorId;
        
        // Get existing data or create new
        $existingData = Cache::get($cacheKey, [
            'first_occurrence' => $errorData['timestamp'],
            'count' => 0,
            'last_occurrence' => null,
            'error_data' => $errorData,
        ]);

        // Update occurrence data
        $existingData['count']++;
        $existingData['last_occurrence'] = $errorData['timestamp'];
        $existingData['error_data'] = $errorData; // Update with latest context

        // Store for 24 hours
        Cache::put($cacheKey, $existingData, now()->addHours(24));
    }

    /**
     * Check for critical error patterns
     */
    protected function checkCriticalPattern(array $errorData): void
    {
        $errorId = $errorData['error_id'];
        $cacheKey = $this->cachePrefix . $errorId;
        $errorInfo = Cache::get($cacheKey);

        if (!$errorInfo) {
            return;
        }

        // Check if error occurred too frequently
        if ($errorInfo['count'] >= $this->criticalErrorThreshold) {
            $this->alertCriticalError($errorData, $errorInfo);
        }

        // Check for database connection errors
        if ($errorData['severity'] === 'critical' && 
            str_contains($errorData['message'], 'database')) {
            $this->alertDatabaseError($errorData);
        }
    }

    /**
     * Update error statistics
     */
    protected function updateStatistics(array $errorData): void
    {
        $hour = now()->format('Y-m-d-H');
        $statsKey = $this->cachePrefix . 'stats:' . $hour;
        
        $stats = Cache::get($statsKey, [
            'total_errors' => 0,
            'critical_errors' => 0,
            'warning_errors' => 0,
            'info_errors' => 0,
            'unique_errors' => [],
        ]);

        $stats['total_errors']++;
        $stats[$errorData['severity'] . '_errors']++;
        $stats['unique_errors'][$errorData['error_id']] = true;

        Cache::put($statsKey, $stats, now()->addHours(25));
    }

    /**
     * Get error statistics for a time period
     */
    public function getStatistics(int $hours = 24): array
    {
        $stats = [
            'total_errors' => 0,
            'critical_errors' => 0,
            'warning_errors' => 0,
            'info_errors' => 0,
            'unique_errors' => 0,
            'hourly_breakdown' => [],
        ];

        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $statsKey = $this->cachePrefix . 'stats:' . $hour;
            $hourlyStats = Cache::get($statsKey, []);

            if (!empty($hourlyStats)) {
                $stats['total_errors'] += $hourlyStats['total_errors'] ?? 0;
                $stats['critical_errors'] += $hourlyStats['critical_errors'] ?? 0;
                $stats['warning_errors'] += $hourlyStats['warning_errors'] ?? 0;
                $stats['info_errors'] += $hourlyStats['info_errors'] ?? 0;
                
                $stats['hourly_breakdown'][$hour] = $hourlyStats;
            }
        }

        // Count unique errors across all hours
        $allUniqueErrors = [];
        foreach ($stats['hourly_breakdown'] as $hourlyData) {
            if (isset($hourlyData['unique_errors'])) {
                $allUniqueErrors = array_merge($allUniqueErrors, array_keys($hourlyData['unique_errors']));
            }
        }
        $stats['unique_errors'] = count(array_unique($allUniqueErrors));

        return $stats;
    }

    /**
     * Get recent errors
     */
    public function getRecentErrors(int $limit = 50): array
    {
        $errors = [];
        $pattern = $this->cachePrefix . '*';
        
        // Get all error cache keys
        $keys = Cache::getRedis()->keys($pattern);
        
        foreach ($keys as $key) {
            if (str_contains($key, 'stats:')) {
                continue; // Skip statistics keys
            }
            
            $errorData = Cache::get(str_replace(config('cache.prefix') . ':', '', $key));
            if ($errorData && isset($errorData['error_data'])) {
                $errors[] = $errorData;
            }
        }

        // Sort by last occurrence
        usort($errors, function ($a, $b) {
            return strtotime($b['last_occurrence']) - strtotime($a['last_occurrence']);
        });

        return array_slice($errors, 0, $limit);
    }

    /**
     * Alert for critical errors
     */
    protected function alertCriticalError(array $errorData, array $errorInfo): void
    {
        Log::critical('Critical error pattern detected', [
            'error_id' => $errorData['error_id'],
            'message' => $errorData['message'],
            'occurrences' => $errorInfo['count'],
            'first_occurrence' => $errorInfo['first_occurrence'],
            'last_occurrence' => $errorInfo['last_occurrence'],
        ]);

        // Here you could send notifications, emails, Slack messages, etc.
        // Example: Notification::send($admins, new CriticalErrorNotification($errorData));
    }

    /**
     * Alert for database errors
     */
    protected function alertDatabaseError(array $errorData): void
    {
        Log::critical('Database error detected', [
            'error_id' => $errorData['error_id'],
            'message' => $errorData['message'],
            'file' => $errorData['file'],
            'line' => $errorData['line'],
        ]);

        // Here you could trigger database health checks, failover procedures, etc.
    }

    /**
     * Clear old error data
     */
    public function cleanup(): void
    {
        $pattern = $this->cachePrefix . '*';
        $keys = Cache::getRedis()->keys($pattern);
        
        foreach ($keys as $key) {
            $keyName = str_replace(config('cache.prefix') . ':', '', $key);
            $data = Cache::get($keyName);
            
            if ($data && isset($data['last_occurrence'])) {
                $lastOccurrence = Carbon::parse($data['last_occurrence']);
                
                // Remove errors older than 7 days
                if ($lastOccurrence->lt(now()->subDays(7))) {
                    Cache::forget($keyName);
                }
            }
        }
    }

    /**
     * Check system health based on error patterns
     */
    public function getSystemHealth(): array
    {
        $stats = $this->getStatistics(1); // Last hour
        
        $health = [
            'status' => 'healthy',
            'score' => 100,
            'issues' => [],
        ];

        // Check error rate
        if ($stats['total_errors'] > $this->maxErrorsPerHour) {
            $health['status'] = 'degraded';
            $health['score'] -= 30;
            $health['issues'][] = 'High error rate detected';
        }

        // Check critical errors
        if ($stats['critical_errors'] > 0) {
            $health['status'] = $stats['critical_errors'] > 5 ? 'unhealthy' : 'degraded';
            $health['score'] -= $stats['critical_errors'] * 10;
            $health['issues'][] = "Critical errors detected: {$stats['critical_errors']}";
        }

        // Ensure score doesn't go below 0
        $health['score'] = max(0, $health['score']);

        return $health;
    }
}
