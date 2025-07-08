<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;

class TestJsonColumnsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:json-columns 
                            {--create-sample : Create sample data}
                            {--test-queries : Test JSON queries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test JSON columns functionality after removing default values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing JSON Columns Functionality');
        $this->newLine();

        // Check if tables exist
        $this->checkTables();

        // Test JSON column structure
        $this->testJsonColumnStructure();

        if ($this->option('create-sample')) {
            $this->createSampleData();
        }

        if ($this->option('test-queries')) {
            $this->testJsonQueries();
        }

        $this->info('🎉 JSON columns test completed!');
        return 0;
    }

    /**
     * Check if required tables exist.
     */
    protected function checkTables()
    {
        $this->info('📋 Checking table existence...');

        $tables = ['notifications', 'notification_settings'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("   ✅ Table '{$table}' exists");
            } else {
                $this->error("   ❌ Table '{$table}' does not exist");
            }
        }

        $this->newLine();
    }

    /**
     * Test JSON column structure.
     */
    protected function testJsonColumnStructure()
    {
        $this->info('🔍 Testing JSON column structure...');

        // Test notifications table
        if (Schema::hasTable('notifications')) {
            $columns = Schema::getColumnListing('notifications');
            
            if (in_array('channels', $columns)) {
                $this->info('   ✅ notifications.channels column exists');
                
                // Check column type
                $columnType = DB::select("SHOW COLUMNS FROM notifications LIKE 'channels'")[0]->Type ?? '';
                if (strpos(strtolower($columnType), 'json') !== false) {
                    $this->info('   ✅ notifications.channels is JSON type');
                } else {
                    $this->warn("   ⚠️  notifications.channels type: {$columnType}");
                }
            } else {
                $this->error('   ❌ notifications.channels column missing');
            }

            if (in_array('data', $columns)) {
                $this->info('   ✅ notifications.data column exists');
            }
        }

        // Test notification_settings table
        if (Schema::hasTable('notification_settings')) {
            $columns = Schema::getColumnListing('notification_settings');
            
            if (in_array('channels', $columns)) {
                $this->info('   ✅ notification_settings.channels column exists');
            }

            if (in_array('quiet_days', $columns)) {
                $this->info('   ✅ notification_settings.quiet_days column exists');
            }
        }

        $this->newLine();
    }

    /**
     * Create sample data to test JSON functionality.
     */
    protected function createSampleData()
    {
        $this->info('📝 Creating sample data...');

        try {
            // Create a test user if needed
            $user = User::first();
            if (!$user) {
                $this->warn('   ⚠️  No users found, creating test user...');
                $user = User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);
                $this->info('   ✅ Test user created');
            }

            // Test creating notification with JSON channels
            $notification = Notification::create([
                'type' => 'test',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'title' => 'Test Notification',
                'message' => 'This is a test notification',
                'data' => ['test_key' => 'test_value'],
                'channels' => ['web', 'email'],
                'priority' => 'normal',
            ]);

            $this->info('   ✅ Test notification created with ID: ' . $notification->id);

            // Test creating notification setting
            $setting = NotificationSetting::create([
                'user_id' => $user->id,
                'notification_type' => 'test_type',
                'channels' => ['web', 'email', 'sms'],
                'is_enabled' => true,
                'quiet_days' => [0, 6], // Sunday and Saturday
            ]);

            $this->info('   ✅ Test notification setting created with ID: ' . $setting->id);

            // Test default values
            $notificationWithDefaults = Notification::create([
                'type' => 'test_defaults',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'title' => 'Test Default Values',
                'message' => 'Testing default values',
            ]);

            $this->info('   ✅ Notification with defaults created');
            $this->info('   📊 Default channels: ' . json_encode($notificationWithDefaults->channels));

        } catch (\Exception $e) {
            $this->error('   ❌ Error creating sample data: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test JSON queries.
     */
    protected function testJsonQueries()
    {
        $this->info('🔍 Testing JSON queries...');

        try {
            // Test JSON contains query
            $webNotifications = Notification::whereJsonContains('channels', 'web')->count();
            $this->info("   ✅ Notifications with 'web' channel: {$webNotifications}");

            // Test JSON length query
            $multiChannelNotifications = Notification::whereRaw('JSON_LENGTH(channels) > 1')->count();
            $this->info("   ✅ Notifications with multiple channels: {$multiChannelNotifications}");

            // Test JSON extract query
            $priorities = Notification::select(DB::raw('priority, COUNT(*) as count'))
                                    ->groupBy('priority')
                                    ->get();
            
            $this->info('   📊 Notifications by priority:');
            foreach ($priorities as $priority) {
                $this->info("      - {$priority->priority}: {$priority->count}");
            }

            // Test notification settings queries
            $enabledSettings = NotificationSetting::enabled()->count();
            $this->info("   ✅ Enabled notification settings: {$enabledSettings}");

            $webSettings = NotificationSetting::whereJsonContains('channels', 'web')->count();
            $this->info("   ✅ Settings with 'web' channel: {$webSettings}");

        } catch (\Exception $e) {
            $this->error('   ❌ Error testing JSON queries: ' . $e->getMessage());
        }

        $this->newLine();
    }
}
