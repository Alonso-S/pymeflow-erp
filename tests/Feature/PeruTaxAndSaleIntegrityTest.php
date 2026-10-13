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

class PeruTaxAndSaleIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seller;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin.peru@pymeflow.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->seller = User::create([
            'name' => 'Vendedor Test',
            'email' => 'vendedor.peru@pymeflow.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SELLER,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Equipos TI',
            'slug' => 'equipos-ti',
            'is_active' => true,
        ]);
    }

    public function test_sale_with_gravado_products_and_igv_included_calculates_exact_base_and_igv(): void
    {
        // S/ 118.00 con IGV incluido -> Base = S/ 100.00, IGV (18%) = S/ 18.00, Total = S/ 118.00
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'GRAV-INC-01',
            'name' => 'Monitor 24 Pulgadas',
            'cost_price' => 80.00,
            'sale_price' => 118.00,
            'tax_type' => 'gravado',
            'stock' => 20,
            'min_stock' => 5,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $sale = $saleService->createSale($this->seller, [
            'payment_method' => 'yape',
            'prices_include_tax' => true,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        // 2 * 118.00 = 236.00 -> Base: 200.00, IGV: 36.00
        $this->assertEquals('PEN', $sale->currency);
        $this->assertTrue($sale->prices_include_tax);
        $this->assertEquals(200.00, (float) $sale->taxable_amount);
        $this->assertEquals(0.00, (float) $sale->exempt_amount);
        $this->assertEquals(0.00, (float) $sale->unaffected_amount);
        $this->assertEquals(200.00, (float) $sale->subtotal);
        $this->assertEquals(36.00, (float) $sale->tax);
        $this->assertEquals(236.00, (float) $sale->total);

        $item = $sale->items->first();
        $this->assertEquals('gravado', $item->tax_type);
        $this->assertEquals(18.00, (float) $item->tax_rate);
        $this->assertEquals(100.00, (float) $item->unit_value);
        $this->assertEquals(200.00, (float) $item->tax_base);
        $this->assertEquals(36.00, (float) $item->tax_amount);
        $this->assertEquals(236.00, (float) $item->line_total);
    }

    public function test_sale_with_gravado_products_and_igv_excluded_adds_18_percent_igv(): void
    {
        // Valor unitario S/ 100.00 más IGV (prices_include_tax = false) -> Base = 100.00, IGV = 18.00, Total = 118.00
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'GRAV-EXC-01',
            'name' => 'Switch Corporativo',
            'cost_price' => 60.00,
            'sale_price' => 100.00,
            'tax_type' => 'gravado',
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $sale = $saleService->createSale($this->seller, [
            'payment_method' => 'transferencia',
            'prices_include_tax' => false,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        // 3 * 100.00 = Base 300.00 + IGV 54.00 = Total 354.00
        $this->assertFalse($sale->prices_include_tax);
        $this->assertEquals(300.00, (float) $sale->taxable_amount);
        $this->assertEquals(300.00, (float) $sale->subtotal);
        $this->assertEquals(54.00, (float) $sale->tax);
        $this->assertEquals(354.00, (float) $sale->total);
    }

    public function test_sale_with_mixed_gravado_exonerado_and_inafecto_products(): void
    {
        $gravado = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'MIX-GRAV',
            'name' => 'Teclado Mecánico',
            'cost_price' => 50.00,
            'sale_price' => 118.00,
            'tax_type' => 'gravado',
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $exonerado = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'MIX-EXO',
            'name' => 'Libro Técnico Tributario',
            'cost_price' => 30.00,
            'sale_price' => 50.00,
            'tax_type' => 'exonerado',
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $inafecto = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'MIX-INA',
            'name' => 'Trámite Administrativo Inafecto',
            'cost_price' => 10.00,
            'sale_price' => 25.00,
            'tax_type' => 'inafecto',
            'stock' => 10,
            'min_stock' => 1,
            'unit' => 'servicio',
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $sale = $saleService->createSale($this->seller, [
            'payment_method' => 'plin',
            'prices_include_tax' => true,
            'items' => [
                ['product_id' => $gravado->id, 'quantity' => 1],   // Base 100.00, IGV 18.00, Total 118.00
                ['product_id' => $exonerado->id, 'quantity' => 2], // Exonerado 100.00, IGV 0.00, Total 100.00
                ['product_id' => $inafecto->id, 'quantity' => 1],  // Inafecto 25.00, IGV 0.00, Total 25.00
            ],
        ]);

        $this->assertEquals(100.00, (float) $sale->taxable_amount);
        $this->assertEquals(100.00, (float) $sale->exempt_amount);
        $this->assertEquals(25.00, (float) $sale->unaffected_amount);
        $this->assertEquals(225.00, (float) $sale->subtotal);
        $this->assertEquals(18.00, (float) $sale->tax);
        $this->assertEquals(243.00, (float) $sale->total);
    }

    public function test_multi_item_rounding_preserves_accounting_invariant_subtotal_plus_tax_equals_total(): void
    {
        $p1 = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'RND-01',
            'name' => 'Cable UTP Cat6 Metro',
            'cost_price' => 1.00,
            'sale_price' => 2.50,
            'tax_type' => 'gravado',
            'stock' => 100,
            'min_stock' => 10,
            'unit' => 'metro',
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'RND-02',
            'name' => 'Conector RJ45 Blindado',
            'cost_price' => 0.50,
            'sale_price' => 1.30,
            'tax_type' => 'gravado',
            'stock' => 100,
            'min_stock' => 10,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $sale = $saleService->createSale($this->seller, [
            'payment_method' => 'efectivo',
            'prices_include_tax' => true,
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 3], // 7.50 -> Base 6.36, IGV 1.14
                ['product_id' => $p2->id, 'quantity' => 7], // 9.10 -> Base 7.71, IGV 1.39
            ],
        ]);

        // Total esperado: 7.50 + 9.10 = 16.60
        // Base total: 6.36 + 7.71 = 14.07
        // IGV total: 1.14 + 1.39 = 2.53
        // 14.07 + 2.53 == 16.60
        $this->assertEquals(14.07, (float) $sale->subtotal);
        $this->assertEquals(2.53, (float) $sale->tax);
        $this->assertEquals(16.60, (float) $sale->total);
        $this->assertEquals(
            round((float) $sale->subtotal + (float) $sale->tax, 2),
            (float) $sale->total
        );
    }

    public function test_client_cannot_tamper_with_product_price_in_request_payload(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SEC-PRICE-01',
            'name' => 'Laptop Corporativa',
            'cost_price' => 2000.00,
            'sale_price' => 3540.00,
            'tax_type' => 'gravado',
            'stock' => 5,
            'min_stock' => 1,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->seller)->post(route('sales.store'), [
            'payment_method' => 'efectivo',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 1.00, // Intento malicioso de pagar S/ 1.00
                    'price' => 1.00,
                    'total' => 1.00,
                ],
            ],
        ]);

        $response->assertRedirect();
        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(3540.00, (float) $sale->total);
        $this->assertEquals(3000.00, (float) $sale->subtotal);
        $this->assertEquals(540.00, (float) $sale->tax);
    }

    public function test_idempotency_key_prevents_duplicate_sale_and_double_stock_deduction(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'IDEMP-01',
            'name' => 'Disco Solido 1TB',
            'cost_price' => 180.00,
            'sale_price' => 295.00,
            'tax_type' => 'gravado',
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $payload = [
            'idempotency_key' => 'POS-UUID-12345-ABCDE',
            'payment_method' => 'yape',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ];

        $firstSale = $saleService->createSale($this->seller, $payload);
        $secondSale = $saleService->createSale($this->seller, $payload);

        $this->assertEquals($firstSale->id, $secondSale->id);
        $this->assertEquals(1, Sale::count());
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_sale_cancellation_restores_stock_and_prevents_double_cancellation(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'CANC-01',
            'name' => 'Impresora Multifuncional',
            'cost_price' => 450.00,
            'sale_price' => 708.00,
            'tax_type' => 'gravado',
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $saleService = app(SaleService::class);
        $sale = $saleService->createSale($this->seller, [
            'payment_method' => 'tarjeta',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $this->assertEquals(7, $product->fresh()->stock);

        // Anular venta como administrador
        $cancelledSale = $saleService->cancelSale($sale, $this->admin, 'Error en digitación de RUC del cliente');

        $this->assertEquals('cancelled', $cancelledSale->status);
        $this->assertNotNull($cancelledSale->cancelled_at);
        $this->assertEquals($this->admin->id, $cancelledSale->cancelled_by);
        $this->assertEquals(10, $product->fresh()->stock);

        // Verificar movimiento de reversión en Kardex
        $reversalMovement = InventoryMovement::where('reference_type', 'SaleCancellation')
            ->where('reference_id', $sale->id)
            ->first();
        $this->assertNotNull($reversalMovement);
        $this->assertEquals('in', $reversalMovement->type);
        $this->assertEquals(3, $reversalMovement->quantity);

        // Intentar anular por segunda vez debe lanzar DomainException y no duplicar stock
        try {
            $saleService->cancelSale($cancelledSale, $this->admin, 'Intento duplicado');
            $this->fail('Se esperaba DomainException al intentar anular una venta ya anulada.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('ya se encuentra anulada', $e->getMessage());
        }

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(1, InventoryMovement::where('reference_type', 'SaleCancellation')->where('reference_id', $sale->id)->count());
    }
}
