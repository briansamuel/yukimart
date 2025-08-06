<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedCachingService
{
    protected $defaultTtl = 3600; // 1 hour
    protected $cachePrefix = 'yukimart:';
    protected $warmupBatchSize = 100;

    /**
     * Multi-level cache get with fallback
     */
    public function get(string $key, callable $callback = null, int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $fullKey = $this->cachePrefix . $key;

        // Level 1: Memory cache (if available)
        $memoryValue = $this->getFromMemory($fullKey);
        if ($memoryValue !== null) {
            return $memoryValue;
        }

        // Level 2: Redis cache
        $redisValue = $this->getFromRedis($fullKey);
        if ($redisValue !== null) {
            $this->setInMemory($fullKey, $redisValue, 300); // 5 min memory cache
            return $redisValue;
        }

        // Level 3: Database/callback
        if ($callback) {
            $value = $callback();
            $this->setMultiLevel($fullKey, $value, $ttl);
            return $value;
        }

        return null;
    }

    /**
     * Set value in multiple cache levels
     */
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $fullKey = $this->cachePrefix . $key;

        return $this->setMultiLevel($fullKey, $value, $ttl);
    }

    /**
     * Forget key from all cache levels
     */
    public function forget(string $key): bool
    {
        $fullKey = $this->cachePrefix . $key;

        $this->forgetFromMemory($fullKey);
        $this->forgetFromRedis($fullKey);
        
        return Cache::forget($fullKey);
    }

    /**
     * Cache warming for frequently accessed data
     */
    public function warmupCache(): array
    {
        $warmedUp = [];

        try {
            // Warm up products cache
            $warmedUp['products'] = $this->warmupProducts();
            
            // Warm up categories cache
            $warmedUp['categories'] = $this->warmupCategories();
            
            // Warm up customers cache
            $warmedUp['customers'] = $this->warmupCustomers();
            
            // Warm up inventory cache
            $warmedUp['inventory'] = $this->warmupInventory();

            Log::info('Cache warmup completed', $warmedUp);

        } catch (\Exception $e) {
            Log::error('Cache warmup failed: ' . $e->getMessage());
        }

        return $warmedUp;
    }

    /**
     * Intelligent cache invalidation
     */
    public function invalidateRelated(string $entity, int $entityId): array
    {
        $invalidated = [];

        switch ($entity) {
            case 'product':
                $invalidated = $this->invalidateProductCache($entityId);
                break;
                
            case 'order':
                $invalidated = $this->invalidateOrderCache($entityId);
                break;
                
            case 'customer':
                $invalidated = $this->invalidateCustomerCache($entityId);
                break;
                
            case 'inventory':
                $invalidated = $this->invalidateInventoryCache($entityId);
                break;
        }

        return $invalidated;
    }

    /**
     * Cache statistics and health
     */
    public function getStatistics(): array
    {
        return [
            'redis_stats' => $this->getRedisStats(),
            'memory_stats' => $this->getMemoryStats(),
            'hit_rates' => $this->getHitRates(),
            'cache_sizes' => $this->getCacheSizes(),
            'performance' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Preload cache for specific patterns
     */
    public function preloadPattern(string $pattern, callable $loader): int
    {
        $loaded = 0;
        $keys = $this->getKeysByPattern($pattern);

        foreach (array_chunk($keys, $this->warmupBatchSize) as $batch) {
            foreach ($batch as $key) {
                try {
                    $value = $loader($key);
                    if ($value !== null) {
                        $this->set($key, $value);
                        $loaded++;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to preload cache key: {$key}", ['error' => $e->getMessage()]);
                }
            }
        }

        return $loaded;
    }

    /**
     * Get from memory cache (APCu if available)
     */
    protected function getFromMemory(string $key): mixed
    {
        if (function_exists('apcu_fetch')) {
            $value = apcu_fetch($key, $success);
            return $success ? $value : null;
        }

        return null;
    }

    /**
     * Set in memory cache
     */
    protected function setInMemory(string $key, mixed $value, int $ttl): bool
    {
        if (function_exists('apcu_store')) {
            return apcu_store($key, $value, $ttl);
        }

        return false;
    }

    /**
     * Forget from memory cache
     */
    protected function forgetFromMemory(string $key): bool
    {
        if (function_exists('apcu_delete')) {
            return apcu_delete($key);
        }

        return false;
    }

    /**
     * Get from Redis cache
     */
    protected function getFromRedis(string $key): mixed
    {
        try {
            $value = Redis::get($key);
            return $value ? unserialize($value) : null;
        } catch (\Exception $e) {
            Log::warning('Redis get failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set in Redis cache
     */
    protected function setInRedis(string $key, mixed $value, int $ttl): bool
    {
        try {
            return Redis::setex($key, $ttl, serialize($value));
        } catch (\Exception $e) {
            Log::warning('Redis set failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Forget from Redis cache
     */
    protected function forgetFromRedis(string $key): bool
    {
        try {
            return Redis::del($key) > 0;
        } catch (\Exception $e) {
            Log::warning('Redis delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set value in multiple cache levels
     */
    protected function setMultiLevel(string $key, mixed $value, int $ttl): bool
    {
        $results = [];

        // Memory cache (short TTL)
        $results['memory'] = $this->setInMemory($key, $value, min($ttl, 300));
        
        // Redis cache
        $results['redis'] = $this->setInRedis($key, $value, $ttl);
        
        // Laravel cache (fallback)
        $results['laravel'] = Cache::put($key, $value, now()->addSeconds($ttl));

        return in_array(true, $results);
    }

    /**
     * Warm up products cache
     */
    protected function warmupProducts(): int
    {
        $count = 0;
        
        // Popular products
        $popularProducts = DB::table('products')
            ->where('product_status', 'publish')
            ->orderBy('view_count', 'desc')
            ->limit(100)
            ->get();

        foreach ($popularProducts as $product) {
            $this->set("product:{$product->id}", $product, 7200); // 2 hours
            $count++;
        }

        // Product categories
        $categories = DB::table('categories')->get();
        foreach ($categories as $category) {
            $categoryProducts = DB::table('products')
                ->where('category_id', $category->id)
                ->where('product_status', 'publish')
                ->get();
            
            $this->set("category_products:{$category->id}", $categoryProducts, 3600);
            $count++;
        }

        return $count;
    }

    /**
     * Warm up categories cache
     */
    protected function warmupCategories(): int
    {
        $categories = DB::table('categories')->get();
        $this->set('all_categories', $categories, 7200);
        
        return 1;
    }

    /**
     * Warm up customers cache
     */
    protected function warmupCustomers(): int
    {
        $count = 0;
        
        // Active customers
        $activeCustomers = DB::table('customers')
            ->where('customer_status', 'active')
            ->where('updated_at', '>=', now()->subDays(30))
            ->limit(500)
            ->get();

        foreach ($activeCustomers as $customer) {
            $this->set("customer:{$customer->id}", $customer, 1800); // 30 minutes
            $count++;
        }

        return $count;
    }

    /**
     * Warm up inventory cache
     */
    protected function warmupInventory(): int
    {
        $count = 0;
        
        $inventory = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('products.product_status', 'publish')
            ->select('inventories.*')
            ->get();

        foreach ($inventory as $item) {
            $this->set("inventory:product:{$item->product_id}", $item, 1800);
            $count++;
        }

        return $count;
    }

    /**
     * Invalidate product-related cache
     */
    protected function invalidateProductCache(int $productId): array
    {
        $invalidated = [];
        
        $keys = [
            "product:{$productId}",
            "inventory:product:{$productId}",
            "product_variants:{$productId}",
            "product_reviews:{$productId}"
        ];

        foreach ($keys as $key) {
            if ($this->forget($key)) {
                $invalidated[] = $key;
            }
        }

        // Invalidate category cache if needed
        $product = DB::table('products')->find($productId);
        if ($product && $product->category_id) {
            $categoryKey = "category_products:{$product->category_id}";
            if ($this->forget($categoryKey)) {
                $invalidated[] = $categoryKey;
            }
        }

        return $invalidated;
    }

    /**
     * Invalidate order-related cache
     */
    protected function invalidateOrderCache(int $orderId): array
    {
        $invalidated = [];
        
        $keys = [
            "order:{$orderId}",
            "order_items:{$orderId}",
            "customer_orders:*" // Pattern for customer orders
        ];

        foreach ($keys as $key) {
            if (str_contains($key, '*')) {
                // Handle pattern invalidation
                $patternKeys = $this->getKeysByPattern($key);
                foreach ($patternKeys as $patternKey) {
                    if ($this->forget($patternKey)) {
                        $invalidated[] = $patternKey;
                    }
                }
            } else {
                if ($this->forget($key)) {
                    $invalidated[] = $key;
                }
            }
        }

        return $invalidated;
    }

    /**
     * Invalidate customer-related cache
     */
    protected function invalidateCustomerCache(int $customerId): array
    {
        $invalidated = [];
        
        $keys = [
            "customer:{$customerId}",
            "customer_orders:{$customerId}",
            "customer_stats:{$customerId}"
        ];

        foreach ($keys as $key) {
            if ($this->forget($key)) {
                $invalidated[] = $key;
            }
        }

        return $invalidated;
    }

    /**
     * Invalidate inventory-related cache
     */
    protected function invalidateInventoryCache(int $productId): array
    {
        $invalidated = [];
        
        $keys = [
            "inventory:product:{$productId}",
            "stock_levels:product:{$productId}",
            "low_stock_products"
        ];

        foreach ($keys as $key) {
            if ($this->forget($key)) {
                $invalidated[] = $key;
            }
        }

        return $invalidated;
    }

    /**
     * Get Redis statistics
     */
    protected function getRedisStats(): array
    {
        try {
            $info = Redis::info();
            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0
            ];
        } catch (\Exception $e) {
            return ['error' => 'Redis not available'];
        }
    }

    /**
     * Get memory cache statistics
     */
    protected function getMemoryStats(): array
    {
        if (function_exists('apcu_cache_info')) {
            $info = apcu_cache_info();
            return [
                'num_slots' => $info['num_slots'] ?? 0,
                'num_hits' => $info['num_hits'] ?? 0,
                'num_misses' => $info['num_misses'] ?? 0,
                'memory_size' => $info['memory_size'] ?? 0
            ];
        }

        return ['error' => 'APCu not available'];
    }

    /**
     * Calculate hit rates
     */
    protected function getHitRates(): array
    {
        $redisStats = $this->getRedisStats();
        $memoryStats = $this->getMemoryStats();

        $rates = [];

        // Redis hit rate
        if (isset($redisStats['keyspace_hits']) && isset($redisStats['keyspace_misses'])) {
            $total = $redisStats['keyspace_hits'] + $redisStats['keyspace_misses'];
            $rates['redis'] = $total > 0 ? round(($redisStats['keyspace_hits'] / $total) * 100, 2) : 0;
        }

        // Memory hit rate
        if (isset($memoryStats['num_hits']) && isset($memoryStats['num_misses'])) {
            $total = $memoryStats['num_hits'] + $memoryStats['num_misses'];
            $rates['memory'] = $total > 0 ? round(($memoryStats['num_hits'] / $total) * 100, 2) : 0;
        }

        return $rates;
    }

    /**
     * Get cache sizes
     */
    protected function getCacheSizes(): array
    {
        return [
            'redis_keys' => $this->getRedisKeyCount(),
            'memory_keys' => $this->getMemoryKeyCount(),
            'laravel_cache_size' => $this->getLaravelCacheSize()
        ];
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        $start = microtime(true);
        
        // Test cache performance
        $testKey = 'performance_test_' . time();
        $testValue = ['test' => 'data', 'timestamp' => time()];
        
        $this->set($testKey, $testValue, 60);
        $retrieved = $this->get($testKey);
        $this->forget($testKey);
        
        $duration = (microtime(true) - $start) * 1000;

        return [
            'write_read_delete_time' => round($duration, 2),
            'data_integrity' => $retrieved === $testValue
        ];
    }

    /**
     * Get keys by pattern
     */
    protected function getKeysByPattern(string $pattern): array
    {
        try {
            return Redis::keys($this->cachePrefix . $pattern);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Redis key count
     */
    protected function getRedisKeyCount(): int
    {
        try {
            return count(Redis::keys($this->cachePrefix . '*'));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get memory key count
     */
    protected function getMemoryKeyCount(): int
    {
        if (function_exists('apcu_cache_info')) {
            $info = apcu_cache_info();
            return $info['num_entries'] ?? 0;
        }
        return 0;
    }

    /**
     * Get Laravel cache size (approximate)
     */
    protected function getLaravelCacheSize(): string
    {
        // This is an approximation - actual implementation would depend on cache driver
        return 'N/A';
    }
}
