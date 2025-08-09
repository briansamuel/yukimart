<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendInvoiceNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            $invoice = $event->invoice ?? null;
            
            if (!$invoice || !($invoice instanceof Invoice)) {
                Log::warning('SendInvoiceNotificationListener: Invalid invoice data');
                return;
            }

            // Chỉ gửi notification cho hóa đơn completed
            if ($this->shouldSendNotification($invoice, $event)) {
                $this->sendInvoiceNotification($invoice);
            }

        } catch (\Exception $e) {
            Log::error('SendInvoiceNotificationListener error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Determine if notification should be sent.
     */
    private function shouldSendNotification($invoice, $event): bool
    {
        // Gửi notification cho hóa đơn mới với status completed
        if (isset($event->isNewInvoice) && $event->isNewInvoice && $invoice->status === 'completed') {
            return true;
        }

        // Gửi notification khi status thay đổi thành completed
        if (isset($event->statusChanged) && $event->statusChanged && $invoice->status === 'completed') {
            return true;
        }

        // Gửi notification cho hóa đơn có status completed (fallback)
        if ($invoice->status === 'completed') {
            return true;
        }

        return false;
    }

    /**
     * Send invoice notification.
     */
    private function sendInvoiceNotification($invoice): void
    {
        try {
            // Refresh invoice to get latest data including calculated totals
            $invoice = $invoice->fresh();

            // Tạo notification cho admin users
            $adminUsers = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->get();

            if ($adminUsers->isEmpty()) {
                // Fallback: gửi cho tất cả users nếu không có admin
                $adminUsers = \App\Models\User::limit(10)->get();
            }

            foreach ($adminUsers as $user) {
                $notification = Notification::createWithFCM(
                    $user,
                    'invoice_created',
                    $this->getInvoiceNotificationTitle($invoice),
                    $this->getInvoiceNotificationMessage($invoice),
                    [
                        'invoice_id' => $invoice->id,
                        'invoice_code' => $invoice->invoice_code ?? 'HD-' . $invoice->id,
                        'customer_name' => $invoice->customer_name ?? 'Khách lẻ',
                        'total_amount' => $invoice->total_amount ?? 0,
                        'status' => $invoice->status,
                        'created_at' => $invoice->created_at->toISOString(),
                        'seller_name' => $invoice->seller->full_name ?? $invoice->seller->name ?? 'Không xác định',
                        'branch_shop_name' => $invoice->branchShop->name ?? 'Không xác định',
                    ],
                    [
                        'priority' => 'high',
                        'action_url' => url('/admin/invoices/' . $invoice->id),
                        'action_text' => 'Xem hóa đơn',
                        'color' => 'success',
                        'icon' => 'receipt'
                    ]
                );

                Log::info('Invoice notification sent', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $user->id,
                    'notification_id' => $notification->id ?? null
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send invoice notification: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get notification title for invoice.
     */
    private function getInvoiceNotificationTitle($invoice): string
    {
        $invoiceCode = $invoice->invoice_number ?? 'HD-' . $invoice->id;

        return "💰 Hóa đơn mới - {$invoiceCode}";
    }

    /**
     * Get notification message for invoice.
     */
    private function getInvoiceNotificationMessage($invoice): string
    {
        //$customerName = $invoice->customer_name ?? 'Khách lẻ';
        $totalAmount = number_format($invoice->total_amount ?? 0, 0, ',', '.') . ' VNĐ';
        $invoiceCode = $invoice->invoice_number ?? 'HD-' . $invoice->id;

        return "Hóa đơn mới {$invoiceCode} vừa bán được với tổng tiền {$totalAmount}";
    }
}
