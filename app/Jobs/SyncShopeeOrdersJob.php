<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Shopee\ShopeeOrderService;
use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SyncShopeeOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopId;
    protected $params;
    protected $retryCount;

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
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(?string $shopId = null, array $params = [], int $retryCount = 0)
    {
        $this->shopId = $shopId;
        $this->params = $params;
        $this->retryCount = $retryCount;
        
        // Set queue based on priority
        $this->onQueue('shopee-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(ShopeeOrderService $orderService): void
    {
        $startTime = microtime(true);
        
        Log::channel(config('shopee.logging.channel'))->info('Shopee order sync job started', [
            'shop_id' => $this->shopId,
            'params' => $this->params,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Get tokens to sync
            $tokens = $this->getTokensToSync();

            if ($tokens->isEmpty()) {
                Log::channel(config('shopee.logging.channel'))->warning('No valid Shopee tokens found for sync job');
                return;
            }

            $totalResults = [
                'success' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => [],
                'shops_processed' => 0,
            ];

            // Sync orders for each token
            foreach ($tokens as $token) {
                try {
                    $results = $orderService->syncOrders($token);
                    $this->mergeResults($totalResults, $results);
                    $totalResults['shops_processed']++;
                    
                    // Mark token as used
                    $token->markAsUsed();
                    
                    Log::channel(config('shopee.logging.channel'))->info('Orders synced for shop', [
                        'shop_id' => $token->shop_id,
                        'shop_name' => $token->shop_name,
                        'results' => $results,
                    ]);

                } catch (\Exception $e) {
                    $totalResults['failed']++;
                    $totalResults['errors'][] = "Shop {$token->shop_id}: " . $e->getMessage();
                    
                    Log::channel(config('shopee.logging.channel'))->error('Failed to sync orders for shop', [
                        'shop_id' => $token->shop_id,
                        'shop_name' => $token->shop_name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            
            Log::channel(config('shopee.logging.channel'))->info('Shopee order sync job completed', [
                'duration_seconds' => $duration,
                'total_results' => $totalResults,
                'job_params' => $this->params,
            ]);

            // Send notification if there were errors
            if (!empty($totalResults['errors']) && config('shopee.notifications.notify_on_error')) {
                $this->notifyErrors($totalResults);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee order sync job failed', [
                'shop_id' => $this->shopId,
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
        Log::channel(config('shopee.logging.channel'))->error('Shopee order sync job failed permanently', [
            'shop_id' => $this->shopId,
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
     * Merge results from multiple syncs
     */
    protected function mergeResults(array &$total, array $results): void
    {
        $total['success'] += $results['success'];
        $total['failed'] += $results['failed'];
        $total['skipped'] += $results['skipped'];
        $total['errors'] = array_merge($total['errors'], $results['errors']);
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
            Log::channel(config('shopee.logging.channel'))->info('Sending Shopee sync error notification', [
                'admin_email' => $adminEmail,
                'error_count' => count($results['errors']),
                'results' => $results,
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeSyncErrorsMail($results));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send error notification', [
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
            Log::channel(config('shopee.logging.channel'))->error('Sending critical Shopee sync error notification', [
                'admin_email' => $adminEmail,
                'error' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeCriticalErrorMail($exception, $this->attempts()));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send critical error notification', [
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
            Log::channel(config('shopee.logging.channel'))->error('Sending Shopee job failure notification', [
                'admin_email' => $adminEmail,
                'error' => $exception->getMessage(),
                'max_attempts_reached' => true,
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeJobFailureMail($exception, $this->tries));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send job failure notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 120, 300]; // 30 seconds, 2 minutes, 5 minutes
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2); // Stop retrying after 2 hours
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = ['shopee', 'sync', 'orders'];
        
        if ($this->shopId) {
            $tags[] = "shop:{$this->shopId}";
        }
        
        return $tags;
    }
}
