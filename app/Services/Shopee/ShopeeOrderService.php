<?php

namespace App\Services\Shopee;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ShopeeToken;
use App\Models\MarketplaceProductLink;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ShopeeOrderService extends ShopeeApiService
{
    /**
     * Get order list from Shopee
     */
    public function getOrderList(ShopeeToken $token, array $params = []): array
    {
        $defaultParams = [
            'time_range_field' => 'create_time',
            'time_from' => now()->subDays(config('shopee.sync.sync_days_back', 7))->timestamp,
            'time_to' => now()->timestamp,
            'page_size' => config('shopee.sync.max_orders_per_sync', 100),
            'cursor' => '',
            'order_status' => 'ALL',
            'response_optional_fields' => [
                'order_status',
                'total_amount',
                'create_time',
                'update_time',
                'payment_method',
                'recipient_address',
                'actual_shipping_fee',
            ],
        ];

        $params = array_merge($defaultParams, $params);

        try {
            $response = $this->makeRequest('order/get_order_list', $params, 'GET', $token);

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to get order list: ' . $this->getErrorMessage($response));
            }

            return $response['response'] ?? [];

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get Shopee order list', [
                'shop_id' => $token->shop_id,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get order detail from Shopee
     */
    public function getOrderDetail(string $orderSn, ShopeeToken $token): array
    {
        $cacheKey = "shopee_order_detail_{$orderSn}_{$token->shop_id}";
        $cached = $this->getCachedResponse($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->makeRequest('order/get_order_detail', [
                'order_sn_list' => [$orderSn],
                'response_optional_fields' => [
                    'order_status',
                    'total_amount',
                    'create_time',
                    'update_time',
                    'payment_method',
                    'recipient_address',
                    'actual_shipping_fee',
                    'goods_to_declare',
                    'note',
                    'note_update_time',
                    'item_list',
                    'pay_time',
                    'dropshipper',
                    'credit_card_number',
                    'dropshipper_phone',
                    'split_up',
                    'buyer_user_id',
                    'buyer_username',
                    'estimated_shipping_fee',
                    'recipient_address',
                    'actual_shipping_fee',
                    'cod',
                    'total_amount',
                    'order_status',
                    'shipping_carrier',
                    'payment_method',
                    'total_amount',
                    'buyer_cancel_reason',
                    'cancel_by',
                    'cancel_reason',
                    'actual_shipping_fee_confirmed',
                    'fulfillment_flag',
                    'pickup_done_time',
                    'package_list',
                    'invoice_data',
                ],
            ], 'GET', $token);

            if (!$this->isSuccessResponse($response)) {
                throw new \Exception('Failed to get order detail: ' . $this->getErrorMessage($response));
            }

            $orderList = $response['response']['order_list'] ?? [];
            $orderDetail = $orderList[0] ?? [];

            $this->cacheResponse($cacheKey, $orderDetail, 600); // Cache for 10 minutes
            return $orderDetail;

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Failed to get Shopee order detail', [
                'order_sn' => $orderSn,
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Sync orders from Shopee
     */
    public function syncOrders(ShopeeToken $token): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            $orderList = $this->getOrderList($token);
            $orders = $orderList['order_list'] ?? [];

            foreach ($orders as $orderBasic) {
                try {
                    $orderSn = $orderBasic['order_sn'];
                    
                    // Check if order already exists
                    if (Order::where('marketplace_order_sn', $orderSn)->exists()) {
                        $results['skipped']++;
                        continue;
                    }

                    // Get detailed order information
                    $orderDetail = $this->getOrderDetail($orderSn, $token);
                    
                    if (empty($orderDetail)) {
                        $results['failed']++;
                        $results['errors'][] = "Failed to get detail for order: {$orderSn}";
                        continue;
                    }

                    // Validate order data
                    if (!$this->validateOrderData($orderDetail)) {
                        $results['failed']++;
                        $results['errors'][] = "Invalid order data for: {$orderSn}";
                        continue;
                    }

                    // Create order in local system
                    $this->createLocalOrder($orderDetail, $token);
                    $results['success']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Error processing order {$orderBasic['order_sn']}: " . $e->getMessage();
                    
                    // Send critical error notification
                    $this->notifyOrderSyncError($orderBasic['order_sn'], $e->getMessage());
                }
            }

            Log::channel(config('shopee.logging.channel'))->info('Shopee order sync completed', [
                'shop_id' => $token->shop_id,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::channel(config('shopee.logging.channel'))->error('Shopee order sync failed', [
                'shop_id' => $token->shop_id,
                'error' => $e->getMessage(),
            ]);
            
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Validate order data
     */
    protected function validateOrderData(array $orderData): bool
    {
        $requiredFields = config('shopee.validation.required_fields.order', [
            'order_sn', 'total_amount', 'create_time'
        ]);

        return $this->validateResponse($orderData, $requiredFields);
    }

    /**
     * Create local order from Shopee data
     */
    protected function createLocalOrder(array $orderData, ShopeeToken $token): Order
    {
        DB::beginTransaction();

        try {
            // Get or create customer
            $customer = $this->getOrCreateCustomer($orderData);

            // Create order
            $order = Order::create([
                'order_code' => $this->generateOrderCode($orderData['order_sn']),
                'customer_id' => $customer->id,
                'branch_shop_id' => config('shopee.order_mapping.default_branch_shop_id'),
                'total_amount' => $orderData['total_amount'],
                'final_amount' => $orderData['total_amount'],
                'payment_method' => $this->mapPaymentMethod($orderData['payment_method'] ?? ''),
                'payment_status' => $this->mapPaymentStatus($orderData),
                'status' => $this->mapOrderStatus($orderData['order_status']),
                'delivery_status' => 'pending',
                'channel' => 'online',
                'notes' => $orderData['note'] ?? '',
                'marketplace_platform' => 'shopee',
                'marketplace_order_id' => $orderData['order_sn'],
                'marketplace_order_sn' => $orderData['order_sn'],
                'marketplace_data' => $orderData,
                'marketplace_created_at' => Carbon::createFromTimestamp($orderData['create_time']),
                'marketplace_status' => $orderData['order_status'],
                'marketplace_shipping_fee' => $orderData['actual_shipping_fee'] ?? 0,
                'marketplace_payment_method' => $orderData['payment_method'] ?? '',
                'is_marketplace_order' => true,
                'created_by_user' => 1, // System user
            ]);

            // Create order items
            $this->createOrderItems($order, $orderData['item_list'] ?? [], $token);

            // Recalculate order totals
            $order->calculateTotals();

            DB::commit();

            Log::channel(config('shopee.logging.channel'))->info('Shopee order created locally', [
                'order_id' => $order->id,
                'order_sn' => $orderData['order_sn'],
                'total_amount' => $orderData['total_amount'],
            ]);

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get or create customer from order data
     */
    protected function getOrCreateCustomer(array $orderData): Customer
    {
        $recipientAddress = $orderData['recipient_address'] ?? [];
        $buyerUsername = $orderData['buyer_username'] ?? 'Shopee Customer';
        
        // Try to find existing customer by phone or name
        $phone = $recipientAddress['phone'] ?? '';
        $name = $recipientAddress['name'] ?? $buyerUsername;

        $customer = null;
        
        if ($phone) {
            $customer = Customer::where('phone', $phone)->first();
        }
        
        if (!$customer && $name !== 'Shopee Customer') {
            $customer = Customer::where('name', $name)->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name' => $name,
                'phone' => $phone,
                'email' => '', // Shopee doesn't provide email
                'address' => $this->formatAddress($recipientAddress),
                'notes' => 'Created from Shopee order',
            ]);
        }

        return $customer;
    }

    /**
     * Create order items from Shopee data
     */
    protected function createOrderItems(Order $order, array $items, ShopeeToken $token): void
    {
        foreach ($items as $item) {
            // Validate item data
            $requiredFields = config('shopee.validation.required_fields.item', [
                'item_id', 'item_name', 'item_sku', 'model_quantity_purchased'
            ]);

            if (!$this->validateResponse($item, $requiredFields)) {
                Log::channel(config('shopee.logging.channel'))->warning('Invalid item data in Shopee order', [
                    'order_sn' => $order->marketplace_order_sn,
                    'item' => $item,
                ]);
                continue;
            }

            // Find linked product
            $product = $this->findLinkedProduct($item, $token);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product?->id,
                'product_name' => $item['item_name'],
                'quantity' => $item['model_quantity_purchased'],
                'unit_price' => $item['model_original_price'] ?? 0,
                'discount' => ($item['model_original_price'] ?? 0) - ($item['model_discounted_price'] ?? 0),
                'total_price' => ($item['model_discounted_price'] ?? 0) * $item['model_quantity_purchased'],
                'marketplace_item_id' => $item['item_id'],
                'marketplace_variation_id' => $item['model_id'] ?? null,
                'marketplace_sku' => $item['item_sku'],
                'marketplace_item_data' => $item,
                'marketplace_price' => $item['model_discounted_price'] ?? 0,
                'marketplace_discount' => ($item['model_original_price'] ?? 0) - ($item['model_discounted_price'] ?? 0),
            ]);

            // Update product inventory if linked
            if ($product && config('shopee.sync.auto_update_inventory')) {
                $product->removeStock(
                    $item['model_quantity_purchased'],
                    'sale',
                    $order,
                    "Shopee order: {$order->marketplace_order_sn}"
                );
            }
        }
    }

    /**
     * Find linked product by Shopee item
     */
    protected function findLinkedProduct(array $item, ShopeeToken $token): ?Product
    {
        // First try to find by marketplace link
        $link = MarketplaceProductLink::where('platform', 'shopee')
            ->where('marketplace_item_id', $item['item_id'])
            ->first();

        if ($link) {
            return $link->product;
        }

        // Try to find by SKU if auto-linking is enabled
        if (config('shopee.product_mapping.auto_link_by_sku') && !empty($item['item_sku'])) {
            $product = Product::where('sku', $item['item_sku'])->first();
            
            if ($product) {
                // Create marketplace link
                try {
                    MarketplaceProductLink::create([
                        'product_id' => $product->id,
                        'platform' => 'shopee',
                        'marketplace_item_id' => $item['item_id'],
                        'sku' => $item['item_sku'],
                        'name' => $item['item_name'],
                        'shop_id' => $token->shop_id,
                        'shop_name' => $token->shop_name,
                        'status' => MarketplaceProductLink::STATUS_ACTIVE,
                        'platform_data' => $item,
                        'last_synced_at' => now(),
                    ]);

                    Log::channel(config('shopee.logging.channel'))->info('Auto-linked product by SKU', [
                        'product_id' => $product->id,
                        'sku' => $item['item_sku'],
                        'shopee_item_id' => $item['item_id'],
                    ]);
                } catch (\Exception $e) {
                    Log::channel(config('shopee.logging.channel'))->warning('Failed to auto-link product', [
                        'sku' => $item['item_sku'],
                        'error' => $e->getMessage(),
                    ]);
                }
                
                return $product;
            }
        }

        return null;
    }

    /**
     * Generate order code
     */
    protected function generateOrderCode(string $orderSn): string
    {
        $prefix = config('shopee.order_mapping.order_prefix', 'SP');
        return $prefix . '-' . $orderSn;
    }

    /**
     * Map Shopee payment method to local payment method
     */
    protected function mapPaymentMethod(string $shopeeMethod): string
    {
        $mapping = config('shopee.order_mapping.payment_method_mapping', []);
        return $mapping[$shopeeMethod] ?? 'other';
    }

    /**
     * Map Shopee order status to local order status
     */
    protected function mapOrderStatus(string $shopeeStatus): string
    {
        $mapping = config('shopee.order_mapping.status_mapping', []);
        return $mapping[$shopeeStatus] ?? 'processing';
    }

    /**
     * Map payment status based on order data
     */
    protected function mapPaymentStatus(array $orderData): string
    {
        $orderStatus = $orderData['order_status'] ?? '';
        $payTime = $orderData['pay_time'] ?? 0;

        if ($payTime > 0) {
            return 'paid';
        }

        if (in_array($orderStatus, ['CANCELLED', 'TO_RETURN'])) {
            return 'unpaid';
        }

        return 'unpaid';
    }

    /**
     * Format address from Shopee data
     */
    protected function formatAddress(array $addressData): string
    {
        $parts = array_filter([
            $addressData['full_address'] ?? '',
            $addressData['district'] ?? '',
            $addressData['city'] ?? '',
            $addressData['state'] ?? '',
        ]);

        return implode(', ', $parts);
    }

    /**
     * Send error notification for order sync
     */
    protected function notifyOrderSyncError(string $orderSn, string $error): void
    {
        if (!config('shopee.notifications.notify_on_error')) {
            return;
        }

        $adminEmail = config('shopee.notifications.admin_email');
        
        if (!$adminEmail) {
            return;
        }

        Log::channel(config('shopee.logging.channel'))->error('Critical Shopee order sync error', [
            'order_sn' => $orderSn,
            'error' => $error,
            'admin_notified' => true,
        ]);

        // You can implement email notification here
        // Mail::to($adminEmail)->send(new ShopeeOrderSyncErrorMail($orderSn, $error));
    }
}
