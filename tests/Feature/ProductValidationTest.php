<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Hardware',
            'slug' => 'hardware',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Teclado Original',
            'cost_price' => 20.00,
            'sale_price' => 35.00,
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001', // SKU Duplicado
            'name' => 'Teclado Copia',
            'cost_price' => 22.00,
            'sale_price' => 38.00,
            'stock' => 5,
            'min_stock' => 2,
        ]);

        $response->assertSessionHasErrors('sku');
        $this->assertEquals(1, Product::where('sku', 'SKU-TEST-001')->count());
    }

    public function test_sale_price_cannot_be_less_than_cost_price(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-LOSS-001',
            'name' => 'Producto a Pérdida',
            'cost_price' => 50.00,
            'sale_price' => 30.00, // Precio de venta inferior al costo
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $response->assertSessionHasErrors('sale_price');
        $this->assertDatabaseMissing('products', ['sku' => 'SKU-LOSS-001']);
    }

    public function test_valid_product_creation_audits_initial_stock(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->category->id,
            'sku' => 'SKU-NEW-001',
            'name' => 'Mouse Gaming',
            'cost_price' => 15.00,
            'sale_price' => 29.99,
            'stock' => 25,
            'min_stock' => 5,
        ]);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success');

        $product = Product::where('sku', 'SKU-NEW-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals(25, $product->stock);

        // Verificar que se haya creado el movimiento de inventario correspondiente
        $movement = InventoryMovement::where('product_id', $product->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(25, $movement->quantity);
        $this->assertEquals(0, $movement->previous_stock);
        $this->assertEquals(25, $movement->new_stock);
    }
}
