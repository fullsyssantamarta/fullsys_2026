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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('resolution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            
            // Datos básicos de la factura
            $table->string('prefix', 10)->nullable(); // Prefijo de la resolución
            $table->string('number', 50); // Número consecutivo
            $table->string('full_number', 60)->virtualAs("CONCAT(COALESCE(prefix, ''), number)"); // Número completo (prefix + number)
            $table->integer('type_document_id')->default(1); // 1 = Factura electrónica
            $table->date('date'); // Fecha de emisión
            $table->time('time'); // Hora de emisión
            $table->text('notes')->nullable(); // Notas generales
            
            // Datos de establecimiento (opcional)
            $table->string('establishment_name')->nullable();
            $table->string('establishment_address')->nullable();
            $table->string('establishment_phone', 20)->nullable();
            $table->integer('establishment_municipality')->nullable();
            
            // Textos adicionales para la representación gráfica
            $table->text('head_note')->nullable(); // Nota del encabezado
            $table->text('foot_note')->nullable(); // Nota del pie de página
            
            // Forma de pago
            $table->integer('payment_form_id')->default(1); // 1 = Contado, 2 = Crédito
            $table->integer('payment_method_id')->default(10); // 10 = Efectivo, 30 = Transferencia, etc.
            $table->date('payment_due_date')->nullable(); // Fecha de vencimiento
            $table->integer('duration_measure')->nullable(); // Días de plazo
            
            // Totales monetarios
            $table->decimal('line_extension_amount', 15, 2)->default(0); // Subtotal sin IVA
            $table->decimal('tax_exclusive_amount', 15, 2)->default(0); // Igual al subtotal si no hay cargos
            $table->decimal('tax_inclusive_amount', 15, 2)->default(0); // Total con IVA
            $table->decimal('payable_amount', 15, 2)->default(0); // Total a pagar
            $table->decimal('discount_amount', 15, 2)->default(0); // Descuentos globales
            
            // Totales de impuestos
            $table->decimal('tax_amount', 15, 2)->default(0); // Total de impuestos
            $table->string('tax_percent', 10)->nullable(); // Porcentaje de IVA predominante
            
            // Datos APIDIAN (respuesta de la DIAN)
            $table->string('cufe', 255)->nullable(); // Código Único de Factura Electrónica
            $table->string('qr_code', 255)->nullable(); // URL del código QR
            $table->string('zip_key', 255)->nullable(); // Clave del ZIP para consultas asíncronas
            $table->string('dian_status', 50)->nullable(); // Estado en la DIAN (Aprobada, Rechazada, etc.)
            $table->text('dian_response')->nullable(); // Respuesta completa de la DIAN (JSON)
            $table->timestamp('sent_to_dian_at')->nullable(); // Fecha/hora de envío a DIAN
            
            // URLs de documentos
            $table->string('pdf_url', 500)->nullable(); // URL del PDF
            $table->string('xml_url', 500)->nullable(); // URL del XML
            
            // Control de envío de emails
            $table->boolean('sendmail')->default(false); // Enviar email al cliente
            $table->boolean('sendmailtome')->default(false); // Enviar copia al emisor
            $table->timestamp('emailed_at')->nullable(); // Fecha/hora de envío de email
            
            // Período de facturación (opcional)
            $table->string('seze', 50)->nullable(); // Período o serie (ej: "2021-2017")
            
            // Control de estado
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'voided'])->default('draft');
            // draft = Borrador
            // sent = Enviada a DIAN (pendiente respuesta)
            // approved = Aprobada por DIAN
            // rejected = Rechazada por DIAN
            // voided = Anulada (con nota crédito)
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('date');
            $table->index('status');
            $table->index(['prefix', 'number']);
            $table->unique(['resolution_id', 'prefix', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
