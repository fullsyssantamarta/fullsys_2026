<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resolution_id')->nullable()->constrained()->nullOnDelete();
            
            // Numeración
            $table->string('prefix', 10);
            $table->string('number', 20);
            $table->string('consecutive', 20); // Consecutivo de nómina
            $table->unsignedInteger('type_document_id')->default(102); // 102=Nómina Individual
            
            // Periodo de pago
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->date('issue_date'); // Fecha de emisión
            
            // Tipo de nómina
            $table->unsignedInteger('payroll_type_id'); // 1=Ordinaria, 2=Extraordinaria
            $table->unsignedInteger('payment_method_id'); // 1=Contado, 2=Crédito
            
            // Información laboral del periodo
            $table->integer('worked_days')->default(30);
            $table->decimal('worked_hours', 8, 2)->default(0);
            
            // Devengados (Accruals) - Totales
            $table->decimal('salary', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('overtime', 15, 2)->default(0);
            $table->decimal('bonuses', 15, 2)->default(0);
            $table->decimal('commissions', 15, 2)->default(0);
            $table->decimal('severance', 15, 2)->default(0); // Cesantías
            $table->decimal('vacation', 15, 2)->default(0);
            $table->decimal('other_accruals', 15, 2)->default(0);
            $table->decimal('total_accruals', 15, 2)->default(0); // Total devengado
            
            // Deducciones (Deductions) - Totales
            $table->decimal('health_contribution', 15, 2)->default(0); // EPS
            $table->decimal('pension_contribution', 15, 2)->default(0); // Pensión
            $table->decimal('unemployment_fund', 15, 2)->default(0); // Fondo cesantías
            $table->decimal('tax_withholding', 15, 2)->default(0); // Retención fuente
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0); // Total deducciones
            
            // Neto a pagar
            $table->decimal('net_payment', 15, 2)->default(0);
            
            // Respuesta DIAN
            $table->string('cune', 500)->nullable(); // Código Único de Nómina Electrónica
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
            
            $table->timestamps();
            
            // Índices
            $table->index(['prefix', 'number']);
            $table->index('worker_id');
            $table->index('issue_date');
            $table->index('status');
            $table->index('dian_status');
            $table->index(['period_start_date', 'period_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
