<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixAmountPaidConstraintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:amount-paid-constraint 
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Force fix without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix null values in amount_paid columns to prevent constraint violations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for null amount_paid values...');
        $this->newLine();

        $issues = [];

        // Check orders table
        if (Schema::hasTable('orders')) {
            $nullOrdersCount = DB::table('orders')->whereNull('amount_paid')->count();
            if ($nullOrdersCount > 0) {
                $issues[] = [
                    'table' => 'orders',
                    'column' => 'amount_paid',
                    'count' => $nullOrdersCount,
                ];
                $this->warn("⚠️  Found {$nullOrdersCount} orders with null amount_paid");
            } else {
                $this->info("✅ No null amount_paid values found in orders table");
            }
        }

        // Check invoices table
        if (Schema::hasTable('invoices')) {
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                $nullInvoicesCount = DB::table('invoices')->whereNull('paid_amount')->count();
                if ($nullInvoicesCount > 0) {
                    $issues[] = [
                        'table' => 'invoices',
                        'column' => 'paid_amount',
                        'count' => $nullInvoicesCount,
                    ];
                    $this->warn("⚠️  Found {$nullInvoicesCount} invoices with null paid_amount");
                } else {
                    $this->info("✅ No null paid_amount values found in invoices table");
                }
            }
        }

        // Check for other potential amount columns
        $this->checkOtherAmountColumns();

        if (empty($issues)) {
            $this->info('🎉 No issues found! All amount_paid columns are properly set.');
            return 0;
        }

        $this->newLine();
        $this->warn('📋 Summary of issues found:');
        foreach ($issues as $issue) {
            $this->line("   - {$issue['table']}.{$issue['column']}: {$issue['count']} null values");
        }

        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->showWhatWouldBeFixed($issues);
            return 0;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to fix these issues by setting null values to 0?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔧 Fixing null amount values...');
        $this->newLine();

        $fixedCount = 0;
        foreach ($issues as $issue) {
            $fixed = $this->fixNullValues($issue['table'], $issue['column']);
            if ($fixed) {
                $fixedCount++;
                $this->info("✅ Fixed {$issue['count']} null values in {$issue['table']}.{$issue['column']}");
            } else {
                $this->error("❌ Failed to fix {$issue['table']}.{$issue['column']}");
            }
        }

        $this->newLine();
        if ($fixedCount === count($issues)) {
            $this->info("🎉 Successfully fixed all {$fixedCount} issues!");
        } else {
            $this->warn("⚠️  Fixed {$fixedCount} out of " . count($issues) . " issues");
        }

        return 0;
    }

    /**
     * Show what would be fixed in dry run mode.
     */
    protected function showWhatWouldBeFixed($issues)
    {
        $this->newLine();
        $this->info('📝 Changes that would be made:');
        
        foreach ($issues as $issue) {
            $this->line("   📄 Table: {$issue['table']}");
            $this->line("      Column: {$issue['column']}");
            $this->line("      Action: UPDATE {$issue['table']} SET {$issue['column']} = 0 WHERE {$issue['column']} IS NULL");
            $this->line("      Affected rows: {$issue['count']}");
            $this->newLine();
        }
    }

    /**
     * Fix null values in a specific table and column.
     */
    protected function fixNullValues($table, $column)
    {
        try {
            $updated = DB::table($table)
                        ->whereNull($column)
                        ->update([$column => 0]);
            
            return $updated >= 0; // Return true if update was successful (even if 0 rows affected)
        } catch (\Exception $e) {
            $this->error("Error fixing {$table}.{$column}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check for other potential amount columns that might have null issues.
     */
    protected function checkOtherAmountColumns()
    {
        $this->info('🔍 Checking other amount-related columns...');

        $tablesToCheck = [
            'orders' => ['discount_amount', 'total_amount', 'final_amount'],
            'invoices' => ['subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'remaining_amount'],
            'invoice_items' => ['unit_price', 'discount_amount', 'tax_amount', 'line_total'],
        ];

        foreach ($tablesToCheck as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                $nullCount = DB::table($table)->whereNull($column)->count();
                if ($nullCount > 0) {
                    $this->warn("⚠️  Found {$nullCount} null values in {$table}.{$column}");
                }
            }
        }
    }

    /**
     * Validate data integrity after fixes.
     */
    protected function validateDataIntegrity()
    {
        $this->info('🔍 Validating data integrity...');

        // Check for negative amounts
        $negativeAmounts = DB::table('orders')
                            ->where('amount_paid', '<', 0)
                            ->count();

        if ($negativeAmounts > 0) {
            $this->warn("⚠️  Found {$negativeAmounts} orders with negative amount_paid");
        }

        // Check for amount_paid > final_amount
        $overpaidOrders = DB::table('orders')
                           ->whereRaw('amount_paid > final_amount')
                           ->count();

        if ($overpaidOrders > 0) {
            $this->info("ℹ️  Found {$overpaidOrders} orders with amount_paid > final_amount (this might be intentional for overpayments)");
        }

        // Check payment status consistency
        $inconsistentPayments = DB::table('orders')
                                 ->where('amount_paid', '>', 0)
                                 ->where('payment_status', 'unpaid')
                                 ->count();

        if ($inconsistentPayments > 0) {
            $this->warn("⚠️  Found {$inconsistentPayments} orders with amount_paid > 0 but payment_status = 'unpaid'");
            $this->line("   Consider running: php artisan order:sync-payment-status");
        }

        $this->info('✅ Data integrity validation completed');
    }
}
