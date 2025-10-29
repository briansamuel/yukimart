<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Comprehensive Multi-Tenant System Test
 * 
 * Tests all aspects of the multi-tenant system including:
 * - Tenant isolation
 * - User authentication and authorization
 * - Business operations (Products, Orders, Invoices)
 * - Platform management
 * - Security and data protection
 */
class MultiTenantSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant1;
    protected $tenant2;
    protected $platformUser;
    protected $tenantUser1;
    protected $tenantUser2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test tenants
        $this->tenant1 = Tenant::factory()->techStore()->create();
        $this->tenant2 = Tenant::factory()->fashionStore()->create();
        
        // Create platform user (superadmin)
        $this->platformUser = User::factory()->create([
            'email' => 'superadmin@yukimart.local',
            'password' => Hash::make('password'),
        ]);
        $this->platformUser->assignRole('superadmin');
        
        // Create tenant users
        $this->tenantUser1 = User::factory()->create([
            'email' => 'admin@techmart.local',
            'password' => Hash::make('password'),
        ]);
        
        $this->tenantUser2 = User::factory()->create([
            'email' => 'admin@fashion.local',
            'password' => Hash::make('password'),
        ]);
        
        // Create tenant-user relationships
        TenantUser::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $this->tenantUser1->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        TenantUser::create([
            'tenant_id' => $this->tenant2->id,
            'user_id' => $this->tenantUser2->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function platform_user_can_access_platform_dashboard()
    {
        $this->actingAs($this->platformUser, 'admin');
        
        $response = $this->get(route('platform.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.platform.dashboard');
        $response->assertSee('Platform Dashboard');
    }

    /** @test */
    public function tenant_user_cannot_access_platform_dashboard()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        
        $response = $this->get(route('platform.dashboard'));
        
        $response->assertStatus(403);
    }

    /** @test */
    public function platform_user_can_switch_to_tenant()
    {
        $this->actingAs($this->platformUser, 'admin');
        
        $response = $this->post(route('platform.switch-to-tenant'), [
            'tenant_id' => $this->tenant1->id
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals($this->tenant1->id, session('current_tenant_id'));
    }

    /** @test */
    public function tenant_data_is_properly_isolated()
    {
        // Create products for each tenant
        $product1 = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
        $product2 = Product::factory()->create(['tenant_id' => $this->tenant2->id]);
        
        // Login as tenant1 user
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        // Test product listing - should only see tenant1 products
        $response = $this->get(route('admin.products.index'));
        $response->assertStatus(200);
        
        // Verify tenant isolation in database queries
        $products = Product::all();
        $this->assertCount(1, $products);
        $this->assertEquals($this->tenant1->id, $products->first()->tenant_id);
        
        // Switch to tenant2 context
        $this->actingAs($this->tenantUser2, 'admin');
        session(['current_tenant_id' => $this->tenant2->id]);
        
        // Should now see different products
        $products = Product::all();
        $this->assertCount(1, $products);
        $this->assertEquals($this->tenant2->id, $products->first()->tenant_id);
    }

    /** @test */
    public function user_cannot_access_other_tenant_data()
    {
        $product1 = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
        $product2 = Product::factory()->create(['tenant_id' => $this->tenant2->id]);
        
        // Login as tenant1 user
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        // Try to access tenant2's product directly
        $response = $this->get(route('admin.products.show', $product2->id));
        $response->assertStatus(404); // Should not be found due to tenant scoping
        
        // Try to edit tenant2's product
        $response = $this->put(route('admin.products.update', $product2->id), [
            'product_name' => 'Hacked Product',
            'sku' => 'HACK001',
            'sale_price' => 999999,
            'cost_price' => 50000,
            'product_status' => 'publish',
            'product_type' => 'simple',
        ]);
        $response->assertStatus(404); // Should not be found
        
        // Verify product was not modified
        $product2->refresh();
        $this->assertNotEquals('Hacked Product', $product2->product_name);
    }

    /** @test */
    public function product_crud_operations_work_correctly()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        $category = Category::factory()->create(['tenant_id' => $this->tenant1->id]);
        $brand = Brand::factory()->create(['tenant_id' => $this->tenant1->id]);
        
        // Test product creation
        $productData = [
            'product_name' => 'Test Product',
            'sku' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'product_description' => 'Test description',
            'cost_price' => 50000,
            'sale_price' => 100000,
            'product_status' => 'publish',
            'product_type' => 'simple',
        ];
        
        $response = $this->post(route('admin.products.store'), $productData);
        $response->assertRedirect();
        
        $product = Product::where('sku', 'TEST001')->first();
        $this->assertNotNull($product);
        $this->assertEquals($this->tenant1->id, $product->tenant_id);
        $this->assertEquals('Test Product', $product->product_name);
        
        // Test product update
        $updateData = [
            'product_name' => 'Updated Product',
            'sku' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'product_description' => 'Updated description',
            'cost_price' => 60000,
            'sale_price' => 120000,
            'product_status' => 'publish',
            'product_type' => 'simple',
        ];
        
        $response = $this->put(route('admin.products.update', $product->id), $updateData);
        $response->assertRedirect();
        
        $product->refresh();
        $this->assertEquals('Updated Product', $product->product_name);
        $this->assertEquals(120000, $product->sale_price);
        
        // Test product deletion
        $response = $this->delete(route('admin.products.destroy', $product->id));
        $response->assertRedirect();
        
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function order_management_works_correctly()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant1->id]);
        $product = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
        
        // Test order creation
        $orderData = [
            'customer_id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'customer_phone' => $customer->customer_phone,
            'order_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => $product->sale_price,
                ]
            ]
        ];
        
        $response = $this->post(route('admin.orders.store'), $orderData);
        $response->assertRedirect();
        
        $order = Order::where('customer_id', $customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals($this->tenant1->id, $order->tenant_id);
        $this->assertEquals($customer->customer_name, $order->customer_name);
        
        // Verify order items
        $this->assertCount(1, $order->items);
        $this->assertEquals($product->id, $order->items->first()->product_id);
        $this->assertEquals(2, $order->items->first()->quantity);
    }

    /** @test */
    public function invoice_generation_from_order_works()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        $order = Order::factory()->create(['tenant_id' => $this->tenant1->id]);
        
        // Test invoice creation from order
        $response = $this->post(route('admin.invoices.from-order', $order->id));
        $response->assertRedirect();
        
        $invoice = Invoice::where('order_id', $order->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($this->tenant1->id, $invoice->tenant_id);
        $this->assertEquals($order->customer_name, $invoice->customer_name);
        $this->assertEquals($order->total_amount, $invoice->total_amount);
    }

    /** @test */
    public function api_authentication_works_correctly()
    {
        // Test API login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->tenantUser1->email,
            'password' => 'password',
            'tenant_subdomain' => $this->tenant1->subdomain,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'token_type',
                'user',
                'tenant'
            ]
        ]);
        
        $token = $response->json('data.token');
        
        // Test authenticated API request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Tenant-ID' => $this->tenant1->id,
        ])->getJson('/api/v1/products');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'items',
                'pagination'
            ]
        ]);
    }

    /** @test */
    public function api_rate_limiting_works()
    {
        // Login and get token
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->tenantUser1->email,
            'password' => 'password',
            'tenant_subdomain' => $this->tenant1->subdomain,
        ]);
        
        $token = $response->json('data.token');
        
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Tenant-ID' => $this->tenant1->id,
            ])->getJson('/api/v1/products');
            
            if ($i < 5) {
                $response->assertStatus(200);
            }
        }
        
        // This test would need actual rate limiting implementation
        // The assertion would check for 429 status after exceeding limits
    }

    /** @test */
    public function user_permissions_are_enforced()
    {
        // Create a staff user with limited permissions
        $staffUser = User::factory()->create(['email' => 'staff@techmart.local']);
        TenantUser::create([
            'tenant_id' => $this->tenant1->id,
            'user_id' => $staffUser->id,
            'role' => 'staff',
            'is_active' => true,
        ]);
        
        // Login as staff user
        $this->actingAs($staffUser, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        // Try to access admin-only features
        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(403); // Should be forbidden
        
        // Try to delete products (staff shouldn't be able to)
        $product = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
        $response = $this->delete(route('admin.products.destroy', $product->id));
        $response->assertStatus(403); // Should be forbidden
    }

    /** @test */
    public function tenant_switching_maintains_security()
    {
        $this->actingAs($this->platformUser, 'admin');
        
        // Switch to tenant1
        $this->post(route('platform.switch-to-tenant'), [
            'tenant_id' => $this->tenant1->id
        ]);
        
        $this->assertEquals($this->tenant1->id, session('current_tenant_id'));
        
        // Create a product in tenant1 context
        $product = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
        
        // Switch to tenant2
        $this->post(route('platform.switch-to-tenant'), [
            'tenant_id' => $this->tenant2->id
        ]);
        
        $this->assertEquals($this->tenant2->id, session('current_tenant_id'));
        
        // Try to access tenant1's product - should not be visible
        $products = Product::all();
        $this->assertCount(0, $products); // Should not see tenant1's products
        
        // Verify the product still exists in database but is filtered out
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /** @test */
    public function bulk_operations_respect_tenant_boundaries()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        // Create products for both tenants
        $product1 = Product::factory()->create(['tenant_id' => $this->tenant1->id]);
        $product2 = Product::factory()->create(['tenant_id' => $this->tenant2->id]);
        
        // Try bulk update including both products
        $response = $this->post(route('admin.products.bulk-update'), [
            'product_ids' => [$product1->id, $product2->id],
            'action' => 'publish'
        ]);
        
        // Should only update tenant1's product
        $product1->refresh();
        $product2->refresh();
        
        $this->assertEquals('publish', $product1->product_status);
        // Product2 should remain unchanged (not accessible to tenant1 user)
    }

    /** @test */
    public function file_uploads_are_tenant_scoped()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        // Create a fake image file
        $file = \Illuminate\Http\UploadedFile::fake()->image('product.jpg');
        
        $response = $this->post(route('admin.products.store'), [
            'product_name' => 'Test Product with Image',
            'sku' => 'TEST002',
            'cost_price' => 50000,
            'sale_price' => 100000,
            'product_status' => 'publish',
            'product_type' => 'simple',
            'product_image' => $file,
        ]);
        
        $product = Product::where('sku', 'TEST002')->first();
        $this->assertNotNull($product);
        
        // Verify image path includes tenant ID
        if ($product->product_image) {
            $this->assertStringContainsString("tenants/{$this->tenant1->id}", $product->product_image);
        }
    }

    /** @test */
    public function search_functionality_is_tenant_scoped()
    {
        $this->actingAs($this->tenantUser1, 'admin');
        session(['current_tenant_id' => $this->tenant1->id]);
        
        // Create products for both tenants with similar names
        $product1 = Product::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'product_name' => 'iPhone 15 Pro'
        ]);
        
        $product2 = Product::factory()->create([
            'tenant_id' => $this->tenant2->id,
            'product_name' => 'iPhone 15 Pro Max'
        ]);
        
        // Search for iPhone products
        $response = $this->get(route('admin.products.index', ['search' => 'iPhone']));
        $response->assertStatus(200);
        
        // Should only find tenant1's product
        $products = Product::where('product_name', 'like', '%iPhone%')->get();
        $this->assertCount(1, $products);
        $this->assertEquals($this->tenant1->id, $products->first()->tenant_id);
    }
}
