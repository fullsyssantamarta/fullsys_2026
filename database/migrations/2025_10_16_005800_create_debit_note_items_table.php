<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debit_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            
            // Identificación del ítem (UBL 2.1)
            $table->string('code', 50);
            $table->text('description');
            $table->unsignedInteger('type_item_identification_id')->default(4); // 4=Estándar
            $table->unsignedInteger('unit_measure_id')->default(70); // 70=Unidad
            
            // Cantidades
            $table->decimal('invoiced_quantity', 15, 6)->default(1);
            $table->decimal('base_quantity', 15, 6)->default(1);
            
            // Precios
            $table->decimal('price_amount', 15, 6);
            $table->decimal('line_extension_amount', 15, 2)->default(0); // Subtotal línea
            
            // Descuentos
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            
            // Impuestos (IVA)
            $table->unsignedInteger('tax_id')->default(1); // 1=IVA
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0); // Base gravable
            $table->decimal('tax_percent', 5, 2)->default(19); // % IVA
            
            // Indicador de gratuidad
            $table->boolean('free_of_charge_indicator')->default(false);
            
            // Orden
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index('debit_note_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_note_items');
    }
};
