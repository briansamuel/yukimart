<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_run_notifications_migration()
    {
        // Check if notifications table exists
        $this->assertTrue(Schema::hasTable('notifications'));
        
        // Check if all required columns exist
        $this->assertTrue(Schema::hasColumn('notifications', 'id'));
        $this->assertTrue(Schema::hasColumn('notifications', 'type'));
        $this->assertTrue(Schema::hasColumn('notifications', 'notifiable_type'));
        $this->assertTrue(Schema::hasColumn('notifications', 'notifiable_id'));
        $this->assertTrue(Schema::hasColumn('notifications', 'title'));
        $this->assertTrue(Schema::hasColumn('notifications', 'message'));
        $this->assertTrue(Schema::hasColumn('notifications', 'data'));
        $this->assertTrue(Schema::hasColumn('notifications', 'priority'));
        $this->assertTrue(Schema::hasColumn('notifications', 'channels'));
        $this->assertTrue(Schema::hasColumn('notifications', 'read_at'));
        $this->assertTrue(Schema::hasColumn('notifications', 'expires_at'));
        $this->assertTrue(Schema::hasColumn('notifications', 'created_by'));
        $this->assertTrue(Schema::hasColumn('notifications', 'created_at'));
        $this->assertTrue(Schema::hasColumn('notifications', 'updated_at'));
    }

    /** @test */
    public function it_can_run_translations_migration()
    {
        // Check if translations table exists
        $this->assertTrue(Schema::hasTable('translations'));
        
        // Check if all required columns exist
        $this->assertTrue(Schema::hasColumn('translations', 'id'));
        $this->assertTrue(Schema::hasColumn('translations', 'translatable_type'));
        $this->assertTrue(Schema::hasColumn('translations', 'translatable_id'));
        $this->assertTrue(Schema::hasColumn('translations', 'language_code'));
        $this->assertTrue(Schema::hasColumn('translations', 'field_name'));
        $this->assertTrue(Schema::hasColumn('translations', 'field_value'));
        $this->assertTrue(Schema::hasColumn('translations', 'is_approved'));
        $this->assertTrue(Schema::hasColumn('translations', 'created_by'));
        $this->assertTrue(Schema::hasColumn('translations', 'updated_by'));
        $this->assertTrue(Schema::hasColumn('translations', 'created_at'));
        $this->assertTrue(Schema::hasColumn('translations', 'updated_at'));
    }

    /** @test */
    public function it_can_run_orders_payment_migration()
    {
        // Check if orders table has payment columns
        $this->assertTrue(Schema::hasColumn('orders', 'payment_method'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_status'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_reference'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_date'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_notes'));
        $this->assertTrue(Schema::hasColumn('orders', 'due_date'));
        $this->assertTrue(Schema::hasColumn('orders', 'internal_notes'));
    }

    /** @test */
    public function it_has_correct_indexes_on_notifications_table()
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('notifications');
        
        $indexNames = array_keys($indexes);
        
        // Check if required indexes exist
        $this->assertContains('notifications_notifiable_index', $indexNames);
        $this->assertContains('notifications_notifiable_read_index', $indexNames);
        $this->assertContains('notifications_type_created_index', $indexNames);
        $this->assertContains('notifications_priority_created_index', $indexNames);
        $this->assertContains('notifications_expires_index', $indexNames);
    }

    /** @test */
    public function it_has_correct_indexes_on_translations_table()
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('translations');
        
        $indexNames = array_keys($indexes);
        
        // Check if required indexes exist
        $this->assertContains('translations_translatable_index', $indexNames);
        $this->assertContains('translations_language_approved_index', $indexNames);
        $this->assertContains('translations_unique', $indexNames);
    }

    /** @test */
    public function it_can_insert_data_into_notifications_table()
    {
        $data = [
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'order',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => 1,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'data' => json_encode(['test' => 'data']),
            'priority' => 'normal',
            'channels' => json_encode(['web']),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $inserted = \DB::table('notifications')->insert($data);
        $this->assertTrue($inserted);

        $notification = \DB::table('notifications')->where('id', $data['id'])->first();
        $this->assertNotNull($notification);
        $this->assertEquals('Test Notification', $notification->title);
    }

    /** @test */
    public function it_can_insert_data_into_translations_table()
    {
        // First create a language record
        \DB::table('languages')->insert([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = [
            'translatable_type' => 'App\\Models\\Product',
            'translatable_id' => 1,
            'language_code' => 'en',
            'field_name' => 'name',
            'field_value' => 'Test Product',
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $inserted = \DB::table('translations')->insert($data);
        $this->assertTrue($inserted);

        $translation = \DB::table('translations')
            ->where('translatable_type', $data['translatable_type'])
            ->where('translatable_id', $data['translatable_id'])
            ->where('language_code', $data['language_code'])
            ->where('field_name', $data['field_name'])
            ->first();
            
        $this->assertNotNull($translation);
        $this->assertEquals('Test Product', $translation->field_value);
    }

    /** @test */
    public function it_enforces_unique_constraint_on_translations()
    {
        // First create a language record
        \DB::table('languages')->insert([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = [
            'translatable_type' => 'App\\Models\\Product',
            'translatable_id' => 1,
            'language_code' => 'en',
            'field_name' => 'name',
            'field_value' => 'Test Product',
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert first record
        \DB::table('translations')->insert($data);

        // Try to insert duplicate - should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        \DB::table('translations')->insert($data);
    }

    /** @test */
    public function it_can_rollback_migrations()
    {
        // Test rollback of notifications migration
        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_06_18_000003_create_notifications_table.php',
            '--force' => true
        ]);

        $this->assertFalse(Schema::hasTable('notifications'));

        // Re-run the migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_06_18_000003_create_notifications_table.php',
            '--force' => true
        ]);

        $this->assertTrue(Schema::hasTable('notifications'));
    }
}
