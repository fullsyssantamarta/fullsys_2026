<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_regime',
        'tax_liability',
        'notes',
        'status',
    ];
    
    /**
     * Get the invoices for the customer
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    /**
     * Get active customers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
