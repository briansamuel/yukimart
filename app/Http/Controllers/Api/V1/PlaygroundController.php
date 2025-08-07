<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class PlaygroundController extends Controller
{
    protected CodeGeneratorService $codeGenerator;

    public function __construct(CodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Execute API request in playground
     */
    public function executeRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
            'endpoint' => 'required|string',
            'headers' => 'sometimes|array',
            'body' => 'sometimes|array',
            'query_params' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $method = strtolower($request->input('method'));
            $endpoint = $request->input('endpoint');
            $headers = $request->input('headers', []);
            $body = $request->input('body', []);
            $queryParams = $request->input('query_params', []);

            // Build full URL
            $baseUrl = config('app.url') . '/api/v1';
            $url = $baseUrl . '/' . ltrim($endpoint, '/');

            // Prepare headers
            $defaultHeaders = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Playground-Request' => 'true',
            ];

            $requestHeaders = array_merge($defaultHeaders, $headers);

            // Log playground request
            Log::info('Playground API Request', [
                'method' => $method,
                'url' => $url,
                'headers' => $requestHeaders,
                'body' => $body,
                'query_params' => $queryParams,
                'user_ip' => $request->ip(),
            ]);

            // Execute request
            $startTime = microtime(true);
            
            $httpClient = Http::withHeaders($requestHeaders);
            
            if (!empty($queryParams)) {
                $httpClient = $httpClient->withQueryParameters($queryParams);
            }

            $response = match($method) {
                'get' => $httpClient->get($url),
                'post' => $httpClient->post($url, $body),
                'put' => $httpClient->put($url, $body),
                'patch' => $httpClient->patch($url, $body),
                'delete' => $httpClient->delete($url),
                default => throw new \Exception('Unsupported HTTP method')
            };

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            // Parse response
            $responseData = [
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
                'response_time_ms' => $responseTime,
                'success' => $response->successful(),
            ];

            // Log response
            Log::info('Playground API Response', [
                'status_code' => $response->status(),
                'response_time_ms' => $responseTime,
                'success' => $response->successful(),
            ]);

            return $this->successResponse('Request executed successfully', $responseData);

        } catch (\Exception $e) {
            Log::error('Playground request failed', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint ?? null,
                'method' => $method ?? null,
            ]);

            return $this->errorResponse('Request execution failed', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ], 500);
        }
    }

    /**
     * Generate code examples for API request
     */
    public function generateCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
            'endpoint' => 'required|string',
            'headers' => 'sometimes|array',
            'body' => 'sometimes|array',
            'query_params' => 'sometimes|array',
            'languages' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $requestData = [
                'method' => $request->input('method'),
                'endpoint' => $request->input('endpoint'),
                'headers' => $request->input('headers', []),
                'body' => $request->input('body', []),
                'query_params' => $request->input('query_params', []),
            ];

            $languages = $request->input('languages', [
                'curl', 'javascript', 'php', 'python', 'dart', 'java'
            ]);

            $codeExamples = [];
            foreach ($languages as $language) {
                $codeExamples[$language] = $this->codeGenerator->generate($language, $requestData);
            }

            return $this->successResponse('Code examples generated successfully', [
                'request' => $requestData,
                'code_examples' => $codeExamples,
            ]);

        } catch (\Exception $e) {
            Log::error('Code generation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return $this->errorResponse('Code generation failed', [
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get playground authentication token
     */
    public function getAuthToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            // Make login request to get token
            $response = Http::post(config('app.url') . '/api/v1/auth/login', [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'device_name' => 'API Playground',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->successResponse('Authentication successful', [
                    'token' => $data['data']['token'],
                    'user' => $data['data']['user'],
                    'expires_at' => $data['data']['expires_at'] ?? null,
                ]);
            }

            return $this->errorResponse('Authentication failed', [
                'message' => $response->json()['message'] ?? 'Invalid credentials',
            ], 401);

        } catch (\Exception $e) {
            Log::error('Playground authentication failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
            ]);

            return $this->errorResponse('Authentication failed', [
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get playground statistics
     */
    public function getStatistics()
    {
        try {
            // This could be enhanced with actual database queries
            $stats = [
                'total_requests' => rand(1000, 5000),
                'successful_requests' => rand(900, 4500),
                'average_response_time' => rand(200, 800) . 'ms',
                'popular_endpoints' => [
                    '/auth/login' => rand(100, 500),
                    '/products' => rand(80, 400),
                    '/orders' => rand(60, 300),
                    '/customers' => rand(40, 200),
                ],
                'popular_languages' => [
                    'curl' => rand(30, 60),
                    'javascript' => rand(25, 55),
                    'dart' => rand(20, 50),
                    'php' => rand(15, 45),
                    'python' => rand(10, 40),
                ],
                'last_updated' => now()->toISOString(),
            ];

            return $this->successResponse('Statistics retrieved successfully', $stats);

        } catch (\Exception $e) {
            Log::error('Failed to get playground statistics', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Failed to get statistics', [
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate API endpoint
     */
    public function validateEndpoint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string',
            'method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $endpoint = $request->input('endpoint');
            $method = strtoupper($request->input('method'));

            // Check if endpoint exists in routes
            $routes = collect(Route::getRoutes())->filter(function ($route) use ($endpoint, $method) {
                $uri = $route->uri();
                $methods = $route->methods();
                
                return str_starts_with($uri, 'api/v1/') && 
                       in_array($method, $methods) &&
                       $this->matchesPattern($uri, $endpoint);
            });

            $isValid = $routes->isNotEmpty();
            $suggestions = [];

            if (!$isValid) {
                // Find similar endpoints
                $allApiRoutes = collect(Route::getRoutes())->filter(function ($route) {
                    return str_starts_with($route->uri(), 'api/v1/');
                })->map(function ($route) {
                    return [
                        'uri' => str_replace('api/v1/', '', $route->uri()),
                        'methods' => $route->methods(),
                    ];
                });

                $suggestions = $allApiRoutes->take(5)->toArray();
            }

            return $this->successResponse('Endpoint validation completed', [
                'endpoint' => $endpoint,
                'method' => $method,
                'is_valid' => $isValid,
                'suggestions' => $suggestions,
            ]);

        } catch (\Exception $e) {
            Log::error('Endpoint validation failed', [
                'error' => $e->getMessage(),
                'endpoint' => $request->input('endpoint'),
                'method' => $request->input('method'),
            ]);

            return $this->errorResponse('Endpoint validation failed', [
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if URI pattern matches endpoint
     */
    private function matchesPattern(string $pattern, string $endpoint): bool
    {
        $pattern = str_replace('api/v1/', '', $pattern);
        $pattern = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

        return preg_match($pattern, ltrim($endpoint, '/'));
    }

    /**
     * Return success response
     */
    protected function successResponse(string $message, $data = null, int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1',
                'request_id' => uniqid(),
            ]
        ], $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, $errors = null, int $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => 'v1',
                'request_id' => uniqid(),
            ]
        ], $statusCode);
    }
}
