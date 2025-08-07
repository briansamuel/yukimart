<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\OpenApiGeneratorService;
use App\Services\PostmanSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentationController extends BaseApiController
{
    protected OpenApiGeneratorService $openApiService;
    protected PostmanSyncService $postmanService;

    public function __construct(
        OpenApiGeneratorService $openApiService,
        PostmanSyncService $postmanService
    ) {
        $this->openApiService = $openApiService;
        $this->postmanService = $postmanService;
    }

    /**
     * Get OpenAPI specification
     */
    public function openapi(Request $request): JsonResponse
    {
        try {
            $spec = $this->openApiService->generateOpenApiSpec();
            
            return response()->json($spec)
                ->header('Content-Type', 'application/json')
                ->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Download OpenAPI specification as file
     */
    public function downloadOpenApi(Request $request)
    {
        try {
            $format = $request->get('format', 'json');
            $spec = $this->openApiService->generateOpenApiSpec();
            
            if ($format === 'yaml') {
                $content = yaml_emit($spec);
                $filename = 'yukimart-api-v1.yaml';
                $contentType = 'application/x-yaml';
            } else {
                $content = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $filename = 'yukimart-api-v1.json';
                $contentType = 'application/json';
            }

            return response($content)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Sync API documentation to Postman
     */
    public function syncPostman(Request $request): JsonResponse
    {
        try {
            if (!config('api.documentation.postman_sync')) {
                return $this->errorResponse(
                    'Postman sync is not enabled',
                    null,
                    'POSTMAN_SYNC_DISABLED',
                    403
                );
            }

            $result = $this->postmanService->syncToPostman();

            if ($result['success']) {
                return $this->successResponse($result, 'Postman collection synced successfully');
            } else {
                return $this->errorResponse($result['message'], null, 'POSTMAN_SYNC_FAILED', 500);
            }

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get API documentation info
     */
    public function info(Request $request): JsonResponse
    {
        try {
            $info = [
                'api_version' => 'v1',
                'openapi_version' => '3.0.0',
                'title' => 'YukiMart API',
                'description' => 'RESTful API for YukiMart mobile application',
                'version' => '1.0.0',
                'base_url' => config('app.url') . '/api/v1',
                'documentation_url' => config('app.url') . '/api/v1/docs',
                'postman_collection_url' => config('api.documentation.postman_collection_id') 
                    ? "https://www.postman.com/collections/" . config('api.documentation.postman_collection_id')
                    : null,
                'endpoints' => $this->getEndpointsSummary(),
                'authentication' => [
                    'type' => 'Bearer Token',
                    'description' => 'Use Laravel Sanctum tokens for authentication',
                    'header' => 'Authorization: Bearer {token}'
                ],
                'rate_limiting' => [
                    'general' => '60 requests per minute',
                    'auth' => '5 requests per minute',
                    'mobile' => '120 requests per minute (authenticated)'
                ],
                'supported_formats' => ['JSON'],
                'error_codes' => config('api.error_codes'),
                'last_updated' => now()->toISOString()
            ];

            return $this->successResponse($info, 'API documentation info retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get API health status
     */
    public function health(Request $request): JsonResponse
    {
        try {
            $health = [
                'status' => 'healthy',
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
                'uptime' => $this->getUptime(),
                'database' => $this->checkDatabaseHealth(),
                'cache' => $this->checkCacheHealth(),
                'storage' => $this->checkStorageHealth(),
                'dependencies' => [
                    'laravel' => app()->version(),
                    'php' => PHP_VERSION,
                    'sanctum' => 'enabled'
                ]
            ];

            return $this->successResponse($health, 'API health check completed');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get endpoints summary
     */
    protected function getEndpointsSummary(): array
    {
        $routes = collect(\Route::getRoutes())
            ->filter(fn($route) => str_starts_with($route->uri(), 'api/v1/'))
            ->groupBy(function ($route) {
                $uri = $route->uri();
                if (str_contains($uri, 'auth')) return 'Authentication';
                if (str_contains($uri, 'invoices')) return 'Invoices';
                if (str_contains($uri, 'orders')) return 'Orders';
                if (str_contains($uri, 'products')) return 'Products';
                if (str_contains($uri, 'customers')) return 'Customers';
                if (str_contains($uri, 'payments')) return 'Payments';
                if (str_contains($uri, 'user')) return 'User';
                return 'General';
            })
            ->map(fn($routes) => $routes->count())
            ->toArray();

        return $routes;
    }

    /**
     * Get API uptime
     */
    protected function getUptime(): string
    {
        $startTime = cache()->remember('api_start_time', 86400, fn() => now());
        $uptime = now()->diffInSeconds($startTime);
        
        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }

    /**
     * Check database health
     */
    protected function checkDatabaseHealth(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }

    /**
     * Check cache health
     */
    protected function checkCacheHealth(): string
    {
        try {
            cache()->put('health_check', 'ok', 10);
            $value = cache()->get('health_check');
            return $value === 'ok' ? 'working' : 'failed';
        } catch (\Exception $e) {
            return 'failed';
        }
    }

    /**
     * Check storage health
     */
    protected function checkStorageHealth(): string
    {
        try {
            $testFile = storage_path('app/health_check.txt');
            File::put($testFile, 'health check');
            $content = File::get($testFile);
            File::delete($testFile);
            return $content === 'health check' ? 'working' : 'failed';
        } catch (\Exception $e) {
            return 'failed';
        }
    }
}
