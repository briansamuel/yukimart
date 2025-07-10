<?php

namespace App\Services;

use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReturnOrderService
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

            // Create return order
            $returnOrder = ReturnOrder::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $data['branch_shop_id'] ?? $invoice->branch_shop_id,
                'return_date' => $data['return_date'] ?? now(),
                'reason' => $data['reason'] ?? 'customer_request',
                'reason_detail' => $data['reason_detail'] ?? null,
                'refund_method' => $data['refund_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create return order items with grouping
            if (isset($data['items']) && is_array($data['items'])) {
                $this->createReturnOrderItems($returnOrder, $data['items']);
            }

            // Calculate totals
            $returnOrder->calculateTotals();

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
            $inventoryResult = $this->inventoryService->updateInventoryForReturn($returnOrder);
            if (!$inventoryResult['success']) {
                throw new Exception($inventoryResult['message']);
            }

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
    public function rejectReturnOrder(ReturnOrder $returnOrder, string $reason = null)
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
                'return_order_code' => $this->generateReturnOrderCode(),
                'invoice_id' => $originalReturnOrder->invoice_id,
                'customer_id' => $originalReturnOrder->customer_id,
                'branch_shop_id' => $originalReturnOrder->branch_shop_id,
                'return_date' => now(),
                'total_amount' => $originalReturnOrder->total_amount,
                'status' => 'pending',
                'reason' => $originalReturnOrder->reason,
                'notes' => 'Sao chép từ: ' . $originalReturnOrder->return_order_code,
                'created_by' => auth()->id(),
                'receiver_id' => auth()->id()
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
