<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;

class UserTimeStampTraitTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_sets_created_by_and_updated_by_when_creating_product()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $this->assertEquals($this->user->id, $product->created_by);
        $this->assertEquals($this->user->id, $product->updated_by);
    }

    /** @test */
    public function it_sets_updated_by_when_updating_product()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        // Create another user and switch to them
        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);

        $product->update(['name' => 'Updated Product']);

        $this->assertEquals($this->user->id, $product->created_by);
        $this->assertEquals($anotherUser->id, $product->updated_by);
    }

    /** @test */
    public function it_has_creator_relationship()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $this->assertInstanceOf(User::class, $product->creator);
        $this->assertEquals($this->user->id, $product->creator->id);
        $this->assertEquals($this->user->name, $product->creator->name);
    }

    /** @test */
    public function it_has_updater_relationship()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $this->assertInstanceOf(User::class, $product->updater);
        $this->assertEquals($this->user->id, $product->updater->id);
    }

    /** @test */
    public function it_can_scope_by_creator()
    {
        $user1 = $this->user;
        $user2 = User::factory()->create();

        // Create products as user1
        $this->actingAs($user1);
        Product::create([
            'name' => 'Product 1',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        // Create products as user2
        $this->actingAs($user2);
        Product::create([
            'name' => 'Product 2',
            'sku' => 'TEST-002',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $user1Products = Product::createdBy($user1->id)->get();
        $user2Products = Product::createdBy($user2->id)->get();

        $this->assertCount(1, $user1Products);
        $this->assertCount(1, $user2Products);
        $this->assertEquals('Product 1', $user1Products->first()->name);
        $this->assertEquals('Product 2', $user2Products->first()->name);
    }

    /** @test */
    public function it_provides_created_by_info_attribute()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $createdByInfo = $product->created_by_info;

        $this->assertIsArray($createdByInfo);
        $this->assertEquals($this->user->name, $createdByInfo['user']);
        $this->assertEquals($this->user->email, $createdByInfo['email']);
        $this->assertNotNull($createdByInfo['date']);
    }

    /** @test */
    public function it_provides_audit_trail_attribute()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $auditTrail = $product->audit_trail;

        $this->assertIsArray($auditTrail);
        $this->assertCount(1, $auditTrail); // Only created action
        $this->assertEquals('created', $auditTrail[0]['action']);
        $this->assertEquals($this->user->name, $auditTrail[0]['user']);
    }

    /** @test */
    public function it_can_check_edit_permissions()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        // Creator should be able to edit
        $this->assertTrue($product->canEdit());

        // Another user should not be able to edit
        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);
        $this->assertFalse($product->canEdit());
    }

    /** @test */
    public function it_can_check_delete_permissions()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        // Creator should be able to delete
        $this->assertTrue($product->canDelete());

        // Another user should not be able to delete
        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);
        $this->assertFalse($product->canDelete());
    }

    /** @test */
    public function it_can_get_user_activity_summary()
    {
        // Create some products
        Product::create([
            'name' => 'Product 1',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        Product::create([
            'name' => 'Product 2',
            'sku' => 'TEST-002',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $summary = Product::getUserActivitySummary($this->user->id);

        $this->assertIsArray($summary);
        $this->assertEquals(2, $summary['created']);
        $this->assertEquals(2, $summary['updated']); // Updated during creation
        $this->assertEquals(0, $summary['deleted']);
    }

    /** @test */
    public function it_can_get_recent_activity()
    {
        // Create some products
        Product::create([
            'name' => 'Product 1',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        Product::create([
            'name' => 'Product 2',
            'sku' => 'TEST-002',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $recentActivity = Product::getRecentActivity($this->user->id, 5);

        $this->assertCount(2, $recentActivity);
        $this->assertInstanceOf(Product::class, $recentActivity->first());
    }

    /** @test */
    public function it_works_with_order_model()
    {
        $order = Order::create([
            'order_code' => 'ORD-001',
            'customer_id' => 1,
            'branch_id' => 1,
            'total_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'processing',
        ]);

        $this->assertEquals($this->user->id, $order->created_by);
        $this->assertEquals($this->user->id, $order->updated_by);
        $this->assertInstanceOf(User::class, $order->creator);
        $this->assertTrue($order->canEdit());
    }

    /** @test */
    public function it_handles_unauthenticated_users()
    {
        // Logout user
        auth()->logout();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'sale_price' => 100000,
            'cost_price' => 80000,
        ]);

        $this->assertNull($product->created_by);
        $this->assertNull($product->updated_by);
        $this->assertFalse($product->canEdit());
        $this->assertFalse($product->canDelete());
    }
}
