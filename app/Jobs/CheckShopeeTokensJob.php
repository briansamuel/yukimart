<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Shopee\ShopeeOAuthService;
use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckShopeeTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $autoRefresh;
    protected $sendNotifications;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 120; // 2 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(bool $autoRefresh = true, bool $sendNotifications = true)
    {
        $this->autoRefresh = $autoRefresh;
        $this->sendNotifications = $sendNotifications;
        
        // Set queue for token management
        $this->onQueue('shopee-tokens');
    }

    /**
     * Execute the job.
     */
    public function handle(ShopeeOAuthService $oauthService): void
    {
        $startTime = microtime(true);
        
        Log::channel(config('shopee.logging.channel'))->info('Shopee token check job started', [
            'auto_refresh' => $this->autoRefresh,
            'send_notifications' => $this->sendNotifications,
            'attempt' => $this->attempts(),
        ]);

        try {
            $tokens = ShopeeToken::all();

            if ($tokens->isEmpty()) {
                Log::channel(config('shopee.logging.channel'))->info('No Shopee tokens found to check');
                return;
            }

            $stats = [
                'total' => $tokens->count(),
                'valid' => 0,
                'expired' => 0,
                'expiring_soon' => 0,
                'refreshed' => 0,
                'refresh_failed' => 0,
                'notifications_sent' => 0,
            ];

            foreach ($tokens as $token) {
                $this->checkToken($token, $oauthService, $stats);
            }

            $duration = round(microtime(true) - $startTime, 2);
            
            Log::channel(config('shopee.logging.channel'))->info('Shopee token check job completed', [
                'duration_seconds' => $duration,
                'stats' => $stats,
                'job_params' => [
                    'auto_refresh' => $this->autoRefresh,
                    'send_notifications' => $this->sendNotifications,
                ],
            ]);

            // Send summary notification if there are issues
            if (($stats['expired'] > 0 || $stats['expiring_soon'] > 0 || $stats['refresh_failed'] > 0) 
                && $this->sendNotifications) {
                $this->sendSummaryNotification($stats);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee token check job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel(config('shopee.logging.channel'))->error('Shopee token check job failed permanently', [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        // Send failure notification
        $this->notifyJobFailure($exception);
    }

    /**
     * Check individual token
     */
    protected function checkToken(ShopeeToken $token, ShopeeOAuthService $oauthService, array &$stats): void
    {
        $shopName = $token->shop_name ?: "Shop {$token->shop_id}";
        
        Log::channel(config('shopee.logging.channel'))->debug('Checking token', [
            'shop_id' => $token->shop_id,
            'shop_name' => $shopName,
            'expires_at' => $token->expired_at->toISOString(),
        ]);

        if ($token->isExpired()) {
            $stats['expired']++;
            
            Log::channel(config('shopee.logging.channel'))->warning('Token expired', [
                'shop_id' => $token->shop_id,
                'shop_name' => $shopName,
                'expired_at' => $token->expired_at->toISOString(),
            ]);
            
            if ($this->autoRefresh) {
                $this->attemptRefresh($token, $oauthService, $stats);
            }
            
        } elseif ($token->isExpiringSoon()) {
            $stats['expiring_soon']++;
            $hoursRemaining = $token->expired_at->diffInHours(now());
            
            Log::channel(config('shopee.logging.channel'))->warning('Token expiring soon', [
                'shop_id' => $token->shop_id,
                'shop_name' => $shopName,
                'expires_at' => $token->expired_at->toISOString(),
                'hours_remaining' => $hoursRemaining,
            ]);
            
            if ($this->autoRefresh) {
                $this->attemptRefresh($token, $oauthService, $stats);
            }
            
            if ($this->sendNotifications) {
                $this->sendExpiryNotification($token, $oauthService, $stats);
            }
            
        } else {
            $stats['valid']++;
            
            Log::channel(config('shopee.logging.channel'))->debug('Token is valid', [
                'shop_id' => $token->shop_id,
                'shop_name' => $shopName,
                'expires_at' => $token->expired_at->toISOString(),
            ]);
        }
    }

    /**
     * Attempt to refresh token
     */
    protected function attemptRefresh(ShopeeToken $token, ShopeeOAuthService $oauthService, array &$stats): void
    {
        try {
            $refreshedToken = $oauthService->refreshAccessToken($token);
            $stats['refreshed']++;
            
            Log::channel(config('shopee.logging.channel'))->info('Token refreshed successfully', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'new_expires_at' => $refreshedToken->expired_at->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            $stats['refresh_failed']++;
            
            Log::channel(config('shopee.logging.channel'))->error('Failed to refresh token', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send expiry notification
     */
    protected function sendExpiryNotification(ShopeeToken $token, ShopeeOAuthService $oauthService, array &$stats): void
    {
        try {
            // Check if notification was already sent recently
            $lastNotificationKey = "shopee_token_expiry_notification_{$token->shop_id}";
            $lastNotification = cache($lastNotificationKey);
            
            if ($lastNotification && Carbon::parse($lastNotification)->diffInHours(now()) < 12) {
                // Don't send notification if one was sent in the last 12 hours
                return;
            }
            
            $oauthService->notifyTokenExpiry($token);
            $stats['notifications_sent']++;
            
            // Cache notification timestamp
            cache([$lastNotificationKey => now()->toISOString()], now()->addDay());
            
            Log::channel(config('shopee.logging.channel'))->info('Expiry notification sent', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
            ]);
            
        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send expiry notification', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send summary notification
     */
    protected function sendSummaryNotification(array $stats): void
    {
        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        try {
            Log::channel(config('shopee.logging.channel'))->info('Sending Shopee token check summary notification', [
                'admin_email' => $adminEmail,
                'stats' => $stats,
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeTokenCheckSummaryMail($stats));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send token check summary notification', [
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
            Log::channel(config('shopee.logging.channel'))->error('Sending Shopee token check job failure notification', [
                'admin_email' => $adminEmail,
                'error' => $exception->getMessage(),
                'max_attempts_reached' => true,
            ]);

            // You can implement email notification here
            // Mail::to($adminEmail)->send(new ShopeeTokenCheckJobFailureMail($exception, $this->tries));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send token check job failure notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300]; // 1 minute, 5 minutes
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30); // Stop retrying after 30 minutes
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['shopee', 'tokens', 'maintenance'];
    }
}
