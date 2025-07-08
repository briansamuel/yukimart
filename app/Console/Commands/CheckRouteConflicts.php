<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class CheckRouteConflicts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:check-conflicts {--prefix=admin : Route prefix to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for potential route conflicts in admin routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prefix = $this->option('prefix');
        $this->info("🔍 Checking route conflicts for prefix: {$prefix}");
        $this->newLine();

        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(function ($route) use ($prefix) {
                return str_starts_with($route->uri(), $prefix . '/');
            });

        $conflicts = $this->detectConflicts($routes);

        if ($conflicts->isEmpty()) {
            $this->info('✅ No route conflicts detected!');
            return 0;
        }

        $this->error('🚨 Route conflicts detected:');
        $this->newLine();

        foreach ($conflicts as $conflict) {
            $this->warn("Conflict Group:");
            foreach ($conflict as $route) {
                $this->line("  - {$route['method']} {$route['uri']} → {$route['action']}");
            }
            $this->newLine();
        }

        $this->info('💡 Recommendations:');
        $this->line('1. Move specific routes (without parameters) before parameterized routes');
        $this->line('2. Use route groups to organize related routes');
        $this->line('3. Consider using different prefixes for different functionalities');
        $this->newLine();

        return 1;
    }

    /**
     * Detect route conflicts
     */
    private function detectConflicts($routes)
    {
        $conflicts = collect();
        $routesByMethod = $routes->groupBy(function ($route) {
            return implode('|', $route->methods());
        });

        foreach ($routesByMethod as $method => $methodRoutes) {
            $uriGroups = $this->groupSimilarUris($methodRoutes);
            
            foreach ($uriGroups as $group) {
                if (count($group) > 1) {
                    $conflicts->push($group);
                }
            }
        }

        return $conflicts;
    }

    /**
     * Group URIs that could conflict
     */
    private function groupSimilarUris($routes)
    {
        $groups = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            $pattern = $this->getUriPattern($uri);
            
            if (!isset($groups[$pattern])) {
                $groups[$pattern] = [];
            }

            $groups[$pattern][] = [
                'method' => implode('|', $route->methods()),
                'uri' => $uri,
                'action' => $route->getActionName(),
                'name' => $route->getName()
            ];
        }

        // Only return groups with potential conflicts
        return array_filter($groups, function ($group) {
            return $this->hasConflict($group);
        });
    }

    /**
     * Convert URI to pattern for comparison
     */
    private function getUriPattern($uri)
    {
        // Replace parameters with wildcards
        $pattern = preg_replace('/\{[^}]+\}/', '*', $uri);
        
        // Split into segments
        $segments = explode('/', $pattern);
        
        return implode('/', $segments);
    }

    /**
     * Check if a group has conflicts
     */
    private function hasConflict($group)
    {
        if (count($group) < 2) {
            return false;
        }

        // Check for parameter vs literal conflicts
        $literals = [];
        $parameterized = [];

        foreach ($group as $route) {
            if (str_contains($route['uri'], '{')) {
                $parameterized[] = $route;
            } else {
                $literals[] = $route;
            }
        }

        // Conflict if we have both literal and parameterized routes
        // that could match the same pattern
        if (!empty($literals) && !empty($parameterized)) {
            foreach ($literals as $literal) {
                foreach ($parameterized as $param) {
                    if ($this->couldConflict($literal['uri'], $param['uri'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if two URIs could conflict
     */
    private function couldConflict($literal, $parameterized)
    {
        $literalSegments = explode('/', $literal);
        $paramSegments = explode('/', $parameterized);

        if (count($literalSegments) !== count($paramSegments)) {
            return false;
        }

        for ($i = 0; $i < count($literalSegments); $i++) {
            $literalSeg = $literalSegments[$i];
            $paramSeg = $paramSegments[$i];

            // If param segment is a parameter, it could match literal
            if (str_starts_with($paramSeg, '{') && str_ends_with($paramSeg, '}')) {
                continue;
            }

            // If both are literals, they must match exactly
            if ($literalSeg !== $paramSeg) {
                return false;
            }
        }

        return true;
    }
}
