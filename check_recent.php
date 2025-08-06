<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking invoices created after 17:20 today...\n";
$recentInvoices = DB::table('invoices')
    ->where('created_at', '>=', '2025-08-01 17:20:00')
    ->orderBy('created_at', 'desc')
    ->get();

if ($recentInvoices->count() > 0) {
    foreach($recentInvoices as $invoice) {
        echo "ID: " . $invoice->id . " | Status: " . $invoice->status . " | Number: " . $invoice->invoice_number . " | Amount: " . number_format($invoice->total_amount) . " | Created: " . $invoice->created_at . "\n";
    }
} else {
    echo "No invoices created after 17:20 today\n";
}
