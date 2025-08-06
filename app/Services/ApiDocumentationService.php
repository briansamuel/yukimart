<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class ApiDocumentationService
{
    protected $outputPath;
    protected $baseUrl;

    public function __construct()
    {
        $this->outputPath = storage_path('app/api-docs');
        $this->baseUrl = config('app.url');
        
        if (!File::exists($this->outputPath)) {
            File::makeDirectory($this->outputPath, 0755, true);
        }
    }

    /**
     * Generate complete API documentation
     */
    public function generateDocumentation(): array
    {
        $routes = $this->getApiRoutes();
        $documentation = $this->buildDocumentation($routes);
        
        // Generate different formats
        $this->generateMarkdown($documentation);
        $this->generateJson($documentation);
        $this->generateHtml($documentation);
        
        return [
            'routes_documented' => count($routes),
            'files_generated' => [
                'markdown' => $this->outputPath . '/api-documentation.md',
                'json' => $this->outputPath . '/api-documentation.json',
                'html' => $this->outputPath . '/api-documentation.html'
            ]
        ];
    }

    /**
     * Get all API routes
     */
    protected function getApiRoutes(): array
    {
        $routes = [];
        
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            
            // Filter API routes
            if (Str::startsWith($uri, 'api/') || Str::contains($uri, '/api/')) {
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
     * Get controller information
     */
    protected function getControllerInfo(string $action): array
    {
        if (Str::contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            
            try {
                $reflection = new ReflectionClass($controller);
                $methodReflection = $reflection->getMethod($method);
                
                return [
                    'class' => $controller,
                    'method' => $method,
                    'description' => $this->extractDocComment($methodReflection->getDocComment()),
                    'parameters' => $this->extractParameters($methodReflection),
                    'return_type' => $this->extractReturnType($methodReflection)
                ];
                
            } catch (\Exception $e) {
                return [
                    'class' => $controller,
                    'method' => $method,
                    'error' => 'Could not reflect controller'
                ];
            }
        }

        return ['action' => $action];
    }

    /**
     * Extract documentation from doc comment
     */
    protected function extractDocComment(?string $docComment): array
    {
        if (!$docComment) {
            return ['description' => 'No description available'];
        }

        $lines = explode("\n", $docComment);
        $description = '';
        $tags = [];

        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B/*");
            
            if (empty($line)) continue;
            
            if (Str::startsWith($line, '@')) {
                $parts = explode(' ', $line, 2);
                $tag = substr($parts[0], 1);
                $value = $parts[1] ?? '';
                $tags[$tag] = $value;
            } else {
                $description .= $line . ' ';
            }
        }

        return [
            'description' => trim($description),
            'tags' => $tags
        ];
    }

    /**
     * Extract method parameters
     */
    protected function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $param) {
            $parameters[] = [
                'name' => $param->getName(),
                'type' => $param->getType() ? $param->getType()->getName() : 'mixed',
                'required' => !$param->isOptional(),
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
            ];
        }

        return $parameters;
    }

    /**
     * Extract return type
     */
    protected function extractReturnType(ReflectionMethod $method): string
    {
        $returnType = $method->getReturnType();
        return $returnType ? $returnType->getName() : 'mixed';
    }

    /**
     * Build documentation structure
     */
    protected function buildDocumentation(array $routes): array
    {
        $grouped = $this->groupRoutesByController($routes);
        
        return [
            'info' => [
                'title' => 'YukiMart API Documentation',
                'version' => '1.0.0',
                'description' => 'Comprehensive API documentation for YukiMart system',
                'base_url' => $this->baseUrl,
                'generated_at' => now()->toISOString()
            ],
            'authentication' => [
                'type' => 'Bearer Token',
                'description' => 'Include Authorization header with Bearer token'
            ],
            'endpoints' => $grouped,
            'examples' => $this->generateExamples($routes)
        ];
    }

    /**
     * Group routes by controller
     */
    protected function groupRoutesByController(array $routes): array
    {
        $grouped = [];
        
        foreach ($routes as $route) {
            $controller = $route['controller']['class'] ?? 'Unknown';
            $controllerName = class_basename($controller);
            
            if (!isset($grouped[$controllerName])) {
                $grouped[$controllerName] = [
                    'controller' => $controller,
                    'routes' => []
                ];
            }
            
            $grouped[$controllerName]['routes'][] = $route;
        }

        return $grouped;
    }

    /**
     * Generate usage examples
     */
    protected function generateExamples(array $routes): array
    {
        $examples = [];
        
        foreach (array_slice($routes, 0, 5) as $route) {
            $examples[] = [
                'endpoint' => $route['uri'],
                'method' => $route['method'],
                'curl' => $this->generateCurlExample($route),
                'javascript' => $this->generateJavaScriptExample($route),
                'response' => $this->generateResponseExample($route)
            ];
        }

        return $examples;
    }

    /**
     * Generate cURL example
     */
    protected function generateCurlExample(array $route): string
    {
        $method = explode('|', $route['method'])[0];
        $url = $this->baseUrl . '/' . $route['uri'];
        
        $curl = "curl -X {$method} \\\n";
        $curl .= "  '{$url}' \\\n";
        $curl .= "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
        $curl .= "  -H 'Content-Type: application/json' \\\n";
        $curl .= "  -H 'Accept: application/json'";

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $curl .= " \\\n  -d '{\"key\": \"value\"}'";
        }

        return $curl;
    }

    /**
     * Generate JavaScript example
     */
    protected function generateJavaScriptExample(array $route): string
    {
        $method = strtolower(explode('|', $route['method'])[0]);
        $url = $this->baseUrl . '/' . $route['uri'];
        
        $js = "fetch('{$url}', {\n";
        $js .= "  method: '" . strtoupper($method) . "',\n";
        $js .= "  headers: {\n";
        $js .= "    'Authorization': 'Bearer YOUR_TOKEN',\n";
        $js .= "    'Content-Type': 'application/json',\n";
        $js .= "    'Accept': 'application/json'\n";
        $js .= "  }";

        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $js .= ",\n  body: JSON.stringify({\n    key: 'value'\n  })";
        }

        $js .= "\n})\n.then(response => response.json())\n.then(data => console.log(data));";

        return $js;
    }

    /**
     * Generate response example
     */
    protected function generateResponseExample(array $route): array
    {
        return [
            'success' => [
                'status' => 200,
                'body' => [
                    'success' => true,
                    'data' => ['example' => 'data'],
                    'message' => 'Operation successful'
                ]
            ],
            'error' => [
                'status' => 400,
                'body' => [
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => ['field' => ['Field is required']]
                ]
            ]
        ];
    }

    /**
     * Generate Markdown documentation
     */
    protected function generateMarkdown(array $documentation): void
    {
        $markdown = "# {$documentation['info']['title']}\n\n";
        $markdown .= "{$documentation['info']['description']}\n\n";
        $markdown .= "**Version:** {$documentation['info']['version']}\n";
        $markdown .= "**Base URL:** {$documentation['info']['base_url']}\n";
        $markdown .= "**Generated:** {$documentation['info']['generated_at']}\n\n";

        $markdown .= "## Authentication\n\n";
        $markdown .= "{$documentation['authentication']['description']}\n\n";
        $markdown .= "```\nAuthorization: Bearer YOUR_TOKEN\n```\n\n";

        $markdown .= "## Endpoints\n\n";
        
        foreach ($documentation['endpoints'] as $controllerName => $controller) {
            $markdown .= "### {$controllerName}\n\n";
            
            foreach ($controller['routes'] as $route) {
                $markdown .= "#### {$route['method']} /{$route['uri']}\n\n";
                
                if (isset($route['controller']['description'])) {
                    $markdown .= "{$route['controller']['description']['description']}\n\n";
                }
                
                $markdown .= "**Parameters:**\n";
                if (!empty($route['parameters'])) {
                    foreach ($route['parameters'] as $param) {
                        $markdown .= "- `{$param}` (path parameter)\n";
                    }
                } else {
                    $markdown .= "- None\n";
                }
                $markdown .= "\n";
            }
        }

        $markdown .= "## Examples\n\n";
        foreach ($documentation['examples'] as $example) {
            $markdown .= "### {$example['method']} /{$example['endpoint']}\n\n";
            $markdown .= "**cURL:**\n```bash\n{$example['curl']}\n```\n\n";
            $markdown .= "**JavaScript:**\n```javascript\n{$example['javascript']}\n```\n\n";
        }

        File::put($this->outputPath . '/api-documentation.md', $markdown);
    }

    /**
     * Generate JSON documentation
     */
    protected function generateJson(array $documentation): void
    {
        File::put(
            $this->outputPath . '/api-documentation.json',
            json_encode($documentation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Generate HTML documentation
     */
    protected function generateHtml(array $documentation): void
    {
        $html = $this->buildHtmlTemplate($documentation);
        File::put($this->outputPath . '/api-documentation.html', $html);
    }

    /**
     * Build HTML template
     */
    protected function buildHtmlTemplate(array $documentation): string
    {
        $title = $documentation['info']['title'];
        $description = $documentation['info']['description'];
        
        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<title>{$title}</title>\n";
        $html .= "<meta charset='utf-8'>\n";
        $html .= "<style>\n";
        $html .= $this->getHtmlStyles();
        $html .= "</style>\n</head>\n<body>\n";
        
        $html .= "<div class='container'>\n";
        $html .= "<h1>{$title}</h1>\n";
        $html .= "<p>{$description}</p>\n";
        
        $html .= "<div class='info'>\n";
        $html .= "<p><strong>Version:</strong> {$documentation['info']['version']}</p>\n";
        $html .= "<p><strong>Base URL:</strong> {$documentation['info']['base_url']}</p>\n";
        $html .= "</div>\n";
        
        $html .= "<h2>Authentication</h2>\n";
        $html .= "<p>{$documentation['authentication']['description']}</p>\n";
        $html .= "<pre><code>Authorization: Bearer YOUR_TOKEN</code></pre>\n";
        
        $html .= "<h2>Endpoints</h2>\n";
        foreach ($documentation['endpoints'] as $controllerName => $controller) {
            $html .= "<h3>{$controllerName}</h3>\n";
            foreach ($controller['routes'] as $route) {
                $method = explode('|', $route['method'])[0];
                $html .= "<div class='endpoint'>\n";
                $html .= "<h4><span class='method {$method}'>{$method}</span> /{$route['uri']}</h4>\n";
                $html .= "</div>\n";
            }
        }
        
        $html .= "</div>\n</body>\n</html>";
        
        return $html;
    }

    /**
     * Get HTML styles
     */
    protected function getHtmlStyles(): string
    {
        return "
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .container { max-width: 1200px; margin: 0 auto; }
            h1 { color: #333; border-bottom: 2px solid #009ef7; padding-bottom: 10px; }
            h2 { color: #555; margin-top: 30px; }
            h3 { color: #666; margin-top: 25px; }
            .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .endpoint { margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
            .method { padding: 3px 8px; border-radius: 3px; color: white; font-weight: bold; }
            .GET { background: #28a745; }
            .POST { background: #007bff; }
            .PUT { background: #ffc107; color: black; }
            .DELETE { background: #dc3545; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        ";
    }
}
