<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            
            // Tipo de devengado según DIAN
            $table->string('concept_code', 20); // SALARY, TRANSPORT, OVERTIME, etc
            $table->string('concept_name', 100);
            
            // Valores
            $table->decimal('amount', 15, 2);
            $table->decimal('quantity', 10, 2)->default(1); // Horas, días, etc
            $table->decimal('percentage', 5, 2)->nullable(); // Para porcentajes
            
            // Observaciones
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index('payroll_id');
            $table->index('concept_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_accruals');
    }
};
