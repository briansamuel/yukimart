<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeProductService;
use App\Services\Shopee\ShopeeApiService;
use App\Models\ShopeeToken;
use App\Models\MarketplaceProductLink;
use Illuminate\Support\Facades\Log;

class SyncShopeeInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopee:sync-inventory 
                            {--shop-id= : Specific shop ID to sync}
                            {--product-id= : Specific product ID to sync}
                            {--limit=50 : Maximum number of products to sync}
                            {--force : Force sync even if disabled in config}
                            {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync inventory from local products to Shopee';

    protected $productService;
    protected $apiService;

    /**
     * Create a new command instance.
     */
    public function __construct(ShopeeProductService $productService, ShopeeApiService $apiService)
    {
        parent::__construct();
        $this->productService = $productService;
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        
        $this->info('Starting Shopee inventory sync...');

        // Check if sync is enabled
        if (!config('shopee.sync.enabled') && !$this->option('force')) {
            $this->warn('Shopee sync is disabled in configuration. Use --force to override.');
            return 1;
        }

        try {
            // Get tokens to sync
            $tokens = $this->getTokensToSync();

            if ($tokens->isEmpty()) {
                $this->warn('No valid Shopee tokens found for syncing.');
                return 1;
            }

            $totalResults = [
                'success' => 0,
                'failed' => 0,
                'errors' => [],
            ];

            // Sync inventory for each token
            foreach ($tokens as $token) {
                $this->info("Syncing inventory for shop: {$token->shop_name} (ID: {$token->shop_id})");
                
                if ($this->option('dry-run')) {
                    $this->dryRunSync($token);
                } else {
                    $results = $this->syncInventoryForToken($token);
                    $this->mergeResults($totalResults, $results);
                    $this->displayResults($results, $token->shop_name);
                }
            }

            if (!$this->option('dry-run')) {
                $this->displayTotalResults($totalResults);
                $this->logSyncCompletion($totalResults, $startTime);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Inventory sync failed: ' . $e->getMessage());
            
            Log::channel(config('shopee.logging.channel'))->error('Shopee inventory sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Get tokens to sync based on options
     */
    protected function getTokensToSync()
    {
        $query = ShopeeToken::valid();

        if ($shopId = $this->option('shop-id')) {
            $query->where('shop_id', $shopId);
        }

        return $query->get();
    }

    /**
     * Perform dry run sync
     */
    protected function dryRunSync(ShopeeToken $token)
    {
        try {
            $this->info("  [DRY RUN] Would sync inventory for shop: {$token->shop_name}");
            
            // Get products that need syncing
            $links = $this->getLinksToSync($token);

            $this->info("  [DRY RUN] Found {" . $links->count() . "} products to potentially sync");

            if ($links->count() > 0) {
                $this->table(
                    ['Product Name', 'SKU', 'Current Stock', 'Last Synced'],
                    $links->take(10)->map(function ($link) {
                        return [
                            $link->product->product_name ?? 'N/A',
                            $link->sku,
                            $link->product->stock_quantity ?? 0,
                            $link->last_synced_at ? $link->last_synced_at->format('Y-m-d H:i:s') : 'Never',
                        ];
                    })->toArray()
                );

                if ($links->count() > 10) {
                    $this->info("  [DRY RUN] ... and " . ($links->count() - 10) . " more products");
                }
            }

        } catch (\Exception $e) {
            $this->error("  [DRY RUN] Failed to get products: " . $e->getMessage());
        }
    }

    /**
     * Sync inventory for a specific token
     */
    protected function syncInventoryForToken(ShopeeToken $token)
    {
        try {
            $results = $this->productService->bulkSyncInventory($token, $this->option('limit'));
            
            // Mark token as used
            $token->markAsUsed();
            
            return $results;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to sync inventory for shop', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => 0,
                'failed' => 1,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Get links that need syncing
     */
    protected function getLinksToSync(ShopeeToken $token)
    {
        $query = MarketplaceProductLink::shopee()
            ->active()
            ->where('shop_id', $token->shop_id)
            ->with('product');

        if ($productId = $this->option('product-id')) {
            $query->where('product_id', $productId);
        } else {
            $query->needsSync();
        }

        return $query->limit($this->option('limit'))->get();
    }

    /**
     * Merge results from multiple syncs
     */
    protected function mergeResults(array &$total, array $results)
    {
        $total['success'] += $results['success'];
        $total['failed'] += $results['failed'];
        $total['errors'] = array_merge($total['errors'], $results['errors']);
    }

    /**
     * Display results for a single shop
     */
    protected function displayResults(array $results, string $shopName)
    {
        $this->info("  Results for {$shopName}:");
        $this->info("    ✓ Success: {$results['success']}");
        $this->info("    ✗ Failed: {$results['failed']}");

        if (!empty($results['errors'])) {
            $this->warn("    Errors:");
            foreach (array_slice($results['errors'], 0, 3) as $error) {
                $this->warn("      - " . (is_array($error) ? json_encode($error) : $error));
            }
            
            if (count($results['errors']) > 3) {
                $this->warn("      ... and " . (count($results['errors']) - 3) . " more errors");
            }
        }
    }

    /**
     * Display total results
     */
    protected function displayTotalResults(array $results)
    {
        $this->info('');
        $this->info('=== INVENTORY SYNC SUMMARY ===');
        $this->info("Total Success: {$results['success']}");
        $this->info("Total Failed: {$results['failed']}");
        
        if ($results['success'] > 0) {
            $this->info('✓ Inventory sync completed successfully!');
        } elseif ($results['failed'] > 0) {
            $this->warn('⚠ Inventory sync completed with errors.');
        } else {
            $this->info('ℹ No inventory updates needed.');
        }
    }

    /**
     * Log sync completion
     */
    protected function logSyncCompletion(array $results, float $startTime)
    {
        $duration = round(microtime(true) - $startTime, 2);
        
        Log::channel(config('shopee.logging.channel'))->info('Shopee inventory sync completed', [
            'duration_seconds' => $duration,
            'results' => $results,
            'command_options' => [
                'shop_id' => $this->option('shop-id'),
                'product_id' => $this->option('product-id'),
                'limit' => $this->option('limit'),
                'force' => $this->option('force'),
            ],
        ]);
    }
}
