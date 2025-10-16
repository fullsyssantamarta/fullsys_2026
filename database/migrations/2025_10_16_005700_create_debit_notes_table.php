<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            
            // Referencias
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resolution_id')->constrained()->cascadeOnDelete();
            
            // Numeración
            $table->string('prefix', 10);
            $table->string('number', 20);
            $table->unsignedInteger('type_document_id')->default(92); // 92 = Nota Débito
            
            // Fechas
            $table->date('date');
            $table->time('time');
            
            // Billing Reference (referencia a factura original)
            $table->string('billing_reference_number'); // Número de factura afectada
            $table->string('billing_reference_uuid')->nullable(); // CUFE de factura original
            $table->date('billing_reference_issue_date'); // Fecha emisión factura original
            
            // Discrepancy Response (razón de la nota)
            $table->string('discrepancy_response_code', 10); // 1=Intereses, 2=Gastos cobro, 3=Cambio valor
            $table->text('discrepancy_response_description'); // Descripción detallada
            
            // Totales APIDIAN (UBL 2.1)
            $table->decimal('line_extension_amount', 15, 2)->default(0); // Subtotal
            $table->decimal('tax_exclusive_amount', 15, 2)->default(0); // Base gravable
            $table->decimal('tax_inclusive_amount', 15, 2)->default(0); // Total con impuestos
            $table->decimal('payable_amount', 15, 2)->default(0); // Total a pagar
            
            // Respuesta DIAN
            $table->string('cude', 500)->nullable(); // Código Único Nota Débito
            $table->string('qr_code', 500)->nullable();
            $table->string('zip_key', 500)->nullable();
            $table->enum('dian_status', ['pending', 'processing', 'approved', 'rejected'])->default('pending');
            $table->json('dian_response')->nullable();
            $table->timestamp('sent_to_dian_at')->nullable();
            
            // URLs documentos
            $table->string('pdf_url', 500)->nullable();
            $table->string('xml_url', 500)->nullable();
            
            // Estado
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'voided'])->default('draft');
            
            // Notificaciones
            $table->boolean('sendmail')->default(true);
            $table->boolean('sendmailtome')->default(false);
            
            $table->timestamps();
            
            // Índices
            $table->index(['prefix', 'number']);
            $table->index('date');
            $table->index('status');
            $table->index('dian_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
