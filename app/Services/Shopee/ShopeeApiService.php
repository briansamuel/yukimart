<?php

namespace App\Services\Shopee;

use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ShopeeApiService
{
    protected $baseUrl;
    protected $partnerId;
    protected $partnerKey;
    protected $timeout;
    protected $retryAttempts;
    protected $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('shopee.api.base_url');
        $this->partnerId = config('shopee.credentials.partner_id');
        $this->partnerKey = config('shopee.credentials.partner_key');
        $this->timeout = config('shopee.api.timeout', 30);
        $this->retryAttempts = config('shopee.api.retry_attempts', 3);
        $this->retryDelay = config('shopee.api.retry_delay', 1000);
    }

    /**
     * Make authenticated API request
     */
    public function makeRequest(string $endpoint, array $params = [], string $method = 'GET', ?ShopeeToken $token = null): array
    {
        $url = $this->buildUrl($endpoint);
        $timestamp = time();
        
        // Add common parameters
        $params['partner_id'] = $this->partnerId;
        $params['timestamp'] = $timestamp;
        
        if ($token) {
            $params['access_token'] = $token->access_token;
            $params['shop_id'] = $token->shop_id;
        }

        // Generate signature
        $params['sign'] = $this->generateSignature($endpoint, $timestamp, $params, $token);

        $response = $this->executeRequest($url, $params, $method);

        // Log request if enabled
        if (config('shopee.logging.log_requests')) {
            Log::channel(config('shopee.logging.channel'))->info('Shopee API Request', [
                'endpoint' => $endpoint,
                'method' => $method,
                'params' => $this->sanitizeParams($params),
            ]);
        }

        // Log response if enabled
        if (config('shopee.logging.log_responses')) {
            Log::channel(config('shopee.logging.channel'))->info('Shopee API Response', [
                'endpoint' => $endpoint,
                'response' => $response,
            ]);
        }

        return $response;
    }

    /**
     * Execute HTTP request with retry logic
     */
    protected function executeRequest(string $url, array $params, string $method): array
    {
        $attempt = 0;
        
        while ($attempt < $this->retryAttempts) {
            try {
                $response = Http::timeout($this->timeout)
                    ->when($method === 'GET', function ($http) use ($params) {
                        return $http->get($url, $params);
                    })
                    ->when($method === 'POST', function ($http) use ($url, $params) {
                        return $http->post($url, $params);
                    });

                if ($response->successful()) {
                    return $response->json();
                }

                // Handle specific error codes
                $statusCode = $response->status();
                $responseData = $response->json();

                if ($statusCode === 429) { // Rate limit
                    $this->handleRateLimit($attempt);
                } elseif ($statusCode === 401 && isset($responseData['error']) && $responseData['error'] === 'error_auth') {
                    throw new \Exception('Authentication failed. Token may be expired.');
                } else {
                    throw new \Exception("API request failed with status {$statusCode}: " . $response->body());
                }

            } catch (\Exception $e) {
                $attempt++;
                
                if ($attempt >= $this->retryAttempts) {
                    Log::channel(config('shopee.logging.channel'))->error('Shopee API Request Failed', [
                        'url' => $url,
                        'method' => $method,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);
                    
                    throw $e;
                }

                // Wait before retry
                usleep($this->retryDelay * 1000 * $attempt);
            }
        }

        throw new \Exception('Max retry attempts reached');
    }

    /**
     * Build full API URL
     */
    protected function buildUrl(string $endpoint): string
    {
        $version = config('shopee.api.version');
        return rtrim($this->baseUrl, '/') . "/api/{$version}/{$endpoint}";
    }

    /**
     * Generate API signature
     */
    protected function generateSignature(string $endpoint, int $timestamp, array $params, ?ShopeeToken $token = null): string
    {
        $version = config('shopee.api.version');
        $baseString = $this->partnerId . "/api/{$version}/{$endpoint}" . $timestamp;
        
        if ($token) {
            $baseString .= $token->access_token . $token->shop_id;
        }

        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Handle rate limiting
     */
    protected function handleRateLimit(int $attempt): void
    {
        $delay = min(pow(2, $attempt) * 1000, 30000); // Exponential backoff, max 30 seconds
        usleep($delay * 1000);
    }

    /**
     * Sanitize parameters for logging (remove sensitive data)
     */
    protected function sanitizeParams(array $params): array
    {
        $sanitized = $params;
        unset($sanitized['access_token'], $sanitized['sign'], $sanitized['partner_key']);
        return $sanitized;
    }

    /**
     * Check if response is successful
     */
    public function isSuccessResponse(array $response): bool
    {
        return isset($response['error']) && $response['error'] === '';
    }

    /**
     * Get error message from response
     */
    public function getErrorMessage(array $response): string
    {
        return $response['message'] ?? $response['error'] ?? 'Unknown error';
    }

    /**
     * Cache API response
     */
    protected function cacheResponse(string $key, array $data, int $ttl = null): void
    {
        if (!config('shopee.cache.enabled')) {
            return;
        }

        $ttl = $ttl ?? config('shopee.cache.ttl', 3600);
        $cacheKey = config('shopee.cache.prefix') . $key;
        
        Cache::put($cacheKey, $data, $ttl);
    }

    /**
     * Get cached response
     */
    protected function getCachedResponse(string $key): ?array
    {
        if (!config('shopee.cache.enabled')) {
            return null;
        }

        $cacheKey = config('shopee.cache.prefix') . $key;
        return Cache::get($cacheKey);
    }

    /**
     * Validate required fields in response
     */
    public function validateResponse(array $response, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($response[$field])) {
                Log::channel(config('shopee.logging.channel'))->warning('Missing required field in Shopee response', [
                    'field' => $field,
                    'response' => $response,
                ]);
                return false;
            }
        }
        return true;
    }

    /**
     * Get valid token for shop
     */
    public function getValidToken(string $shopId = null): ?ShopeeToken
    {
        $query = ShopeeToken::valid();

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        $token = $query->first();

        if (!$token) {
            return null;
        }

        // Check if token is expiring soon and try to refresh
        if ($token->isExpiringSoon()) {
            try {
                $oauthService = new ShopeeOAuthService();
                $token = $oauthService->refreshAccessToken($token);
            } catch (\Exception $e) {
                Log::channel(config('shopee.logging.channel'))->error('Failed to refresh expiring token', [
                    'shop_id' => $token->shop_id,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        }

        return $token;
    }
}
