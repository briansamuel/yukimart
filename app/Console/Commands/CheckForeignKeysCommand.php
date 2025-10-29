<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckForeignKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:check-foreign-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check foreign key constraint names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Foreign Key Constraints');
        $this->info('='.str_repeat('=', 40));

        $tables = ['users', 'roles', 'permissions'];

        foreach ($tables as $table) {
            $this->info("\n📋 Table: {$table}");
            $this->info('-'.str_repeat('-', 20));

            $constraints = DB::select("
                SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);

            if (empty($constraints)) {
                $this->info("   No foreign key constraints found");
            } else {
                foreach ($constraints as $constraint) {
                    $this->info("   ✅ {$constraint->CONSTRAINT_NAME}");
                    $this->info("      Column: {$constraint->COLUMN_NAME}");
                    $this->info("      References: {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}");
                }
            }
        }

        return 0;
    }
}
