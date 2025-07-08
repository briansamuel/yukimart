<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Shopee\ShopeeOAuthService;
use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Log;

class CheckShopeeTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopee:check-tokens 
                            {--refresh : Automatically refresh expiring tokens}
                            {--notify : Send notifications for expiring tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Shopee token status and refresh expiring tokens';

    protected $oauthService;

    /**
     * Create a new command instance.
     */
    public function __construct(ShopeeOAuthService $oauthService)
    {
        parent::__construct();
        $this->oauthService = $oauthService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Shopee token status...');

        try {
            $tokens = ShopeeToken::all();

            if ($tokens->isEmpty()) {
                $this->warn('No Shopee tokens found.');
                return 0;
            }

            $stats = [
                'total' => $tokens->count(),
                'valid' => 0,
                'expired' => 0,
                'expiring_soon' => 0,
                'refreshed' => 0,
                'refresh_failed' => 0,
            ];

            $this->info("Found {$stats['total']} tokens to check.");
            $this->info('');

            foreach ($tokens as $token) {
                $this->checkToken($token, $stats);
            }

            $this->displaySummary($stats);
            $this->logResults($stats);

            return 0;

        } catch (\Exception $e) {
            $this->error('Token check failed: ' . $e->getMessage());
            
            Log::channel(config('shopee.logging.channel'))->error('Shopee token check command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Check individual token
     */
    protected function checkToken(ShopeeToken $token, array &$stats)
    {
        $shopName = $token->shop_name ?: "Shop {$token->shop_id}";
        
        $this->info("Checking token for: {$shopName}");

        if ($token->isExpired()) {
            $stats['expired']++;
            $this->warn("  ✗ Token expired on {$token->expired_at->format('Y-m-d H:i:s')}");
            
            if ($this->option('refresh')) {
                $this->attemptRefresh($token, $stats);
            }
        } elseif ($token->isExpiringSoon()) {
            $stats['expiring_soon']++;
            $hoursRemaining = $token->expired_at->diffInHours(now());
            $this->warn("  ⚠ Token expires in {$hoursRemaining} hours ({$token->expired_at->format('Y-m-d H:i:s')})");
            
            if ($this->option('refresh')) {
                $this->attemptRefresh($token, $stats);
            }
            
            if ($this->option('notify')) {
                $this->sendExpiryNotification($token);
            }
        } else {
            $stats['valid']++;
            $daysRemaining = $token->expired_at->diffInDays(now());
            $this->info("  ✓ Token valid for {$daysRemaining} more days");
        }

        // Display additional info
        $this->line("    Last used: " . ($token->last_used_at ? $token->last_used_at->format('Y-m-d H:i:s') : 'Never'));
        $this->line("    Status: " . ($token->is_active ? 'Active' : 'Inactive'));
        $this->line('');
    }

    /**
     * Attempt to refresh token
     */
    protected function attemptRefresh(ShopeeToken $token, array &$stats)
    {
        $this->info("  🔄 Attempting to refresh token...");
        
        try {
            $refreshedToken = $this->oauthService->refreshAccessToken($token);
            $stats['refreshed']++;
            $this->info("  ✓ Token refreshed successfully. New expiry: {$refreshedToken->expired_at->format('Y-m-d H:i:s')}");
            
        } catch (\Exception $e) {
            $stats['refresh_failed']++;
            $this->error("  ✗ Failed to refresh token: " . $e->getMessage());
            
            Log::channel(config('shopee.logging.channel'))->error('Failed to refresh Shopee token', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send expiry notification
     */
    protected function sendExpiryNotification(ShopeeToken $token)
    {
        try {
            $this->oauthService->notifyTokenExpiry($token);
            $this->info("  📧 Expiry notification sent");
            
        } catch (\Exception $e) {
            $this->warn("  ⚠ Failed to send notification: " . $e->getMessage());
        }
    }

    /**
     * Display summary
     */
    protected function displaySummary(array $stats)
    {
        $this->info('=== TOKEN CHECK SUMMARY ===');
        $this->info("Total tokens: {$stats['total']}");
        $this->info("Valid tokens: {$stats['valid']}");
        $this->info("Expired tokens: {$stats['expired']}");
        $this->info("Expiring soon: {$stats['expiring_soon']}");
        
        if ($this->option('refresh')) {
            $this->info("Tokens refreshed: {$stats['refreshed']}");
            $this->info("Refresh failures: {$stats['refresh_failed']}");
        }

        $this->info('');

        // Recommendations
        if ($stats['expired'] > 0) {
            $this->warn("⚠ You have {$stats['expired']} expired tokens. Please reconnect these shops.");
        }

        if ($stats['expiring_soon'] > 0) {
            $this->warn("⚠ You have {$stats['expiring_soon']} tokens expiring soon. Consider refreshing them.");
        }

        if ($stats['valid'] === $stats['total']) {
            $this->info('✓ All tokens are valid!');
        }
    }

    /**
     * Log results
     */
    protected function logResults(array $stats)
    {
        Log::channel(config('shopee.logging.channel'))->info('Shopee token check completed', [
            'stats' => $stats,
            'command_options' => [
                'refresh' => $this->option('refresh'),
                'notify' => $this->option('notify'),
            ],
        ]);
    }
}
