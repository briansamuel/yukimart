<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateModelsForTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:update-models 
                            {--dry-run : Show what would be done without executing}
                            {--force : Force update even if backup exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing models to support multi-tenant functionality';

    /**
     * Models that need tenant support
     */
    private $modelsToUpdate = [
        'Customer',
        'Supplier', 
        'Category',
        'Order',
        'OrderItem',
        'Invoice',
        'InvoiceItem',
        'ReturnOrder',
        'ReturnOrderItem',
        'Payment',
        'BankAccount',
        'Inventory',
        'InventoryTransaction',
        'Notification',
        'BranchShop',
        'Warehouse',
        'ProductAttribute',
        'ProductVariant',
        'Setting'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 UPDATING MODELS FOR TENANT SUPPORT');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->showDryRun();
            return 0;
        }

        try {
            $this->createBackups();
            $this->updateModels();
            $this->info('✅ ALL MODELS UPDATED SUCCESSFULLY!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ UPDATE FAILED: ' . $e->getMessage());
            $this->restoreBackups();
            return 1;
        }
    }

    /**
     * Show what would be done without executing
     */
    private function showDryRun()
    {
        $this->info('🔍 DRY RUN - SHOWING PLANNED ACTIONS');
        $this->newLine();

        $this->info('📋 Models to be updated:');
        foreach ($this->modelsToUpdate as $index => $model) {
            $this->line("  " . ($index + 1) . ". {$model}");
        }

        $this->newLine();
        $this->info('🔧 Changes that would be made:');
        $this->line('  ✓ Add TenantScoped trait import');
        $this->line('  ✓ Add TenantScoped to class uses');
        $this->line('  ✓ Add tenant relationship method');
        $this->line('  ✓ Create backup of original files');

        $this->newLine();
        $this->info('💡 To execute actual updates, run without --dry-run flag');
    }

    /**
     * Create backups of model files
     */
    private function createBackups()
    {
        $this->info('💾 Creating backups...');
        
        $backupDir = storage_path('app/model-backups/' . date('Y-m-d_H-i-s'));
        File::makeDirectory($backupDir, 0755, true);

        foreach ($this->modelsToUpdate as $model) {
            $modelPath = app_path("Models/{$model}.php");
            
            if (File::exists($modelPath)) {
                $backupPath = $backupDir . "/{$model}.php";
                File::copy($modelPath, $backupPath);
                $this->line("  ✓ Backed up {$model}.php");
            }
        }

        $this->info("📁 Backups created in: {$backupDir}");
        $this->newLine();
    }

    /**
     * Update all models
     */
    private function updateModels()
    {
        $this->info('🔧 Updating models...');

        foreach ($this->modelsToUpdate as $model) {
            $this->updateModel($model);
        }

        $this->newLine();
    }

    /**
     * Update individual model
     */
    private function updateModel(string $model)
    {
        $modelPath = app_path("Models/{$model}.php");
        
        if (!File::exists($modelPath)) {
            $this->warn("  ⚠️  {$model}.php not found, skipping...");
            return;
        }

        $content = File::get($modelPath);
        
        // Check if already updated
        if (str_contains($content, 'use App\Traits\TenantScoped;')) {
            $this->line("  ✓ {$model}.php already updated");
            return;
        }

        try {
            $updatedContent = $this->addTenantSupport($content, $model);
            File::put($modelPath, $updatedContent);
            $this->line("  ✅ Updated {$model}.php");

        } catch (\Exception $e) {
            $this->error("  ❌ Failed to update {$model}.php: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add tenant support to model content
     */
    private function addTenantSupport(string $content, string $model): string
    {
        // Add TenantScoped import
        $content = $this->addTenantScopedImport($content);
        
        // Add TenantScoped to class uses
        $content = $this->addTenantScopedToClass($content);
        
        // Add tenant relationship method
        $content = $this->addTenantRelationship($content, $model);

        return $content;
    }

    /**
     * Add TenantScoped import
     */
    private function addTenantScopedImport(string $content): string
    {
        // Find the last use statement
        $lines = explode("\n", $content);
        $lastUseIndex = -1;

        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), 'use ') && !str_contains($line, '\\{')) {
                $lastUseIndex = $index;
            }
        }

        if ($lastUseIndex >= 0) {
            array_splice($lines, $lastUseIndex + 1, 0, 'use App\Traits\TenantScoped;');
        }

        return implode("\n", $lines);
    }

    /**
     * Add TenantScoped to class uses
     */
    private function addTenantScopedToClass(string $content): string
    {
        // Find the use statement in class
        $pattern = '/use\s+([^;]+);/';
        
        if (preg_match($pattern, $content, $matches)) {
            $currentUses = trim($matches[1]);
            $newUses = $currentUses . ', TenantScoped';
            $content = str_replace($matches[0], "use {$newUses};", $content);
        }

        return $content;
    }

    /**
     * Add tenant relationship method
     */
    private function addTenantRelationship(string $content, string $model): string
    {
        // Find the end of the class (before the last closing brace)
        $lastBracePos = strrpos($content, '}');
        
        if ($lastBracePos !== false) {
            $tenantMethod = $this->getTenantRelationshipMethod($model);
            $content = substr_replace($content, $tenantMethod . "\n}", $lastBracePos, 1);
        }

        return $content;
    }

    /**
     * Get tenant relationship method
     */
    private function getTenantRelationshipMethod(string $model): string
    {
        return "
    /**
     * Get the tenant that owns this {$model}
     */
    public function tenant()
    {
        return \$this->belongsTo(Tenant::class);
    }";
    }

    /**
     * Restore backups
     */
    private function restoreBackups()
    {
        $this->warn('🔄 Restoring backups...');
        
        // Find the latest backup directory
        $backupDirs = glob(storage_path('app/model-backups/*'));
        
        if (empty($backupDirs)) {
            $this->error('No backups found to restore');
            return;
        }

        $latestBackup = max($backupDirs);
        
        foreach ($this->modelsToUpdate as $model) {
            $backupPath = $latestBackup . "/{$model}.php";
            $modelPath = app_path("Models/{$model}.php");
            
            if (File::exists($backupPath)) {
                File::copy($backupPath, $modelPath);
                $this->line("  ✓ Restored {$model}.php");
            }
        }

        $this->info('✅ Backups restored successfully');
    }

    /**
     * Validate model updates
     */
    private function validateUpdates()
    {
        $this->info('🔍 Validating updates...');

        foreach ($this->modelsToUpdate as $model) {
            $modelPath = app_path("Models/{$model}.php");
            
            if (!File::exists($modelPath)) {
                continue;
            }

            $content = File::get($modelPath);
            
            // Check if TenantScoped is imported
            if (!str_contains($content, 'use App\Traits\TenantScoped;')) {
                throw new \Exception("TenantScoped import missing in {$model}");
            }

            // Check if TenantScoped is used in class
            if (!str_contains($content, 'TenantScoped')) {
                throw new \Exception("TenantScoped trait not used in {$model}");
            }

            $this->line("  ✓ {$model}.php validated");
        }

        $this->info('✅ All updates validated successfully');
    }
}
