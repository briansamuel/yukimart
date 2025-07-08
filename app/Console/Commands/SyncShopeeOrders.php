<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeOrderService;
use App\Services\Shopee\ShopeeApiService;
use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SyncShopeeOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopee:sync-orders 
                            {--shop-id= : Specific shop ID to sync}
                            {--days-back= : Number of days to look back for orders}
                            {--force : Force sync even if disabled in config}
                            {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync orders from Shopee to local database';

    protected $orderService;
    protected $apiService;

    /**
     * Create a new command instance.
     */
    public function __construct(ShopeeOrderService $orderService, ShopeeApiService $apiService)
    {
        parent::__construct();
        $this->orderService = $orderService;
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        
        $this->info('Starting Shopee order sync...');

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
                'skipped' => 0,
                'errors' => [],
            ];

            // Sync orders for each token
            foreach ($tokens as $token) {
                $this->info("Syncing orders for shop: {$token->shop_name} (ID: {$token->shop_id})");
                
                if ($this->option('dry-run')) {
                    $this->dryRunSync($token);
                } else {
                    $results = $this->syncOrdersForToken($token);
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
            $this->error('Sync failed: ' . $e->getMessage());
            
            Log::channel(config('shopee.logging.channel'))->error('Shopee order sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->notifyError($e);
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
            $this->info("  [DRY RUN] Would sync orders for shop: {$token->shop_name}");
            
            // Get order list without creating local orders
            $orderList = $this->orderService->getOrderList($token, $this->buildSyncParams());
            $orders = $orderList['order_list'] ?? [];

            $this->info("  [DRY RUN] Found {" . count($orders) . "} orders to potentially sync");

            if (count($orders) > 0) {
                $this->table(
                    ['Order SN', 'Status', 'Total Amount', 'Create Time'],
                    array_slice(array_map(function ($order) {
                        return [
                            $order['order_sn'],
                            $order['order_status'] ?? 'N/A',
                            number_format($order['total_amount'] ?? 0, 0, ',', '.') . ' VND',
                            date('Y-m-d H:i:s', $order['create_time'] ?? 0),
                        ];
                    }, $orders), 0, 10)
                );

                if (count($orders) > 10) {
                    $this->info("  [DRY RUN] ... and " . (count($orders) - 10) . " more orders");
                }
            }

        } catch (\Exception $e) {
            $this->error("  [DRY RUN] Failed to get orders: " . $e->getMessage());
        }
    }

    /**
     * Sync orders for a specific token
     */
    protected function syncOrdersForToken(ShopeeToken $token)
    {
        try {
            $results = $this->orderService->syncOrders($token);
            
            // Mark token as used
            $token->markAsUsed();
            
            return $results;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to sync orders for shop', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => 0,
                'failed' => 1,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Build sync parameters based on command options
     */
    protected function buildSyncParams()
    {
        $params = [];

        if ($daysBack = $this->option('days-back')) {
            $params['time_from'] = now()->subDays($daysBack)->timestamp;
            $params['time_to'] = now()->timestamp;
        }

        return $params;
    }

    /**
     * Merge results from multiple syncs
     */
    protected function mergeResults(array &$total, array $results)
    {
        $total['success'] += $results['success'];
        $total['failed'] += $results['failed'];
        $total['skipped'] += $results['skipped'];
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
        $this->info("    ⊘ Skipped: {$results['skipped']}");

        if (!empty($results['errors'])) {
            $this->warn("    Errors:");
            foreach (array_slice($results['errors'], 0, 3) as $error) {
                $this->warn("      - {$error}");
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
        $this->info('=== SYNC SUMMARY ===');
        $this->info("Total Success: {$results['success']}");
        $this->info("Total Failed: {$results['failed']}");
        $this->info("Total Skipped: {$results['skipped']}");
        
        if ($results['success'] > 0) {
            $this->info('✓ Sync completed successfully!');
        } elseif ($results['failed'] > 0) {
            $this->warn('⚠ Sync completed with errors.');
        } else {
            $this->info('ℹ No new orders to sync.');
        }
    }

    /**
     * Log sync completion
     */
    protected function logSyncCompletion(array $results, float $startTime)
    {
        $duration = round(microtime(true) - $startTime, 2);
        
        Log::channel(config('shopee.logging.channel'))->info('Shopee order sync completed', [
            'duration_seconds' => $duration,
            'results' => $results,
            'command_options' => [
                'shop_id' => $this->option('shop-id'),
                'days_back' => $this->option('days-back'),
                'force' => $this->option('force'),
            ],
        ]);
    }

    /**
     * Send error notification
     */
    protected function notifyError(\Exception $e)
    {
        if (!config('shopee.notifications.notify_on_error')) {
            return;
        }

        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        try {
            // You can implement email notification here
            Log::channel(config('shopee.logging.channel'))->error('Critical Shopee sync error - admin notified', [
                'error' => $e->getMessage(),
                'admin_email' => $adminEmail,
            ]);

            // Example: Mail::to($adminEmail)->send(new ShopeeSyncErrorMail($e));

        } catch (\Exception $mailException) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send error notification', [
                'original_error' => $e->getMessage(),
                'mail_error' => $mailException->getMessage(),
            ]);
        }
    }
}
