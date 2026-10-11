<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios Demo
        $admin = User::firstOrCreate(
            ['email' => 'admin@pymeflow.com'],
            [
                'name' => 'Carlos Mendoza (Administrador)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $seller = User::firstOrCreate(
            ['email' => 'vendedor@pymeflow.com'],
            [
                'name' => 'Ana Gómez (Vendedora)',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'is_active' => true,
            ]
        );

        // 2. Categorías
        $catElectronics = Category::firstOrCreate(['name' => 'Electrónica y Gadgets'], [
            'slug' => Str::slug('Electrónica y Gadgets'),
            'description' => 'Dispositivos, accesorios y periféricos de computación.',
            'is_active' => true,
        ]);

        $catOffice = Category::firstOrCreate(['name' => 'Oficina y Mobiliario'], [
            'slug' => Str::slug('Oficina y Mobiliario'),
            'description' => 'Artículos, escritorios y mobiliario ergonómico para empresas.',
            'is_active' => true,
        ]);

        $catFood = Category::firstOrCreate(['name' => 'Alimentos y Café'], [
            'slug' => Str::slug('Alimentos y Café'),
            'description' => 'Insumos corporativos, café de especialidad y snacks.',
            'is_active' => true,
        ]);

        $catTools = Category::firstOrCreate(['name' => 'Ferretería y Mantenimiento'], [
            'slug' => Str::slug('Ferretería y Mantenimiento'),
            'description' => 'Herramientas de precisión y mantenimiento industrial.',
            'is_active' => true,
        ]);

        // 3. Proveedores
        Supplier::firstOrCreate(['company_name' => 'TechImports Mayorista S.A.'], [
            'contact_name' => 'Roberto Sánchez',
            'tax_id' => '900888123-1',
            'email' => 'ventas@techimports.com',
            'phone' => '+57 1 8000123',
            'address' => 'Zona Franca Bodega 14, Bogotá',
            'is_active' => true,
        ]);

        Supplier::firstOrCreate(['company_name' => 'Insumos Globales & Papelería'], [
            'contact_name' => 'Marta Domínguez',
            'tax_id' => '900333444-2',
            'email' => 'pedidos@insumosglobales.com',
            'phone' => '+57 1 7654321',
            'address' => 'Parque Industrial Sur, Medellín',
            'is_active' => true,
        ]);

        // 4. Clientes
        $cust1 = Customer::firstOrCreate(['tax_id' => '900123456-1'], [
            'name' => 'Acero & Construcciones S.A.S.',
            'email' => 'contacto@acero.com',
            'phone' => '+57 300 1234567',
            'address' => 'Av. Industrial 45 # 12-30',
            'is_active' => true,
        ]);

        $cust2 = Customer::firstOrCreate(['tax_id' => '900765432-8'], [
            'name' => 'Distribuidora del Norte Ltda.',
            'email' => 'compras@norte.com',
            'phone' => '+57 311 9876543',
            'address' => 'Calle 100 # 15-20, Cali',
            'is_active' => true,
        ]);

        $cust3 = Customer::firstOrCreate(['tax_id' => '1020304050'], [
            'name' => 'María Fernández',
            'email' => 'maria.fernandez@gmail.com',
            'phone' => '+57 320 5551234',
            'address' => 'Carrera 7 # 45-10',
            'is_active' => true,
        ]);

        // 5. Productos
        $productsData = [
            [
                'category_id' => $catElectronics->id,
                'sku' => 'PRD-TEC-001',
                'name' => 'Teclado Mecánico RGB Switch Blue',
                'description' => 'Teclado mecánico con retroiluminación RGB configurable, anti-ghosting.',
                'cost_price' => 25.00,
                'sale_price' => 49.99,
                'stock' => 25,
                'min_stock' => 5,
            ],
            [
                'category_id' => $catElectronics->id,
                'sku' => 'PRD-TEC-002',
                'name' => 'Mouse Inalámbrico Ergonómico 2.4G',
                'description' => 'Sensor óptico de alta precisión con agarre vertical anti fatiga.',
                'cost_price' => 12.00,
                'sale_price' => 24.50,
                'stock' => 40,
                'min_stock' => 8,
            ],
            [
                'category_id' => $catElectronics->id,
                'sku' => 'PRD-TEC-003',
                'name' => 'Monitor LED 24" IPS Full HD 75Hz',
                'description' => 'Pantalla ultra delgada con conectividad HDMI y DisplayPort.',
                'cost_price' => 95.00,
                'sale_price' => 149.00,
                'stock' => 12,
                'min_stock' => 3,
            ],
            [
                'category_id' => $catOffice->id,
                'sku' => 'PRD-OFI-001',
                'name' => 'Silla Ergonómica Ejecutiva Lumbar',
                'description' => 'Soporte lumbar ajustable, malla transpirable y ruedas de silicona.',
                'cost_price' => 60.00,
                'sale_price' => 115.00,
                'stock' => 3, // ALERTA DE STOCK BAJO (min: 5)
                'min_stock' => 5,
            ],
            [
                'category_id' => $catOffice->id,
                'sku' => 'PRD-OFI-002',
                'name' => 'Cuaderno Corporativo A5 Tapa Dura 100 Hojas',
                'description' => 'Papel ecológico de 90g con elástico y cinta separadora.',
                'cost_price' => 3.50,
                'sale_price' => 7.90,
                'stock' => 85,
                'min_stock' => 15,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'PRD-ALI-001',
                'name' => 'Café Gourmet Origen Huila 500g',
                'description' => 'Café tostado en grano de notas achocolatadas y acidez cítrica media.',
                'cost_price' => 6.00,
                'sale_price' => 13.50,
                'stock' => 2, // ALERTA DE STOCK BAJO (min: 10)
                'min_stock' => 10,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'PRD-ALI-002',
                'name' => 'Té Verde Matcha Puro 100g',
                'description' => 'Matcha ceremonial orgánico de alta pureza.',
                'cost_price' => 4.20,
                'sale_price' => 9.50,
                'stock' => 30,
                'min_stock' => 6,
            ],
            [
                'category_id' => $catTools->id,
                'sku' => 'PRD-FER-001',
                'name' => 'Kit Destornilladores de Precisión 24 en 1',
                'description' => 'Puntas magnéticas de acero S2 en estuche de aluminio.',
                'cost_price' => 8.00,
                'sale_price' => 18.00,
                'stock' => 15,
                'min_stock' => 4,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $pData) {
            $p = Product::firstOrCreate(['sku' => $pData['sku']], $pData);
            $createdProducts[$p->sku] = $p;

            // Registrar movimiento inicial de inventario si no existe
            InventoryMovement::firstOrCreate(
                ['product_id' => $p->id, 'reason' => 'Inventario inicial del sistema'],
                [
                    'user_id' => $admin->id,
                    'type' => 'in',
                    'quantity' => $p->stock,
                    'previous_stock' => 0,
                    'new_stock' => $p->stock,
                    'reference_type' => 'InitialSetup',
                    'reference_id' => null,
                    'created_at' => Carbon::now()->subDays(10),
                ]
            );
        }

        // 6. Ventas de Demostración con fechas para alimentar gráficos y KPIs
        if (Sale::count() === 0) {
            // Venta 1: Ayer
            $sale1 = Sale::create([
                'sale_number' => 'VEN-202610-0001',
                'user_id' => $seller->id,
                'customer_id' => $cust1->id,
                'customer_name' => $cust1->name,
                'subtotal' => 223.99,
                'tax' => 0.00,
                'total' => 223.99,
                'payment_method' => 'transferencia',
                'status' => 'completed',
                'notes' => 'Entrega en oficina central',
                'created_at' => Carbon::yesterday()->setHour(14)->setMinute(30),
            ]);

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $createdProducts['PRD-TEC-001']->id,
                'product_name' => $createdProducts['PRD-TEC-001']->name,
                'product_sku' => $createdProducts['PRD-TEC-001']->sku,
                'quantity' => 2,
                'unit_price' => 49.99,
                'cost_price' => 25.00,
                'subtotal' => 99.98,
                'created_at' => Carbon::yesterday()->setHour(14)->setMinute(30),
            ]);

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $createdProducts['PRD-TEC-002']->id,
                'product_name' => $createdProducts['PRD-TEC-002']->name,
                'product_sku' => $createdProducts['PRD-TEC-002']->sku,
                'quantity' => 1,
                'unit_price' => 24.50,
                'cost_price' => 12.00,
                'subtotal' => 24.50,
                'created_at' => Carbon::yesterday()->setHour(14)->setMinute(30),
            ]);

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $createdProducts['PRD-TEC-001']->id,
                'product_name' => $createdProducts['PRD-TEC-001']->name,
                'product_sku' => $createdProducts['PRD-TEC-001']->sku,
                'quantity' => 1,
                'unit_price' => 49.99,
                'cost_price' => 25.00,
                'subtotal' => 49.99,
                'created_at' => Carbon::yesterday()->setHour(14)->setMinute(30),
            ]);

            // Venta 2: Hoy
            $sale2 = Sale::create([
                'sale_number' => 'VEN-202610-0002',
                'user_id' => $seller->id,
                'customer_id' => $cust2->id,
                'customer_name' => $cust2->name,
                'subtotal' => 156.90,
                'tax' => 0.00,
                'total' => 156.90,
                'payment_method' => 'efectivo',
                'status' => 'completed',
                'notes' => 'Venta en mostrador',
                'created_at' => Carbon::now()->subHours(2),
            ]);

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $createdProducts['PRD-TEC-003']->id,
                'product_name' => $createdProducts['PRD-TEC-003']->name,
                'product_sku' => $createdProducts['PRD-TEC-003']->sku,
                'quantity' => 1,
                'unit_price' => 149.00,
                'cost_price' => 95.00,
                'subtotal' => 149.00,
                'created_at' => Carbon::now()->subHours(2),
            ]);

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $createdProducts['PRD-OFI-002']->id,
                'product_name' => $createdProducts['PRD-OFI-002']->name,
                'product_sku' => $createdProducts['PRD-OFI-002']->sku,
                'quantity' => 1,
                'unit_price' => 7.90,
                'cost_price' => 3.50,
                'subtotal' => 7.90,
                'created_at' => Carbon::now()->subHours(2),
            ]);

            // Venta 3: Hoy (Cliente Ocasional)
            $sale3 = Sale::create([
                'sale_number' => 'VEN-202610-0003',
                'user_id' => $admin->id,
                'customer_id' => null,
                'customer_name' => 'Cliente Ocasional',
                'subtotal' => 36.00,
                'tax' => 0.00,
                'total' => 36.00,
                'payment_method' => 'tarjeta',
                'status' => 'completed',
                'notes' => 'Pago sin contacto',
                'created_at' => Carbon::now()->subMinutes(30),
            ]);

            SaleItem::create([
                'sale_id' => $sale3->id,
                'product_id' => $createdProducts['PRD-FER-001']->id,
                'product_name' => $createdProducts['PRD-FER-001']->name,
                'product_sku' => $createdProducts['PRD-FER-001']->sku,
                'quantity' => 2,
                'unit_price' => 18.00,
                'cost_price' => 8.00,
                'subtotal' => 36.00,
                'created_at' => Carbon::now()->subMinutes(30),
            ]);
        }
    }
}
