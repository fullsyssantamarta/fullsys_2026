<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            
            // Datos del ítem según estructura APIDIAN
            $table->string('code', 100); // Código del producto
            $table->text('description'); // Descripción del producto/servicio
            $table->text('notes')->nullable(); // Notas específicas de la línea
            
            // Unidad de medida
            $table->integer('unit_measure_id')->default(70); // 70 = unidad
            
            // Cantidades y precios
            $table->decimal('invoiced_quantity', 15, 4); // Cantidad facturada
            $table->decimal('base_quantity', 15, 4)->default(1); // Cantidad base para el precio
            $table->decimal('price_amount', 15, 2); // Precio unitario
            
            // Montos
            $table->decimal('line_extension_amount', 15, 2); // Subtotal de la línea (sin IVA)
            
            // Descuentos a nivel de línea
            $table->decimal('discount_amount', 15, 2)->default(0); // Descuento aplicado
            $table->decimal('discount_percent', 5, 2)->default(0); // Porcentaje de descuento
            
            // Cargos adicionales a nivel de línea
            $table->decimal('charge_amount', 15, 2)->default(0); // Cargos adicionales
            
            // Impuestos de la línea
            $table->integer('tax_id')->default(1); // 1 = IVA
            $table->decimal('tax_amount', 15, 2)->default(0); // Valor del impuesto
            $table->decimal('taxable_amount', 15, 2); // Base gravable
            $table->string('tax_percent', 10)->default('19.00'); // Porcentaje de IVA
            
            // Tipo de identificación del ítem
            $table->integer('type_item_identification_id')->default(4); // 4 = Estándar
            
            // Indicadores
            $table->boolean('free_of_charge_indicator')->default(false); // ¿Es gratuito?
            
            // Orden
            $table->integer('sort_order')->default(0); // Orden de la línea
            
            $table->timestamps();
            
            // Índices
            $table->index('invoice_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
