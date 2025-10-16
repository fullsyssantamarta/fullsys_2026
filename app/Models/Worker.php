<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_document_id',
        'identification_number',
        'first_name',
        'second_name',
        'surname',
        'second_surname',
        'email',
        'phone',
        'address',
        'municipality_id',
        'country_code',
        'type_worker_id',
        'subtype_worker_id',
        'type_contract_id',
        'high_risk_pension',
        'integral_salary',
        'salary',
        'bank_name',
        'account_type',
        'account_number',
        'status',
        'hire_date',
        'retirement_date',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'retirement_date' => 'date',
        'high_risk_pension' => 'boolean',
        'integral_salary' => 'boolean',
        'salary' => 'decimal:2',
    ];

    // Relaciones
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        $name = $this->first_name;
        if ($this->second_name) {
            $name .= ' ' . $this->second_name;
        }
        $name .= ' ' . $this->surname;
        if ($this->second_surname) {
            $name .= ' ' . $this->second_surname;
        }
        return $name;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsRetiredAttribute(): bool
    {
        return $this->status === 'retired';
    }

    // Métodos de negocio
    public function calculateTransportAllowance(): float
    {
        // Auxilio de transporte 2025: $162,000 (aproximado)
        // Solo para salarios <= 2 SMMLV
        $smmlv = 1300000; // Salario mínimo 2025 (aproximado)
        
        if ($this->salary <= ($smmlv * 2)) {
            return 162000;
        }
        
        return 0;
    }

    public function calculateHealthContribution(float $totalAccruals): float
    {
        // 4% del trabajador sobre salario base
        return $totalAccruals * 0.04;
    }

    public function calculatePensionContribution(float $totalAccruals): float
    {
        // 4% del trabajador sobre salario base
        return $totalAccruals * 0.04;
    }
}
