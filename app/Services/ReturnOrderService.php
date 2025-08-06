<?php

namespace App\Services;

use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\Inventory;
use App\Services\PaymentService;
use App\Services\InventoryService;
use App\Services\BaseQuickOrderService;
use App\Services\PrefixGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReturnOrderService extends BaseQuickOrderService
{
    protected $paymentService;
    protected $inventoryService;

    public function __construct(PaymentService $paymentService, InventoryService $inventoryService)
    {
        $this->paymentService = $paymentService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create return order from invoice.
     */
    public function createReturnOrder(array $data)
    {
        try {
            DB::beginTransaction();

            // Validate invoice exists
            $invoice = Invoice::findOrFail($data['invoice_id']);

            // Create return order with completed status
            $returnOrder = ReturnOrder::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $data['branch_shop_id'] ?? $invoice->branch_shop_id,
                'return_date' => $data['return_date'] ?? now(),
                'reason' => $data['reason'] ?? 'customer_request',
                'reason_detail' => $data['reason_detail'] ?? null,
                'refund_method' => $data['refund_method'] ?? 'cash',
                'status' => 'completed', // Set status to completed for immediate processing
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create return order items with grouping
            if (isset($data['items']) && is_array($data['items'])) {
                $this->createReturnOrderItems($returnOrder, $data['items']);
            }

            // Calculate totals
            $returnOrder->calculateTotals();

            // Create inventory transactions for return order
            $this->createInventoryTransactions($returnOrder);

            DB::commit();

            Log::info('Return order created successfully', ['return_order_id' => $returnOrder->id]);
            
            return [
                'success' => true,
                'message' => 'Đơn trả hàng đã được tạo thành công',
                'data' => $returnOrder->load(['returnOrderItems.product', 'customer', 'invoice'])
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create return order', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể tạo đơn trả hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create inventory transactions for return order.
     * - Return items: increase stock (import)
     * - Exchange items: decrease stock (sale)
     */
    private function createInventoryTransactions(ReturnOrder $returnOrder)
    {
        try {
            Log::info('Creating inventory transactions for return order', [
                'return_order_id' => $returnOrder->id,
                'return_number' => $returnOrder->return_number
            ]);

            // Load return order items with products
            $returnOrder->load(['returnOrderItems.product', 'branchShop']);

            foreach ($returnOrder->returnOrderItems as $item) {
                $product = $item->product;

                if (!$product) {
                    Log::warning('Product not found for return order item', [
                        'return_order_item_id' => $item->id,
                        'product_id' => $item->product_id
                    ]);
                    continue;
                }

                // Get current inventory
                $inventory = Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $returnOrder->branch_shop_id ?? 1)
                    ->first();

                if (!$inventory) {
                    // Create inventory record if not exists
                    $inventory = Inventory::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $returnOrder->branch_shop_id ?? 1,
                        'quantity' => 0
                    ]);
                    Log::info('Created new inventory record', [
                        'product_id' => $product->id,
                        'warehouse_id' => $returnOrder->branch_shop_id ?? 1
                    ]);
                }

                $oldQuantity = $inventory->quantity;

                // Only process return items - exchange items are handled by InventoryService
                // Use quantity_returned field for return order items
                $quantity = $item->quantity_returned ?? 0;

                if ($quantity > 0) {
                    // Return item: increase stock (return)
                    $newQuantity = $oldQuantity + $quantity;

                    // Update inventory
                    $inventory->update(['quantity' => $newQuantity]);

                    // Create inventory transaction with return type
                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $returnOrder->branch_shop_id ?? 1,
                        'transaction_type' => InventoryTransaction::TYPE_RETURN,
                        'quantity' => $quantity, // Positive for return (stock increase)
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'unit_cost' => $item->unit_price,
                        'total_value' => $item->line_total ?? ($item->unit_price * $quantity),
                        'reference_type' => 'App\\Models\\ReturnOrder',
                        'reference_id' => $returnOrder->id,
                        'notes' => "Trả hàng - {$returnOrder->return_number} - {$product->product_name}",
                        'created_by_user' => Auth::id(),
                    ]);

                    Log::info('Created return inventory transaction', [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity
                    ]);
                } else {
                    Log::info('Skipped return item with zero quantity', [
                        'return_order_item_id' => $item->id,
                        'product_id' => $product->id
                    ]);
                }
            }

            Log::info('Successfully created all inventory transactions for return order', [
                'return_order_id' => $returnOrder->id
            ]);

        } catch (Exception $e) {
            Log::error('Failed to create inventory transactions for return order', [
                'return_order_id' => $returnOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception to prevent return order creation failure
            // Just log the error and continue
        }
    }

    /**
     * Create return order from Quick Order.
     */
    public function createQuickOrderReturn(array $data)
    {
        try {
            Log::info('=== STARTING RETURN ORDER CREATION ===', [
                'input_data' => $data,
                'user_id' => Auth::id()
            ]);

            // Step 1: Validate required fields
            if (!isset($data['invoice_id'])) {
                Log::error('Invoice ID missing');
                throw new Exception('Invoice ID is required');
            }
            Log::info('✓ Invoice ID validation passed');

            // Step 2: Get invoice
            Log::info('Fetching invoice...', ['invoice_id' => $data['invoice_id']]);
            $invoice = Invoice::findOrFail($data['invoice_id']);
            Log::info('✓ Invoice found', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $invoice->branch_shop_id
            ]);

            // Step 3: Test database connection
            Log::info('Testing database connection...');
            $testCount = DB::table('return_orders')->count();
            Log::info('✓ Database connection OK', ['existing_returns_count' => $testCount]);

            // Step 4: Begin transaction
            Log::info('Starting database transaction...');
            DB::beginTransaction();
            Log::info('✓ Transaction started');

            // Step 5: Create return order step by step
            Log::info('Creating return order object...');
            $returnOrder = new ReturnOrder();
            Log::info('✓ Return order object created');

            Log::info('Setting return order fields...');
            $returnOrder->invoice_id = $invoice->id;
            $returnOrder->customer_id = $invoice->customer_id;
            $returnOrder->branch_shop_id = $invoice->branch_shop_id ?? 1;
            $returnOrder->return_date = now();
            $returnOrder->reason = 'customer_request';
            $returnOrder->refund_method = 'cash';
            $returnOrder->subtotal = 0;
            $returnOrder->total_amount = 0;
            $returnOrder->status = 'completed'; // Set status to completed for immediate processing
            $returnOrder->created_by = Auth::id();
            Log::info('✓ Return order fields set', [
                'invoice_id' => $returnOrder->invoice_id,
                'customer_id' => $returnOrder->customer_id,
                'branch_shop_id' => $returnOrder->branch_shop_id,
                'created_by' => $returnOrder->created_by
            ]);

            Log::info('Saving return order to database...');
            $returnOrder->save();
            Log::info('✓ Return order saved successfully', [
                'return_order_id' => $returnOrder->id,
                'return_number' => $returnOrder->return_number
            ]);

            // Create return order items
            if (isset($data['return_items']) && is_array($data['return_items'])) {
                Log::info('Creating return order items', ['items_count' => count($data['return_items'])]);
                $this->createQuickOrderReturnItems($returnOrder, $data['return_items'], $invoice);
                Log::info('Return order items created successfully');
            }

            // Handle exchange items if any
            $exchangeInvoice = null;
            if (isset($data['exchange_items']) && is_array($data['exchange_items']) && count($data['exchange_items']) > 0) {
                Log::info('Creating exchange order with HDD_TH invoice', ['exchange_items_count' => count($data['exchange_items'])]);
                $exchangeInvoice = $this->createExchangeOrder($returnOrder, $data);
                Log::info('Exchange order created successfully', [
                    'exchange_invoice_id' => $exchangeInvoice->id,
                    'exchange_invoice_number' => $exchangeInvoice->invoice_number
                ]);
            }

            // Create payment record if needed
            if (isset($data['net_payable']) && $data['net_payable'] > 0 && $exchangeInvoice) {
                $this->createExchangePayment($exchangeInvoice, $data);
            }

            // Create inventory transactions for return order
            $this->createInventoryTransactions($returnOrder);

            DB::commit();

            Log::info('Quick Order return created successfully', [
                'return_order_id' => $returnOrder->id,
                'exchange_invoice_id' => $exchangeInvoice ? $exchangeInvoice->id : null
            ]);

            return [
                'success' => true,
                'message' => 'Trả hàng thành công',
                'data' => [
                    'return_order' => $returnOrder->load(['returnOrderItems.product', 'customer', 'invoice']),
                    'exchange_invoice' => $exchangeInvoice ? $exchangeInvoice->load(['invoiceItems.product']) : null,
                    'return_order_code' => $returnOrder->return_number,
                    'redirect_url' => route('admin.return.show', $returnOrder->id)
                ]
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create Quick Order return', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Không thể tạo đơn trả hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create return order items with grouping logic.
     */
    private function createReturnOrderItems(ReturnOrder $returnOrder, array $items)
    {
        // Group items by product_id and unit_price to merge duplicates
        $groupedItems = [];

        foreach ($items as $itemData) {
            $invoiceItem = InvoiceItem::findOrFail($itemData['invoice_item_id']);
            $unitPrice = $itemData['unit_price'] ?? $invoiceItem->unit_price;

            // Create grouping key based on product_id and unit_price
            $groupKey = $invoiceItem->product_id . '_' . $unitPrice;

            if (!isset($groupedItems[$groupKey])) {
                $groupedItems[$groupKey] = [
                    'invoice_item_id' => $invoiceItem->id,
                    'product_id' => $invoiceItem->product_id,
                    'product_name' => $itemData['product_name'] ?? $invoiceItem->product_name,
                    'product_sku' => $itemData['product_sku'] ?? $invoiceItem->product_sku,
                    'unit_price' => $unitPrice,
                    'quantity_returned' => 0,
                    'condition' => $itemData['condition'] ?? 'new',
                    'notes' => $itemData['notes'] ?? null,
                ];
            }

            // Add quantity to grouped item
            $groupedItems[$groupKey]['quantity_returned'] += $itemData['quantity_returned'];

            // Merge notes if different
            if (!empty($itemData['notes']) && $groupedItems[$groupKey]['notes'] !== $itemData['notes']) {
                $groupedItems[$groupKey]['notes'] = trim($groupedItems[$groupKey]['notes'] . '; ' . $itemData['notes'], '; ');
            }
        }

        // Create return order items from grouped data
        $sortOrder = 0;
        foreach ($groupedItems as $groupedItem) {
            $this->createReturnOrderItem($returnOrder, $groupedItem, $sortOrder++);
        }
    }

    /**
     * Create return order item.
     */
    private function createReturnOrderItem(ReturnOrder $returnOrder, array $itemData, int $sortOrder = 0)
    {
        // Get invoice item for reference
        $invoiceItem = InvoiceItem::findOrFail($itemData['invoice_item_id']);

        // Validate quantity
        $quantityReturned = $itemData['quantity_returned'];
        if ($quantityReturned <= 0) {
            throw new Exception('Số lượng trả phải lớn hơn 0');
        }

        // Check if quantity doesn't exceed original quantity
        $totalReturned = ReturnOrderItem::whereHas('returnOrder', function($query) use ($returnOrder) {
                $query->where('invoice_id', $returnOrder->invoice_id)
                      ->where('status', '!=', 'rejected');
            })
            ->where('invoice_item_id', $invoiceItem->id)
            ->sum('quantity_returned');

        if (($totalReturned + $quantityReturned) > $invoiceItem->quantity) {
            throw new Exception('Số lượng trả vượt quá số lượng đã mua');
        }

        $unitPrice = $itemData['unit_price'] ?? $invoiceItem->unit_price;
        $lineTotal = $quantityReturned * $unitPrice;

        return ReturnOrderItem::create([
            'return_order_id' => $returnOrder->id,
            'invoice_item_id' => $itemData['invoice_item_id'] ?? $invoiceItem->id,
            'product_id' => $itemData['product_id'] ?? $invoiceItem->product_id,
            'product_name' => $itemData['product_name'] ?? $invoiceItem->product_name,
            'product_sku' => $itemData['product_sku'] ?? $invoiceItem->product_sku,
            'quantity_returned' => $quantityReturned,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'condition' => $itemData['condition'] ?? 'new',
            'notes' => $itemData['notes'] ?? null,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Create return order items for Quick Order.
     */
    private function createQuickOrderReturnItems(ReturnOrder $returnOrder, array $items, Invoice $invoice)
    {
        $sortOrder = 0;
        foreach ($items as $itemData) {
            // Find product by ID (more reliable than SKU)
            $product = Product::find($itemData['product_id']);
            if (!$product) {
                throw new Exception("Không tìm thấy sản phẩm với ID: {$itemData['product_id']}");
            }

            // Find matching invoice item
            $invoiceItem = $invoice->invoiceItems()
                ->where('product_id', $product->id)
                ->first();

            if (!$invoiceItem) {
                throw new Exception("Sản phẩm {$itemData['product_name']} không có trong hóa đơn gốc");
            }

            // Validate quantity
            $quantityReturned = $itemData['quantity'] ?? $itemData['quantity_returned'] ?? 0;
            if ($quantityReturned <= 0) {
                throw new Exception('Số lượng trả phải lớn hơn 0');
            }

            // Check if quantity doesn't exceed original quantity
            $totalReturned = ReturnOrderItem::whereHas('returnOrder', function($query) use ($returnOrder) {
                    $query->where('invoice_id', $returnOrder->invoice_id)
                          ->where('status', '!=', 'rejected')
                          ->where('id', '!=', $returnOrder->id); // Exclude current return order
                })
                ->where('invoice_item_id', $invoiceItem->id)
                ->sum('quantity_returned');



            if (($totalReturned + $quantityReturned) > $invoiceItem->quantity) {
                throw new Exception("Số lượng trả vượt quá số lượng gốc cho sản phẩm {$invoiceItem->product_name}. Gốc: {$invoiceItem->quantity}, Đã trả: {$totalReturned}, Trả thêm: {$quantityReturned}");
            }

            $unitPrice = $itemData['price'];
            $lineTotal = $quantityReturned * $unitPrice;

            ReturnOrderItem::create([
                'return_order_id' => $returnOrder->id,
                'invoice_item_id' => $invoiceItem->id,
                'product_id' => $product->id,
                'product_name' => $itemData['product_name'] ?: $invoiceItem->product_name,
                'product_sku' => $itemData['product_sku'] ?: $product->sku,
                'quantity_returned' => $quantityReturned,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'condition' => 'new',
                'notes' => null,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    /**
     * Map payment method to refund method.
     */
    private function mapRefundMethod(string $paymentMethod): string
    {
        return match($paymentMethod) {
            'transfer' => 'transfer',
            'card' => 'card',
            'ewallet' => 'cash',
            default => 'cash',
        };
    }

    /**
     * Create exchange invoice for return (trả và đổi hàng).
     */
    private function createExchangeOrder(ReturnOrder $returnOrder, array $data)
    {
        // Create invoice for exchange items with custom prefix HDD_TH
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateExchangeInvoiceNumber(),
            'customer_id' => $returnOrder->customer_id,
            'branch_shop_id' => $returnOrder->branch_shop_id,
            'sold_by' => Auth::id(),
            'created_by' => Auth::id(),
            'invoice_type' => 'return', // Set invoice_type = return for exchange
            'sales_channel' => 'pos',
            'invoice_date' => now(),
            'due_date' => now(),
            'status' => 'completed', // Set status to completed for immediate processing
            'notes' => "Đổi hàng từ trả hàng #{$returnOrder->return_number}",
            'subtotal' => $data['exchange_subtotal'] ?? 0,
            'discount_amount' => $data['exchange_discount'] ?? 0,
            'tax_amount' => 0,
            'total_amount' => $data['exchange_total'] ?? 0,
        ]);

        // Create invoice items
        if (isset($data['exchange_items']) && is_array($data['exchange_items'])) {
            $sortOrder = 0;
            foreach ($data['exchange_items'] as $itemData) {
                // Find product by ID (more reliable than SKU)
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    throw new Exception("Không tìm thấy sản phẩm với ID: {$itemData['product_id']}");
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $itemData['product_name'] ?: $product->name,
                    'product_sku' => $itemData['product_sku'] ?: $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'line_total' => $itemData['quantity'] * $itemData['price'],
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        // Recalculate totals
        $invoice->calculateTotals();

        // Create inventory transactions for exchange items (sale type)
        try {
            $inventoryService = app(InventoryService::class);
            $inventoryResult = $inventoryService->updateInventoryForInvoice($invoice);

            if (!$inventoryResult['success']) {
                Log::warning('Exchange invoice inventory update failed', [
                    'invoice_id' => $invoice->id,
                    'error' => $inventoryResult['message'] ?? 'Unknown error'
                ]);
                throw new Exception('Không thể cập nhật tồn kho cho hàng đổi: ' . ($inventoryResult['message'] ?? 'Unknown error'));
            }

            Log::info('Exchange invoice inventory updated successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update inventory for exchange invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Lỗi cập nhật tồn kho cho hàng đổi: ' . $e->getMessage());
        }

        return $invoice;
    }

    /**
     * Create payment record for exchange invoice.
     */
    private function createExchangePayment(Invoice $invoice, array $data)
    {
        if (isset($data['net_payable']) && $data['net_payable'] > 0) {
            // Use PaymentService to create payment with proper prefix TT{invoice_id}
            $paymentResult = app(PaymentService::class)->createInvoicePayment($invoice, [
                'amount' => $data['net_payable'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => "Thanh toán đổi hàng từ trả hàng",
            ]);

            if (!$paymentResult['success']) {
                throw new Exception('Không thể tạo payment record: ' . $paymentResult['message']);
            }

            return $paymentResult['data'];
        }

        return null;
    }

    /**
     * Update return order.
     */
    public function updateReturnOrder(ReturnOrder $returnOrder, array $data)
    {
        try {
            DB::beginTransaction();

            // Update return order
            $returnOrder->update([
                'return_date' => $data['return_date'] ?? $returnOrder->return_date,
                'reason' => $data['reason'] ?? $returnOrder->reason,
                'reason_detail' => $data['reason_detail'] ?? $returnOrder->reason_detail,
                'refund_method' => $data['refund_method'] ?? $returnOrder->refund_method,
                'notes' => $data['notes'] ?? $returnOrder->notes,
            ]);

            // Update return order items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $returnOrder->returnOrderItems()->delete();
                
                // Create new items
                foreach ($data['items'] as $index => $itemData) {
                    $this->createReturnOrderItem($returnOrder, $itemData, $index);
                }
            }

            // Calculate totals
            $returnOrder->calculateTotals();

            DB::commit();

            Log::info('Return order updated successfully', ['return_order_id' => $returnOrder->id]);
            
            return [
                'success' => true,
                'message' => 'Đơn trả hàng đã được cập nhật thành công',
                'data' => $returnOrder->load(['returnOrderItems.product', 'customer', 'invoice'])
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to update return order', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể cập nhật đơn trả hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Approve return order.
     */
    public function approveReturnOrder(ReturnOrder $returnOrder, array $data = [])
    {
        try {
            DB::beginTransaction();

            // Update status to approved
            $returnOrder->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => $data['notes'] ?? $returnOrder->notes,
            ]);

            // Update inventory (add returned items back to stock)
            // TODO: Implement updateInventoryForReturn method in InventoryService
            // $inventoryResult = $this->inventoryService->updateInventoryForReturn($returnOrder);
            // if (!$inventoryResult['success']) {
            //     throw new Exception($inventoryResult['message']);
            // }

            DB::commit();

            Log::info('Return order approved successfully', ['return_order_id' => $returnOrder->id]);
            
            return [
                'success' => true,
                'message' => 'Đơn trả hàng đã được duyệt thành công',
                'data' => $returnOrder->fresh()
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to approve return order', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể duyệt đơn trả hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Complete return order and process refund.
     */
    public function completeReturnOrder(ReturnOrder $returnOrder, array $paymentData = [])
    {
        try {
            DB::beginTransaction();

            // Ensure return order is approved
            if ($returnOrder->status !== 'approved') {
                throw new Exception('Đơn trả hàng phải được duyệt trước khi hoàn thành');
            }

            // Create refund payment
            if ($returnOrder->total_amount > 0) {
                $paymentResult = $this->paymentService->createPayment([
                    'payment_type' => 'payment', // Phiếu chi
                    'reference_type' => 'return_order',
                    'reference_id' => $returnOrder->id,
                    'customer_id' => $returnOrder->customer_id,
                    'branch_shop_id' => $returnOrder->branch_shop_id,
                    'amount' => $returnOrder->total_amount,
                    'payment_method' => $paymentData['payment_method'] ?? $returnOrder->refund_method,
                    'description' => 'Hoàn tiền đơn trả hàng ' . $returnOrder->return_number,
                    'notes' => $paymentData['notes'] ?? null,
                ]);

                if (!$paymentResult['success']) {
                    throw new Exception($paymentResult['message']);
                }
            }

            // Update status to completed
            $returnOrder->update([
                'status' => 'completed',
            ]);

            DB::commit();

            Log::info('Return order completed successfully', ['return_order_id' => $returnOrder->id]);
            
            return [
                'success' => true,
                'message' => 'Đơn trả hàng đã được hoàn thành thành công',
                'data' => $returnOrder->fresh()
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to complete return order', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể hoàn thành đơn trả hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject return order.
     */
    public function rejectReturnOrder(ReturnOrder $returnOrder, ?string $reason = null)
    {
        try {
            $returnOrder->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => $reason ? 'Lý do từ chối: ' . $reason : $returnOrder->notes,
            ]);

            Log::info('Return order rejected', ['return_order_id' => $returnOrder->id]);
            
            return [
                'success' => true,
                'message' => 'Đơn trả hàng đã bị từ chối',
                'data' => $returnOrder->fresh()
            ];

        } catch (Exception $e) {
            Log::error('Failed to reject return order', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể từ chối đơn trả hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Copy return order
     */
    public function copyReturnOrder(ReturnOrder $originalReturnOrder)
    {
        DB::beginTransaction();

        try {
            // Load original return order with items
            $originalReturnOrder->load(['returnOrderItems.product']);

            // Create new return order
            $newReturnOrder = ReturnOrder::create([
                'invoice_id' => $originalReturnOrder->invoice_id,
                'customer_id' => $originalReturnOrder->customer_id,
                'branch_shop_id' => $originalReturnOrder->branch_shop_id,
                'return_date' => now(),
                'total_amount' => $originalReturnOrder->total_amount,
                'status' => 'pending',
                'reason' => $originalReturnOrder->reason,
                'notes' => 'Sao chép từ: ' . $originalReturnOrder->return_number,
                'created_by' => auth()->id(),
            ]);

            // Copy return order items
            foreach ($originalReturnOrder->returnOrderItems as $item) {
                $newReturnOrder->returnOrderItems()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_amount' => $item->total_amount,
                    'discount_amount' => $item->discount_amount,
                    'reason' => $item->reason
                ]);
            }

            DB::commit();

            return $newReturnOrder;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error copying return order: ' . $e->getMessage());
            throw $e;
        }
    }
}
