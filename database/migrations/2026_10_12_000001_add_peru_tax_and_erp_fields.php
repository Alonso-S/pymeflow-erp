<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clientes: Tipo de documento (DNI, RUC, CE, PASAPORTE)
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'document_type')) {
                $table->string('document_type', 20)->default('DNI')->after('name');
            }
        });

        // 2. Proveedores: Tipo de documento (por defecto RUC 11 dígitos en Perú)
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'document_type')) {
                $table->string('document_type', 20)->default('RUC')->after('contact_name');
            }
        });

        // 3. Productos: Tipo de afectación al IGV (gravado, exonerado, inafecto)
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tax_type')) {
                $table->string('tax_type', 20)->default('gravado')->after('sale_price');
            }
        });

        // 4. Ventas: Moneda, modo de precio, bases imponibles, descuentos, idempotencia y auditoría de anulación
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'idempotency_key')) {
                $table->string('idempotency_key', 100)->nullable()->unique()->after('sale_number');
            }
            if (!Schema::hasColumn('sales', 'currency')) {
                $table->string('currency', 3)->default('PEN')->after('customer_name');
            }
            if (!Schema::hasColumn('sales', 'prices_include_tax')) {
                $table->boolean('prices_include_tax')->default(true)->after('currency');
            }
            if (!Schema::hasColumn('sales', 'taxable_amount')) {
                $table->decimal('taxable_amount', 12, 2)->default(0.00)->after('prices_include_tax');
                $table->decimal('exempt_amount', 12, 2)->default(0.00)->after('taxable_amount');
                $table->decimal('unaffected_amount', 12, 2)->default(0.00)->after('exempt_amount');
                $table->decimal('discount_total', 12, 2)->default(0.00)->after('unaffected_amount');
            }
            if (!Schema::hasColumn('sales', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('notes');
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
                $table->string('cancellation_reason', 500)->nullable()->after('cancelled_by');
            }
        });

        // 5. Ítems de Venta: Instantánea histórica tributaria por línea
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'tax_type')) {
                $table->string('tax_type', 20)->default('gravado')->after('product_sku');
                $table->decimal('tax_rate', 5, 2)->default(18.00)->after('tax_type');
                $table->decimal('unit_value', 12, 4)->default(0.0000)->after('quantity');
                $table->decimal('discount_amount', 12, 2)->default(0.00)->after('cost_price');
                $table->decimal('tax_base', 12, 2)->default(0.00)->after('discount_amount');
                $table->decimal('tax_amount', 12, 2)->default(0.00)->after('tax_base');
                $table->decimal('line_total', 12, 2)->default(0.00)->after('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn([
                'tax_type',
                'tax_rate',
                'unit_value',
                'discount_amount',
                'tax_base',
                'tax_amount',
                'line_total',
            ]);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'idempotency_key',
                'currency',
                'prices_include_tax',
                'taxable_amount',
                'exempt_amount',
                'unaffected_amount',
                'discount_total',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tax_type');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }
};
