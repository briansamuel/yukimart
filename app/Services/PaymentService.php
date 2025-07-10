<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ReturnOrder;
use App\Models\Order;
use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class PaymentService
{
    /**
     * Create payment record.
     */
    public function createPayment(array $data)
    {
        try {
            DB::beginTransaction();

            // Validate bank account if provided
            if (isset($data['bank_account_id']) && $data['bank_account_id']) {
                $bankAccount = BankAccount::find($data['bank_account_id']);
                if (!$bankAccount) {
                    throw new Exception('Tài khoản ngân hàng không tồn tại');
                }

                if (!$bankAccount->is_active) {
                    throw new Exception('Tài khoản ngân hàng không hoạt động');
                }
            }

            // Auto-select bank account for transfer/card payments if not provided
            if (!isset($data['bank_account_id']) && in_array($data['payment_method'], ['transfer', 'card'])) {
                $defaultBankAccount = BankAccount::getDefault();
                if ($defaultBankAccount) {
                    $data['bank_account_id'] = $defaultBankAccount->id;
                }
            }

            // Generate payment number if not provided
            if (!isset($data['payment_number'])) {
                $referenceNumber = null;
                if (isset($data['reference_type']) && isset($data['reference_id'])) {
                    $referenceNumber = $this->getReferenceNumber($data['reference_type'], $data['reference_id']);
                }
                $data['payment_number'] = Payment::generatePaymentNumber($data['payment_type'], $referenceNumber, $data['reference_type'] ?? null);
            }

            // Create payment
            $payment = Payment::create([
                'payment_number' => $data['payment_number'],
                'payment_type' => $data['payment_type'], // receipt/payment
                'reference_type' => $data['reference_type'] ?? 'manual',
                'reference_id' => $data['reference_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'branch_shop_id' => $data['branch_shop_id'] ?? null,
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'payment_date' => $data['payment_date'] ?? now(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status' => $data['status'] ?? 'completed',
                'actual_amount' => $data['actual_amount'] ?? $data['amount'],
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            Log::info('Payment created successfully', ['payment_id' => $payment->id]);
            
            return [
                'success' => true,
                'message' => 'Phiếu thu/chi đã được tạo thành công',
                'data' => $payment->load(['customer', 'branchShop', 'bankAccount', 'creator'])
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to create payment', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể tạo phiếu thu/chi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update payment record.
     */
    public function updatePayment(Payment $payment, array $data)
    {
        try {
            DB::beginTransaction();

            $payment->update([
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'amount' => $data['amount'] ?? $payment->amount,
                'payment_method' => $data['payment_method'] ?? $payment->payment_method,
                'status' => $data['status'] ?? $payment->status,
                'actual_amount' => $data['actual_amount'] ?? $payment->actual_amount,
                'description' => $data['description'] ?? $payment->description,
                'notes' => $data['notes'] ?? $payment->notes,
                'bank_name' => $data['bank_name'] ?? $payment->bank_name,
                'account_number' => $data['account_number'] ?? $payment->account_number,
                'transaction_reference' => $data['transaction_reference'] ?? $payment->transaction_reference,
            ]);

            DB::commit();

            Log::info('Payment updated successfully', ['payment_id' => $payment->id]);
            
            return [
                'success' => true,
                'message' => 'Phiếu thu/chi đã được cập nhật thành công',
                'data' => $payment->fresh()
            ];

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to update payment', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể cập nhật phiếu thu/chi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create payment for invoice.
     */
    public function createInvoicePayment(Invoice $invoice, array $paymentData)
    {
        return $this->createPayment([
            'payment_type' => 'receipt',
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'branch_shop_id' => $invoice->branch_shop_id,
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'] ?? 'cash',
            'description' => 'Thanh toán hóa đơn ' . $invoice->invoice_number,
            'notes' => $paymentData['notes'] ?? null,
            'bank_name' => $paymentData['bank_name'] ?? null,
            'account_number' => $paymentData['account_number'] ?? null,
            'transaction_reference' => $paymentData['transaction_reference'] ?? null,
        ]);
    }

    /**
     * Create payment for return order.
     */
    public function createReturnPayment(ReturnOrder $returnOrder, array $paymentData)
    {
        return $this->createPayment([
            'payment_type' => 'payment',
            'reference_type' => 'return_order',
            'reference_id' => $returnOrder->id,
            'customer_id' => $returnOrder->customer_id,
            'branch_shop_id' => $returnOrder->branch_shop_id,
            'amount' => $paymentData['amount'] ?? $returnOrder->total_amount,
            'payment_method' => $paymentData['payment_method'] ?? $returnOrder->refund_method,
            'description' => 'Hoàn tiền đơn trả hàng ' . $returnOrder->return_number,
            'notes' => $paymentData['notes'] ?? null,
            'bank_name' => $paymentData['bank_name'] ?? null,
            'account_number' => $paymentData['account_number'] ?? null,
            'transaction_reference' => $paymentData['transaction_reference'] ?? null,
        ]);
    }

    /**
     * Approve payment.
     */
    public function approvePayment(Payment $payment, array $data = [])
    {
        try {
            $payment->update([
                'status' => 'completed',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'actual_amount' => $data['actual_amount'] ?? $payment->amount,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            Log::info('Payment approved successfully', ['payment_id' => $payment->id]);
            
            return [
                'success' => true,
                'message' => 'Phiếu thu/chi đã được duyệt thành công',
                'data' => $payment->fresh()
            ];

        } catch (Exception $e) {
            Log::error('Failed to approve payment', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể duyệt phiếu thu/chi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel payment.
     */
    public function cancelPayment(Payment $payment, string $reason = null)
    {
        try {
            $payment->update([
                'status' => 'cancelled',
                'notes' => $reason ? 'Lý do hủy: ' . $reason : $payment->notes,
            ]);

            Log::info('Payment cancelled', ['payment_id' => $payment->id]);
            
            return [
                'success' => true,
                'message' => 'Phiếu thu/chi đã bị hủy',
                'data' => $payment->fresh()
            ];

        } catch (Exception $e) {
            Log::error('Failed to cancel payment', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Không thể hủy phiếu thu/chi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get reference number for payment number generation.
     */
    private function getReferenceNumber($referenceType, $referenceId)
    {
        switch ($referenceType) {
            case 'invoice':
                $invoice = Invoice::find($referenceId);
                return $invoice ? $invoice->invoice_number : null;

            case 'return_order':
                $returnOrder = ReturnOrder::find($referenceId);
                return $returnOrder ? $returnOrder->return_number : null;

            case 'order':
                $order = Order::find($referenceId);
                return $order ? $order->order_code : null;

            default:
                return null;
        }
    }

    /**
     * Get payment statistics for dashboard.
     */
    public function getPaymentStatistics($branchShopId = null, $dateFrom = null, $dateTo = null)
    {
        $query = Payment::where('status', 'completed');

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        if ($dateFrom) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $receipts = $query->clone()->where('payment_type', 'receipt')->sum('actual_amount');
        $payments = $query->clone()->where('payment_type', 'payment')->sum('actual_amount');

        return [
            'total_receipts' => $receipts,
            'total_payments' => $payments,
            'net_amount' => $receipts - $payments,
            'receipt_count' => $query->clone()->where('payment_type', 'receipt')->count(),
            'payment_count' => $query->clone()->where('payment_type', 'payment')->count(),
        ];
    }
}
