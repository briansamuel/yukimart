<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\LoyaltyPointsService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Services\BaseQuickOrderService;
use App\Services\PrefixGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvoiceService extends BaseQuickOrderService
{
    protected $inventoryService;
    protected $loyaltyPointsService;
    protected $notificationService;
    protected $paymentService;

    public function __construct(InventoryService $inventoryService, LoyaltyPointsService $loyaltyPointsService, NotificationService $notificationService, PaymentService $paymentService)
    {
        $this->inventoryService = $inventoryService;
        $this->loyaltyPointsService = $loyaltyPointsService;
        $this->notificationService = $notificationService;
        $this->paymentService = $paymentService;
    }
    /**
     * Create a new invoice.
     */
    public function createInvoice(array $data)
    {
        try {
            DB::beginTransaction();

            // Handle customer_id - null for walk-in customers
            $customerId = empty($data['customer_id']) || $data['customer_id'] == 0 ? null : $data['customer_id'];

            // Map channel to sales_channel for invoices
            $salesChannel = $this->mapChannelToSalesChannel($data['channel'] ?? 'offline');

            // Create invoice (without payment fields - they go to payments table)
            $invoice = Invoice::create([
                'customer_id' => $customerId,
                'order_id' => $data['order_id'] ?? null,
                'branch_shop_id' => $data['branch_shop_id'] ?? null,
                'invoice_type' => $data['invoice_type'] ?? 'sale',
                'sales_channel' => $salesChannel,
                'invoice_date' => $data['invoice_date'] ?? now(),
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'tax_rate' => $data['tax_rate'] ?? 0,
                'discount_rate' => $data['discount_rate'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'status' => $data['status'] ?? 'processing',
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create invoice items manually to avoid BaseService issues
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $index => $itemData) {
                    $this->createInvoiceItem($invoice, $itemData, $index);
                }
            }

            // Calculate totals
            $invoice->calculateTotals();

            // Create payment record if amount_paid is provided
            if (isset($data['amount_paid']) && $data['amount_paid'] > 0) {
                try {
                    $paymentResult = $this->paymentService->createInvoicePayment($invoice, [
                        'amount' => $data['amount_paid'],
                        'payment_method' => $data['payment_method'] ?? 'cash',
                        'notes' => 'Thanh toán tại thời điểm tạo hóa đơn',
                        'bank_name' => $data['bank_name'] ?? null,
                        'account_number' => $data['account_number'] ?? null,
                        'transaction_reference' => $data['transaction_reference'] ?? null,
                    ]);

                    if (!$paymentResult['success']) {
                        Log::warning('Payment creation failed but continuing', ['error' => $paymentResult['message']]);
                    }
                } catch (Exception $e) {
                    Log::warning('Payment service error but continuing', ['error' => $e->getMessage()]);
                }
            }

            // Update inventory
            try {
                $inventoryResult = $this->inventoryService->updateInventoryForInvoice($invoice);
                if (!$inventoryResult['success']) {
                    Log::warning('Inventory update failed but continuing', ['error' => $inventoryResult['message']]);
                }
            } catch (Exception $e) {
                Log::warning('Inventory service error but continuing', ['error' => $e->getMessage()]);
            }

            // Send notification
            try {
                $this->sendSaleNotification($invoice);
            } catch (Exception $e) {
                Log::warning('Notification failed but continuing', ['error' => $e->getMessage()]);
            }

            // Award loyalty points if applicable
            $loyaltyResult = null;
            try {
                $loyaltyResult = $this->loyaltyPointsService->awardPointsForInvoice($invoice);
                if ($loyaltyResult['success'] && $loyaltyResult['points_awarded'] > 0) {
                    Log::info('Loyalty points awarded', [
                        'invoice_id' => $invoice->id,
                        'points_awarded' => $loyaltyResult['points_awarded'],
                        'customer_id' => $invoice->customer_id
                    ]);
                }
            } catch (Exception $e) {
                Log::warning('Loyalty points calculation failed but continuing', ['error' => $e->getMessage()]);
            }

            DB::commit();

            Log::info('Invoice created successfully', ['invoice_id' => $invoice->id]);

            $result = [
                'success' => true,
                'message' => 'Hóa đơn đã được tạo thành công',
                'data' => $invoice->load(['customer', 'invoiceItems.product'])
            ];

            // Add inventory warnings if any
            if (isset($inventoryResult['warnings']) && !empty($inventoryResult['warnings'])) {
                $result['warnings'] = $inventoryResult['warnings'];
            }

            // Add loyalty points information if any
            if ($loyaltyResult && $loyaltyResult['success'] && $loyaltyResult['points_awarded'] > 0) {
                $result['loyalty_points'] = [
                    'points_awarded' => $loyaltyResult['points_awarded'],
                    'new_balance' => $loyaltyResult['new_balance'],
                    'message' => $loyaltyResult['message']
                ];
            }

            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create invoice', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể tạo hóa đơn: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update an existing invoice.
     */
    public function updateInvoice(Invoice $invoice, array $data)
    {
        try {
            DB::beginTransaction();

            // Update invoice
            $invoice->update([
                'customer_id' => $data['customer_id'] ?? $invoice->customer_id,
                'order_id' => $data['order_id'] ?? $invoice->order_id,
                'branch_shop_id' => $data['branch_shop_id'] ?? $invoice->branch_shop_id,
                'invoice_type' => $data['invoice_type'] ?? $invoice->invoice_type,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'tax_rate' => $data['tax_rate'] ?? $invoice->tax_rate,
                'discount_rate' => $data['discount_rate'] ?? $invoice->discount_rate,
                'discount_amount' => $data['discount_amount'] ?? $invoice->discount_amount,
                'payment_method' => $data['payment_method'] ?? $invoice->payment_method,
                'notes' => $data['notes'] ?? $invoice->notes,
                'terms_conditions' => $data['terms_conditions'] ?? $invoice->terms_conditions,
                'reference_number' => $data['reference_number'] ?? $invoice->reference_number,
            ]);

            // Update invoice items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $invoice->invoiceItems()->delete();
                
                // Create new items
                foreach ($data['items'] as $index => $itemData) {
                    $this->createInvoiceItem($invoice, $itemData, $index);
                }
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            Log::info('Invoice updated successfully', ['invoice_id' => $invoice->id]);
            
            return [
                'success' => true,
                'message' => 'Hóa đơn đã được cập nhật thành công',
                'data' => $invoice->load(['customer', 'invoiceItems.product'])
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update invoice', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể cập nhật hóa đơn: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create invoice item.
     */
    private function createInvoiceItem(Invoice $invoice, array $itemData, int $sortOrder = 0)
    {
        $product = null;
        if (isset($itemData['product_id'])) {
            $product = Product::find($itemData['product_id']);
        }

        // Handle both 'price' and 'unit_price' from frontend
        $unitPrice = $itemData['unit_price'] ?? $itemData['price'] ?? $product?->product_price ?? 0;

        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $itemData['product_id'] ?? null,
            'product_name' => $itemData['product_name'] ?? $product?->product_name ?? 'Sản phẩm',
            'product_sku' => $itemData['product_sku'] ?? $product?->sku ?? null,
            'product_description' => $itemData['product_description'] ?? $product?->product_description ?? null,
            'quantity' => $itemData['quantity'] ?? 1,
            'unit' => $itemData['unit'] ?? 'cái',
            'unit_price' => $unitPrice,
            'discount_rate' => $itemData['discount_rate'] ?? 0,
            'discount_amount' => $itemData['discount_amount'] ?? 0,
            'tax_rate' => $itemData['tax_rate'] ?? 0,
            'tax_amount' => $itemData['tax_amount'] ?? 0,
            'notes' => $itemData['notes'] ?? null,
            'sort_order' => $sortOrder,
        ]);

        // Calculate line total
        $invoiceItem->calculateLineTotal();
        $invoiceItem->save();

        return $invoiceItem;
    }

    /**
     * Create invoice from order.
     */
    public function createInvoiceFromOrder(Order $order, array $additionalData = [])
    {
        try {
            $invoiceData = [
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'branch_shop_id' => $order->branch_shop_id,
                'invoice_type' => 'sale',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'payment_method' => $order->payment_method,
                'notes' => 'Hóa đơn được tạo từ đơn hàng #' . $order->order_code,
                'items' => []
            ];

            // Add order items to invoice
            foreach ($order->orderItems as $orderItem) {
                $invoiceData['items'][] = [
                    'product_id' => $orderItem->product_id,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'discount_amount' => $orderItem->discount,
                ];
            }

            // Merge additional data
            $invoiceData = array_merge($invoiceData, $additionalData);

            return $this->createInvoice($invoiceData);

        } catch (Exception $e) {
            Log::error('Failed to create invoice from order', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể tạo hóa đơn từ đơn hàng: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record payment for invoice.
     */
    public function recordPayment(Invoice $invoice, float $amount, array $paymentData = [])
    {
        try {
            // Use PaymentService to create payment record
            $paymentResult = $this->paymentService->createInvoicePayment($invoice, [
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'notes' => $paymentData['notes'] ?? null,
                'bank_name' => $paymentData['bank_name'] ?? null,
                'account_number' => $paymentData['account_number'] ?? null,
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
            ]);

            if (!$paymentResult['success']) {
                throw new Exception($paymentResult['message']);
            }

            Log::info('Payment recorded for invoice', ['invoice_id' => $invoice->id, 'amount' => $amount]);

            return [
                'success' => true,
                'message' => 'Thanh toán đã được ghi nhận thành công',
                'data' => $invoice->fresh()
            ];

        } catch (Exception $e) {
            Log::error('Failed to record payment', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Không thể ghi nhận thanh toán: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send invoice to customer.
     */
    public function sendInvoice(Invoice $invoice)
    {
        try {
            $invoice->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info('Invoice sent to customer', ['invoice_id' => $invoice->id]);
            
            return [
                'success' => true,
                'message' => 'Hóa đơn đã được gửi thành công',
                'data' => $invoice->fresh()
            ];

        } catch (Exception $e) {
            Log::error('Failed to send invoice', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể gửi hóa đơn: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel invoice using soft delete.
     */
    public function cancelInvoice(Invoice $invoice, string $reason = '')
    {
        try {
            $invoice->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'deleted_at' => now(),
                'updated_by' => auth()->id(),
                'notes' => $invoice->notes . "\n\nHóa đơn bị hủy: " . $reason,
            ]);

            // Reverse loyalty points if applicable
            $loyaltyResult = null;
            try {
                $loyaltyResult = $this->loyaltyPointsService->reversePointsForInvoice($invoice);
                if ($loyaltyResult['success'] && $loyaltyResult['points_reversed'] > 0) {
                    Log::info('Loyalty points reversed for cancelled invoice', [
                        'invoice_id' => $invoice->id,
                        'points_reversed' => $loyaltyResult['points_reversed'],
                        'customer_id' => $invoice->customer_id
                    ]);
                }
            } catch (Exception $e) {
                Log::warning('Loyalty points reversal failed but continuing', ['error' => $e->getMessage()]);
            }

            Log::info('Invoice cancelled', [
                'invoice_id' => $invoice->id,
                'reason' => $reason,
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now()
            ]);

            $result = [
                'success' => true,
                'message' => 'Hóa đơn đã được hủy thành công',
                'data' => $invoice->fresh()
            ];

            // Add loyalty points information if any
            if ($loyaltyResult && $loyaltyResult['success'] && $loyaltyResult['points_reversed'] > 0) {
                $result['loyalty_points'] = [
                    'points_reversed' => $loyaltyResult['points_reversed'],
                    'new_balance' => $loyaltyResult['new_balance'],
                    'message' => $loyaltyResult['message']
                ];
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Failed to cancel invoice', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Không thể hủy hóa đơn: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get invoice statistics.
     */
    public function getStatistics(array $filters = [])
    {
        return Invoice::getStatistics($filters);
    }

    /**
     * Map channel from orders to sales_channel for invoices.
     */
    private function mapChannelToSalesChannel(string $channel): string
    {
        $mapping = [
            'direct' => 'offline',
            'online' => 'online',
            'pos' => 'offline',
            'other' => 'offline',
            'shopee' => 'marketplace',
            'tiktok' => 'marketplace',
            'facebook' => 'social_media',
            'offline' => 'offline',
        ];

        return $mapping[$channel] ?? 'offline';
    }

    /**
     * Get overdue invoices.
     */
    public function getOverdueInvoices()
    {
        return Invoice::overdue()
                     ->with(['customer', 'branch'])
                     ->orderBy('due_date', 'asc')
                     ->get();
    }

    /**
     * Delete invoice.
     */
    public function deleteInvoice(Invoice $invoice)
    {
        try {
            DB::beginTransaction();

            // Delete invoice items first
            $invoice->invoiceItems()->delete();

            // Delete invoice
            $invoice->delete();

            DB::commit();

            Log::info('Invoice deleted successfully', ['invoice_id' => $invoice->id]);

            return [
                'success' => true,
                'message' => 'Hóa đơn đã được xóa thành công'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete invoice', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Không thể xóa hóa đơn: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send sale notification.
     */
    private function sendSaleNotification($invoice)
    {
        try {
            // Only send notification if status is paid and type is not invoice_sale
            if ($invoice->status !== 'paid' || $invoice->invoice_type === 'invoice_sale') {
                Log::info('Sale notification skipped', [
                    'invoice_id' => $invoice->id,
                    'status' => $invoice->status,
                    'invoice_type' => $invoice->invoice_type,
                    'reason' => $invoice->status !== 'paid' ? 'status not paid' : 'invoice_sale type'
                ]);
                return;
            }

            $seller = $invoice->creator;
            if (!$seller) {
                return;
            }

            $totalAmount = number_format($invoice->total_amount, 0, ',', '.');
            $sellerName = $seller->full_name ?? $seller->username ?? 'Nhân viên';

            // Create formatted message
            $message = $sellerName . ' vừa bán đơn hàng với giá trị ' . $totalAmount . ' VND';

            // Create notification for all admin users
            $adminUsers = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->get();

            // If no admin users found, send to all users
            if ($adminUsers->isEmpty()) {
                $adminUsers = \App\Models\User::all();
            }

            foreach ($adminUsers as $user) {
                \App\Models\Notification::create([
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $user->id,
                    'type' => 'invoice_sale',
                    'title' => 'Bán hàng thành công',
                    'message' => $message,
                    'data' => [
                        'invoice_id' => $invoice->id,
                        'seller_id' => $seller->id,
                        'seller_name' => $sellerName,
                        'total_amount' => $invoice->total_amount,
                        'formatted_amount' => $totalAmount . ' VND',
                    ],
                    'priority' => 'normal',
                    'channels' => ['web'],
                    'action_url' => '/admin/invoices?Code=' . ($invoice->invoice_number ?? $invoice->id),
                    'action_text' => 'Xem hóa đơn',
                    'created_by' => $seller->id,
                ]);
            }

            Log::info('Sale notification sent', [
                'seller' => $sellerName,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount,
                'recipients' => $adminUsers->count()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send sale notification', [
                'invoice_id' => $invoice->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}
