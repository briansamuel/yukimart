<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class CreateAuditLogsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:create-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create audit_logs table if it does not exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking if audit_logs table exists...');

        if (Schema::hasTable('audit_logs')) {
            $this->info('✅ audit_logs table already exists!');
            
            // Show table structure
            $this->showTableStructure();
            
            return 0;
        }

        $this->warn('❌ audit_logs table does not exist. Creating it now...');

        try {
            // Run the specific migration
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2024_01_20_000000_create_audit_logs_table.php',
                '--force' => true
            ]);

            $this->info('✅ audit_logs table created successfully!');
            
            // Show table structure
            $this->showTableStructure();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to create audit_logs table: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show table structure
     */
    private function showTableStructure()
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        $this->info("\n📋 Table Structure:");
        
        $columns = Schema::getColumnListing('audit_logs');
        
        $this->table(
            ['Column Name'],
            array_map(fn($col) => [$col], $columns)
        );

        $this->info("\n📊 Table Statistics:");
        
        try {
            $count = \DB::table('audit_logs')->count();
            $this->info("Total records: {$count}");
            
            if ($count > 0) {
                $latestRecord = \DB::table('audit_logs')
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($latestRecord) {
                    $this->info("Latest record: {$latestRecord->created_at}");
                }
            }
        } catch (\Exception $e) {
            $this->warn("Could not get table statistics: " . $e->getMessage());
        }
    }
}
