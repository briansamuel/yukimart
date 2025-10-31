# YukiMart API Optimization - Quick Reference Guide

## 🚀 **Quick Start for Developers**

### **Using ApiOptimizationTrait**

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiOptimizationTrait;
use Illuminate\Http\Request;

class YourController extends Controller
{
    use ApiOptimizationTrait;
    
    public function index(Request $request)
    {
        $startTime = microtime(true);
        
        // 1. Generate cache key
        $cacheKey = $this->generateCacheKey($request, 'your_prefix');
        
        // 2. Try cache first
        $cachedResponse = $this->cacheResponse($cacheKey, null, 10);
        if ($cachedResponse) {
            $response = response()->json($cachedResponse);
            return $this->addPerformanceHeaders($response, $startTime);
        }
        
        // 3. Process your data
        $query = YourModel::query();
        
        // 4. Apply filters (implement your own method)
        $this->applyFilters($query, $request);
        
        // 5. Optimize pagination
        $data = $this->optimizePagination($query, $request);
        
        // 6. Parse fields for selection
        $fields = $this->parseFields($request->get('fields'));
        
        // 7. Apply field selection if needed
        if (!empty($fields)) {
            $data = $this->applyFieldSelection($data, $fields);
        }
        
        // 8. Prepare response
        $responseData = [
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $data,
            'meta' => [
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]
        ];
        
        // 9. Cache the response
        $this->cacheResponse($cacheKey, $responseData, 10);
        
        // 10. Return optimized response
        $response = response()->json($responseData);
        $response = $this->addPerformanceHeaders($response, $startTime);
        $response = $this->addRateLimitHeaders($response, 120, 1);
        
        return $response;
    }
}
```

---

## 📋 **Available Methods**

### **Caching Methods**
```php
// Cache a response for specified minutes
$this->cacheResponse($key, $data, $minutes);

// Generate a unique cache key based on request
$this->generateCacheKey($request, $prefix);

// Example usage
$cacheKey = $this->generateCacheKey($request, 'products');
$cachedData = $this->cacheResponse($cacheKey, null, 15); // Try to get
$this->cacheResponse($cacheKey, $responseData, 15); // Store
```

### **Field Selection Methods**
```php
// Parse fields from request parameter
$fields = $this->parseFields($request->get('fields'));
// Input: "id,name,email" → Output: ['id', 'name', 'email']

// Apply field selection to data
$filteredData = $this->applyFieldSelection($data, $fields);
```

### **Performance Methods**
```php
// Add performance headers to response
$response = $this->addPerformanceHeaders($response, $startTime);
// Adds: X-Response-Time, X-Memory-Usage

// Add rate limiting headers
$response = $this->addRateLimitHeaders($response, $limit, $window);
// Adds: X-RateLimit-Limit, X-RateLimit-Remaining
```

### **Pagination Methods**
```php
// Optimize pagination with field selection
$paginatedData = $this->optimizePagination($query, $request);
// Automatically handles per_page, fields selection
```

### **Error Handling**
```php
// Standardized error response
return $this->errorResponse('Error message', 400, $errors);
// Returns consistent error format
```

---

## ⚡ **Caching Guidelines**

### **Cache Duration Recommendations**
```php
// Real-time data (30 seconds - 2 minutes)
Cache::remember($key, 30, $callback);     // Live notifications
Cache::remember($key, 120, $callback);    // Real-time stats

// Frequently accessed (5-15 minutes)
Cache::remember($key, 300, $callback);    // Product lists
Cache::remember($key, 600, $callback);    // User profiles
Cache::remember($key, 900, $callback);    // Order lists

// Stable data (15-60 minutes)
Cache::remember($key, 1800, $callback);   // Categories
Cache::remember($key, 3600, $callback);   // Menu trees
```

### **Cache Key Patterns**
```php
// Format: {prefix}_{hash}_{user_id}
"products_a1b2c3d4e5f6_123"
"orders_f6e5d4c3b2a1_456"
"dashboard_9z8y7x6w5v_789"
```

### **Cache Invalidation**
```php
// Clear specific cache
Cache::forget("user_profile_{$userId}");

// Clear multiple related caches
$cacheKeys = [
    "user_profile_{$userId}",
    "user_permissions_{$userId}",
    "user_statistics_{$userId}"
];

foreach ($cacheKeys as $key) {
    Cache::forget($key);
}
```

---

## 🔍 **Request Parameters**

### **Standard Parameters**
```php
// Pagination
?per_page=20          // Items per page (max 100)
?page=1               // Page number

// Field Selection
?fields=id,name,email // Select specific fields

// Includes (relationships)
?include=user,category // Load relationships

// Sorting
?sort_by=created_at   // Sort field
?sort_order=desc      // Sort direction

// Search
?search=keyword       // Search term
?search_fields=name,description // Search fields

