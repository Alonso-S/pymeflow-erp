<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('supplier_document', 60)->nullable()->comment('Referencia de factura o guía del proveedor');
            $table->string('currency', 3)->default('PEN');
            $table->boolean('prices_include_tax')->default(false);
            $table->decimal('taxable_amount', 12, 2)->default(0.00);
            $table->decimal('exempt_amount', 12, 2)->default(0.00);
            $table->decimal('unaffected_amount', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->string('status', 20)->default('ordered'); // ordered, received, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->string('tax_type', 20)->default('gravado');
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 4);
            $table->decimal('tax_base', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
