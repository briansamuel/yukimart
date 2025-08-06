<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "�� Checking latest invoice...\n";
$invoice = DB::table('invoices')->orderBy('created_at', 'desc')->first();
echo "Latest Invoice ID: " . $invoice->id . "\n";
echo "Status: " . $invoice->status . "\n";
echo "Number: " . $invoice->invoice_number . "\n";
echo "Amount: " . number_format($invoice->total_amount) . " VND\n";
echo "Created: " . $invoice->created_at . "\n";
