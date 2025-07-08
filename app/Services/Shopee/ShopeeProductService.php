<?php

namespace App\Services\Shopee;

use App\Models\Product;
use App\Models\MarketplaceProductLink;
use App\Models\ShopeeToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShopeeProductService extends ShopeeApiService
{
    /**
     * Search for products by SKU on Shopee
     */
    public function searchProductsBySku(string $sku, ShopeeToken $token): array
    {
        $cacheKey = "shopee_search_sku_{$sku}_{$token->shop_id}";
        $cached = $this->getCachedResponse($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->makeRequest('product/get_item_list', [
                'offset' => 0,
                'page_size' => 100,
                'item_status' => ['NORMAL', 'BANNED', 'DELETED'],
            ], 'GET', $token);

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to get item list: ' . $this->getErrorMessage($response));
            }

            $items = $response['response']['item'] ?? [];
            $matchingItems = [];

            // Search for items with matching SKU
            foreach ($items as $item) {
                $itemDetail = $this->getItemDetail($item['item_id'], $token);
                
                if ($itemDetail && $this->itemHasSku($itemDetail, $sku)) {
                    $matchingItems[] = $itemDetail;
                }
            }

            $this->cacheResponse($cacheKey, $matchingItems, 1800); // Cache for 30 minutes
            return $matchingItems;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to search products by SKU', [
                'sku' => $sku,
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Get item detail from Shopee
     */
    public function getItemDetail(int $itemId, ShopeeToken $token): ?array
    {
        try {
            $response = $this->makeRequest('product/get_item_base_info', [
                'item_id_list' => [$itemId],
            ], 'GET', $token);

            if (!$this->isSuccessResponse($response)) {
                return null;
            }

            $items = $response['response']['item_list'] ?? [];
            return $items[0] ?? null;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get item detail', [
                'item_id' => $itemId,
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Check if item has specific SKU
     */
    protected function itemHasSku(array $item, string $sku): bool
    {
        // Check main item SKU
        if (isset($item['item_sku']) && $item['item_sku'] === $sku) {
            return true;
        }

        // Check variation SKUs
        if (isset($item['tier_variation'])) {
            foreach ($item['tier_variation'] as $variation) {
                if (isset($variation['variation_sku']) && $variation['variation_sku'] === $sku) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Link local product to Shopee item
     */
    public function linkProduct(Product $product, array $shopeeItem, ShopeeToken $token): MarketplaceProductLink
    {
        try {
            DB::beginTransaction();

            $link = MarketplaceProductLink::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'platform' => MarketplaceProductLink::PLATFORM_SHOPEE,
                    'marketplace_item_id' => $shopeeItem['item_id'],
                ],
                [
                    'sku' => $shopeeItem['item_sku'] ?? $product->sku,
                    'name' => $shopeeItem['item_name'] ?? $product->product_name,
                    'image_url' => $this->getItemImageUrl($shopeeItem),
                    'shop_name' => $token->shop_name,
                    'shop_id' => $token->shop_id,
                    'price' => $this->getItemPrice($shopeeItem),
                    'stock_quantity' => $this->getItemStock($shopeeItem),
                    'status' => MarketplaceProductLink::STATUS_ACTIVE,
                    'platform_data' => $shopeeItem,
                    'last_synced_at' => now(),
                ]
            );

            DB::commit();

            Log::channel(config('shopee.logging.channel'))->info('Product linked to Shopee', [
                'product_id' => $product->id,
                'shopee_item_id' => $shopeeItem['item_id'],
                'sku' => $product->sku,
            ]);

            return $link;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::channel(config('shopee.logging.channel'))->error('Failed to link product', [
                'product_id' => $product->id,
                'shopee_item_id' => $shopeeItem['item_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Create new product on Shopee
     */
    public function createProduct(Product $product, ShopeeToken $token, array $options = []): array
    {
        try {
            $productData = $this->buildProductData($product, $options);
            
            $response = $this->makeRequest('product/add_item', $productData, 'POST', $token);

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to create product: ' . $this->getErrorMessage($response));
            }

            $itemId = $response['response']['item_id'];

            // Create marketplace link
            $this->linkProduct($product, [
                'item_id' => $itemId,
                'item_name' => $product->product_name,
                'item_sku' => $product->sku,
            ], $token);

            Log::channel(config('shopee.logging.channel'))->info('Product created on Shopee', [
                'product_id' => $product->id,
                'shopee_item_id' => $itemId,
                'sku' => $product->sku,
            ]);

            return $response['response'];

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to create product on Shopee', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Build product data for Shopee API
     */
    protected function buildProductData(Product $product, array $options): array
    {
        return [
            'item_name' => $product->product_name,
            'description' => $product->product_description ?? $product->product_content ?? '',
            'item_sku' => $product->sku,
            'category_id' => $options['category_id'] ?? config('shopee.product_mapping.default_category_id'),
            'price' => $product->sale_price,
            'stock' => $product->stock_quantity ?? 0,
            'item_status' => 'NORMAL',
            'dimension' => [
                'package_length' => $product->length ?? 0,
                'package_width' => $product->width ?? 0,
                'package_height' => $product->height ?? 0,
            ],
            'weight' => ($product->weight ?? 0) / 1000, // Convert grams to kg
            'logistics' => $options['logistics'] ?? [],
            'images' => $this->getProductImages($product),
        ];
    }

    /**
     * Get product images for Shopee
     */
    protected function getProductImages(Product $product): array
    {
        $images = [];
        
        if ($product->product_thumbnail) {
            $images[] = $product->product_thumbnail;
        }

        // Add more images if available
        // You can extend this based on your product image structure
        
        return $images;
    }

    /**
     * Get item image URL from Shopee data
     */
    protected function getItemImageUrl(array $item): ?string
    {
        if (isset($item['image']['image_url_list'][0])) {
            return $item['image']['image_url_list'][0];
        }
        
        return null;
    }

    /**
     * Get item price from Shopee data
     */
    protected function getItemPrice(array $item): ?float
    {
        if (isset($item['price_info']['current_price'])) {
            return $item['price_info']['current_price'];
        }
        
        return null;
    }

    /**
     * Get item stock from Shopee data
     */
    protected function getItemStock(array $item): ?int
    {
        if (isset($item['stock_info']['current_stock'])) {
            return $item['stock_info']['current_stock'];
        }
        
        return null;
    }

    /**
     * Sync product inventory to Shopee
     */
    public function syncInventory(MarketplaceProductLink $link, ShopeeToken $token): bool
    {
        try {
            $response = $this->makeRequest('product/update_stock', [
                'item_id' => $link->marketplace_item_id,
                'stock_list' => [
                    [
                        'model_id' => 0, // For simple products
                        'normal_stock' => $link->product->stock_quantity ?? 0,
                    ]
                ],
            ], 'POST', $token);

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to sync inventory: ' . $this->getErrorMessage($response));
            }

            $link->markAsSynced();

            Log::channel(config('shopee.logging.channel'))->info('Inventory synced to Shopee', [
                'product_id' => $link->product_id,
                'shopee_item_id' => $link->marketplace_item_id,
                'stock' => $link->product->stock_quantity,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to sync inventory', [
                'product_id' => $link->product_id,
                'shopee_item_id' => $link->marketplace_item_id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Bulk sync inventory for multiple products
     */
    public function bulkSyncInventory(ShopeeToken $token, int $limit = 50): array
    {
        $links = MarketplaceProductLink::shopee()
            ->active()
            ->needsSync()
            ->with('product')
            ->limit($limit)
            ->get();

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($links as $link) {
            try {
                if ($this->syncInventory($link, $token)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'product_id' => $link->product_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
