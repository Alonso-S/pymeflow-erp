<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected Customer $customer;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create([
            'role' => 'seller',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Empresa Cliente Test',
            'tax_id' => '900999888-1',
            'email' => 'cliente@test.com',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'General',
            'slug' => 'general',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'category_id' => $category->id,
            'sku' => 'SKU-001',
            'name' => 'Producto A',
            'cost_price' => 10.00,
            'sale_price' => 20.00,
            'stock' => 15,
            'min_stock' => 3,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'category_id' => $category->id,
            'sku' => 'SKU-002',
            'name' => 'Producto B',
            'cost_price' => 30.00,
            'sale_price' => 50.00,
            'stock' => 5,
            'min_stock' => 2,
            'is_active' => true,
        ]);
    }

    public function test_valid_sale_registers_sale_header_items_and_decrements_stock(): void
    {
        $response = $this->actingAs($this->seller)->post('/sales', [
            'customer_id' => $this->customer->id,
            'payment_method' => 'efectivo',
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 3],
                ['product_id' => $this->product2->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Verificar encabezado de la venta
        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals($this->customer->id, $sale->customer_id);
        $this->assertEquals($this->customer->name, $sale->customer_name);
        $this->assertEquals($this->seller->id, $sale->user_id);

        // 3 * 20.00 = 60.00, 2 * 50.00 = 100.00 -> Total: 160.00
        $this->assertEquals(160.00, (float) $sale->total);
        $this->assertEquals(2, $sale->items()->count());

        // Verificar decremento del inventario
        $this->assertEquals(12, $this->product1->fresh()->stock); // 15 - 3 = 12
        $this->assertEquals(3, $this->product2->fresh()->stock);  // 5 - 2 = 3

        // Verificar movimientos de auditoría inmutables
        $movements = InventoryMovement::where('reference_type', 'Sale')->get();
        $this->assertCount(2, $movements);
        $this->assertEquals(3, $movements->where('product_id', $this->product1->id)->first()->quantity);
        $this->assertEquals('out', $movements->where('product_id', $this->product1->id)->first()->type);
    }

    public function test_sale_with_insufficient_stock_is_rejected(): void
    {
        $response = $this->actingAs($this->seller)->post('/sales', [
            'customer_id' => $this->customer->id,
            'payment_method' => 'efectivo',
            'items' => [
                ['product_id' => $this->product2->id, 'quantity' => 10], // Stock es solo 5
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // No debe haberse registrado ninguna venta
        $this->assertEquals(0, Sale::count());

        // El stock original debe permanecer intacto
        $this->assertEquals(5, $this->product2->fresh()->stock);
    }

    public function test_failed_sale_rolls_back_entire_transaction_atomically(): void
    {
        // En una venta con múltiples productos donde el segundo falla,
        // el primero NO debe sufrir descuento parcial
        $response = $this->actingAs($this->seller)->post('/sales', [
            'customer_id' => $this->customer->id,
            'payment_method' => 'efectivo',
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2], // Disponible (15)
                ['product_id' => $this->product2->id, 'quantity' => 99], // Insuficiente (5)
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Verificar atomicidad estricta: ninguna venta creada
        $this->assertEquals(0, Sale::count());

        // Ambos stocks deben conservar exactamente su valor inicial
        $this->assertEquals(15, $this->product1->fresh()->stock);
        $this->assertEquals(5, $this->product2->fresh()->stock);

        // Ningún movimiento de inventario generado
        $this->assertEquals(0, InventoryMovement::where('reference_type', 'Sale')->count());
    }

    public function test_server_calculates_totals_ignoring_tampered_client_data(): void
    {
        $saleService = app(SaleService::class);

        // Incluso si se intenta manipular precios en el payload
        $sale = $saleService->createSale($this->seller, [
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2,
                    'unit_price' => 1.00, // Precio alterado fraudulentamente
                    'subtotal' => 2.00,
                ]
            ],
        ]);

        // El servidor debió calcular: 2 unidades * $20.00 (precio real DB) = $40.00
        $this->assertEquals(40.00, (float) $sale->total);
        $this->assertEquals(20.00, (float) $sale->items->first()->unit_price);
    }
}
