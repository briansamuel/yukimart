<?php

// Debug script to check routes
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== Order Routes Debug ===\n\n";

// Get all routes
$routes = Route::getRoutes();
$orderRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'admin/order') !== false) {
        $orderRoutes[] = [
            'uri' => $uri,
            'name' => $route->getName(),
            'methods' => implode('|', $route->methods()),
            'action' => $route->getActionName()
        ];
    }
}

// Sort by URI
usort($orderRoutes, function($a, $b) {
    return strcmp($a['uri'], $b['uri']);
});

echo "Found " . count($orderRoutes) . " order routes:\n\n";

foreach ($orderRoutes as $route) {
    echo sprintf("%-8s %-35s %-30s %s\n", 
        $route['methods'], 
        $route['uri'], 
        $route['name'] ?: 'unnamed',
        $route['action']
    );
}

echo "\n=== Checking specific routes ===\n\n";

$testRoutes = [
    'admin.order.customers',
    'admin.order.products',
    'admin.order.show'
];

foreach ($testRoutes as $routeName) {
    try {
        $route = Route::getRoutes()->getByName($routeName);
        if ($route) {
            echo "✅ {$routeName}: {$route->uri()}\n";
        } else {
            echo "❌ {$routeName}: Not found\n";
        }
    } catch (Exception $e) {
        echo "❌ {$routeName}: Error - " . $e->getMessage() . "\n";
    }
}

echo "\n=== Potential conflicts ===\n\n";

// Check for conflicts between /admin/order/{id} and specific routes
$parameterizedRoutes = [];
$specificRoutes = [];

foreach ($orderRoutes as $route) {
    if (strpos($route['uri'], '{') !== false) {
        $parameterizedRoutes[] = $route;
    } else {
        $specificRoutes[] = $route;
    }
}

echo "Parameterized routes:\n";
foreach ($parameterizedRoutes as $route) {
    echo "  {$route['uri']} → {$route['name']}\n";
}

echo "\nSpecific routes:\n";
foreach ($specificRoutes as $route) {
    echo "  {$route['uri']} → {$route['name']}\n";
}

// Check if any specific route could be matched by a parameterized route
echo "\nConflict analysis:\n";
foreach ($parameterizedRoutes as $paramRoute) {
    foreach ($specificRoutes as $specRoute) {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $paramRoute['uri']);
        if (preg_match("#^{$pattern}$#", $specRoute['uri'])) {
            echo "⚠️  CONFLICT: {$paramRoute['uri']} could match {$specRoute['uri']}\n";
        }
    }
}

echo "\nDone.\n";
