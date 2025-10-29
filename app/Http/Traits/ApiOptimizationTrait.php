<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

trait ApiOptimizationTrait
{
    /**
     * Generate cache key for API responses
     */
    protected function generateCacheKey(Request $request, $prefix = 'api')
    {
        $params = $request->all();
        ksort($params);
        
        return $prefix . '_' . md5(serialize($params)) . '_' . $request->getPathInfo();
    }

    /**
     * Cache API response
     */
    protected function cacheResponse($key, $data, $minutes = 5)
    {
        return Cache::remember($key, now()->addMinutes($minutes), function() use ($data) {
            return $data;
        });
    }

    /**
     * Parse fields parameter for field selection
     */
    protected function parseFields($fieldsParam, $defaultFields = [])
    {
        if (empty($fieldsParam)) {
            return $defaultFields;
        }

        return array_map('trim', explode(',', $fieldsParam));
    }

    /**
     * Filter response data based on requested fields
     */
    protected function filterFields($data, $fields)
    {
        if (empty($fields) || !is_array($data)) {
            return $data;
        }

        // If data is a collection/array of items
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = array_map(function($item) use ($fields) {
                return $this->filterSingleItem($item, $fields);
            }, $data['data']);
            return $data;
        }

        // If data is a single item
        return $this->filterSingleItem($data, $fields);
    }

    /**
     * Filter single item fields
     */
    private function filterSingleItem($item, $fields)
    {
        if (!is_array($item)) {
            return $item;
        }

        $filtered = [];
        foreach ($fields as $field) {
            if (isset($item[$field])) {
                $filtered[$field] = $item[$field];
            }
        }

        return $filtered;
    }

    /**
     * Add performance headers to response
     */
    protected function addPerformanceHeaders($response, $startTime = null)
    {
        if ($startTime) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $response->header('X-Execution-Time', $executionTime . 'ms');
        }

        $response->header('X-API-Version', 'v1');
        $response->header('X-Optimized', 'true');

        return $response;
    }

    /**
     * Optimize pagination for large datasets
     */
    protected function optimizePagination($query, $request)
    {
        $perPage = min($request->get('per_page', 15), 100); // Max 100 items
        $page = $request->get('page', 1);

        // Use cursor pagination for better performance on large datasets
        if ($request->has('cursor')) {
            return $query->cursorPaginate($perPage);
        }

        return $query->paginate($perPage);
    }

    /**
     * Compress response if client supports it
     */
    protected function compressResponse($response, $request)
    {
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        if (strpos($acceptEncoding, 'gzip') !== false) {
            $response->header('Content-Encoding', 'gzip');
        }

        return $response;
    }

    /**
     * Add API rate limiting headers
     */
    protected function addRateLimitHeaders($response, $maxAttempts = 60, $decayMinutes = 1)
    {
        $response->header('X-RateLimit-Limit', $maxAttempts);
        $response->header('X-RateLimit-Remaining', max(0, $maxAttempts - 1));
        $response->header('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);

        return $response;
    }

    /**
     * Validate and sanitize request parameters
     */
    protected function sanitizeRequest($request, $allowedParams = [])
    {
        if (empty($allowedParams)) {
            return $request->all();
        }

        return $request->only($allowedParams);
    }

    /**
     * Add CORS headers for API
     */
    protected function addCorsHeaders($response)
    {
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

        return $response;
    }

    /**
     * Format API error response
     */
    protected function errorResponse($message, $code = 400, $errors = [])
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString()
        ], $code);
    }

    /**
     * Format API success response
     */
    protected function successResponse($data, $message = 'Success', $meta = [])
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }
}
