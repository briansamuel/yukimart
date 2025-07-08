<?php

namespace App\Http\Controllers\Admin\Shopee;

use App\Http\Controllers\Controller;
use App\Services\Shopee\ShopeeOrderService;
use App\Services\Shopee\ShopeeApiService;
use App\Models\ShopeeToken;
use App\Models\Order;
use App\Jobs\SyncShopeeOrdersJob;
use App\Jobs\SyncShopeeInventoryJob;
use App\Jobs\CheckShopeeTokensJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ShopeeSyncController extends Controller
{
    protected $orderService;
    protected $apiService;

    public function __construct(ShopeeOrderService $orderService, ShopeeApiService $apiService)
    {
        $this->orderService = $orderService;
        $this->apiService = $apiService;
    }

    /**
     * Sync orders from Shopee
     */
    public function syncOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'nullable|string',
            'time_from' => 'nullable|date',
            'time_to' => 'nullable|date|after_or_equal:time_from',
            'order_status' => 'nullable|string|in:ALL,UNPAID,TO_SHIP,SHIPPED,TO_CONFIRM_RECEIVE,IN_CANCEL,CANCELLED,TO_RETURN,COMPLETED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $shopId = $request->get('shop_id');
            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            // Build sync parameters
            $params = [];
            
            if ($request->has('time_from')) {
                $params['time_from'] = Carbon::parse($request->get('time_from'))->timestamp;
            }
            
            if ($request->has('time_to')) {
                $params['time_to'] = Carbon::parse($request->get('time_to'))->timestamp;
            }
            
            if ($request->has('order_status')) {
                $params['order_status'] = $request->get('order_status');
            }

            // Check if we should use jobs or direct sync
            if (config('shopee.queue.enabled') && config('shopee.sync.use_jobs')) {
                // Dispatch job for async processing
                SyncShopeeOrdersJob::dispatch($shopId, $params)
                    ->onQueue(config('shopee.queue.order_sync_queue'));

                return response()->json([
                    'success' => true,
                    'message' => 'Order sync job dispatched successfully',
                    'async' => true,
                ]);
            } else {
                // Direct sync
                $results = $this->orderService->syncOrders($token);

                return response()->json([
                    'success' => true,
                    'message' => 'Order sync completed',
                    'results' => $results,
                    'async' => false,
                ]);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to sync orders', [
                'user_id' => Auth::id(),
                'shop_id' => $request->get('shop_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to sync orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order sync status
     */
    public function getSyncStatus(Request $request)
    {
        try {
            $shopId = $request->get('shop_id');
            
            // Get last sync information
            $lastSyncOrder = Order::marketplace()
                ->when($shopId, function ($query, $shopId) {
                    return $query->where('marketplace_data->shop_id', $shopId);
                })
                ->orderBy('marketplace_created_at', 'desc')
                ->first();

            // Get sync statistics
            $stats = [
                'total_synced_orders' => Order::marketplace()->count(),
                'last_sync_at' => $lastSyncOrder?->created_at?->toISOString(),
                'last_order_date' => $lastSyncOrder?->marketplace_created_at?->toISOString(),
            ];

            if ($shopId) {
                $stats['shop_orders'] = Order::marketplace()
                    ->where('marketplace_data->shop_id', $shopId)
                    ->count();
            }

            return response()->json([
                'success' => true,
                'status' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get sync status', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order detail from Shopee
     */
    public function getOrderDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_sn' => 'required|string',
            'shop_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $orderSn = $request->get('order_sn');
            $shopId = $request->get('shop_id');

            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            $orderDetail = $this->orderService->getOrderDetail($orderSn, $token);

            if (empty($orderDetail)) {
                return response()->json([
                    'error' => 'Order not found or inaccessible.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'order' => $orderDetail,
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get order detail', [
                'user_id' => Auth::id(),
                'order_sn' => $request->get('order_sn'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get order detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manual sync single order
     */
    public function syncSingleOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_sn' => 'required|string',
            'shop_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $orderSn = $request->get('order_sn');
            $shopId = $request->get('shop_id');

            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            // Check if order already exists
            $existingOrder = Order::where('marketplace_order_sn', $orderSn)->first();

            if ($existingOrder) {
                return response()->json([
                    'error' => 'Order already exists in the system.',
                    'order_id' => $existingOrder->id,
                ], 409);
            }

            // Get order detail and create local order
            $orderDetail = $this->orderService->getOrderDetail($orderSn, $token);

            if (empty($orderDetail)) {
                return response()->json([
                    'error' => 'Order not found on Shopee.',
                ], 404);
            }

            // Create order using reflection to access protected method
            $reflection = new \ReflectionClass($this->orderService);
            $method = $reflection->getMethod('createLocalOrder');
            $method->setAccessible(true);
            $order = $method->invoke($this->orderService, $orderDetail, $token);

            return response()->json([
                'success' => true,
                'message' => 'Order synced successfully',
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to sync single order', [
                'user_id' => Auth::id(),
                'order_sn' => $request->get('order_sn'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to sync order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync logs
     */
    public function getSyncLogs(Request $request)
    {
        try {
            $limit = $request->get('limit', 50);
            $offset = $request->get('offset', 0);

            // This is a simplified version - you might want to implement a proper logging system
            $logs = collect([
                [
                    'timestamp' => now()->toISOString(),
                    'type' => 'info',
                    'message' => 'Sync logs feature coming soon',
                ]
            ]);

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'total' => $logs->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get sync logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Shopee API connection
     */
    public function testConnection(Request $request)
    {
        try {
            $shopId = $request->get('shop_id');
            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid Shopee token found.',
                ], 401);
            }

            // Test API call - get shop info
            $response = $this->apiService->makeRequest('shop/get_shop_info', [], 'GET', $token);

            if ($this->apiService->isSuccessResponse($response)) {
                $token->markAsUsed();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful',
                    'shop_info' => $response['response'] ?? [],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'API call failed: ' . $this->apiService->getErrorMessage($response),
                ], 400);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to test Shopee connection', [
                'user_id' => Auth::id(),
                'shop_id' => $request->get('shop_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get marketplace orders
     */
    public function getMarketplaceOrders(Request $request)
    {
        try {
            $platform = $request->get('platform', 'shopee');
            $limit = $request->get('limit', 20);
            $offset = $request->get('offset', 0);

            $orders = Order::marketplace()
                ->when($platform, function ($query, $platform) {
                    return $query->where('marketplace_platform', $platform);
                })
                ->with(['customer', 'orderItems.product'])
                ->orderBy('marketplace_created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'marketplace_order_sn' => $order->marketplace_order_sn,
                        'customer_name' => $order->customer->name ?? 'N/A',
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'marketplace_status' => $order->marketplace_status,
                        'marketplace_created_at' => $order->marketplace_created_at?->toISOString(),
                        'created_at' => $order->created_at->toISOString(),
                        'items_count' => $order->orderItems->count(),
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get marketplace orders', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get marketplace orders: ' . $e->getMessage()
            ], 500);
        }
    }
}
