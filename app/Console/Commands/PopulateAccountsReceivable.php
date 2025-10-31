<?php

namespace App\Console\Commands;

use App\Models\Analytics\AccountsReceivable;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateAccountsReceivable extends Command
{
    protected $signature = 'analytics:populate-accounts-receivable';
    protected $description = 'Populate accounts receivable table from invoices';

    public function handle()
    {
        $this->info("Populating accounts receivable...");

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->populateForTenant($tenant->id);
        }

        $this->info('Accounts receivable populated successfully!');
    }

    protected function populateForTenant($tenantId)
    {
        // Get all invoices with payment terms
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereIn('payment_status', ['pending', 'partial'])
            ->get();

        foreach ($invoices as $invoice) {
            // Calculate amounts
            $invoiceAmount = $invoice->total_amount;
            $paidAmount = DB::table('payments')
                ->where('reference_type', 'invoice')
                ->where('reference_id', $invoice->id)
                ->where('payment_type', 'receipt')
                ->sum('amount') ?? 0;
            
            $outstandingAmount = $invoiceAmount - $paidAmount;

            // Determine due date (default 30 days from invoice date)
            $dueDate = $invoice->due_date ?? Carbon::parse($invoice->invoice_date)->addDays(30);
            
            // Calculate days overdue
            $daysOverdue = now()->gt($dueDate) ? now()->diffInDays($dueDate) : 0;

            // Determine status
            $status = $this->determineStatus($paidAmount, $invoiceAmount, $daysOverdue);

            // Delete existing record
            AccountsReceivable::where('tenant_id', $tenantId)
                ->where('invoice_id', $invoice->id)
                ->delete();

            // Insert new record
            AccountsReceivable::create([
                'tenant_id' => $tenantId,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $dueDate,
                'invoice_amount' => $invoiceAmount,
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'days_overdue' => $daysOverdue,
                'status' => $status,
            ]);
        }

        // Also handle cancelled invoices
        $cancelledInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->where('status', 'cancelled')
            ->get();

        foreach ($cancelledInvoices as $invoice) {
            AccountsReceivable::where('tenant_id', $tenantId)
                ->where('invoice_id', $invoice->id)
                ->update(['status' => 'cancelled']);
        }

        // Handle fully paid invoices
        $paidInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->where('payment_status', 'paid')
            ->get();

        foreach ($paidInvoices as $invoice) {
            AccountsReceivable::where('tenant_id', $tenantId)
                ->where('invoice_id', $invoice->id)
                ->update([
                    'status' => 'paid',
                    'outstanding_amount' => 0,
                ]);
        }
    }

    protected function determineStatus($paidAmount, $invoiceAmount, $daysOverdue)
    {
        if ($paidAmount >= $invoiceAmount) {
            return 'paid';
        } elseif ($daysOverdue > 0) {
            return 'overdue';
        } elseif ($paidAmount > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }
}

