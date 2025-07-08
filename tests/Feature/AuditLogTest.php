<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_audit_log_entry()
    {
        $auditLog = AuditLog::create([
            'user_id' => $this->user->id,
            'action' => 'created',
            'model_type' => 'App\Models\Product',
            'model_id' => 1,
            'description' => 'Test audit log entry',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'url' => 'http://test.com',
            'method' => 'POST',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'created',
            'model_type' => 'App\Models\Product',
            'model_id' => 1,
        ]);

        $this->assertEquals('created', $auditLog->action);
        $this->assertEquals($this->user->id, $auditLog->user_id);
    }

    /** @test */
    public function it_can_log_model_creation()
    {
        $product = Product::factory()->create();
        
        $auditLog = AuditLog::logCreated($product, 'Product created via test');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'model_type' => 'App\Models\Product',
            'model_id' => $product->id,
        ]);

        $this->assertEquals('created', $auditLog->action);
        $this->assertEquals(get_class($product), $auditLog->model_type);
        $this->assertEquals($product->id, $auditLog->model_id);
    }

    /** @test */
    public function it_can_log_model_update()
    {
        $product = Product::factory()->create(['name' => 'Original Name']);
        $oldValues = $product->toArray();
        
        $product->update(['name' => 'Updated Name']);
        
        $auditLog = AuditLog::logUpdated($product, $oldValues, 'Product updated via test');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'model_type' => 'App\Models\Product',
            'model_id' => $product->id,
        ]);

        $this->assertEquals('updated', $auditLog->action);
        $this->assertArrayHasKey('name', $auditLog->old_values);
        $this->assertEquals('Original Name', $auditLog->old_values['name']);
        $this->assertEquals('Updated Name', $auditLog->new_values['name']);
    }

    /** @test */
    public function it_can_log_model_deletion()
    {
        $product = Product::factory()->create();
        
        $auditLog = AuditLog::logDeleted($product, 'Product deleted via test');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'model_type' => 'App\Models\Product',
            'model_id' => $product->id,
        ]);

        $this->assertEquals('deleted', $auditLog->action);
        $this->assertNotEmpty($auditLog->old_values);
        $this->assertEmpty($auditLog->new_values);
    }

    /** @test */
    public function it_can_log_user_login()
    {
        $auditLog = AuditLog::logLogin($this->user);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals('login', $auditLog->action);
        $this->assertEquals($this->user->id, $auditLog->user_id);
    }

    /** @test */
    public function it_can_log_user_logout()
    {
        $auditLog = AuditLog::logLogout($this->user);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'logout',
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals('logout', $auditLog->action);
        $this->assertEquals($this->user->id, $auditLog->user_id);
    }

    /** @test */
    public function it_can_filter_by_action()
    {
        AuditLog::factory()->count(3)->create(['action' => 'created']);
        AuditLog::factory()->count(2)->create(['action' => 'updated']);

        $createdLogs = AuditLog::action('created')->get();
        $updatedLogs = AuditLog::action('updated')->get();

        $this->assertCount(3, $createdLogs);
        $this->assertCount(2, $updatedLogs);
    }

    /** @test */
    public function it_can_filter_by_user()
    {
        $otherUser = User::factory()->create();
        
        AuditLog::factory()->count(3)->forUser($this->user)->create();
        AuditLog::factory()->count(2)->forUser($otherUser)->create();

        $userLogs = AuditLog::byUser($this->user->id)->get();
        $otherUserLogs = AuditLog::byUser($otherUser->id)->get();

        $this->assertCount(3, $userLogs);
        $this->assertCount(2, $otherUserLogs);
    }

    /** @test */
    public function it_can_filter_recent_logs()
    {
        // Create old logs
        AuditLog::factory()->count(2)->create([
            'created_at' => now()->subDays(10)
        ]);
        
        // Create recent logs
        AuditLog::factory()->count(3)->recent()->create();

        $recentLogs = AuditLog::recent()->get();

        $this->assertCount(3, $recentLogs);
    }

    /** @test */
    public function it_has_correct_relationships()
    {
        $auditLog = AuditLog::factory()->forUser($this->user)->create();

        $this->assertInstanceOf(User::class, $auditLog->user);
        $this->assertEquals($this->user->id, $auditLog->user->id);
    }

    /** @test */
    public function it_formats_action_display_correctly()
    {
        $auditLog = AuditLog::factory()->create(['action' => 'created']);

        $this->assertEquals('Tạo mới', $auditLog->action_display);
    }

    /** @test */
    public function it_formats_model_display_correctly()
    {
        $auditLog = AuditLog::factory()->create(['model_type' => 'App\Models\Product']);

        $this->assertEquals('Sản phẩm', $auditLog->model_display);
    }

    /** @test */
    public function it_can_get_changes_summary()
    {
        $auditLog = AuditLog::factory()->create([
            'old_values' => ['name' => 'Old Name', 'price' => 100],
            'new_values' => ['name' => 'New Name', 'price' => 150],
        ]);

        $changes = $auditLog->changes_summary;

        $this->assertIsArray($changes);
        $this->assertCount(2, $changes);
        
        $nameChange = collect($changes)->firstWhere('field', 'name');
        $this->assertEquals('Old Name', $nameChange['old']);
        $this->assertEquals('New Name', $nameChange['new']);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        // Create test data
        AuditLog::factory()->count(5)->create(['action' => 'created']);
        AuditLog::factory()->count(3)->create(['action' => 'updated']);
        AuditLog::factory()->count(2)->forUser($this->user)->create();

        $stats = AuditLog::getStatistics();

        $this->assertArrayHasKey('total_logs', $stats);
        $this->assertArrayHasKey('actions_count', $stats);
        $this->assertArrayHasKey('models_count', $stats);
        $this->assertArrayHasKey('users_count', $stats);
        $this->assertArrayHasKey('daily_activity', $stats);

        $this->assertGreaterThan(0, $stats['total_logs']);
    }
}
