<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->seller = User::factory()->create([
            'role' => 'seller',
            'is_active' => true,
        ]);
    }

    public function test_seller_cannot_access_categories_management(): void
    {
        $response = $this->actingAs($this->seller)->get('/categories');
        $response->assertStatus(403);
    }

    public function test_seller_cannot_access_suppliers_management(): void
    {
        $response = $this->actingAs($this->seller)->get('/suppliers');
        $response->assertStatus(403);
    }

    public function test_seller_cannot_access_manual_inventory_adjustments(): void
    {
        $response = $this->actingAs($this->seller)->get('/inventory/create');
        $response->assertStatus(403);
    }

    public function test_seller_cannot_access_financial_reports(): void
    {
        $response = $this->actingAs($this->seller)->get('/reports');
        $response->assertStatus(403);
    }

    public function test_seller_can_access_dashboard_and_pos(): void
    {
        $dashResponse = $this->actingAs($this->seller)->get('/dashboard');
        $dashResponse->assertStatus(200);

        $posResponse = $this->actingAs($this->seller)->get('/sales/create');
        $posResponse->assertStatus(200);
    }

    public function test_admin_has_full_access_to_all_modules(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->admin)->get('/categories')->assertStatus(200);
        $this->actingAs($this->admin)->get('/suppliers')->assertStatus(200);
        $this->actingAs($this->admin)->get('/inventory/create')->assertStatus(200);
        $this->actingAs($this->admin)->get('/reports')->assertStatus(200);
    }
}
