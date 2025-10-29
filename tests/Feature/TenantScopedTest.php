<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Product;
use App\Traits\TenantScoped;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class TenantScopedTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test tenants
        $this->tenant1 = Tenant::factory()->create(['name' => 'Tenant 1']);
        $this->tenant2 = Tenant::factory()->create(['name' => 'Tenant 2']);
    }

    /**
     * Test automatic tenant_id assignment on creation
     */
    public function test_automatic_tenant_id_assignment()
    {
        // Set current tenant
        TenantScoped::setCurrentTenant($this->tenant1);

        // Create a user (which uses TenantScoped trait)
        $user = User::factory()->create(['username' => 'testuser']);

        // Check that tenant_id was automatically assigned
        $this->assertEquals($this->tenant1->id, $user->tenant_id);
    }

    /**
     * Test tenant scope filtering
     */
    public function test_tenant_scope_filtering()
    {
        // Create users for different tenants
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Set current tenant to tenant1
        TenantScoped::setCurrentTenant($this->tenant1);

        // Query users - should only return users from tenant1
        $users = User::all();
        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);

        // Switch to tenant2
        TenantScoped::setCurrentTenant($this->tenant2);

        // Query users - should only return users from tenant2
        $users = User::all();
        $this->assertCount(1, $users);
        $this->assertEquals($user2->id, $users->first()->id);
    }

    /**
     * Test withoutTenantScope method
     */
    public function test_without_tenant_scope()
    {
        // Create users for different tenants
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Set current tenant
        TenantScoped::setCurrentTenant($this->tenant1);

        // Query without tenant scope - should return all users
        $allUsers = User::withoutTenantScope()->get();
        $this->assertCount(2, $allUsers);

        // Query with scope - should return only tenant1 users
        $tenantUsers = User::all();
        $this->assertCount(1, $tenantUsers);
    }

    /**
     * Test forTenant method
     */
    public function test_for_tenant_method()
    {
        // Create users for different tenants
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Query for specific tenant
        $tenant1Users = User::forTenant($this->tenant1->id)->get();
        $this->assertCount(1, $tenant1Users);
        $this->assertEquals($user1->id, $tenant1Users->first()->id);

        $tenant2Users = User::forTenant($this->tenant2->id)->get();
        $this->assertCount(1, $tenant2Users);
        $this->assertEquals($user2->id, $tenant2Users->first()->id);
    }

    /**
     * Test forTenants method (multiple tenants)
     */
    public function test_for_tenants_method()
    {
        $tenant3 = Tenant::factory()->create(['name' => 'Tenant 3']);

        // Create users for different tenants
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);
        $user3 = User::factory()->create(['tenant_id' => $tenant3->id]);

        // Query for multiple tenants
        $users = User::forTenants([$this->tenant1->id, $this->tenant2->id])->get();
        $this->assertCount(2, $users);
        
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertContains($user2->id, $userIds);
        $this->assertNotContains($user3->id, $userIds);
    }

    /**
     * Test tenant context switching
     */
    public function test_tenant_context_switching()
    {
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Test asTenant method
        $result = TenantScoped::asTenant($this->tenant1, function() {
            return User::count();
        });
        $this->assertEquals(1, $result);

        $result = TenantScoped::asTenant($this->tenant2, function() {
            return User::count();
        });
        $this->assertEquals(1, $result);
    }

    /**
     * Test current tenant resolution
     */
    public function test_current_tenant_resolution()
    {
        // Test service container binding
        app()->instance('current_tenant', $this->tenant1);
        $this->assertEquals($this->tenant1->id, TenantScoped::getCurrentTenantId());

        // Test session-based resolution
        app()->forgetInstance('current_tenant');
        session(['current_tenant_id' => $this->tenant2->id]);
        $this->assertEquals($this->tenant2->id, TenantScoped::getCurrentTenantId());

        // Clean up
        session()->forget('current_tenant_id');
    }

    /**
     * Test tenant_id update prevention
     */
    public function test_tenant_id_update_prevention()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);

        // Try to update tenant_id - should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot change tenant_id after record creation for security reasons');
        
        $user->update(['tenant_id' => $this->tenant2->id]);
    }

    /**
     * Test belongsToCurrentTenant method
     */
    public function test_belongs_to_current_tenant()
    {
        TenantScoped::setCurrentTenant($this->tenant1);
        
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        $this->assertTrue($user1->belongsToCurrentTenant());
        $this->assertFalse($user2->belongsToCurrentTenant());
    }

    /**
     * Test ensureBelongsToCurrentTenant method
     */
    public function test_ensure_belongs_to_current_tenant()
    {
        TenantScoped::setCurrentTenant($this->tenant1);
        
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Should not throw exception for user1
        $user1->ensureBelongsToCurrentTenant();

        // Should throw exception for user2
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Access denied: Record does not belong to current tenant');
        $user2->ensureBelongsToCurrentTenant();
    }

    /**
     * Test tenant cache key generation
     */
    public function test_tenant_cache_key()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        
        $cacheKey = $user->getTenantCacheKey('user_settings');
        $expectedKey = "tenant_{$this->tenant1->id}_user_settings";
        
        $this->assertEquals($expectedKey, $cacheKey);
    }

    /**
     * Test currentTenant scope
     */
    public function test_current_tenant_scope()
    {
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Set current tenant
        TenantScoped::setCurrentTenant($this->tenant1);

        // Use currentTenant scope
        $users = User::withoutTenantScope()->currentTenant()->get();
        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }

    /**
     * Test tenant scope
     */
    public function test_tenant_scope()
    {
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Use tenant scope
        $users = User::withoutTenantScope()->tenant($this->tenant1->id)->get();
        $this->assertCount(1, $users);
        $this->assertEquals($user1->id, $users->first()->id);
    }

    /**
     * Test exceptTenant scope
     */
    public function test_except_tenant_scope()
    {
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Use exceptTenant scope
        $users = User::withoutTenantScope()->exceptTenant($this->tenant1->id)->get();
        $this->assertCount(1, $users);
        $this->assertEquals($user2->id, $users->first()->id);
    }

    /**
     * Test tenant relationship
     */
    public function test_tenant_relationship()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        
        // Test tenant relationship
        $this->assertEquals($this->tenant1->id, $user->tenant->id);
        $this->assertEquals($this->tenant1->name, $user->tenant->name);
    }

    /**
     * Test security when no tenant context
     */
    public function test_security_without_tenant_context()
    {
        // Clear any tenant context
        app()->forgetInstance('current_tenant');
        session()->forget('current_tenant_id');
        
        $user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);

        // Without tenant context, should return no results for security
        $users = User::all();
        $this->assertCount(0, $users);
    }

    /**
     * Test that Tenant model itself is not scoped
     */
    public function test_tenant_model_not_scoped()
    {
        // Create multiple tenants
        $tenant3 = Tenant::factory()->create(['name' => 'Tenant 3']);

        // Set current tenant
        TenantScoped::setCurrentTenant($this->tenant1);

        // Tenant model should return all tenants, not be scoped
        $tenants = Tenant::all();
        $this->assertGreaterThanOrEqual(3, $tenants->count());
    }

    /**
     * Test console context exemption
     */
    public function test_console_context_exemption()
    {
        // This test would need to simulate console context
        // For now, we'll test the basic functionality
        $this->assertTrue(true); // Placeholder
    }
}
