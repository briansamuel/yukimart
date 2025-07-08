<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Shopee\ShopeeProductService;
use App\Models\ShopeeToken;
use App\Models\MarketplaceProductLink;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncShopeeInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopId;
    protected $productId;
    protected $limit;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 180; // 3 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(?string $shopId = null, ?int $productId = null, int $limit = 50)
    {
        $this->shopId = $shopId;
        $this->productId = $productId;
        $this->limit = $limit;
        
        // Set queue based on priority
        $this->onQueue('shopee-inventory');
    }

    /**
     * Execute the job.
     */
    public function handle(ShopeeProductService $productService): void
    {
        $startTime = microtime(true);
        
        Log::channel(config('shopee.logging.channel'))->info('Shopee inventory sync job started', [
            'shop_id' => $this->shopId,
            'product_id' => $this->productId,
            'limit' => $this->limit,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Get tokens to sync
            $tokens = $this->getTokensToSync();

            if ($tokens->isEmpty()) {
                Log::channel(config('shopee.logging.channel'))->warning('No valid Shopee tokens found for inventory sync job');
                return;
            }

            $totalResults = [
                'success' => 0,
                'failed' => 0,
                'errors' => [],
                'shops_processed' => 0,
                'products_processed' => 0,
            ];

            // Sync inventory for each token
            foreach ($tokens as $token) {
                try {
                    if ($this->productId) {
                        // Sync specific product
                        $results = $this->syncSpecificProduct($productService, $token);
                    } else {
                        // Bulk sync inventory
                        $results = $productService->bulkSyncInventory($token, $this->limit);
                    }
                    
                    $this->mergeResults($totalResults, $results);
                    $totalResults['shops_processed']++;
                    
                    // Mark token as used
                    $token->markAsUsed();
                    
                    Log::channel(config('shopee.logging.channel'))->info('Inventory synced for shop', [
                        'shop_id' => $token->shop_id,
                        'shop_name' => $token->shop_name,
                        'results' => $results,
                    ]);

                } catch (\Exception $e) {
                    $totalResults['failed']++;
                    $totalResults['errors'][] = "Shop {$token->shop_id}: " . $e->getMessage();
                    
                    Log::channel(config('shopee.logging.channel'))->error('Failed to sync inventory for shop', [
                        'shop_id' => $token->shop_id,
                        'shop_name' => $token->shop_name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            
            Log::channel(config('shopee.logging.channel'))->info('Shopee inventory sync job completed', [
                'duration_seconds' => $duration,
                'total_results' => $totalResults,
                'job_params' => [
                    'shop_id' => $this->shopId,
                    'product_id' => $this->productId,
                    'limit' => $this->limit,
                ],
            ]);

            // Send notification if there were errors
            if (!empty($totalResults['errors']) && config('shopee.notifications.notify_on_error')) {
                $this->notifyErrors($totalResults);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee inventory sync job failed', [
                'shop_id' => $this->shopId,
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            // Send critical error notification
            $this->notifyCriticalError($e);
            
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel(config('shopee.logging.channel'))->error('Shopee inventory sync job failed permanently', [
            'shop_id' => $this->shopId,
            'product_id' => $this->productId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        // Send failure notification
        $this->notifyJobFailure($exception);
    }

    /**
     * Get tokens to sync based on job parameters
     */
    protected function getTokensToSync()
    {
        $query = ShopeeToken::valid();

        if ($this->shopId) {
            $query->where('shop_id', $this->shopId);
        }

        return $query->get();
    }

    /**
     * Sync specific product inventory
     */
    protected function syncSpecificProduct(ShopeeProductService $productService, ShopeeToken $token): array
    {
        $link = MarketplaceProductLink::where('product_id', $this->productId)
            ->where('platform', 'shopee')
            ->where('shop_id', $token->shop_id)
            ->where('status', MarketplaceProductLink::STATUS_ACTIVE)
            ->with('product')
            ->first();

        if (!$link) {
            return [
                'success' => 0,
                'failed' => 1,
                'errors' => ["Product {$this->productId} not linked to shop {$token->shop_id}"],
            ];
        }

        $success = $productService->syncInventory($link, $token);

        return [
            'success' => $success ? 1 : 0,
            'failed' => $success ? 0 : 1,
            'errors' => $success ? [] : ["Failed to sync product {$this->productId}"],
        ];
    }

    /**
     * Merge results from multiple syncs
     */
    protected function mergeResults(array &$total, array $results): void
    {
        $total['success'] += $results['success'];
        $total['failed'] += $results['failed'];
        $total['errors'] = array_merge($total['errors'], $results['errors']);
        $total['products_processed'] += $results['success'] + $results['failed'];
    }

    /**
     * Send error notifications
     */
    protected function notifyErrors(array $results): void
    {
        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        try {
            Log::channel(config('shopee.logging.channel'))->info('Sending Shopee inventory sync error notification', [
                'admin_email' => $adminEmail,
                'error_count' => count($results['errors']),
                'results' => $results,
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeInventorySyncErrorsMail($results));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send inventory sync error notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send critical error notification
     */
    protected function notifyCriticalError(\Exception $exception): void
    {
        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        try {
            Log::channel(config('shopee.logging.channel'))->error('Sending critical Shopee inventory sync error notification', [
                'admin_email' => $adminEmail,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeInventoryCriticalErrorMail($exception, $this->attempts()));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send critical inventory sync error notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send job failure notification
     */
    protected function notifyJobFailure(\Throwable $exception): void
    {
        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        try {
            Log::channel(config('shopee.logging.channel'))->error('Sending Shopee inventory job failure notification', [
                'admin_email' => $adminEmail,
                'error' => $exception->getMessage(),
                'max_attempts_reached' => true,
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeInventoryJobFailureMail($exception, $this->tries));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send inventory job failure notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [15, 60, 180]; // 15 seconds, 1 minute, 3 minutes
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHour(); // Stop retrying after 1 hour
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = ['shopee', 'sync', 'inventory'];
        
        if ($this->shopId) {
            $tags[] = "shop:{$this->shopId}";
        }
        
        if ($this->productId) {
            $tags[] = "product:{$this->productId}";
        }
        
        return $tags;
    }
}
