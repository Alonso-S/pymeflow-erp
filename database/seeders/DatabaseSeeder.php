<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SaleService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios Demo (Perú)
        $admin = User::updateOrCreate(
            ['email' => 'admin@pymeflow.com'],
            [
                'name' => 'Carlos Mendoza (Administrador)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $seller = User::updateOrCreate(
            ['email' => 'vendedor@pymeflow.com'],
            [
                'name' => 'Ana Quispe (Vendedora)',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'is_active' => true,
            ]
        );

        // 2. Categorías
        $catElectronics = Category::updateOrCreate(['name' => 'Tecnología y Cómputo'], [
            'slug' => Str::slug('Tecnología y Cómputo'),
            'description' => 'Dispositivos, accesorios y periféricos de computación.',
            'is_active' => true,
        ]);

        $catOffice = Category::updateOrCreate(['name' => 'Oficina y Librería'], [
            'slug' => Str::slug('Oficina y Librería'),
            'description' => 'Artículos, libros técnicos, papelería y mobiliario ergonómico.',
            'is_active' => true,
        ]);

        $catFood = Category::updateOrCreate(['name' => 'Alimentos y Cafetería'], [
            'slug' => Str::slug('Alimentos y Cafetería'),
            'description' => 'Insumos corporativos, café peruano de especialidad y productos agrícolas.',
            'is_active' => true,
        ]);

        $catTools = Category::updateOrCreate(['name' => 'Ferretería y Mantenimiento'], [
            'slug' => Str::slug('Ferretería y Mantenimiento'),
            'description' => 'Herramientas de precisión y mantenimiento industrial.',
            'is_active' => true,
        ]);

        // 3. Proveedores Peruanos (RUC 11 dígitos)
        Supplier::updateOrCreate(['tax_id' => '20554433221'], [
            'company_name' => 'Importaciones Tecnológicas Lima S.A.C.',
            'contact_name' => 'Roberto Sánchez',
            'document_type' => 'RUC',
            'email' => 'ventas@importacionestec.pe',
            'phone' => '+51 1 4458900',
            'address' => 'Av. Garcilaso de la Vega 1250, Cercado de Lima',
            'is_active' => true,
        ]);

        Supplier::updateOrCreate(['tax_id' => '20601122334'], [
            'company_name' => 'Distribuidora Papelera Andina E.I.R.L.',
            'contact_name' => 'Marta Paredes',
            'document_type' => 'RUC',
            'email' => 'pedidos@papeleraandina.pe',
            'phone' => '+51 1 3216540',
            'address' => 'Av. Argentina 2410, Callao, Lima',
            'is_active' => true,
        ]);

        // 4. Clientes Peruanos (RUC y DNI)
        $cust1 = Customer::updateOrCreate(['tax_id' => '20518899776'], [
            'name' => 'Constructora & Ingeniería Pacífico S.A.C.',
            'document_type' => 'RUC',
            'email' => 'logistica@pacificoperu.pe',
            'phone' => '+51 998 112 233',
            'address' => 'Av. Javier Prado Este 2890, San Borja, Lima',
            'is_active' => true,
        ]);

        $cust2 = Customer::updateOrCreate(['tax_id' => '20456789012'], [
            'name' => 'Comercializadora del Sur S.R.L.',
            'document_type' => 'RUC',
            'email' => 'compras@comercialsur.pe',
            'phone' => '+51 954 334 455',
            'address' => 'Av. Ejército 710, Yanahuara, Arequipa',
            'is_active' => true,
        ]);

        $cust3 = Customer::updateOrCreate(['tax_id' => '45891236'], [
            'name' => 'María Elena Huamán Rojas',
            'document_type' => 'DNI',
            'email' => 'mhuaman@gmail.com',
            'phone' => '+51 987 654 321',
            'address' => 'Jr. Las Magnolias 450, Jesús María, Lima',
            'is_active' => true,
        ]);

        // 5. Catálogo de Productos en Soles (PEN - S/) con afectación IGV diferenciada
        $productsData = [
            [
                'category_id' => $catElectronics->id,
                'sku' => 'PRD-TEC-001',
                'name' => 'Teclado Mecánico RGB Switch Blue',
                'description' => 'Teclado mecánico con retroiluminación RGB configurable, anti-ghosting.',
                'cost_price' => 95.00,
                'sale_price' => 177.00, // Gravado: Base S/ 150.00 + IGV S/ 27.00
                'tax_type' => 'gravado',
                'initial_stock' => 28,
                'min_stock' => 5,
            ],
            [
                'category_id' => $catElectronics->id,
                'sku' => 'PRD-TEC-002',
                'name' => 'Mouse Inalámbrico Ergonómico 2.4G',
                'description' => 'Sensor óptico de alta precisión con agarre vertical anti fatiga.',
                'cost_price' => 35.00,
                'sale_price' => 70.80, // Gravado: Base S/ 60.00 + IGV S/ 10.80
                'tax_type' => 'gravado',
                'initial_stock' => 41,
                'min_stock' => 8,
            ],
            [
                'category_id' => $catElectronics->id,
                'sku' => 'PRD-TEC-003',
                'name' => 'Monitor LED 24" IPS Full HD 75Hz',
                'description' => 'Pantalla ultra delgada con conectividad HDMI y DisplayPort.',
                'cost_price' => 360.00,
                'sale_price' => 590.00, // Gravado: Base S/ 500.00 + IGV S/ 90.00
                'tax_type' => 'gravado',
                'initial_stock' => 13,
                'min_stock' => 3,
            ],
            [
                'category_id' => $catOffice->id,
                'sku' => 'PRD-OFI-001',
                'name' => 'Silla Ergonómica Ejecutiva Lumbar',
                'description' => 'Soporte lumbar ajustable, malla transpirable y ruedas de silicona.',
                'cost_price' => 240.00,
                'sale_price' => 413.00, // Gravado: Base S/ 350.00 + IGV S/ 63.00
                'tax_type' => 'gravado',
                'initial_stock' => 3, // Alerta de stock bajo (min: 5)
                'min_stock' => 5,
            ],
            [
                'category_id' => $catOffice->id,
                'sku' => 'PRD-OFI-002',
                'name' => 'Manual Técnico de Contabilidad y Tributación PYME',
                'description' => 'Edición impresa actualizada (Bien exonerado de IGV según ley del libro).',
                'cost_price' => 28.00,
                'sale_price' => 55.00, // Exonerado: Base S/ 55.00 + IGV S/ 0.00
                'tax_type' => 'exonerado',
                'initial_stock' => 86,
                'min_stock' => 15,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'PRD-ALI-001',
                'name' => 'Café Gourmet Villa Rica Origen Pasco 500g',
                'description' => 'Café tostado en grano de altura con notas achocolatadas.',
                'cost_price' => 20.00,
                'sale_price' => 41.30, // Gravado: Base S/ 35.00 + IGV S/ 6.30
                'tax_type' => 'gravado',
                'initial_stock' => 2, // Alerta de stock bajo (min: 10)
                'min_stock' => 10,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'PRD-ALI-002',
                'name' => 'Quinua Orgánica Perlada de Puno 1kg (Grano Natural)',
                'description' => 'Producto agrícola primario exonerado según Apéndice I Ley del IGV.',
                'cost_price' => 9.50,
                'sale_price' => 18.00, // Exonerado: Base S/ 18.00 + IGV S/ 0.00
                'tax_type' => 'exonerado',
                'initial_stock' => 30,
                'min_stock' => 6,
            ],
            [
                'category_id' => $catTools->id,
                'sku' => 'PRD-FER-001',
                'name' => 'Kit Destornilladores de Precisión 24 en 1',
                'description' => 'Puntas magnéticas de acero S2 en estuche de aluminio.',
                'cost_price' => 28.00,
                'sale_price' => 59.00, // Gravado: Base S/ 50.00 + IGV S/ 9.00
                'tax_type' => 'gravado',
                'initial_stock' => 17,
                'min_stock' => 4,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $pData) {
            $initialStock = $pData['initial_stock'];
            unset($pData['initial_stock']);

            $existing = Product::where('sku', $pData['sku'])->first();
            if (!$existing) {
                $pData['stock'] = $initialStock;
                $p = Product::create($pData);

                InventoryMovement::firstOrCreate(
                    ['product_id' => $p->id, 'reference_type' => 'InitialSetup'],
                    [
                        'user_id' => $admin->id,
                        'type' => 'in',
                        'quantity' => $initialStock,
                        'previous_stock' => 0,
                        'new_stock' => $initialStock,
                        'reason' => 'Inventario inicial del sistema',
                        'reference_id' => null,
                        'created_at' => Carbon::now()->subDays(10),
                    ]
                );
            } else {
                $p = $existing;
            }
            $createdProducts[$p->sku] = $p;
        }

        // 6. Ventas de Demostración creadas mediante SaleService (Garantiza cuadre exacto de cabecera, ítems, IGV y Kardex)
        $saleService = app(SaleService::class);

        if (!Sale::where('idempotency_key', 'SEED-SALE-0001')->exists()) {
            $saleService->createSale($seller, [
                'idempotency_key' => 'SEED-SALE-0001',
                'customer_id' => $cust1->id,
                'payment_method' => 'transferencia',
                'notes' => 'Entrega en oficina de San Borja',
                'created_at' => Carbon::yesterday()->setHour(14)->setMinute(30),
                'items' => [
                    ['product_id' => $createdProducts['PRD-TEC-001']->id, 'quantity' => 3], // 3 * 177.00 = 531.00 (Base 450 + IGV 81)
                    ['product_id' => $createdProducts['PRD-TEC-002']->id, 'quantity' => 1], // 1 * 70.80  = 70.80  (Base 60  + IGV 10.80)
                ],
            ]);
        }

        if (!Sale::where('idempotency_key', 'SEED-SALE-0002')->exists()) {
            // Venta mixta (Gravado + Exonerado)
            $saleService->createSale($seller, [
                'idempotency_key' => 'SEED-SALE-0002',
                'customer_id' => $cust2->id,
                'payment_method' => 'yape',
                'notes' => 'Venta mixta con libro exonerado',
                'created_at' => Carbon::now()->subHours(2),
                'items' => [
                    ['product_id' => $createdProducts['PRD-TEC-003']->id, 'quantity' => 1], // Gravado: 590.00 (Base 500 + IGV 90)
                    ['product_id' => $createdProducts['PRD-OFI-002']->id, 'quantity' => 1], // Exonerado: 55.00 (Base 55 + IGV 0)
                ],
            ]);
        }

        if (!Sale::where('idempotency_key', 'SEED-SALE-0003')->exists()) {
            $saleService->createSale($admin, [
                'idempotency_key' => 'SEED-SALE-0003',
                'customer_id' => null,
                'customer_name' => 'Cliente Ocasional',
                'payment_method' => 'tarjeta',
                'notes' => 'Pago POS sin contacto',
                'created_at' => Carbon::now()->subMinutes(30),
                'items' => [
                    ['product_id' => $createdProducts['PRD-FER-001']->id, 'quantity' => 2], // 2 * 59.00 = 118.00 (Base 100 + IGV 18)
                ],
            ]);
        }

        // 7. Órdenes de Compra de Demostración (1 recepcionada y 1 pendiente)
        $supp1 = Supplier::where('tax_id', '20554433221')->first();
        $supp2 = Supplier::where('tax_id', '20601122334')->first();
        $purchaseService = app(\App\Services\PurchaseService::class);

        if ($supp1 && !\App\Models\Purchase::where('supplier_document', 'F001-0004512')->exists()) {
            $purchaseService->createPurchase($admin, [
                'supplier_id' => $supp1->id,
                'supplier_document' => 'F001-0004512',
                'prices_include_tax' => false,
                'receive_immediately' => true,
                'notes' => 'Reposición mensual de periféricos recibida conforme en almacén',
                'items' => [
                    ['product_id' => $createdProducts['PRD-TEC-001']->id, 'quantity' => 5, 'unit_cost' => 95.00],
                ],
            ]);
        }

        if ($supp2 && !\App\Models\Purchase::where('supplier_document', 'OC-2026-089')->exists()) {
            $purchaseService->createPurchase($admin, [
                'supplier_id' => $supp2->id,
                'supplier_document' => 'OC-2026-089',
                'prices_include_tax' => false,
                'receive_immediately' => false,
                'notes' => 'Orden emitida pendiente de llegada de proveedor para reabastecer stock bajo',
                'items' => [
                    ['product_id' => $createdProducts['PRD-OFI-001']->id, 'quantity' => 10, 'unit_cost' => 240.00],
                ],
            ]);
        }
    }
}
