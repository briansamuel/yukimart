<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class TenantUserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test tenant user creation
     */
    public function test_tenant_user_can_be_created()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();

        $tenantUser = TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => TenantUser::ROLE_ADMIN,
            'is_active' => true
        ]);

        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => TenantUser::ROLE_ADMIN,
            'is_active' => true
        ]);

        $this->assertEquals($tenant->id, $tenantUser->tenant_id);
        $this->assertEquals($user->id, $tenantUser->user_id);
        $this->assertEquals(TenantUser::ROLE_ADMIN, $tenantUser->role);
    }

    /**
     * Test tenant user relationships
     */
    public function test_tenant_user_relationships()
    {
        $tenant = Tenant::factory()->create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['username' => 'testuser']);
        $inviter = User::factory()->create(['username' => 'inviter']);

        $tenantUser = TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'invited_by' => $inviter->id
        ]);

        // Test relationships
        $this->assertEquals('Test Tenant', $tenantUser->tenant->name);
        $this->assertEquals('testuser', $tenantUser->user->username);
        $this->assertEquals('inviter', $tenantUser->inviter->username);
    }

    /**
     * Test permission management
     */
    public function test_permission_management()
    {
        $tenantUser = TenantUser::factory()->create([
            'permissions' => ['products.view', 'orders.create']
        ]);

        // Test has permission
        $this->assertTrue($tenantUser->hasPermission('products.view'));
        $this->assertTrue($tenantUser->hasPermission('orders.create'));
        $this->assertFalse($tenantUser->hasPermission('users.delete'));

        // Test add permission
        $tenantUser->addPermission('reports.view');
        $this->assertTrue($tenantUser->hasPermission('reports.view'));

        // Test remove permission
        $tenantUser->removePermission('orders.create');
        $this->assertFalse($tenantUser->hasPermission('orders.create'));
    }

    /**
     * Test restriction management
     */
    public function test_restriction_management()
    {
        $tenantUser = TenantUser::factory()->create([
            'restrictions' => ['no_delete_permissions', 'limited_branch_access']
        ]);

        // Test has restriction
        $this->assertTrue($tenantUser->hasRestriction('no_delete_permissions'));
        $this->assertTrue($tenantUser->hasRestriction('limited_branch_access'));
        $this->assertFalse($tenantUser->hasRestriction('read_only_reports'));
    }

    /**
     * Test role-based methods
     */
    public function test_role_based_methods()
    {
        $owner = TenantUser::factory()->owner()->create();
        $admin = TenantUser::factory()->admin()->create();
        $manager = TenantUser::factory()->manager()->create();
        $staff = TenantUser::factory()->staff()->create();

        // Test isOwner
        $this->assertTrue($owner->isOwner());
        $this->assertFalse($admin->isOwner());

        // Test isAdmin
        $this->assertTrue($owner->isAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($manager->isAdmin());

        // Test canManageUsers
        $this->assertTrue($owner->canManageUsers());
        $this->assertTrue($admin->canManageUsers());
        $this->assertTrue($manager->canManageUsers());
        $this->assertFalse($staff->canManageUsers());
    }

    /**
     * Test activation and deactivation
     */
    public function test_activation_and_deactivation()
    {
        $tenantUser = TenantUser::factory()->create(['is_active' => false]);

        // Test activation
        $tenantUser->activate();
        $this->assertTrue($tenantUser->is_active);
        $this->assertNotNull($tenantUser->approved_at);

        // Test deactivation
        $tenantUser->deactivate();
        $this->assertFalse($tenantUser->is_active);
    }

    /**
     * Test invitation management
     */
    public function test_invitation_management()
    {
        $pendingUser = TenantUser::factory()->pendingInvitation()->create();
        $activeUser = TenantUser::factory()->create(['is_active' => true]);

        // Test accept invitation
        $pendingUser->acceptInvitation();
        $this->assertEquals(TenantUser::INVITATION_ACCEPTED, $pendingUser->invitation_status);
        $this->assertTrue($pendingUser->is_active);
        $this->assertNotNull($pendingUser->joined_at);

        // Test decline invitation
        $anotherPendingUser = TenantUser::factory()->pendingInvitation()->create();
        $anotherPendingUser->declineInvitation();
        $this->assertEquals(TenantUser::INVITATION_DECLINED, $anotherPendingUser->invitation_status);
        $this->assertFalse($anotherPendingUser->is_active);
    }

    /**
     * Test access control
     */
    public function test_access_control()
    {
        $activeUser = TenantUser::factory()->create([
            'is_active' => true,
            'invitation_status' => TenantUser::INVITATION_ACCEPTED
        ]);

        $inactiveUser = TenantUser::factory()->create(['is_active' => false]);
        
        $expiredUser = TenantUser::factory()->expiredAccess()->create();
        
        $pendingUser = TenantUser::factory()->pendingInvitation()->create();

        // Test canAccess
        $this->assertTrue($activeUser->canAccess());
        $this->assertFalse($inactiveUser->canAccess());
        $this->assertFalse($expiredUser->canAccess());
        $this->assertFalse($pendingUser->canAccess());
    }

    /**
     * Test last access update
     */
    public function test_last_access_update()
    {
        $tenantUser = TenantUser::factory()->create();
        $originalLastAccess = $tenantUser->last_access_at;

        // Update last access
        $tenantUser->updateLastAccess();
        
        $tenantUser->refresh();
        $this->assertNotEquals($originalLastAccess, $tenantUser->last_access_at);
        $this->assertNotNull($tenantUser->last_access_at);
    }

    /**
     * Test factory states
     */
    public function test_factory_states()
    {
        // Test owner state
        $owner = TenantUser::factory()->owner()->create();
        $this->assertEquals(TenantUser::ROLE_OWNER, $owner->role);
        $this->assertTrue($owner->is_primary);
        $this->assertContains('*', $owner->permissions);

        // Test admin state
        $admin = TenantUser::factory()->admin()->create();
        $this->assertEquals(TenantUser::ROLE_ADMIN, $admin->role);
        $this->assertContains('users.manage', $admin->permissions);

        // Test staff state
        $staff = TenantUser::factory()->staff()->create();
        $this->assertEquals(TenantUser::ROLE_STAFF, $staff->role);
        $this->assertContains('products.view', $staff->permissions);

        // Test inactive state
        $inactive = TenantUser::factory()->inactive()->create();
        $this->assertFalse($inactive->is_active);

        // Test pending invitation state
        $pending = TenantUser::factory()->pendingInvitation()->create();
        $this->assertEquals(TenantUser::INVITATION_PENDING, $pending->invitation_status);
        $this->assertNotNull($pending->invitation_token);
        $this->assertFalse($pending->is_active);

        // Test expired access state
        $expired = TenantUser::factory()->expiredAccess()->create();
        $this->assertNotNull($expired->access_expires_at);
        $this->assertTrue(Carbon::now()->gt($expired->access_expires_at));
        $this->assertFalse($expired->is_active);
    }

    /**
     * Test scopes
     */
    public function test_scopes()
    {
        $tenant = Tenant::factory()->create();
        
        $activeUser = TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true
        ]);
        
        $inactiveUser = TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => false
        ]);
        
        $adminUser = TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => TenantUser::ROLE_ADMIN
        ]);
        
        $pendingUser = TenantUser::factory()->pendingInvitation()->create([
            'tenant_id' => $tenant->id
        ]);

        // Test active scope
        $activeResults = TenantUser::active()->get();
        $this->assertTrue($activeResults->contains($activeUser));
        $this->assertFalse($activeResults->contains($inactiveUser));

        // Test byRole scope
        $adminResults = TenantUser::byRole(TenantUser::ROLE_ADMIN)->get();
        $this->assertTrue($adminResults->contains($adminUser));
        $this->assertFalse($adminResults->contains($activeUser));

        // Test pendingInvitations scope
        $pendingResults = TenantUser::pendingInvitations()->get();
        $this->assertTrue($pendingResults->contains($pendingUser));
        $this->assertFalse($pendingResults->contains($activeUser));
    }

    /**
     * Test accessors
     */
    public function test_accessors()
    {
        $tenantUser = TenantUser::factory()->create([
            'role' => TenantUser::ROLE_ADMIN,
            'is_active' => true,
            'joined_at' => Carbon::now()->subDays(30)
        ]);

        // Test role badge
        $this->assertStringContainsString('Quản trị viên', $tenantUser->role_badge);

        // Test status badge
        $this->assertStringContainsString('Hoạt động', $tenantUser->status_badge);

        // Test days since joined
        $this->assertEquals(30, $tenantUser->days_since_joined);

        // Test access status
        $this->assertEquals('active', $tenantUser->access_status);
    }

    /**
     * Test constants and static methods
     */
    public function test_constants_and_static_methods()
    {
        // Test role constants
        $roles = TenantUser::getRoles();
        $this->assertArrayHasKey(TenantUser::ROLE_OWNER, $roles);
        $this->assertArrayHasKey(TenantUser::ROLE_ADMIN, $roles);
        $this->assertEquals('Chủ sở hữu', $roles[TenantUser::ROLE_OWNER]);

        // Test invitation status constants
        $statuses = TenantUser::getInvitationStatuses();
        $this->assertArrayHasKey(TenantUser::INVITATION_PENDING, $statuses);
        $this->assertArrayHasKey(TenantUser::INVITATION_ACCEPTED, $statuses);
        $this->assertEquals('Chờ xác nhận', $statuses[TenantUser::INVITATION_PENDING]);
    }
}