// Filtering
?status=active        // Filter by status
?created_from=2024-01-01 // Date range start
?created_to=2024-12-31   // Date range end
```

### **Example API Calls**
```bash
# Get products with field selection
GET /api/v1/products?fields=id,product_name,sale_price&per_page=20

# Get orders with relationships
GET /api/v1/orders?include=customer,items&sort_by=created_at&sort_order=desc

# Search customers
GET /api/v1/customers?search=john&search_fields=full_name,email,phone

# Get dashboard with caching
GET /api/v1/dashboard/overview
```

---

## 📊 **Response Format**

### **Success Response**
```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": {
    // Your data here
  },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "execution_time": "45.67ms",
    "cached": true
  }
}
```

### **Error Response**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### **Response Headers**
```
X-Response-Time: 45.67ms
X-Memory-Usage: 12.34MB
X-Cache-Status: HIT
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 119
```

---

## 🛠️ **Common Patterns**

### **Filter Implementation**
```php
private function applyFilters($query, $request)
{
    // Status filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    // Date range filter
    if ($request->filled('created_from')) {
        $query->whereDate('created_at', '>=', $request->created_from);
    }
    
    if ($request->filled('created_to')) {
        $query->whereDate('created_at', '<=', $request->created_to);
    }
    
    // Search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
```

### **Statistics with Caching**
```php
public function statistics()
{
    $startTime = microtime(true);
    
    $cacheKey = 'your_statistics';
    
    $stats = Cache::remember($cacheKey, 900, function() {
        return [
            'total_count' => YourModel::count(),
            'active_count' => YourModel::where('status', 'active')->count(),
            'recent_count' => YourModel::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    });
    
    $responseData = [
        'status' => 'success',
        'message' => 'Statistics retrieved successfully',
        'data' => $stats,
        'meta' => [
            'cached' => Cache::has($cacheKey),
            'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
        ]
    ];
    
    $response = response()->json($responseData);
    return $this->addPerformanceHeaders($response, $startTime);
}
```

---

## 🚦 **Rate Limiting**

### **Recommended Limits**
```php
// Authentication endpoints
$this->addRateLimitHeaders($response, 10, 1);  // 10 per minute

// General API endpoints
$this->addRateLimitHeaders($response, 120, 1); // 120 per minute

// Heavy operations
$this->addRateLimitHeaders($response, 30, 1);  // 30 per minute
```

---

## 🔧 **Performance Tips**

### **Database Optimization**
```php
// Use eager loading to prevent N+1 queries
$products = Product::with('category:id,name')->get();

// Select only needed fields
$users = User::select('id', 'name', 'email')->get();

// Use efficient pagination
$products = Product::paginate(20);
```

### **Memory Optimization**
```php
// Use chunk for large datasets
Product::chunk(100, function($products) {
    foreach ($products as $product) {
        // Process product
    }
});

// Clear variables when done
unset($largeArray);
```

### **Cache Optimization**
```php
// Use cache tags for group invalidation (if using Redis)
Cache::tags(['products', 'categories'])->put($key, $data, 600);
Cache::tags(['products'])->flush(); // Clear all product caches
```

---

## 📝 **Testing Your Optimizations**

### **Performance Testing**
```php
// Measure execution time
$startTime = microtime(true);
// ... your code ...
$executionTime = round((microtime(true) - $startTime) * 1000, 2);
echo "Execution time: {$executionTime}ms";

// Measure memory usage
$memoryBefore = memory_get_usage(true);
// ... your code ...
$memoryAfter = memory_get_usage(true);
$memoryUsed = round(($memoryAfter - $memoryBefore) / 1024 / 1024, 2);
echo "Memory used: {$memoryUsed}MB";
```

### **Cache Testing**
```php
// Test cache effectiveness
$cacheKey = 'test_key';

// First call (should miss cache)
$startTime = microtime(true);
$data = Cache::remember($cacheKey, 600, function() {
    // Expensive operation
    return expensive_operation();
});
$firstCallTime = microtime(true) - $startTime;

// Second call (should hit cache)
$startTime = microtime(true);
$data = Cache::remember($cacheKey, 600, function() {
    return expensive_operation();
});
$secondCallTime = microtime(true) - $startTime;

echo "First call: " . round($firstCallTime * 1000, 2) . "ms\n";
echo "Second call: " . round($secondCallTime * 1000, 2) . "ms\n";
echo "Cache effectiveness: " . round((1 - $secondCallTime / $firstCallTime) * 100, 2) . "%\n";
```

---

## 🎯 **Best Practices**

1. **Always use ApiOptimizationTrait** for new controllers
2. **Cache responses** based on data volatility
3. **Use field selection** for large datasets
4. **Implement rate limiting** for all endpoints
5. **Add performance headers** for monitoring
6. **Handle errors consistently** using errorResponse()
7. **Test cache effectiveness** during development
8. **Monitor performance** in production

---

**🚀 Happy optimizing! Your APIs will be blazing fast! 🚀**
