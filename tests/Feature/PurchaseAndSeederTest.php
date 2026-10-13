<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_does_not_increase_stock_until_received_and_prevents_double_receipt(): void
    {
        $admin = User::create([
            'name' => 'Admin Compras',
            'email' => 'admin.compras@pymeflow.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'company_name' => 'Importaciones Deltron S.A.',
            'document_type' => 'RUC',
            'tax_id' => '20212331377',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Almacenamiento',
            'slug' => 'almacenamiento',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'ALM-SSD-500',
            'name' => 'SSD NVMe 500GB',
            'cost_price' => 118.00,
            'sale_price' => 189.00,
            'tax_type' => 'gravado',
            'stock' => 5,
            'min_stock' => 3,
            'unit' => 'unidad',
            'is_active' => true,
        ]);

        $purchaseService = app(PurchaseService::class);

        // 1. Crear orden de compra pendiente (receive_immediately = false)
        $purchase = $purchaseService->createPurchase($admin, [
            'supplier_id' => $supplier->id,
            'supplier_document' => 'F001-0099123',
            'prices_include_tax' => true,
            'receive_immediately' => false,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => 118.00,
                ],
            ],
        ]);

        $this->assertEquals('ordered', $purchase->status);
        $this->assertNull($purchase->received_at);
        $this->assertEquals(1000.00, (float) $purchase->subtotal);
        $this->assertEquals(180.00, (float) $purchase->tax);
        $this->assertEquals(1180.00, (float) $purchase->total);

        // El stock sigue en 5 porque aún no ha sido recibida en almacén
        $this->assertEquals(5, $product->fresh()->stock);

        // 2. Confirmar recepción de mercadería en almacén
        $receivedPurchase = $purchaseService->receivePurchase($purchase, $admin);

        $this->assertEquals('received', $receivedPurchase->status);
        $this->assertNotNull($receivedPurchase->received_at);
        $this->assertEquals(15, $product->fresh()->stock);

        // 3. Intentar recibir nuevamente debe fallar sin duplicar el ingreso al Kardex
        try {
            $purchaseService->receivePurchase($receivedPurchase, $admin);
            $this->fail('Se esperaba DomainException al intentar recibir dos veces la misma orden de compra.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('ya fue recepcionada', $e->getMessage());
        }

        $this->assertEquals(15, $product->fresh()->stock);
    }

    public function test_database_seeder_is_idempotent_when_executed_repeatedly(): void
    {
        $this->seed(DatabaseSeeder::class);

        $initialProductCount = Product::count();
        $initialSaleCount = Sale::count();
        $initialPurchaseCount = Purchase::count();
        $initialStocks = Product::pluck('stock', 'sku')->toArray();

        // Ejecutar db:seed por segunda vez
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals($initialProductCount, Product::count());
        $this->assertEquals($initialSaleCount, Sale::count());
        $this->assertEquals($initialPurchaseCount, Purchase::count());
        $this->assertEquals($initialStocks, Product::pluck('stock', 'sku')->toArray());

        // Verificar que todas las ventas semilla cumplen la invariante contable subtotal + tax == total
        foreach (Sale::with('items')->get() as $sale) {
            $this->assertEquals(
                round((float) $sale->subtotal + (float) $sale->tax, 2),
                (float) $sale->total,
                "Inconsistencia en cabecera de venta {$sale->sale_number}"
            );
            $this->assertEquals(
                round((float) $sale->items->sum('line_total'), 2),
                (float) $sale->total,
                "Suma de líneas no coincide con total en venta {$sale->sale_number}"
            );
        }
    }

    public function test_seller_role_cannot_cancel_sales_or_access_purchases_module(): void
    {
        $this->seed(DatabaseSeeder::class);

        $seller = User::where('role', User::ROLE_SELLER)->firstOrFail();
        $sale = Sale::where('status', 'completed')->firstOrFail();

        // Vendedor no puede acceder a órdenes de compra (403)
        $this->actingAs($seller)
            ->get(route('purchases.index'))
            ->assertStatus(403);

        // Vendedor no puede anular una venta (403)
        $this->actingAs($seller)
            ->post(route('sales.cancel', $sale), ['reason' => 'Intento no autorizado'])
            ->assertStatus(403);

        $this->assertEquals('completed', $sale->fresh()->status);
    }
}
