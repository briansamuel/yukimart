<?php

namespace App\Http\Controllers\Admin\Shopee;

use App\Http\Controllers\Controller;
use App\Services\Shopee\ShopeeProductService;
use App\Services\Shopee\ShopeeApiService;
use App\Models\Product;
use App\Models\MarketplaceProductLink;
use App\Models\ShopeeToken;
use App\Jobs\SyncShopeeInventoryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopeeProductController extends Controller
{
    protected $productService;
    protected $apiService;

    public function __construct(ShopeeProductService $productService, ShopeeApiService $apiService)
    {
        $this->productService = $productService;
        $this->apiService = $apiService;
    }

    /**
     * Search products by SKU
     */
    public function searchBySku(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:255',
            'shop_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $sku = $request->get('sku');
            $shopId = $request->get('shop_id');

            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            $products = $this->productService->searchProductsBySku($sku, $token);

            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => count($products),
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to search products by SKU', [
                'user_id' => Auth::id(),
                'sku' => $request->get('sku'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to search products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link product to Shopee item
     */
    public function linkProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'shopee_item_id' => 'required|integer',
            'shop_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product::findOrFail($request->get('product_id'));
            $shopeeItemId = $request->get('shopee_item_id');
            $shopId = $request->get('shop_id');

            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            // Check if product is already linked
            $existingLink = MarketplaceProductLink::where('product_id', $product->id)
                ->where('platform', 'shopee')
                ->where('marketplace_item_id', $shopeeItemId)
                ->first();

            if ($existingLink) {
                return response()->json([
                    'error' => 'Product is already linked to this Shopee item.',
                ], 409);
            }

            // Get Shopee item details
            $shopeeItem = $this->productService->getItemDetail($shopeeItemId, $token);

            if (!$shopeeItem) {
                return response()->json([
                    'error' => 'Shopee item not found or inaccessible.',
                ], 404);
            }

            // Create link
            $link = $this->productService->linkProduct($product, $shopeeItem, $token);

            return response()->json([
                'success' => true,
                'message' => 'Product linked successfully',
                'link' => [
                    'id' => $link->id,
                    'product_name' => $product->product_name,
                    'shopee_item_name' => $link->name,
                    'sku' => $link->sku,
                    'marketplace_url' => $link->marketplace_url,
                ],
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to link product', [
                'user_id' => Auth::id(),
                'product_id' => $request->get('product_id'),
                'shopee_item_id' => $request->get('shopee_item_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to link product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create product on Shopee
     */
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'category_id' => 'required|integer',
            'shop_id' => 'nullable|string',
            'logistics' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = Product::findOrFail($request->get('product_id'));
            $shopId = $request->get('shop_id');

            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            // Check if product is already linked to Shopee
            $existingLink = MarketplaceProductLink::where('product_id', $product->id)
                ->where('platform', 'shopee')
                ->where('shop_id', $token->shop_id)
                ->first();

            if ($existingLink) {
                return response()->json([
                    'error' => 'Product is already linked to Shopee.',
                ], 409);
            }

            $options = [
                'category_id' => $request->get('category_id'),
                'logistics' => $request->get('logistics', []),
            ];

            $shopeeProduct = $this->productService->createProduct($product, $token, $options);

            return response()->json([
                'success' => true,
                'message' => 'Product created on Shopee successfully',
                'shopee_item_id' => $shopeeProduct['item_id'],
                'product_name' => $product->product_name,
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to create product on Shopee', [
                'user_id' => Auth::id(),
                'product_id' => $request->get('product_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create product on Shopee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync inventory to Shopee
     */
    public function syncInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link_id' => 'required|exists:marketplace_product_links,id',
            'shop_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $link = MarketplaceProductLink::with('product')->findOrFail($request->get('link_id'));
            $shopId = $request->get('shop_id');

            if ($link->platform !== 'shopee') {
                return response()->json([
                    'error' => 'This link is not for Shopee platform.',
                ], 400);
            }

            $token = $this->apiService->getValidToken($shopId ?: $link->shop_id);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            $success = $this->productService->syncInventory($link, $token);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inventory synced successfully',
                    'stock_quantity' => $link->product->stock_quantity,
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to sync inventory',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to sync inventory', [
                'user_id' => Auth::id(),
                'link_id' => $request->get('link_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to sync inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk sync inventory
     */
    public function bulkSyncInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $shopId = $request->get('shop_id');
            $limit = $request->get('limit', 50);

            $token = $this->apiService->getValidToken($shopId);

            if (!$token) {
                return response()->json([
                    'error' => 'No valid Shopee token found. Please connect to Shopee first.',
                ], 401);
            }

            // Check if we should use jobs or direct sync
            if (config('shopee.queue.enabled') && config('shopee.sync.use_jobs')) {
                // Dispatch job for async processing
                SyncShopeeInventoryJob::dispatch($shopId, null, $limit)
                    ->onQueue(config('shopee.queue.inventory_sync_queue'));

                return response()->json([
                    'success' => true,
                    'message' => 'Bulk inventory sync job dispatched successfully',
                    'async' => true,
                ]);
            } else {
                // Direct sync
                $results = $this->productService->bulkSyncInventory($token, $limit);

                return response()->json([
                    'success' => true,
                    'message' => 'Bulk inventory sync completed',
                    'results' => $results,
                    'async' => false,
                ]);
            }

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to bulk sync inventory', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to bulk sync inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product links
     */
    public function getLinks(Request $request)
    {
        try {
            $productId = $request->get('product_id');
            $platform = $request->get('platform', 'shopee');

            $query = MarketplaceProductLink::with('product')
                ->where('platform', $platform);

            if ($productId) {
                $query->where('product_id', $productId);
            }

            $links = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'links' => $links->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'product_id' => $link->product_id,
                        'product_name' => $link->product->product_name,
                        'marketplace_item_id' => $link->marketplace_item_id,
                        'sku' => $link->sku,
                        'name' => $link->name,
                        'shop_name' => $link->shop_name,
                        'price' => $link->price,
                        'stock_quantity' => $link->stock_quantity,
                        'status' => $link->status,
                        'marketplace_url' => $link->marketplace_url,
                        'last_synced_at' => $link->last_synced_at?->toISOString(),
                        'created_at' => $link->created_at->toISOString(),
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get product links', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get product links: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlink product
     */
    public function unlinkProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link_id' => 'required|exists:marketplace_product_links,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $link = MarketplaceProductLink::findOrFail($request->get('link_id'));

            $link->update(['status' => MarketplaceProductLink::STATUS_DELETED]);

            Log::channel(config('shopee.logging.channel'))->info('Product unlinked from Shopee', [
                'user_id' => Auth::id(),
                'link_id' => $link->id,
                'product_id' => $link->product_id,
                'marketplace_item_id' => $link->marketplace_item_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product unlinked successfully',
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to unlink product', [
                'user_id' => Auth::id(),
                'link_id' => $request->get('link_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to unlink product: ' . $e->getMessage()
            ], 500);
        }
    }
}
