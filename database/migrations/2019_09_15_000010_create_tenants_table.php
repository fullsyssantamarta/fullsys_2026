<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Company Information
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('email')->unique();
            $table->string('nit')->unique();
            $table->string('dv', 1)->nullable(); // Digito de verificación
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Colombia');
            $table->string('postal_code')->nullable();
            
            // Tax Information
            $table->integer('type_document_identification_id')->nullable();
            $table->integer('type_organization_id')->nullable();
            $table->integer('type_regime_id')->nullable();
            $table->integer('type_liability_id')->nullable();
            $table->integer('municipality_id')->nullable();
            $table->string('merchant_registration')->nullable();
            
            // APIDIAN Configuration
            $table->text('apidian_token')->nullable();
            $table->string('apidian_environment')->default('test'); // test or production
            $table->json('apidian_response')->nullable();
            $table->timestamp('apidian_configured_at')->nullable();
            
            // WhatsApp Configuration
            $table->string('whatsapp_instance')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);
            
            // Email Configuration
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            
            // Plan & Billing
            $table->string('plan')->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])->default('trial');
            
            // Logo
            $table->string('logo')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
