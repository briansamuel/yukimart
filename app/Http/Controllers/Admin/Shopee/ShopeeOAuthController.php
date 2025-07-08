<?php

namespace App\Http\Controllers\Admin\Shopee;

use App\Http\Controllers\Controller;
use App\Services\Shopee\ShopeeOAuthService;
use App\Models\ShopeeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopeeOAuthController extends Controller
{
    protected $oauthService;

    public function __construct(ShopeeOAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Redirect to Shopee authorization
     */
    public function connect(Request $request)
    {
        try {
            // Generate state for security
            $state = Str::random(32);
            session(['shopee_oauth_state' => $state]);

            $authUrl = $this->oauthService->getAuthorizationUrl($state);

            Log::channel(config('shopee.logging.channel'))->info('Shopee OAuth initiated', [
                'user_id' => Auth::id(),
                'state' => $state,
            ]);

            return redirect($authUrl);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to initiate Shopee OAuth', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to connect to Shopee: ' . $e->getMessage());
        }
    }

    /**
     * Handle Shopee OAuth callback
     */
    public function callback(Request $request)
    {
        try {
            // Validate state parameter
            $state = $request->get('state');
            $sessionState = session('shopee_oauth_state');

            if (!$state || $state !== $sessionState) {
                throw new \Exception('Invalid state parameter. Possible CSRF attack.');
            }

            // Clear state from session
            session()->forget('shopee_oauth_state');

            // Check for authorization errors
            if ($request->has('error')) {
                throw new \Exception('Authorization denied: ' . $request->get('error_description', $request->get('error')));
            }

            // Validate required parameters
            $code = $request->get('code');
            $shopId = $request->get('shop_id');

            if (!$code || !$shopId) {
                throw new \Exception('Missing authorization code or shop ID');
            }

            // Exchange code for access token
            $token = $this->oauthService->getAccessToken($code, $shopId, Auth::user());

            Log::channel(config('shopee.logging.channel'))->info('Shopee OAuth completed successfully', [
                'user_id' => Auth::id(),
                'shop_id' => $shopId,
                'token_id' => $token->id,
            ]);

            return redirect()->route('admin.shopee.dashboard')->with('success', 'Successfully connected to Shopee shop: ' . ($token->shop_name ?? $shopId));

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee OAuth callback failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_params' => $request->all(),
            ]);

            return redirect()->route('admin.shopee.dashboard')->with('error', 'Failed to connect to Shopee: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token
     */
    public function refresh(Request $request)
    {
        try {
            $shopId = $request->get('shop_id');
            
            if (!$shopId) {
                return response()->json(['error' => 'Shop ID is required'], 400);
            }

            $token = ShopeeToken::where('shop_id', $shopId)->active()->first();

            if (!$token) {
                return response()->json(['error' => 'No active token found for this shop'], 404);
            }

            $refreshedToken = $this->oauthService->refreshAccessToken($token);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'expires_at' => $refreshedToken->expired_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to refresh Shopee token', [
                'user_id' => Auth::id(),
                'shop_id' => $request->get('shop_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to refresh token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke access token
     */
    public function revoke(Request $request)
    {
        try {
            $shopId = $request->get('shop_id');
            
            if (!$shopId) {
                return response()->json(['error' => 'Shop ID is required'], 400);
            }

            $token = ShopeeToken::where('shop_id', $shopId)->active()->first();

            if (!$token) {
                return response()->json(['error' => 'No active token found for this shop'], 404);
            }

            $success = $this->oauthService->revokeToken($token);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Token revoked successfully',
                ]);
            } else {
                return response()->json(['error' => 'Failed to revoke token'], 500);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to revoke Shopee token', [
                'user_id' => Auth::id(),
                'shop_id' => $request->get('shop_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to revoke token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get token status
     */
    public function status(Request $request)
    {
        try {
            $shopId = $request->get('shop_id');
            
            if ($shopId) {
                $token = ShopeeToken::where('shop_id', $shopId)->first();
            } else {
                $token = ShopeeToken::active()->first();
            }

            if (!$token) {
                return response()->json([
                    'connected' => false,
                    'message' => 'No Shopee connection found',
                ]);
            }

            return response()->json([
                'connected' => $token->isValid(),
                'shop_id' => $token->shop_id,
                'shop_name' => $token->shop_name,
                'expires_at' => $token->expired_at->toISOString(),
                'is_expiring_soon' => $token->isExpiringSoon(),
                'last_used_at' => $token->last_used_at?->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get Shopee token status', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get token status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard view
     */
    public function dashboard()
    {
        $tokens = ShopeeToken::with('user')->orderBy('created_at', 'desc')->get();
        
        return view('admin.shopee.dashboard', compact('tokens'));
    }

    /**
     * Check for expiring tokens
     */
    public function checkExpiringTokens()
    {
        try {
            $this->oauthService->checkExpiringTokens();
            
            return response()->json([
                'success' => true,
                'message' => 'Token expiry check completed',
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to check expiring tokens', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to check expiring tokens: ' . $e->getMessage()
            ], 500);
        }
    }
}
