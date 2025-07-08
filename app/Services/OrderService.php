<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $order;
    protected $orderItem;
    protected $customer;
    protected $product;
    protected $inventoryService;

    public function __construct(
        Order $order,
        OrderItem $orderItem,
        Customer $customer,
        Product $product,
        InventoryService $inventoryService
    ) {
        $this->order = $order;
        $this->orderItem = $orderItem;
        $this->customer = $customer;
        $this->product = $product;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get all orders with filters and pagination.
     */
    public function getOrders($filters = [], $perPage = 25)
    {
        $query = $this->order->with(['customer', 'branchShop', 'creator', 'seller']);

        // Apply filters
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['delivery_status']) && $filters['delivery_status']) {
            $query->where('delivery_status', $filters['delivery_status']);
        }

        if (isset($filters['payment_status']) && $filters['payment_status']) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['channel']) && $filters['channel']) {
            $query->where('channel', $filters['channel']);
        }

        if (isset($filters['branch_shop_id']) && $filters['branch_shop_id']) {
            $query->where('branch_shop_id', $filters['branch_shop_id']);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['amount_from']) && $filters['amount_from']) {
            $query->where('final_amount', '>=', $filters['amount_from']);
        }

        if (isset($filters['amount_to']) && $filters['amount_to']) {
            $query->where('final_amount', '<=', $filters['amount_to']);
        }

        if (isset($filters['customer']) && $filters['customer']) {
            $query->whereHas('customer', function($customerQuery) use ($filters) {
                $customerQuery->where('name', 'like', '%' . $filters['customer'] . '%')
                             ->orWhere('phone', 'like', '%' . $filters['customer'] . '%');
            });
        }

        // Barcode search filter
        if (isset($filters['Code']) && $filters['Code']) {
            $barcode = $filters['Code'];
            $query->whereHas('orderItems.product', function($q) use ($barcode) {
                $q->where('barcode', $barcode);
            });
        }

        if (isset($filters['search']) && $filters['search']) {
            $query->search($filters['search']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new order.
     */
    public function createOrder($data)
    {
        try {
            // Handle new customer creation if needed
            if (isset($data['new_customer_data']) && !empty($data['new_customer_data'])) {
                $customerResult = $this->createNewCustomer($data['new_customer_data']);
                if (!$customerResult['success']) {
                    return $customerResult;
                }
                $data['customer_id'] = $customerResult['data']['id'];
            }

            DB::beginTransaction();


            // Validate that branch shop is set
            if (empty($data['branch_shop_id'])) {
                return [
                    'success' => false,
                    'message' => 'Vui lòng cài đặt chi nhánh mặc định trong phần cài đặt người dùng.',
                    'data' => null
                ];
            }

            // Customer validation is optional for walk-in customers
            // Skip validation if customer_id is explicitly set to null (walk-in customer)

            // Get customer
            $customerId = null;
            if (isset($data['customer_id']) && $data['customer_id'] > 0) {
                $customer = $this->customer->find($data['customer_id']);
                if (!$customer) {
                    return [
                        'success' => false,
                        'message' => 'Khách hàng không tồn tại'
                    ];
                }
                $customerId = $customer->id;
            } elseif (isset($data['customer_id']) && ($data['customer_id'] == 0 || $data['customer_id'] === null)) {
                // Walk-in customer (Khách lẻ)
                $customerId = null;
            } elseif (isset($data['customer']) && is_array($data['customer'])) {
                $customer = $this->getOrCreateCustomer($data['customer']);
                $customerId = $customer->id;
            } elseif (!isset($data['customer_id']) && !isset($data['customer'])) {
                // No customer data provided, treat as walk-in customer
                $customerId = null;
            } else {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu khách hàng không hợp lệ'
                ];
            }
         
            // Generate order code
            $orderCode = Order::generateOrderCode();
          
            // Create order with notifications temporarily disabled
            $order = new \App\Models\Order([
                'order_code' => $orderCode,
                'customer_id' => $customerId,
                'branch_shop_id' => $data['branch_shop_id'] ?? null,
                'created_by' => Auth::id(),
                'sold_by' => $data['sold_by'] ?? Auth::id(),
                'channel' => $data['channel'] ?? 'direct',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'payment_date' => $data['payment_date'] ?? null,
                'total_quantity' => 0,
                'total_amount' => 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'other_amount' => $data['other_amount'] ?? 0,
                'shipping_fee' => $data['shipping_fee'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'final_amount' => 0,
                'amount_paid' => $data['amount_paid'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'delivery_status' => $data['delivery_status'] ?? 'pending',
                'note' => $data['notes'] ?? $data['note'] ?? null,
            ]);

            // Disable notifications temporarily
            $order->disableNotifications();
            $order->save();

            // Add order items
            if (isset($data['items'])) {
                $order_items = is_array($data['items']) ? $data['items'] : json_decode($data['items'], true);
                foreach ($order_items as $itemData) {
                    $this->addOrderItem($order, $itemData);
                }
            }

            // Calculate totals (this will also trigger update events, so keep notifications disabled)
            $order->calculateTotals();

            // Create inventory transactions for sale
            $this->createSaleInventoryTransactions($order);

            // Re-enable notifications and trigger ONLY the creation notification with correct amounts
            $order->enableNotifications();
            // Load relationships needed for notification
            $order->load(['customer', 'orderItems', 'seller', 'branchShop']);
            $order->createNotificationForEvent('created');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => $order->load(['customer', 'orderItems.product'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            // Log detailed error for debugging
            Log::error('OrderService::createOrder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại sau.'
            ];
        }
    }

    /**
     * Update an existing order.
     */
    public function updateOrder($orderId, $data)
    {
        try {
            DB::beginTransaction();

            $order = $this->order->findOrFail($orderId);

            // Update customer if provided
            if (isset($data['customer'])) {
                $customer = $this->getOrCreateCustomer($data['customer']);
                $data['customer_id'] = $customer->id;
                unset($data['customer']);
            }

            // Update order
            $order->update($data);

            // Update order items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $order->orderItems()->delete();

                // Add new items
                foreach ($data['items'] as $itemData) {
                    $this->addOrderItem($order, $itemData);
                }

                // Recalculate totals
                $order->calculateTotals();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Đơn hàng đã được cập nhật thành công',
                'data' => $order->load(['customer', 'orderItems.product'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete an order.
     */
    public function deleteOrder($orderId)
    {
        try {
            $order = $this->order->findOrFail($orderId);
            
            // Check if order can be deleted
            if ($order->status === 'completed') {
                return [
                    'success' => false,
                    'message' => 'Không thể xóa đơn hàng đã hoàn thành'
                ];
            }

            $order->delete();

            return [
                'success' => true,
                'message' => 'Đơn hàng đã được xóa thành công'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi xóa đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get detailed order information for order detail page
     */
    public function getOrderDetail($id)
    {
        $order = $this->order->with([
            'customer',
            'branchShop',
            'creator',
            'seller',
            'orderItems.product',
            'orderItems.product.category',
            'orderItems.product.inventory'
        ])->find($id);

        if (!$order) {
            return null;
        }

        // Calculate additional fields
        $order->remaining_amount = $order->final_amount - $order->amount_paid;
        $order->payment_percentage = $order->final_amount > 0 ?
            round(($order->amount_paid / $order->final_amount) * 100, 2) : 0;

        // Calculate order statistics
        $order->total_items = $order->orderItems->count();
        $order->total_products = $order->orderItems->sum('quantity');

        // Add status information
        $order->status_info = $this->getStatusInfo($order);

        // Add timeline information
        $order->timeline = $this->getOrderTimeline($order);

        // Calculate profit information
        $order->profit_info = $this->calculateOrderProfit($order);

        return $order;
    }

    /**
     * Get order by ID.
     */
    public function getOrderById($orderId)
    {
        return $this->order->with([
                'customer',
                'branchShop',
                'creator',
                'seller',
                'orderItems.product',
                'orderItems.product.category'
            ])
            ->findOrFail($orderId);
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus($orderId, $status, $deliveryStatus = null, $paymentStatus = null, $internalNotes = null)
    {
        try {
            $order = $this->order->findOrFail($orderId);

            $updateData = ['status' => $status];
            if ($deliveryStatus) {
                $updateData['delivery_status'] = $deliveryStatus;
            }
            if ($paymentStatus) {
                $updateData['payment_status'] = $paymentStatus;
            }
            if ($internalNotes !== null) {
                $updateData['internal_notes'] = $internalNotes;
            }

            $order->update($updateData);

            return [
                'success' => true,
                'message' => 'Trạng thái đơn hàng đã được cập nhật',
                'data' => $order
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record payment for order.
     */
    public function recordPayment($orderId, $paymentData)
    {
        try {
            DB::beginTransaction();

            $order = $this->order->findOrFail($orderId);

            // Update order payment information
            $newAmountPaid = $order->amount_paid + $paymentData['amount'];
            $order->amount_paid = $newAmountPaid;

            // Update payment status based on amount
            if ($newAmountPaid >= $order->final_amount) {
                $order->payment_status = $newAmountPaid > $order->final_amount ? 'overpaid' : 'paid';
            } else {
                $order->payment_status = $newAmountPaid > 0 ? 'partial' : 'unpaid';
            }

            // Update payment method and reference if provided
            if (isset($paymentData['payment_method'])) {
                $order->payment_method = $paymentData['payment_method'];
            }
            if (isset($paymentData['payment_reference'])) {
                $order->payment_reference = $paymentData['payment_reference'];
            }
            if (isset($paymentData['payment_notes'])) {
                $order->payment_notes = $paymentData['payment_notes'];
            }

            $order->payment_date = now();
            $order->save();

            // TODO: Create payment transaction record if needed
            // PaymentTransaction::create([...]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Đã ghi nhận thanh toán thành công',
                'data' => $order
            ];

        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Lỗi khi ghi nhận thanh toán: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Quick update order for common actions.
     */
    public function quickUpdateOrder($orderId, $updateData)
    {
        try {
            $order = $this->order->findOrFail($orderId);

            $order->update($updateData);

            return [
                'success' => true,
                'message' => 'Đơn hàng đã được cập nhật thành công',
                'data' => $order
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật đơn hàng: ' . $e->getMessage()
            ];
        }
    }



    /**
     * Get order statistics.
     */
    public function getOrderStatistics($filters = [])
    {
        return Order::getStatistics($filters);
    }

    /**
     * Get or create customer.
     */
    private function getOrCreateCustomer($customerData)
    {
        if (isset($customerData['id']) && $customerData['id']) {
            return $this->customer->findOrFail($customerData['id']);
        }

        // Create new customer
        return $this->customer->create([
            'name' => $customerData['name'],
            'phone' => $customerData['phone'] ?? null,
            'email' => $customerData['email'] ?? null,
            'address' => $customerData['address'] ?? null,
        ]);
    }

    /**
     * Add order item to order.
     */
    private function addOrderItem($order, $itemData)
    {
        $product = $this->product->findOrFail($itemData['product_id']);

        return $this->orderItem->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'] ?? $product->sale_price,
            'discount' => $itemData['discount'] ?? 0,
            'total_price' => 0, // Will be calculated automatically
        ]);
    }

    /**
     * Get customers for dropdown.
     */
    public function getCustomersForDropdown($search = '')
    {
        $query = $this->customer->select('id', 'name', 'phone', 'email', 'address', 'customer_type')
                                ->where('status', 'active');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')
                    ->limit(50)
                    ->get()
                    ->map(function($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'address' => $customer->address,
                            'customer_type' => $customer->customer_type,
                            'display_text' => $customer->name . ' - ' . $customer->phone . ($customer->email ? ' (' . $customer->email . ')' : ''),
                            'search_text' => $customer->name . ' ' . $customer->phone . ' ' . $customer->email
                        ];
                    });
    }

    /**
     * Get products for order.
     */
    public function getProductsForOrder($search = '')
    {
        // Get user's default branch shop and its warehouse
        $defaultBranchShopId = Auth::user()->getSetting('default_branch_shop');
        $warehouseId = null;

        if ($defaultBranchShopId) {
            $branchShop = \App\Models\BranchShop::find($defaultBranchShopId);
            if ($branchShop && $branchShop->warehouse_id) {
                $warehouseId = $branchShop->warehouse_id;
            }
        }

        $query = $this->product->select('id', 'product_name', 'sku', 'sale_price', 'product_thumbnail', 'product_status')
                              ->with(['inventory' => function($q) use ($warehouseId) {
                                  $q->select('product_id', 'quantity', 'warehouse_id');
                                  if ($warehouseId) {
                                      $q->where('warehouse_id', $warehouseId);
                                  }
                              }]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // If warehouse is specified, only show products that have inventory in that warehouse
        if ($warehouseId) {
            $query->whereHas('inventory', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->where('quantity', '>', 0);
            });
        }

        return $query->where('product_status', 'publish')
                    ->orderBy('product_name')
                    ->limit(100)
                    ->get()
                    ->map(function($product) {
                        $stockQuantity = $product->inventory ? $product->inventory->quantity : 0;
                        $stockStatus = $stockQuantity > 10 ? 'in_stock' : ($stockQuantity > 0 ? 'low_stock' : 'out_of_stock');

                        return [
                            'id' => $product->id,
                            'name' => $product->product_name,
                            'sku' => $product->sku,
                            'price' => $product->sale_price,
                            'stock_quantity' => $stockQuantity,
                            'stock_status' => $stockStatus,
                            'image' => $product->product_thumbnail,
                            'display_text' => $product->product_name . ' - ' . $product->sku . ' (Tồn: ' . $stockQuantity . ')',
                            'search_text' => $product->product_name . ' ' . $product->sku,
                            'formatted_price' => number_format($product->sale_price, 0, ',', '.') . ' ₫'
                        ];
                    });
    }

    /**
     * Get initial data for order creation page (customers and products).
     */
    public function getInitialOrderData()
    {
        try {
            // Get recent customers (last 20)
            $recentCustomers = $this->customer->select('id', 'name', 'phone', 'email')
                                             ->where('status', 'active')
                                             ->orderBy('created_at', 'desc')
                                             ->limit(20)
                                             ->get()
                                             ->map(function($customer) {
                                                 return [
                                                     'id' => $customer->id,
                                                     'text' => $customer->name . ' - ' . $customer->phone,
                                                     'name' => $customer->name,
                                                     'phone' => $customer->phone,
                                                     'email' => $customer->email
                                                 ];
                                             });

            // Get popular products (top 30 by sales)
            $popularProducts = $this->product->select('products.id', 'products.product_name', 'products.sku', 'products.sale_price')
                                            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                                            ->with(['inventory' => function($q) {
                                                $q->select('product_id', 'quantity');
                                            }])
                                            ->where('products.product_status', 'publish')
                                            ->groupBy('products.id', 'products.product_name', 'products.sku', 'products.sale_price')
                                            ->orderByRaw('COUNT(order_items.id) DESC')
                                            ->limit(30)
                                            ->get()
                                            ->map(function($product) {
                                                $stockQuantity = $product->inventory ? $product->inventory->quantity : 0;
                                                return [
                                                    'id' => $product->id,
                                                    'text' => $product->product_name . ' - ' . $product->sku . ' (Tồn: ' . $stockQuantity . ')',
                                                    'name' => $product->product_name,
                                                    'sku' => $product->sku,
                                                    'price' => $product->sale_price,
                                                    'stock' => $stockQuantity
                                                ];
                                            });

            return [
                'success' => true,
                'data' => [
                    'recent_customers' => $recentCustomers,
                    'popular_products' => $popularProducts
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu ban đầu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get product details for order item.
     */
    public function getProductDetails($productId)
    {
        try {
            $product = $this->product->select('id', 'product_name', 'sku', 'sale_price', 'product_image')
                                    ->with(['inventory' => function($q) {
                                        $q->select('product_id', 'quantity');
                                    }])
                                    ->where('id', $productId)
                                    ->where('product_status', 'publish')
                                    ->first();

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa'
                ];
            }

            $stockQuantity = $product->inventory ? $product->inventory->quantity : 0;

            return [
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'sku' => $product->sku,
                    'price' => $product->sale_price,
                    'stock_quantity' => $stockQuantity,
                    'image' => $product->product_image,
                    'formatted_price' => number_format($product->sale_price, 0, ',', '.') . ' ₫'
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi tải thông tin sản phẩm: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new customer during order creation.
     */
    public function createNewCustomer($customerData)
    {
        try {
            // Validate required fields
            if (empty($customerData['name']) || empty($customerData['phone'])) {
                return [
                    'success' => false,
                    'message' => 'Tên và số điện thoại khách hàng là bắt buộc'
                ];
            }

            // Check if phone number already exists
            $existingCustomer = $this->customer->where('phone', $customerData['phone'])->first();
            if ($existingCustomer) {
                return [
                    'success' => false,
                    'message' => 'Số điện thoại đã tồn tại trong hệ thống',
                    'existing_customer' => [
                        'id' => $existingCustomer->id,
                        'name' => $existingCustomer->name,
                        'phone' => $existingCustomer->phone,
                        'email' => $existingCustomer->email
                    ]
                ];
            }

            // Create new customer
            $customer = $this->customer->create([
                'name' => trim($customerData['name']),
                'phone' => trim($customerData['phone']),
                'email' => !empty($customerData['email']) ? trim($customerData['email']) : null,
                'address' => !empty($customerData['address']) ? trim($customerData['address']) : null,
                'customer_type' => $customerData['customer_type'] ?? 'individual',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Khách hàng mới đã được tạo thành công',
                'data' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'customer_type' => $customer->customer_type,
                    'display_text' => $customer->name . ' - ' . $customer->phone . ($customer->email ? ' (' . $customer->email . ')' : '')
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi tạo khách hàng mới: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate customer data for order creation.
     */
    public function validateCustomerData($customerData)
    {
        $errors = [];

        // Validate name
        if (empty($customerData['name']) || strlen(trim($customerData['name'])) < 2) {
            $errors['name'] = 'Tên khách hàng phải có ít nhất 2 ký tự';
        }

        // Validate phone
        if (empty($customerData['phone'])) {
            $errors['phone'] = 'Số điện thoại là bắt buộc';
        } elseif (!preg_match('/^[0-9+\-\s\(\)]{10,15}$/', $customerData['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ';
        }

        // Validate email if provided
        if (!empty($customerData['email']) && !filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Delete multiple orders.
     */
    public function deleteMultipleOrders($orderIds)
    {
        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $errors = [];

            foreach ($orderIds as $orderId) {
                try {
                    $order = $this->order->findOrFail($orderId);

                    // Check if order can be deleted
                    if ($order->status === 'completed') {
                        $errors[] = "Đơn hàng {$order->order_code} đã hoàn thành, không thể xóa";
                        continue;
                    }

                    $order->delete();
                    $deletedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Lỗi khi xóa đơn hàng ID {$orderId}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Đã xóa {$deletedCount} đơn hàng thành công";
            if (!empty($errors)) {
                $message .= ". Lỗi: " . implode(', ', $errors);
            }

            return [
                'success' => $deletedCount > 0,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Lỗi khi xóa đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create inventory transactions for sale when order is created.
     */
    public function createSaleInventoryTransactions($order)
    {
     
        try {
            foreach ($order->orderItems as $item) {
                // Get current stock before transaction
                $product = $item->product;
                $oldQuantity = $product->inventory ? $product->inventory->quantity : 0;

                // Create inventory transaction with transaction_type = 'sale' (without notifications)
                $transaction = new InventoryTransaction([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $order->branch_id, // Use branch as warehouse
                    'transaction_type' => 'sale',
                    'quantity' => -$item->quantity, // Negative for sale (stock decrease)
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $oldQuantity - $item->quantity,
                    'unit_cost' => $item->unit_price,
                    'total_value' => $item->total_price,
                    'reference_type' => 'App\\Models\\Order',
                    'warehouse_id' => 1,
                    'reference_id' => $order->id,
                    'notes' => "Bán hàng - Đơn hàng {$order->order_code}",
                    'created_by_user' => Auth::id(),
                    // 'transaction_date' => now()
                ]);

                // Disable notifications for inventory transaction to avoid duplicate notifications
                $transaction->disableNotifications();
                $transaction->save();
             
                // Update inventory quantity
                if ($product->inventory) {
                    $product->inventory->decrement('quantity', $item->quantity);
                } else {
                    // Create inventory record if not exists
                    $product->inventory()->create([
                        'quantity' => -$item->quantity,
                        'reserved_quantity' => 0
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Đã tạo giao dịch bán hàng thành công'
            ];

        } catch (\Exception $e) {
            dd($e);
            return [
                'success' => false,
                'message' => 'Lỗi khi tạo giao dịch bán hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create inventory transaction when order is confirmed (legacy method).
     */
    public function createInventoryTransaction($order)
    {
        try {
            foreach ($order->orderItems as $item) {
                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'transaction_type' => 'export',
                    'quantity' => -$item->quantity, // Negative for export
                    'unit_cost' => $item->unit_price,
                    'total_value' => $item->total_price,
                    'reference_type' => 'App\\Models\\Order',
                    'reference_id' => $order->id,
                    'notes' => "Xuất kho cho đơn hàng {$order->order_code}",
                    'created_by_user' => Auth::id(),
                    'transaction_date' => now()
                ]);

                // Update product stock
                $product = $item->product;
                if ($product->inventory) {
                    $product->inventory->decrement('quantity', $item->quantity);
                }
            }

            return [
                'success' => true,
                'message' => 'Đã tạo giao dịch tồn kho thành công'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi tạo giao dịch tồn kho: ' . $e->getMessage()
            ];
        }
    }



    /**
     * Reverse inventory transaction when order is cancelled.
     */
    public function reverseInventoryTransaction($order)
    {
        try {
            foreach ($order->orderItems as $item) {
                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'transaction_type' => 'import',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_value' => $item->total_price,
                    'reference_type' => 'order_cancel',
                    'reference_id' => $order->id,
                    'notes' => "Hoàn trả kho do hủy đơn hàng {$order->order_code}",
                    'created_by' => Auth::id(),
                    'transaction_date' => now()
                ]);

                // Update product stock
                $product = $item->product;
                $product->increment('stock_quantity', $item->quantity);
            }

            return [
                'success' => true,
                'message' => 'Đã hoàn trả tồn kho thành công'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi hoàn trả tồn kho: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get status information for order
     */
    private function getStatusInfo($order)
    {
        $statusMap = [
            'pending' => ['label' => 'Chờ xử lý', 'class' => 'warning', 'icon' => 'clock'],
            'processing' => ['label' => 'Đang xử lý', 'class' => 'info', 'icon' => 'gear'],
            'shipped' => ['label' => 'Đã giao hàng', 'class' => 'primary', 'icon' => 'truck'],
            'delivered' => ['label' => 'Đã giao', 'class' => 'success', 'icon' => 'check'],
            'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger', 'icon' => 'cross'],
            'completed' => ['label' => 'Hoàn thành', 'class' => 'success', 'icon' => 'check-circle']
        ];

        $paymentStatusMap = [
            'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'danger'],
            'partial' => ['label' => 'Thanh toán một phần', 'class' => 'warning'],
            'paid' => ['label' => 'Đã thanh toán', 'class' => 'success'],
            'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'info']
        ];

        $deliveryStatusMap = [
            'pending' => ['label' => 'Chờ giao hàng', 'class' => 'warning'],
            'preparing' => ['label' => 'Đang chuẩn bị', 'class' => 'info'],
            'shipped' => ['label' => 'Đã giao hàng', 'class' => 'primary'],
            'delivered' => ['label' => 'Đã giao', 'class' => 'success'],
            'failed' => ['label' => 'Giao hàng thất bại', 'class' => 'danger']
        ];

        return [
            'status' => $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'secondary', 'icon' => 'question'],
            'payment_status' => $paymentStatusMap[$order->payment_status] ?? ['label' => $order->payment_status, 'class' => 'secondary'],
            'delivery_status' => $deliveryStatusMap[$order->delivery_status] ?? ['label' => $order->delivery_status, 'class' => 'secondary']
        ];
    }

    /**
     * Get order timeline
     */
    private function getOrderTimeline($order)
    {
        $timeline = [];

        // Order created
        $timeline[] = [
            'title' => 'Đơn hàng được tạo',
            'description' => 'Đơn hàng ' . $order->order_code . ' được tạo bởi ' . ($order->creator->name ?? 'Hệ thống'),
            'date' => $order->created_at,
            'type' => 'success',
            'icon' => 'plus'
        ];

        // Payment events
        if ($order->amount_paid > 0) {
            $timeline[] = [
                'title' => 'Ghi nhận thanh toán',
                'description' => 'Đã thanh toán ' . number_format($order->amount_paid, 0, ',', '.') . '₫',
                'date' => $order->payment_date ?? $order->updated_at,
                'type' => 'primary',
                'icon' => 'wallet'
            ];
        }

        // Status changes (this would need to be tracked in a separate table in real implementation)
        if ($order->status === 'completed') {
            $timeline[] = [
                'title' => 'Đơn hàng hoàn thành',
                'description' => 'Đơn hàng đã được hoàn thành thành công',
                'date' => $order->updated_at,
                'type' => 'success',
                'icon' => 'check-circle'
            ];
        }

        return collect($timeline)->sortBy('date')->values()->all();
    }

    /**
     * Calculate order profit information
     */
    private function calculateOrderProfit($order)
    {
        $totalCost = 0;
        $totalRevenue = $order->final_amount;

        foreach ($order->orderItems as $item) {
            if ($item->product && $item->product->cost_price) {
                $totalCost += $item->product->cost_price * $item->quantity;
            }
        }

        $profit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        return [
            'total_cost' => $totalCost,
            'total_revenue' => $totalRevenue,
            'profit' => $profit,
            'profit_margin' => round($profitMargin, 2),
            'profit_status' => $profit > 0 ? 'profitable' : ($profit < 0 ? 'loss' : 'break_even')
        ];
    }

    /**
     * Update order status and handle inventory changes.
     */
    public function updateOrderStatusWithInventory($orderId, $newStatus)
    {
        try {
            DB::beginTransaction();

            $order = $this->order->findOrFail($orderId);
            $oldStatus = $order->status;

            // Update order status
            $order->status = $newStatus;
            $order->save();

            // Handle inventory changes
            $inventoryResult = $this->inventoryService->updateInventoryForOrder($order, $oldStatus);
            if (!$inventoryResult['success']) {
                throw new \Exception($inventoryResult['message']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Trạng thái đơn hàng đã được cập nhật',
                'data' => $order
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order status', [
                'order_id' => $orderId,
                'old_status' => $oldStatus ?? null,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái đơn hàng: ' . $e->getMessage()
            ];
        }
    }
}
