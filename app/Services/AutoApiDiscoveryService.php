<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;

class AutoApiDiscoveryService
{
    private $baseUrl;
    private $exampleGenerator;
    private $excludedRoutes = [
        'api/v1/docs',
        'api/v1/playground',
        'api/v1/docs/openapi',
        'api/v1/docs/postman/sync'
    ];

    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/api/v1';
        $this->exampleGenerator = new ApiExampleGeneratorService();
    }

    /**
     * Discover all API routes with detailed information
     */
    public function discoverRoutes(): array
    {
        $routes = [];
        
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            
            // Filter API v1 routes only
            if (str_starts_with($uri, 'api/v1/') && !$this->isExcludedRoute($uri)) {
                $routeInfo = $this->analyzeRoute($route);
                if ($routeInfo) {
                    $routes[] = $routeInfo;
                }
            }
        }

        return $this->organizeRoutes($routes);
    }

    /**
     * Analyze individual route and extract detailed information
     */
    private function analyzeRoute($route): ?array
    {
        try {
            $action = $route->getActionName();
            $controllerInfo = $this->getControllerInfo($action);
            
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $action,
                'middleware' => $route->middleware(),
                'parameters' => $route->parameterNames(),
                'controller' => $controllerInfo,
                'documentation' => $this->extractDocumentation($controllerInfo),
                'examples' => $this->generateExamples($route),
                'group' => $this->determineGroup($route->uri()),
                'auth_required' => $this->requiresAuth($route->middleware()),
                'description' => $this->generateDescription($controllerInfo)
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to analyze route {$route->uri()}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract documentation from controller method
     */
    private function extractDocumentation(array $controllerInfo): array
    {
        if (!$controllerInfo['class'] || !class_exists($controllerInfo['class'])) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($controllerInfo['class']);
            $method = $reflection->getMethod($controllerInfo['method']);
            $docComment = $method->getDocComment();

            if (!$docComment) {
                return [];
            }

            return [
                'summary' => $this->extractSummary($docComment),
                'description' => $this->extractDescription($docComment),
                'parameters' => $this->extractParameters($docComment),
                'responses' => $this->extractResponses($docComment)
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate examples for route
     */
    private function generateExamples($route): array
    {
        $examples = [];
        $uri = $route->uri();
        $methods = $route->methods();

        // Try to get real examples from ApiExampleGeneratorService
        $realExamples = $this->getRealExamples($uri);

        foreach ($methods as $method) {
            if ($method === 'HEAD' || $method === 'OPTIONS') {
                continue;
            }

            $example = [
                'method' => $method,
                'url' => $this->baseUrl . '/' . str_replace('api/v1/', '', $uri),
                'headers' => $this->getDefaultHeaders($route),
                'description' => $this->generateExampleDescription($method, $uri)
            ];

            // Add body for POST/PUT/PATCH
            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $example['body'] = $this->generateExampleBody($uri, $method);
            }

            // Add query parameters for GET
            if ($method === 'GET') {
                $example['query_params'] = $this->generateQueryParams($uri);
            }

            // Add real response examples if available
            if (isset($realExamples[$method])) {
                $example['response_examples'] = $realExamples[$method];
            }

            $examples[] = $example;
        }

        return $examples;
    }

    /**
     * Get real examples from ApiExampleGeneratorService
     */
    private function getRealExamples(string $uri): array
    {
        try {
            // Cache examples for performance
            $cacheKey = 'api_examples_' . md5($uri);

            return Cache::remember($cacheKey, 3600, function () use ($uri) {
                // Get all examples and filter by URI
                $allExamples = $this->exampleGenerator->generateAllExamples();
                $group = $this->determineGroup($uri);

                return $allExamples[$group] ?? [];
            });
        } catch (\Exception $e) {
            Log::warning("Failed to get real examples for {$uri}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Organize routes by groups
     */
    private function organizeRoutes(array $routes): array
    {
        $organized = [];
        
        foreach ($routes as $route) {
            $group = $route['group'];
            if (!isset($organized[$group])) {
                $organized[$group] = [
                    'name' => ucfirst($group),
                    'description' => $this->getGroupDescription($group),
                    'routes' => []
                ];
            }
            $organized[$group]['routes'][] = $route;
        }

        return $organized;
    }

    /**
     * Determine route group from URI
     */
    private function determineGroup(string $uri): string
    {
        $parts = explode('/', $uri);
        
        // api/v1/{group}/...
        if (isset($parts[2])) {
            return $parts[2];
        }
        
        return 'general';
    }

    /**
     * Check if route requires authentication
     */
    private function requiresAuth(array $middleware): bool
    {
        return in_array('auth:sanctum', $middleware) || 
               in_array('auth', $middleware);
    }

    /**
     * Generate description for controller method
     */
    private function generateDescription(array $controllerInfo): string
    {
        $method = $controllerInfo['method'];
        $class = basename(str_replace('\\', '/', $controllerInfo['class']));
        
        $descriptions = [
            'index' => 'Get list of resources',
            'show' => 'Get specific resource details',
            'store' => 'Create new resource',
            'update' => 'Update existing resource',
            'destroy' => 'Delete resource',
            'getStats' => 'Get statistics data',
            'getRecentOrders' => 'Get recent orders',
            'getTopProducts' => 'Get top products',
            'login' => 'User authentication',
            'logout' => 'User logout',
            'me' => 'Get current user profile'
        ];

        return $descriptions[$method] ?? "Execute {$method} action in {$class}";
    }

    /**
     * Get default headers for route
     */
    private function getDefaultHeaders($route): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        if ($this->requiresAuth($route->middleware())) {
            $headers['Authorization'] = 'Bearer {{auth_token}}';
        }

        return $headers;
    }

    /**
     * Generate example body for POST/PUT/PATCH requests
     */
    private function generateExampleBody(string $uri, string $method): array
    {
        $examples = [
            'auth/login' => [
                'email' => 'yukimart@gmail.com',
                'password' => '123456'
            ],
            'auth/register' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ],
            'customers' => [
                'name' => 'Nguyễn Văn A',
                'phone' => '0987654321',
                'email' => 'customer@example.com',
                'address' => 'Hà Nội, Việt Nam'
            ],
            'products' => [
                'product_name' => 'Sản phẩm mới',
                'sku' => 'SP001',
                'sale_price' => 100000,
                'cost_price' => 80000
            ]
        ];

        foreach ($examples as $pattern => $body) {
            if (str_contains($uri, $pattern)) {
                return $body;
            }
        }

        return [];
    }

    /**
     * Generate query parameters for GET requests
     */
    private function generateQueryParams(string $uri): array
    {
        $params = [];

        // Common pagination params
        if (str_contains($uri, 'index') || str_ends_with($uri, 's')) {
            $params = [
                'page' => 1,
                'per_page' => 15,
                'search' => '',
                'sort_by' => 'created_at',
                'sort_direction' => 'desc'
            ];
        }

        // Dashboard specific params
        if (str_contains($uri, 'dashboard')) {
            $params['period'] = 'month';
            $params['limit'] = 10;
        }

        return $params;
    }

    /**
     * Get group description
     */
    private function getGroupDescription(string $group): string
    {
        $descriptions = [
            'auth' => 'Authentication and user management endpoints',
            'dashboard' => 'Dashboard statistics and analytics endpoints',
            'invoices' => 'Invoice management endpoints',
            'products' => 'Product catalog management endpoints',
            'orders' => 'Order processing endpoints',
            'customers' => 'Customer management endpoints',
            'payments' => 'Payment processing endpoints',
            'docs' => 'API documentation endpoints'
        ];

        return $descriptions[$group] ?? ucfirst($group) . ' related endpoints';
    }

    /**
     * Check if route should be excluded
     */
    private function isExcludedRoute(string $uri): bool
    {
        foreach ($this->excludedRoutes as $excluded) {
            if (str_starts_with($uri, $excluded)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get controller information from action
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

    /**
     * Extract summary from doc comment
     */
    private function extractSummary(string $docComment): string
    {
        if (preg_match('/\*\s*(.+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Extract description from doc comment
     */
    private function extractDescription(string $docComment): string
    {
        // Extract description after summary
        $lines = explode("\n", $docComment);
        $description = '';
        $inDescription = false;
        
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            if (empty($line)) {
                $inDescription = true;
                continue;
            }
            if ($inDescription && !str_starts_with($line, '@')) {
                $description .= $line . ' ';
            }
        }
        
        return trim($description);
    }

    /**
     * Extract parameters from doc comment
     */
    private function extractParameters(string $docComment): array
    {
        $parameters = [];
        if (preg_match_all('/@param\s+(\S+)\s+\$(\S+)\s*(.*)/', $docComment, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $parameters[] = [
                    'name' => $match[2],
                    'type' => $match[1],
                    'description' => trim($match[3] ?? '')
                ];
            }
        }
        return $parameters;
    }

    /**
     * Extract responses from doc comment
     */
    private function extractResponses(string $docComment): array
    {
        $responses = [];
        if (preg_match_all('/@return\s+(\S+)\s*(.*)/', $docComment, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $responses[] = [
                    'type' => $match[1],
                    'description' => trim($match[2] ?? '')
                ];
            }
        }
        return $responses;
    }

    /**
     * Generate example description
     */
    private function generateExampleDescription(string $method, string $uri): string
    {
        $action = strtolower($method);
        $resource = $this->determineGroup($uri);
        
        $templates = [
            'get' => "Retrieve {$resource} data",
            'post' => "Create new {$resource}",
            'put' => "Update {$resource}",
            'patch' => "Partially update {$resource}",
            'delete' => "Delete {$resource}"
        ];

        return $templates[$action] ?? "Execute {$action} on {$resource}";
    }
}
