<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DatabaseOptimizationService
{
    protected $cachePrefix = 'db_optimization:';
    protected $slowQueryThreshold = 1000; // milliseconds

    /**
     * Analyze database performance and suggest optimizations
     */
    public function analyzePerformance(): array
    {
        $analysis = [
            'slow_queries' => $this->findSlowQueries(),
            'missing_indexes' => $this->findMissingIndexes(),
            'unused_indexes' => $this->findUnusedIndexes(),
            'table_sizes' => $this->getTableSizes(),
            'n_plus_one_risks' => $this->findNPlusOneRisks(),
            'recommendations' => []
        ];

        $analysis['recommendations'] = $this->generateRecommendations($analysis);

        return $analysis;
    }

    /**
     * Find slow queries from performance schema
     */
    protected function findSlowQueries(): array
    {
        try {
            // Enable query logging temporarily
            DB::statement('SET GLOBAL slow_query_log = "ON"');
            DB::statement('SET GLOBAL long_query_time = 1');

            // Get slow queries from performance schema (MySQL 5.6+)
            $slowQueries = DB::select("
                SELECT 
                    DIGEST_TEXT as query,
                    COUNT_STAR as exec_count,
                    AVG_TIMER_WAIT/1000000000 as avg_time_seconds,
                    MAX_TIMER_WAIT/1000000000 as max_time_seconds,
                    SUM_ROWS_EXAMINED as total_rows_examined,
                    SUM_ROWS_SENT as total_rows_sent
                FROM performance_schema.events_statements_summary_by_digest 
                WHERE DIGEST_TEXT IS NOT NULL 
                    AND AVG_TIMER_WAIT/1000000000 > 1
                ORDER BY AVG_TIMER_WAIT DESC 
                LIMIT 10
            ");

            return array_map(function($query) {
                return [
                    'query' => $this->sanitizeQuery($query->query),
                    'execution_count' => $query->exec_count,
                    'avg_time' => round($query->avg_time_seconds, 3),
                    'max_time' => round($query->max_time_seconds, 3),
                    'rows_examined' => $query->total_rows_examined,
                    'rows_sent' => $query->total_rows_sent,
                    'efficiency' => $query->total_rows_sent > 0 ? 
                        round($query->total_rows_sent / $query->total_rows_examined * 100, 2) : 0
                ];
            }, $slowQueries);

        } catch (\Exception $e) {
            Log::warning('Could not analyze slow queries: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find missing indexes based on common query patterns
     */
    protected function findMissingIndexes(): array
    {
        $suggestions = [];

        // Common patterns that need indexes
        $indexSuggestions = [
            'products' => [
                ['columns' => ['product_status'], 'reason' => 'Frequent filtering by status'],
                ['columns' => ['sku'], 'reason' => 'Unique lookups by SKU'],
                ['columns' => ['barcode'], 'reason' => 'Barcode scanning lookups'],
                ['columns' => ['category_id', 'product_status'], 'reason' => 'Category filtering with status'],
                ['columns' => ['created_at'], 'reason' => 'Date-based sorting and filtering'],
            ],
            'orders' => [
                ['columns' => ['order_status'], 'reason' => 'Status filtering'],
                ['columns' => ['customer_id'], 'reason' => 'Customer order history'],
                ['columns' => ['created_at'], 'reason' => 'Date-based queries'],
                ['columns' => ['branch_shop_id', 'order_status'], 'reason' => 'Branch-specific status filtering'],
            ],
            'inventories' => [
                ['columns' => ['product_id'], 'reason' => 'Product inventory lookups'],
                ['columns' => ['warehouse_id'], 'reason' => 'Warehouse-based queries'],
                ['columns' => ['product_id', 'warehouse_id'], 'reason' => 'Composite product-warehouse lookups'],
            ],
            'inventory_transactions' => [
                ['columns' => ['product_id'], 'reason' => 'Product transaction history'],
                ['columns' => ['transaction_type'], 'reason' => 'Transaction type filtering'],
                ['columns' => ['created_at'], 'reason' => 'Date-based transaction queries'],
                ['columns' => ['product_id', 'created_at'], 'reason' => 'Product transaction timeline'],
            ],
            'customers' => [
                ['columns' => ['phone'], 'reason' => 'Phone number lookups'],
                ['columns' => ['email'], 'reason' => 'Email lookups'],
                ['columns' => ['customer_status'], 'reason' => 'Status filtering'],
            ]
        ];

        foreach ($indexSuggestions as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $indexInfo) {
                $indexName = $table . '_' . implode('_', $indexInfo['columns']) . '_index';
                
                if (!$this->indexExists($table, $indexName)) {
                    $suggestions[] = [
                        'table' => $table,
                        'columns' => $indexInfo['columns'],
                        'index_name' => $indexName,
                        'reason' => $indexInfo['reason'],
                        'sql' => "CREATE INDEX {$indexName} ON {$table} (" . implode(', ', $indexInfo['columns']) . ")"
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Find unused indexes
     */
    protected function findUnusedIndexes(): array
    {
        try {
            $unusedIndexes = DB::select("
                SELECT 
                    t.TABLE_SCHEMA as database_name,
                    t.TABLE_NAME as table_name,
                    s.INDEX_NAME as index_name,
                    s.COLUMN_NAME as column_name
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.STATISTICS s ON t.TABLE_NAME = s.TABLE_NAME
                LEFT JOIN performance_schema.table_io_waits_summary_by_index_usage p 
                    ON s.TABLE_NAME = p.OBJECT_NAME AND s.INDEX_NAME = p.INDEX_NAME
                WHERE t.TABLE_SCHEMA = DATABASE()
                    AND s.INDEX_NAME IS NOT NULL
                    AND s.INDEX_NAME != 'PRIMARY'
                    AND (p.COUNT_STAR IS NULL OR p.COUNT_STAR = 0)
                ORDER BY t.TABLE_NAME, s.INDEX_NAME
            ");

            return array_map(function($index) {
                return [
                    'table' => $index->table_name,
                    'index_name' => $index->index_name,
                    'column' => $index->column_name,
                    'recommendation' => 'Consider dropping if truly unused'
                ];
            }, $unusedIndexes);

        } catch (\Exception $e) {
            Log::warning('Could not analyze unused indexes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get table sizes and row counts
     */
    protected function getTableSizes(): array
    {
        try {
            $tableSizes = DB::select("
                SELECT 
                    TABLE_NAME as table_name,
                    TABLE_ROWS as row_count,
                    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) as size_mb,
                    ROUND((DATA_LENGTH / 1024 / 1024), 2) as data_mb,
                    ROUND((INDEX_LENGTH / 1024 / 1024), 2) as index_mb
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_TYPE = 'BASE TABLE'
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
            ");

            return array_map(function($table) {
                return [
                    'table' => $table->table_name,
                    'rows' => number_format($table->row_count),
                    'total_size_mb' => $table->size_mb,
                    'data_size_mb' => $table->data_mb,
                    'index_size_mb' => $table->index_mb,
                    'index_ratio' => $table->data_mb > 0 ? 
                        round($table->index_mb / $table->data_mb * 100, 1) : 0
                ];
            }, $tableSizes);

        } catch (\Exception $e) {
            Log::warning('Could not analyze table sizes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find potential N+1 query risks
     */
    protected function findNPlusOneRisks(): array
    {
        $risks = [];

        // Common N+1 patterns in the codebase
        $patterns = [
            [
                'model' => 'Order',
                'relationship' => 'orderItems.product',
                'risk' => 'Loading order items without eager loading products',
                'solution' => "Order::with('orderItems.product')->get()"
            ],
            [
                'model' => 'Product',
                'relationship' => 'inventory',
                'risk' => 'Checking stock for multiple products',
                'solution' => "Product::with('inventory')->get()"
            ],
            [
                'model' => 'Customer',
                'relationship' => 'orders',
                'risk' => 'Loading customer order history',
                'solution' => "Customer::with('orders')->get()"
            ],
            [
                'model' => 'Invoice',
                'relationship' => 'invoiceItems.product',
                'risk' => 'Loading invoice items without products',
                'solution' => "Invoice::with('invoiceItems.product')->get()"
            ]
        ];

        foreach ($patterns as $pattern) {
            $risks[] = [
                'model' => $pattern['model'],
                'relationship' => $pattern['relationship'],
                'description' => $pattern['risk'],
                'solution' => $pattern['solution'],
                'severity' => 'medium'
            ];
        }

        return $risks;
    }

    /**
     * Generate optimization recommendations
     */
    protected function generateRecommendations(array $analysis): array
    {
        $recommendations = [];

        // Slow query recommendations
        if (!empty($analysis['slow_queries'])) {
            $recommendations[] = [
                'type' => 'slow_queries',
                'priority' => 'high',
                'title' => 'Optimize Slow Queries',
                'description' => count($analysis['slow_queries']) . ' slow queries detected',
                'action' => 'Review and optimize queries taking > 1 second'
            ];
        }

        // Missing index recommendations
        if (!empty($analysis['missing_indexes'])) {
            $recommendations[] = [
                'type' => 'missing_indexes',
                'priority' => 'high',
                'title' => 'Add Missing Indexes',
                'description' => count($analysis['missing_indexes']) . ' suggested indexes',
                'action' => 'Create indexes for frequently queried columns'
            ];
        }

        // Large table recommendations
        $largeTables = array_filter($analysis['table_sizes'], function($table) {
            return $table['total_size_mb'] > 100; // Tables > 100MB
        });

        if (!empty($largeTables)) {
            $recommendations[] = [
                'type' => 'large_tables',
                'priority' => 'medium',
                'title' => 'Monitor Large Tables',
                'description' => count($largeTables) . ' tables > 100MB',
                'action' => 'Consider archiving old data or partitioning'
            ];
        }

        // N+1 query recommendations
        if (!empty($analysis['n_plus_one_risks'])) {
            $recommendations[] = [
                'type' => 'n_plus_one',
                'priority' => 'medium',
                'title' => 'Prevent N+1 Queries',
                'description' => count($analysis['n_plus_one_risks']) . ' potential N+1 patterns',
                'action' => 'Use eager loading for relationships'
            ];
        }

        return $recommendations;
    }

    /**
     * Apply automatic optimizations
     */
    public function applyOptimizations(array $optimizations = []): array
    {
        $applied = [];

        foreach ($optimizations as $optimization) {
            try {
                switch ($optimization['type']) {
                    case 'create_index':
                        DB::statement($optimization['sql']);
                        $applied[] = "Created index: {$optimization['index_name']}";
                        break;

                    case 'analyze_table':
                        DB::statement("ANALYZE TABLE {$optimization['table']}");
                        $applied[] = "Analyzed table: {$optimization['table']}";
                        break;

                    case 'optimize_table':
                        DB::statement("OPTIMIZE TABLE {$optimization['table']}");
                        $applied[] = "Optimized table: {$optimization['table']}";
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to apply optimization: " . $e->getMessage());
            }
        }

        return $applied;
    }

    /**
     * Check if index exists
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sanitize query for display
     */
    protected function sanitizeQuery(string $query): string
    {
        // Remove sensitive data and normalize
        $query = preg_replace('/\b\d+\b/', '?', $query);
        $query = preg_replace('/\'[^\']*\'/', '?', $query);
        $query = preg_replace('/\"[^\"]*\"/', '?', $query);
        
        return trim($query);
    }

    /**
     * Get query execution plan
     */
    public function explainQuery(string $query): array
    {
        try {
            $plan = DB::select("EXPLAIN FORMAT=JSON " . $query);
            return json_decode($plan[0]->EXPLAIN, true);
        } catch (\Exception $e) {
            Log::error("Failed to explain query: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Monitor query performance in real-time
     */
    public function startQueryMonitoring(): void
    {
        DB::listen(function ($query) {
            $time = $query->time;
            
            if ($time > $this->slowQueryThreshold) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $time . 'ms'
                ]);

                // Cache slow query for analysis
                $cacheKey = $this->cachePrefix . 'slow_query:' . md5($query->sql);
                $existing = Cache::get($cacheKey, ['count' => 0, 'total_time' => 0]);
                $existing['count']++;
                $existing['total_time'] += $time;
                $existing['avg_time'] = $existing['total_time'] / $existing['count'];
                $existing['last_seen'] = now()->toISOString();
                
                Cache::put($cacheKey, $existing, now()->addHours(24));
            }
        });
    }
}
