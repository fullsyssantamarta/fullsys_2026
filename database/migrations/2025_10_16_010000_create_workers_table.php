<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            
            // Identificación
            $table->unsignedInteger('type_document_id')->default(3); // 3=CC, 4=CE, 5=TI, 6=NIT, etc
            $table->string('identification_number', 20)->unique();
            
            // Datos personales
            $table->string('first_name', 100);
            $table->string('second_name', 100)->nullable();
            $table->string('surname', 100);
            $table->string('second_surname', 100)->nullable();
            
            // Contacto
            $table->string('email', 150);
            $table->string('phone', 20);
            $table->string('address', 200);
            $table->unsignedInteger('municipality_id'); // Código DANE
            $table->string('country_code', 2)->default('CO'); // ISO 3166-1
            
            // Información laboral
            $table->unsignedInteger('type_worker_id'); // 01=Empleado, 02=Contratista, etc
            $table->unsignedInteger('subtype_worker_id')->nullable(); // Subtipo según DIAN
            $table->unsignedInteger('type_contract_id'); // 1=Término fijo, 2=Indefinido, etc
            $table->boolean('high_risk_pension')->default(false);
            $table->boolean('integral_salary')->default(false);
            
            // Salario
            $table->decimal('salary', 15, 2);
            
            // Cuenta bancaria
            $table->string('bank_name', 100)->nullable();
            $table->string('account_type', 20)->nullable(); // Ahorros, Corriente
            $table->string('account_number', 50)->nullable();
            
            // Estado
            $table->enum('status', ['active', 'inactive', 'retired'])->default('active');
            
            // Fechas importantes
            $table->date('hire_date')->nullable();
            $table->date('retirement_date')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('identification_number');
            $table->index('status');
            $table->index(['surname', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
