<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $seller;
    protected Product $product;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@api.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'email' => 'seller@api.com',
            'role' => 'seller',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Oficina',
            'slug' => 'oficina',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'sku' => 'API-PRD-001',
            'name' => 'Silla Ergonómica',
            'cost_price' => 50.00,
            'sale_price' => 100.00,
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Cliente API',
            'tax_id' => '123456789',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(401);
    }

    public function test_api_login_returns_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@api.com',
            'password' => 'password', // User::factory default password
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'token',
                'token_type',
            ]
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_authenticated_user_can_list_products(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'meta' => ['current_page', 'last_page', 'total'],
        ]);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_seller_cannot_create_products_via_api(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')->postJson('/api/v1/products', [
            'sku' => 'NO-AUTH-001',
            'name' => 'Producto Denegado',
            'category_id' => $this->product->category_id,
            'cost_price' => 10.00,
            'sale_price' => 20.00,
            'stock' => 5,
            'min_stock' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_product_via_api(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/v1/products', [
            'sku' => 'API-NEW-001',
            'name' => 'Teclado API',
            'category_id' => $this->product->category_id,
            'cost_price' => 15.00,
            'sale_price' => 30.00,
            'stock' => 8,
            'min_stock' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.sku', 'API-NEW-001');
        $this->assertDatabaseHas('products', ['sku' => 'API-NEW-001']);
    }

    public function test_create_sale_via_api_v1_decrements_stock(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')->postJson('/api/v1/sales', [
            'customer_id' => $this->customer->id,
            'payment_method' => 'efectivo',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertEquals(200.00, (float) $response->json('data.total'));
        $this->assertEquals(8, $this->product->fresh()->stock);
    }

    public function test_dashboard_metrics_endpoint_returns_data(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'today_sales',
                'monthly_sales',
                'active_products_count',
                'low_stock_count',
                'sales_trend',
            ]
        ]);
    }
}
