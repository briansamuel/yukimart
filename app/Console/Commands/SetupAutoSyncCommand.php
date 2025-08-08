<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SetupAutoSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:setup-auto-sync 
                            {--enable : Enable auto-sync}
                            {--disable : Disable auto-sync}
                            {--status : Show current status}';

    /**
     * The console command description.
     */
    protected $description = 'Setup and configure API auto-sync functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 YukiMart API Auto-Sync Setup');
        $this->info('===============================');

        if ($this->option('status')) {
            return $this->showStatus();
        }

        if ($this->option('enable')) {
            return $this->enableAutoSync();
        }

        if ($this->option('disable')) {
            return $this->disableAutoSync();
        }

        return $this->interactiveSetup();
    }

    /**
     * Show current auto-sync status
     */
    private function showStatus()
    {
        $this->info('📊 Current Auto-Sync Status:');
        $this->info('============================');

        $enabled = config('api-auto-sync.enabled', false);
        $this->line('🔄 Auto-Sync: ' . ($enabled ? '✅ Enabled' : '❌ Disabled'));

        if ($enabled) {
            $this->line('📮 Postman Sync: ' . (config('api-auto-sync.sync_targets.postman') ? '✅' : '❌'));
            $this->line('📚 Documentation: ' . (config('api-auto-sync.sync_targets.documentation') ? '✅' : '❌'));
            $this->line('⏰ Scheduled: ' . (config('api-auto-sync.sync_triggers.scheduled') ? '✅' : '❌'));
            $this->line('🔍 Route Changes: ' . (config('api-auto-sync.sync_triggers.route_changes') ? '✅' : '❌'));
        }

        $this->info('');
        $this->info('📋 Configuration:');
        $this->line('- Postman API Key: ' . (config('postman.api_key') ? '✅ Set' : '❌ Not set'));
        $this->line('- Postman Collection ID: ' . (config('postman.collection_id') ? '✅ Set' : '❌ Not set'));
        $this->line('- Postman Workspace ID: ' . (config('postman.workspace_id') ? '✅ Set' : '❌ Not set'));

        return 0;
    }

    /**
     * Enable auto-sync
     */
    private function enableAutoSync()
    {
        $this->info('✅ Enabling API Auto-Sync...');

        // Check prerequisites
        if (!$this->checkPrerequisites()) {
            return 1;
        }

        // Update environment
        $this->updateEnvFile([
            'API_AUTO_SYNC_ENABLED' => 'true',
            'API_SYNC_POSTMAN' => 'true',
            'API_SYNC_DOCUMENTATION' => 'true',
            'POSTMAN_AUTO_SYNC' => 'true'
        ]);

        $this->info('✅ Auto-sync enabled successfully!');
        $this->info('');
        $this->info('📋 Next steps:');
        $this->line('1. Run initial sync: php artisan api:auto-sync --force');
        $this->line('2. Check scheduled tasks: php artisan schedule:list');
        $this->line('3. Monitor logs: tail -f storage/logs/api-auto-sync.log');

        return 0;
    }

    /**
     * Disable auto-sync
     */
    private function disableAutoSync()
    {
        $this->info('❌ Disabling API Auto-Sync...');

        $this->updateEnvFile([
            'API_AUTO_SYNC_ENABLED' => 'false',
            'POSTMAN_AUTO_SYNC' => 'false'
        ]);

        $this->info('✅ Auto-sync disabled successfully!');
        return 0;
    }

    /**
     * Interactive setup
     */
    private function interactiveSetup()
    {
        $this->info('🚀 Interactive Auto-Sync Setup');
        $this->info('==============================');

        // Check if user wants to enable auto-sync
        $enable = $this->confirm('Do you want to enable API auto-sync?', true);

        if (!$enable) {
            $this->disableAutoSync();
            return 0;
        }

        // Check prerequisites
        if (!$this->checkPrerequisites()) {
            $this->error('❌ Prerequisites not met. Please configure Postman settings first.');
            return 1;
        }

        // Configure sync targets
        $syncPostman = $this->confirm('Sync to Postman collection?', true);
        $syncDocs = $this->confirm('Generate documentation?', true);
        $scheduled = $this->confirm('Enable scheduled sync?', true);
        $routeChanges = $this->confirm('Sync on route changes?', true);

        // Update configuration
        $envUpdates = [
            'API_AUTO_SYNC_ENABLED' => 'true',
            'API_SYNC_POSTMAN' => $syncPostman ? 'true' : 'false',
            'API_SYNC_DOCUMENTATION' => $syncDocs ? 'true' : 'false',
            'API_SYNC_SCHEDULED' => $scheduled ? 'true' : 'false',
            'API_SYNC_ON_ROUTE_CHANGES' => $routeChanges ? 'true' : 'false',
            'POSTMAN_AUTO_SYNC' => $syncPostman ? 'true' : 'false'
        ];

        $this->updateEnvFile($envUpdates);

        $this->info('✅ Auto-sync configured successfully!');

        // Ask if user wants to run initial sync
        if ($this->confirm('Run initial sync now?', true)) {
            $this->info('🔄 Running initial sync...');
            Artisan::call('api:auto-sync', ['--force' => true]);
            $this->line(Artisan::output());
        }

        $this->showNextSteps();
        return 0;
    }

    /**
     * Check prerequisites
     */
    private function checkPrerequisites(): bool
    {
        $issues = [];

        if (!config('postman.api_key')) {
            $issues[] = 'POSTMAN_API_KEY not configured';
        }

        if (!config('postman.collection_id')) {
            $issues[] = 'POSTMAN_COLLECTION_ID not configured';
        }

        if (!empty($issues)) {
            $this->error('❌ Configuration issues found:');
            foreach ($issues as $issue) {
                $this->line("  - {$issue}");
            }
            $this->info('');
            $this->info('💡 Please run: php artisan postman:setup first');
            return false;
        }

        return true;
    }

    /**
     * Update environment file
     */
    private function updateEnvFile(array $updates)
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('❌ .env file not found');
            return;
        }

        $envContent = File::get($envPath);

        foreach ($updates as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envPath, $envContent);
        $this->line("✅ Updated .env file");
    }

    /**
     * Show next steps
     */
    private function showNextSteps()
    {
        $this->info('');
        $this->info('🎯 Next Steps:');
        $this->info('==============');
        $this->line('1. 🔄 Test sync: php artisan api:auto-sync --force');
        $this->line('2. 📊 Check status: php artisan api:setup-auto-sync --status');
        $this->line('3. 👀 Watch mode: php artisan api:auto-sync --watch');
        $this->line('4. 📅 Schedule: Add to cron or use Laravel scheduler');
        $this->line('5. 📝 Monitor: tail -f storage/logs/api-auto-sync.log');
        $this->info('');
        $this->info('🔗 Useful commands:');
        $this->line('- php artisan schedule:list (view scheduled tasks)');
        $this->line('- php artisan queue:work (if using queue)');
        $this->line('- php artisan api:auto-sync --postman (Postman only)');
        $this->line('- php artisan api:auto-sync --docs (Documentation only)');
    }
}
