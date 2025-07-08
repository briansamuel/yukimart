<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuickOrderService
{
    const SESSION_KEY = 'quick_order_data';
    const SESSION_TIMEOUT = 3600; // 1 hour

    /**
     * Process quick order data and prepare for order creation
     *
     * @param array $data
     * @return array
     */
    public function processQuickOrder(array $data): array
    {
        // Validate and process items
        $processedItems = $this->processOrderItems($data['items']);
        
        // Calculate totals
        $totals = $this->calculateOrderTotals($processedItems, $data);

        // Map payment methods
        $paymentMethod = $this->mapPaymentMethod($data['payment_method'] ?? 'cash');

        // Handle empty customer (walk-in customer)
        $customerId = empty($data['customer_id']) || $data['customer_id'] == 0 ? null : $data['customer_id'];

        // Handle payment status and amount
        $paymentStatus = $data['payment_status'] ?? 'paid';
        $amountPaid = $data['amount_paid'] ?? $totals['final_amount'];
        $paymentDate = ($paymentStatus === 'paid' || $paymentStatus === 'partial') ? now() : null;

        // Prepare order data
        $orderData = [
            'customer_id' => $customerId,
            'branch_shop_id' => $data['branch_shop_id'],
            'sold_by' => $data['sold_by'] ?? Auth::id(),
            'channel' => $data['channel'] ?? 'direct',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'payment_date' => $paymentDate,
            'amount_paid' => $amountPaid,
            'status' => 'draft',
            'delivery_status' => 'pending',
            'notes' => $data['notes'] ?? '',
            'total_amount' => $totals['total_amount'],
            'discount_amount' => $totals['discount_amount'],
            'other_amount' => $totals['other_amount'],
            'final_amount' => $totals['final_amount'],
            'created_by_user' => Auth::id(),
            'items' => $processedItems,
            'quick_order' => true,
        ];

        // Clear session after successful processing
        $this->clearSessionData();

        return $orderData;
    }

    /**
     * Map payment method from frontend to database values
     *
     * @param string $method
     * @return string
     */
    protected function mapPaymentMethod(string $method): string
    {
        $mapping = [
            'cash' => 'cash',
            'transfer' => 'transfer',
            'card' => 'card',
            'wallet' => 'e_wallet',
            'e_wallet' => 'e_wallet',
        ];

        return $mapping[$method] ?? 'cash';
    }

    /**
     * Process and validate order items
     *
     * @param array $items
     * @return array
     */
    protected function processOrderItems(array $items): array
    {
        $processedItems = [];
        $productIds = collect($items)->pluck('product_id')->unique();
        
        // Load all products at once for efficiency
        $products = Product::whereIn('id', $productIds)
            ->with(['inventory'])
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            
            if (!$product) {
                throw new \Exception(__('Product not found: ID :id', ['id' => $item['product_id']]));
            }

            // Validate stock availability
            $requestedQuantity = (int) $item['quantity'];
            $availableStock = $product->stock_quantity ?? 0;

            if ($requestedQuantity > $availableStock) {
                throw new \Exception(__('Insufficient stock for :product. Available: :available, Requested: :requested', [
                    'product' => $product->product_name,
                    'available' => $availableStock,
                    'requested' => $requestedQuantity
                ]));
            }

            // Use provided price or product's sale price
            $unitPrice = isset($item['price']) ? (float) $item['price'] : $product->sale_price;
            $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
            $totalPrice = ($unitPrice - $discount) * $requestedQuantity;

            $processedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'quantity' => $requestedQuantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'total_price' => $totalPrice,
                'cost_price' => $product->cost_price,
                'weight' => $product->weight,
            ];
        }

        return $processedItems;
    }

    /**
     * Calculate order totals
     *
     * @param array $items
     * @param array $data
     * @return array
     */
    protected function calculateOrderTotals(array $items, array $data = []): array
    {
        $totalAmount = collect($items)->sum('total_price');
        $itemDiscountAmount = collect($items)->sum(function ($item) {
            return $item['discount'] * $item['quantity'];
        });

        // Get order-level discount and other amount from request
        $orderDiscountAmount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0;
        $otherAmount = isset($data['other_amount']) ? (float) $data['other_amount'] : 0;

        // Total discount is item-level discount + order-level discount
        $totalDiscountAmount = $itemDiscountAmount + $orderDiscountAmount;

        // Final amount = total - discount + other charges
        $finalAmount = $totalAmount - $totalDiscountAmount + $otherAmount;

        return [
            'total_amount' => $totalAmount,
            'discount_amount' => $totalDiscountAmount,
            'other_amount' => $otherAmount,
            'final_amount' => $finalAmount,
        ];
    }

    /**
     * Get session data for quick order
     *
     * @return array
     */
    public function getSessionData(): array
    {
        $sessionData = Session::get(self::SESSION_KEY, []);
        
        // Check if session has expired
        if (isset($sessionData['timestamp'])) {
            $sessionAge = time() - $sessionData['timestamp'];
            if ($sessionAge > self::SESSION_TIMEOUT) {
                $this->clearSessionData();
                return [];
            }
        }

        return $sessionData;
    }

    /**
     * Save session data for quick order
     *
     * @param array $data
     * @return void
     */
    public function saveSessionData(array $data): void
    {
        $sessionData = [
            'items' => $data['items'] ?? [],
            'customer_id' => $data['customer_id'] ?? null,
            'branch_shop_id' => $data['branch_shop_id'] ?? null,
            'notes' => $data['notes'] ?? '',
            'timestamp' => time(),
            'user_id' => Auth::id(),
        ];

        Session::put(self::SESSION_KEY, $sessionData);
        
        Log::debug('Quick order session saved', [
            'user_id' => Auth::id(),
            'items_count' => count($sessionData['items']),
        ]);
    }

    /**
     * Clear session data
     *
     * @return void
     */
    public function clearSessionData(): void
    {
        Session::forget(self::SESSION_KEY);
        
        Log::debug('Quick order session cleared', [
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Validate order data before submission
     *
     * @param array $data
     * @return array
     */
    public function validateOrderData(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Validate customer (optional for walk-in customers)
        if (!empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            if (!$customer) {
                $errors[] = __('Selected customer not found');
            } elseif (!$customer->is_active) {
                $warnings[] = __('Selected customer is inactive');
            }
        }
        // If customer_id is empty/null, treat as walk-in customer (no validation needed)

        // Validate branch shop
        if (empty($data['branch_shop_id'])) {
            $errors[] = __('Branch shop is required');
        } else {
            $branchShop = BranchShop::find($data['branch_shop_id']);
            if (!$branchShop) {
                $errors[] = __('Selected branch shop not found');
            } elseif (!$branchShop->is_active) {
                $warnings[] = __('Selected branch shop is inactive');
            }
        }

        // Validate items
        if (empty($data['items']) || !is_array($data['items'])) {
            $errors[] = __('At least one item is required');
        } else {
            foreach ($data['items'] as $index => $item) {
                $itemErrors = $this->validateOrderItem($item, $index + 1);
                $errors = array_merge($errors, $itemErrors);
            }
        }

        return [
            'valid' => empty($errors),
            'message' => empty($errors) 
                ? __('Order validation passed') 
                : __('Order validation failed'),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate individual order item
     *
     * @param array $item
     * @param int $itemNumber
     * @return array
     */
    protected function validateOrderItem(array $item, int $itemNumber): array
    {
        $errors = [];

        // Validate product
        if (empty($item['product_id'])) {
            $errors[] = __('Product is required for item :number', ['number' => $itemNumber]);
        } else {
            $product = Product::find($item['product_id']);
            if (!$product) {
                $errors[] = __('Product not found for item :number', ['number' => $itemNumber]);
            } else {
                // Check stock
                $quantity = (int) ($item['quantity'] ?? 0);
                $availableStock = $product->stock_quantity ?? 0;
                
                if ($quantity <= 0) {
                    $errors[] = __('Invalid quantity for item :number', ['number' => $itemNumber]);
                } elseif ($quantity > $availableStock) {
                    $errors[] = __('Insufficient stock for :product (Item :number). Available: :available', [
                        'product' => $product->product_name,
                        'number' => $itemNumber,
                        'available' => $availableStock
                    ]);
                }

                // Check price
                $price = (float) ($item['price'] ?? 0);
                if ($price < 0) {
                    $errors[] = __('Invalid price for item :number', ['number' => $itemNumber]);
                }
            }
        }

        return $errors;
    }

    /**
     * Get order statistics for dashboard
     *
     * @return array
     */
    public function getOrderStatistics(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Get orders created through quick order (POS channel)
        $todayOrders = Order::where('channel', 'pos')
            ->whereDate('created_at', $today)
            ->count();

        $todayRevenue = Order::where('channel', 'pos')
            ->whereDate('created_at', $today)
            ->sum('final_amount');

        $weekOrders = Order::where('channel', 'pos')
            ->where('created_at', '>=', $thisWeek)
            ->count();

        $weekRevenue = Order::where('channel', 'pos')
            ->where('created_at', '>=', $thisWeek)
            ->sum('final_amount');

        $monthOrders = Order::where('channel', 'pos')
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $monthRevenue = Order::where('channel', 'pos')
            ->where('created_at', '>=', $thisMonth)
            ->sum('final_amount');

        return [
            'today' => [
                'orders' => $todayOrders,
                'revenue' => $todayRevenue,
                'formatted_revenue' => number_format($todayRevenue, 0, ',', '.') . ' VND',
            ],
            'week' => [
                'orders' => $weekOrders,
                'revenue' => $weekRevenue,
                'formatted_revenue' => number_format($weekRevenue, 0, ',', '.') . ' VND',
            ],
            'month' => [
                'orders' => $monthOrders,
                'revenue' => $monthRevenue,
                'formatted_revenue' => number_format($monthRevenue, 0, ',', '.') . ' VND',
            ],
        ];
    }

    /**
     * Find product by barcode
     *
     * @param string $barcode
     * @return array|null
     */
    public function findProductByBarcode($barcode)
    {
        $product = Product::where('sku', $barcode)
            ->orWhere('barcode', $barcode)
            ->orWhere('product_name', $barcode)
            ->with(['category', 'inventory'])
            ->first();

        if (!$product) {
            return null;
        }

        // Get current stock quantity
        $stockQuantity = $product->inventory ? $product->inventory->quantity : 0;

        return [
            'id' => $product->id,
            'name' => $product->product_name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'price' => $product->sale_price,
            'sale_price' => $product->sale_price,
            'cost_price' => $product->cost_price,
            'stock_quantity' => $stockQuantity,
            'category' => $product->category ? $product->category->name : null,
            'image' => $product->product_thumbnail ? $product->product_thumbnail : null,
            'description' => $product->description,
            'status' => $product->status,
        ];
    }

    /**
     * Search products by name, SKU, or barcode
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchProducts($query, $limit = 10)
    {
        \Log::info('QuickOrderService::searchProducts called', [
            'query' => $query,
            'limit' => $limit
        ]);

        $products = Product::where('product_status', 'publish')
            ->where(function ($q) use ($query) {
                $q->where('product_name', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%")
                  ->orWhere('barcode', 'LIKE', "%{$query}%");
            })
            ->with(['category', 'inventory'])
            ->limit($limit)
            ->get();

        \Log::info('QuickOrderService::searchProducts results', [
            'query' => $query,
            'found_count' => $products->count(),
            'products' => $products->pluck('product_name', 'id')->toArray()
        ]);

        $result = $products->map(function ($product) {
            // Get current stock quantity
            $stockQuantity = $product->inventory ? $product->inventory->quantity : 0;

            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $product->sale_price,
                'sale_price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'stock_quantity' => $stockQuantity,
                'category' => $product->category ? $product->category->name : null,
                'image' => $product->product_thumbnail ? $product->product_thumbnail : null,
                'description' => $product->product_description ?? null,
                'status' => $product->product_status,
            ];
        })->toArray();

        \Log::info('QuickOrderService::searchProducts final result', [
            'result_count' => count($result)
        ]);

        return $result;
    }
}
