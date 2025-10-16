<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
    
    protected $fillable = [
        'id',
        'name',
        'business_name',
        'email',
        'nit',
        'dv',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        
        // Tax Information
        'type_document_identification_id',
        'type_organization_id',
        'type_regime_id',
        'type_liability_id',
        'municipality_id',
        'merchant_registration',
        
        // APIDIAN
        'apidian_token',
        'apidian_environment',
        'apidian_response',
        'apidian_configured_at',
        
        // WhatsApp
        'whatsapp_instance',
        'whatsapp_enabled',
        
        // Email Configuration
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        
        // Plan & Status
        'plan',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
        
        // Logo
        'logo',
        
        'data',
    ];
    
    protected $casts = [
        'data' => 'array',
        'apidian_response' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'apidian_configured_at' => 'datetime',
        'whatsapp_enabled' => 'boolean',
    ];
    
    protected $hidden = [
        'mail_password',
        'apidian_token',
    ];
    
    /**
     * Get the attributes that should be cast to native types.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'business_name',
            'email',
            'nit',
            'dv',
            'phone',
            'mobile',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'type_document_identification_id',
            'type_organization_id',
            'type_regime_id',
            'type_liability_id',
            'municipality_id',
            'merchant_registration',
            'apidian_token',
            'apidian_environment',
            'whatsapp_instance',
            'whatsapp_enabled',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'plan',
            'status',
            'trial_ends_at',
            'subscription_ends_at',
            'logo',
        ];
    }
    
    /**
     * Check if tenant is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }
    
    /**
     * Check if tenant subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        return $this->status === 'active' && 
               (!$this->subscription_ends_at || $this->subscription_ends_at->isFuture());
    }
    
    /**
     * Check if tenant can access the system
     */
    public function canAccess(): bool
    {
        return $this->isOnTrial() || $this->hasActiveSubscription();
    }
}
