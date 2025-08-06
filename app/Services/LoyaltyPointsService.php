<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPointTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LoyaltyPointsService
{
    /**
     * Default points calculation rate (1 point per 10,000 VND)
     */
    const DEFAULT_POINTS_RATE = 10000;

    /**
     * Calculate and award loyalty points for an invoice
     */
    public function awardPointsForInvoice(Invoice $invoice): array
    {
        try {
            // Only award points for completed invoices with customers
            if (!$this->shouldAwardPoints($invoice)) {
                return [
                    'success' => true,
                    'message' => 'No points awarded - conditions not met',
                    'points_awarded' => 0
                ];
            }

            DB::beginTransaction();

            // Calculate points based on invoice total
            $pointsToAward = $this->calculatePointsFromAmount($invoice->total_amount);

            if ($pointsToAward <= 0) {
                DB::rollBack();
                return [
                    'success' => true,
                    'message' => 'No points to award',
                    'points_awarded' => 0
                ];
            }

            // Get customer's current point balance
            $currentBalance = $this->getCustomerPointBalance($invoice->customer_id);
            $newBalance = $currentBalance + $pointsToAward;

            // Create point transaction
            $transaction = CustomerPointTransaction::create([
                'customer_id' => $invoice->customer_id,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'transaction_date' => now(),
                'type' => 'purchase',
                'amount' => $invoice->total_amount,
                'points' => $pointsToAward,
                'balance_after' => $newBalance,
                'notes' => "Điểm tích lũy từ hóa đơn {$invoice->invoice_number}"
            ]);

            // Update customer's current points
            $this->updateCustomerPoints($invoice->customer_id, $newBalance);

            DB::commit();

            Log::info('Loyalty points awarded successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'points_awarded' => $pointsToAward,
                'new_balance' => $newBalance,
                'transaction_id' => $transaction->id
            ]);

            return [
                'success' => true,
                'message' => "Đã tích {$pointsToAward} điểm cho hóa đơn {$invoice->invoice_number}",
                'points_awarded' => $pointsToAward,
                'new_balance' => $newBalance,
                'transaction_id' => $transaction->id
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to award loyalty points', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể tích điểm: ' . $e->getMessage(),
                'points_awarded' => 0
            ];
        }
    }

    /**
     * Check if points should be awarded for this invoice
     */
    private function shouldAwardPoints(Invoice $invoice): bool
    {
        // Must have a customer (not walk-in)
        if (!$invoice->customer_id) {
            return false;
        }

        // Must be completed status
        if ($invoice->status !== 'completed') {
            return false;
        }

        // Must have positive total amount
        if ($invoice->total_amount <= 0) {
            return false;
        }

        // Check if points already awarded for this invoice
        $existingTransaction = CustomerPointTransaction::where('reference_type', 'invoice')
            ->where('reference_id', $invoice->id)
            ->where('type', 'purchase')
            ->exists();

        if ($existingTransaction) {
            return false;
        }

        return true;
    }

    /**
     * Calculate points from invoice amount
     */
    private function calculatePointsFromAmount(float $amount): int
    {
        // 1 point per 10,000 VND (configurable)
        return (int) floor($amount / self::DEFAULT_POINTS_RATE);
    }

    /**
     * Get customer's current point balance
     */
    private function getCustomerPointBalance(int $customerId): int
    {
        $latestTransaction = CustomerPointTransaction::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestTransaction ? $latestTransaction->balance_after : 0;
    }

    /**
     * Update customer's current points (if customer model has points field)
     */
    private function updateCustomerPoints(int $customerId, int $newBalance): void
    {
        // Check if customer model has points field
        $customer = Customer::find($customerId);
        if ($customer && $customer->getConnection()->getSchemaBuilder()->hasColumn('customers', 'points')) {
            $customer->update(['points' => $newBalance]);
        }
    }

    /**
     * Reverse points for cancelled/returned invoice
     */
    public function reversePointsForInvoice(Invoice $invoice): array
    {
        try {
            // Find the original points transaction
            $originalTransaction = CustomerPointTransaction::where('reference_type', 'invoice')
                ->where('reference_id', $invoice->id)
                ->where('type', 'purchase')
                ->first();

            if (!$originalTransaction) {
                return [
                    'success' => true,
                    'message' => 'No points to reverse',
                    'points_reversed' => 0
                ];
            }

            DB::beginTransaction();

            $pointsToReverse = $originalTransaction->points;
            $currentBalance = $this->getCustomerPointBalance($invoice->customer_id);
            $newBalance = max(0, $currentBalance - $pointsToReverse); // Don't go negative

            // Create reversal transaction
            $reversalTransaction = CustomerPointTransaction::create([
                'customer_id' => $invoice->customer_id,
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'transaction_date' => now(),
                'type' => 'return',
                'amount' => $invoice->total_amount,
                'points' => -$pointsToReverse,
                'balance_after' => $newBalance,
                'notes' => "Hoàn điểm do hủy/trả hóa đơn {$invoice->invoice_number}"
            ]);

            // Update customer's current points
            $this->updateCustomerPoints($invoice->customer_id, $newBalance);

            DB::commit();

            Log::info('Loyalty points reversed successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'points_reversed' => $pointsToReverse,
                'new_balance' => $newBalance,
                'transaction_id' => $reversalTransaction->id
            ]);

            return [
                'success' => true,
                'message' => "Đã hoàn {$pointsToReverse} điểm cho hóa đơn {$invoice->invoice_number}",
                'points_reversed' => $pointsToReverse,
                'new_balance' => $newBalance,
                'transaction_id' => $reversalTransaction->id
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reverse loyalty points', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể hoàn điểm: ' . $e->getMessage(),
                'points_reversed' => 0
            ];
        }
    }

    /**
     * Get points calculation rate
     */
    public function getPointsRate(): int
    {
        return self::DEFAULT_POINTS_RATE;
    }

    /**
     * Preview points calculation for an amount
     */
    public function previewPointsForAmount(float $amount): int
    {
        return $this->calculatePointsFromAmount($amount);
    }
}
