<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ShopeeOAuthService extends ShopeeApiService
{
    /**
     * Generate authorization URL for Shopee OAuth
     */
    public function getAuthorizationUrl(string $state = null): string
    {
        $params = [
            'partner_id' => $this->partnerId,
            'redirect' => config('shopee.credentials.redirect_uri'),
        ];

        if ($state) {
            $params['state'] = $state;
        }

        $queryString = http_build_query($params);
        return config('shopee.oauth.auth_url') . '?' . $queryString;
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code, string $shopId, ?User $user = null): ShopeeToken
    {
        $endpoint = 'auth/token/get';
        $timestamp = time();
        
        $params = [
            'code' => $code,
            'shop_id' => $shopId,
            'partner_id' => $this->partnerId,
            'timestamp' => $timestamp,
        ];

        // Generate signature for token request
        $params['sign'] = $this->generateTokenSignature($endpoint, $timestamp, $code, $shopId);

        try {
            $response = $this->executeRequest(
                $this->buildUrl($endpoint),
                $params,
                'POST'
            );

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to get access token: ' . $this->getErrorMessage($response));
            }

            // Validate required fields
            $requiredFields = ['access_token', 'refresh_token', 'expire_in'];
            if (!$this->validateResponse($response, $requiredFields)) {
                throw new \Exception('Invalid token response format');
            }

            // Get shop info
            $shopInfo = $this->getShopInfo($response['access_token'], $shopId);

            // Create or update token
            $token = $this->createOrUpdateToken($response, $shopId, $shopInfo, $user);

            Log::channel(config('shopee.logging.channel'))->info('Shopee OAuth token obtained', [
                'shop_id' => $shopId,
                'user_id' => $user?->id,
                'expires_at' => $token->expired_at,
            ]);

            return $token;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee OAuth failed', [
                'shop_id' => $shopId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(ShopeeToken $token): ShopeeToken
    {
        $endpoint = 'auth/access_token/get';
        $timestamp = time();
        
        $params = [
            'refresh_token' => $token->refresh_token,
            'shop_id' => $token->shop_id,
            'partner_id' => $this->partnerId,
            'timestamp' => $timestamp,
        ];

        // Generate signature for refresh request
        $params['sign'] = $this->generateRefreshSignature($endpoint, $timestamp, $token->refresh_token, $token->shop_id);

        try {
            $response = $this->executeRequest(
                $this->buildUrl($endpoint),
                $params,
                'POST'
            );

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to refresh token: ' . $this->getErrorMessage($response));
            }

            // Update token
            $token->update([
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expired_at' => Carbon::now()->addSeconds($response['expire_in']),
                'last_used_at' => now(),
            ]);

            Log::channel(config('shopee.logging.channel'))->info('Shopee token refreshed', [
                'shop_id' => $token->shop_id,
                'expires_at' => $token->expired_at,
            ]);

            return $token->fresh();

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee token refresh failed', [
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
            
            // Deactivate token if refresh fails
            $token->update(['is_active' => false]);
            
            throw $e;
        }
    }

    /**
     * Get shop information
     */
    protected function getShopInfo(string $accessToken, string $shopId): array
    {
        try {
            // Create temporary token for shop info request
            $tempToken = new ShopeeToken([
                'access_token' => $accessToken,
                'shop_id' => $shopId,
            ]);

            $response = $this->makeRequest('shop/get_shop_info', [], 'GET', $tempToken);

            if ($this->isSuccessResponse($response)) {
                return $response['response'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->warning('Failed to get shop info', [
                'shop_id' => $shopId,
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Create or update Shopee token
     */
    protected function createOrUpdateToken(array $response, string $shopId, array $shopInfo, ?User $user): ShopeeToken
    {
        $expiresAt = Carbon::now()->addSeconds($response['expire_in']);

        return ShopeeToken::updateOrCreate(
            ['shop_id' => $shopId],
            [
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'partner_id' => $this->partnerId,
                'expired_at' => $expiresAt,
                'user_id' => $user?->id,
                'shop_info' => $shopInfo,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Generate signature for token request
     */
    protected function generateTokenSignature(string $endpoint, int $timestamp, string $code, string $shopId): string
    {
        $version = config('shopee.api.version');
        $baseString = $this->partnerId . "/api/{$version}/{$endpoint}" . $timestamp;
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Generate signature for refresh request
     */
    protected function generateRefreshSignature(string $endpoint, int $timestamp, string $refreshToken, string $shopId): string
    {
        $version = config('shopee.api.version');
        $baseString = $this->partnerId . "/api/{$version}/{$endpoint}" . $timestamp;
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Check for expiring tokens and send notifications
     */
    public function checkExpiringTokens(): void
    {
        if (!config('shopee.notifications.notify_on_token_expiry')) {
            return;
        }

        $warningHours = config('shopee.notifications.token_expiry_warning_hours', 24);
        $expiringTokens = ShopeeToken::expiringSoon()->get();

        foreach ($expiringTokens as $token) {
            $this->notifyTokenExpiry($token);
        }
    }

    /**
     * Send token expiry notification
     */
    protected function notifyTokenExpiry(ShopeeToken $token): void
    {
        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        try {
            // You can implement email notification here
            Log::channel(config('shopee.logging.channel'))->warning('Shopee token expiring soon', [
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'expires_at' => $token->expired_at,
                'hours_remaining' => $token->expired_at->diffInHours(now()),
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to send token expiry notification', [
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Revoke access token
     */
    public function revokeToken(ShopeeToken $token): bool
    {
        try {
            $token->update(['is_active' => false]);
            
            Log::channel(config('shopee.logging.channel'))->info('Shopee token revoked', [
                'shop_id' => $token->shop_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to revoke token', [
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}
