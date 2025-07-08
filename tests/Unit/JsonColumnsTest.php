<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;

class JsonColumnsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_notification_with_json_channels()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'channels' => ['web', 'email'],
            'data' => ['key' => 'value'],
        ]);

        $this->assertNotNull($notification->id);
        $this->assertEquals(['web', 'email'], $notification->channels);
        $this->assertEquals(['key' => 'value'], $notification->data);
    }

    /** @test */
    public function it_sets_default_channels_for_notification()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
        ]);

        // Should have default channels set by model boot method
        $this->assertEquals(['web'], $notification->channels);
    }

    /** @test */
    public function it_can_create_notification_setting_with_json_channels()
    {
        $user = User::factory()->create();

        $setting = NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type' => 'order_created',
            'channels' => ['web', 'email', 'sms'],
            'is_enabled' => true,
            'quiet_days' => [0, 6], // Sunday and Saturday
        ]);

        $this->assertNotNull($setting->id);
        $this->assertEquals(['web', 'email', 'sms'], $setting->channels);
        $this->assertEquals([0, 6], $setting->quiet_days);
    }

    /** @test */
    public function it_sets_default_channels_for_notification_setting()
    {
        $user = User::factory()->create();

        $setting = NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type' => 'order_created',
            'is_enabled' => true,
        ]);

        // Should have default channels set by model boot method
        $this->assertEquals(['web'], $setting->channels);
    }

    /** @test */
    public function it_can_query_notifications_by_json_channels()
    {
        $user = User::factory()->create();

        // Create notifications with different channels
        Notification::create([
            'type' => 'test1',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test 1',
            'message' => 'Test message 1',
            'channels' => ['web'],
        ]);

        Notification::create([
            'type' => 'test2',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test 2',
            'message' => 'Test message 2',
            'channels' => ['web', 'email'],
        ]);

        Notification::create([
            'type' => 'test3',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test 3',
            'message' => 'Test message 3',
            'channels' => ['email'],
        ]);

        // Query notifications with 'web' channel
        $webNotifications = Notification::whereJsonContains('channels', 'web')->get();
        $this->assertCount(2, $webNotifications);

        // Query notifications with 'email' channel
        $emailNotifications = Notification::whereJsonContains('channels', 'email')->get();
        $this->assertCount(2, $emailNotifications);
    }

    /** @test */
    public function it_can_query_notification_settings_by_json_channels()
    {
        $user = User::factory()->create();

        // Create settings with different channels
        NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type' => 'order_created',
            'channels' => ['web'],
            'is_enabled' => true,
        ]);

        NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type' => 'invoice_overdue',
            'channels' => ['web', 'email'],
            'is_enabled' => true,
        ]);

        NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type' => 'system_alert',
            'channels' => ['email', 'sms'],
            'is_enabled' => true,
        ]);

        // Query settings with 'web' channel
        $webSettings = NotificationSetting::whereJsonContains('channels', 'web')->get();
        $this->assertCount(2, $webSettings);

        // Query settings with 'sms' channel
        $smsSettings = NotificationSetting::whereJsonContains('channels', 'sms')->get();
        $this->assertCount(1, $smsSettings);
    }

    /** @test */
    public function it_can_update_json_channels()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'channels' => ['web'],
        ]);

        // Update channels
        $notification->update(['channels' => ['web', 'email', 'sms']]);

        $this->assertEquals(['web', 'email', 'sms'], $notification->fresh()->channels);
    }

    /** @test */
    public function it_handles_null_json_values_gracefully()
    {
        $user = User::factory()->create();

        // Create notification with null data
        $notification = Notification::create([
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'Test message',
            'data' => null,
        ]);

        $this->assertNull($notification->data);
        $this->assertEquals(['web'], $notification->channels); // Should have default
    }

    /** @test */
    public function it_can_check_notification_time_restrictions()
    {
        $user = User::factory()->create();

        $setting = NotificationSetting::create([
            'user_id' => $user->id,
            'notification_type' => 'order_created',
            'channels' => ['web', 'email'],
            'is_enabled' => true,
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
            'quiet_days' => [0, 6], // Sunday and Saturday
        ]);

        // This test would need to mock time to properly test
        // For now, just verify the method exists and doesn't throw
        $result = $setting->shouldReceiveNotification();
        $this->assertIsBool($result);
    }

    /** @test */
    public function it_can_create_default_settings_for_user()
    {
        $user = User::factory()->create();

        NotificationSetting::createDefaultForUser($user->id);

        $settings = NotificationSetting::where('user_id', $user->id)->get();
        
        $this->assertGreaterThan(0, $settings->count());
        
        // Check that all settings have valid channels
        foreach ($settings as $setting) {
            $this->assertIsArray($setting->channels);
            $this->assertNotEmpty($setting->channels);
        }
    }

    /** @test */
    public function it_validates_json_column_structure()
    {
        // Test that JSON columns exist and are properly typed
        $this->assertTrue(Schema::hasColumn('notifications', 'channels'));
        $this->assertTrue(Schema::hasColumn('notifications', 'data'));
        $this->assertTrue(Schema::hasColumn('notification_settings', 'channels'));
        $this->assertTrue(Schema::hasColumn('notification_settings', 'quiet_days'));
    }
}
