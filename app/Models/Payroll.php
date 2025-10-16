<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'resolution_id',
        'prefix',
        'number',
        'consecutive',
        'type_document_id',
        'period_start_date',
        'period_end_date',
        'issue_date',
        'payroll_type_id',
        'payment_method_id',
        'worked_days',
        'worked_hours',
        'salary',
        'transport_allowance',
        'overtime',
        'bonuses',
        'commissions',
        'severance',
        'vacation',
        'other_accruals',
        'total_accruals',
        'health_contribution',
        'pension_contribution',
        'unemployment_fund',
        'tax_withholding',
        'other_deductions',
        'total_deductions',
        'net_payment',
        'cune',
        'qr_code',
        'zip_key',
        'dian_status',
        'dian_response',
        'sent_to_dian_at',
        'pdf_url',
        'xml_url',
        'status',
        'sendmail',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'issue_date' => 'date',
        'sent_to_dian_at' => 'datetime',
        'dian_response' => 'array',
        'sendmail' => 'boolean',
        'salary' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'overtime' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'commissions' => 'decimal:2',
        'severance' => 'decimal:2',
        'vacation' => 'decimal:2',
        'other_accruals' => 'decimal:2',
        'total_accruals' => 'decimal:2',
        'health_contribution' => 'decimal:2',
        'pension_contribution' => 'decimal:2',
        'unemployment_fund' => 'decimal:2',
        'tax_withholding' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_payment' => 'decimal:2',
    ];

    // Relaciones
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(Resolution::class);
    }

    public function accruals(): HasMany
    {
        return $this->hasMany(PayrollAccrual::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollDeduction::class);
    }

    // Accessors
    public function getFullNumberAttribute(): string
    {
        return $this->prefix . $this->number;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->dian_status === 'approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->dian_status === 'rejected';
    }

    public function getIsSentAttribute(): bool
    {
        return in_array($this->status, ['sent', 'approved', 'rejected']);
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    // Métodos de negocio
    public function calculateTotals(): void
    {
        // Calcular total devengados
        $this->total_accruals = 
            $this->salary +
            $this->transport_allowance +
            $this->overtime +
            $this->bonuses +
            $this->commissions +
            $this->severance +
            $this->vacation +
            $this->other_accruals;

        // Calcular total deducciones
        $this->total_deductions = 
            $this->health_contribution +
            $this->pension_contribution +
            $this->unemployment_fund +
            $this->tax_withholding +
            $this->other_deductions;

        // Calcular neto a pagar
        $this->net_payment = $this->total_accruals - $this->total_deductions;

        $this->save();
    }

    public function toApidianFormat(): array
    {
        $worker = $this->worker;
        
        return [
            'sync' => true,
            'payroll_type_id' => $this->payroll_type_id,
            'consecutive' => (string) $this->consecutive,
            'prefix' => $this->prefix,
            'notes' => 'Nómina periodo ' . $this->period_start_date->format('Y-m-d') . ' a ' . $this->period_end_date->format('Y-m-d'),
            'sendmail' => $this->sendmail,
            
            // Periodo
            'period' => [
                'admision_date' => $worker->hire_date ? $worker->hire_date->format('Y-m-d') : $this->period_start_date->format('Y-m-d'),
                'settlement_start_date' => $this->period_start_date->format('Y-m-d'),
                'settlement_end_date' => $this->period_end_date->format('Y-m-d'),
                'worked_time' => $this->worked_days,
                'issue_date' => $this->issue_date->format('Y-m-d'),
            ],
            
            // Información del trabajador
            'worker' => [
                'type_worker_id' => $worker->type_worker_id,
                'sub_type_worker_id' => $worker->subtype_worker_id ?? 0,
                'payroll_type_document_identification_id' => $worker->type_document_id,
                'municipality_id' => $worker->municipality_id,
                'type_contract_id' => $worker->type_contract_id,
                'high_risk_pension' => $worker->high_risk_pension,
                'identification_number' => $worker->identification_number,
                'surname' => $worker->surname,
                'second_surname' => $worker->second_surname ?? '',
                'first_name' => $worker->first_name,
                'middle_name' => $worker->second_name ?? '',
                'address' => $worker->address,
                'integral_salarary' => $worker->integral_salary,
                'salary' => number_format($worker->salary, 2, '.', ''),
                'worker_code' => (string) $worker->id,
            ],
            
            // Método de pago
            'payment' => [
                'payment_method_id' => $this->payment_method_id,
                'bank_name' => $worker->bank_name ?? 'N/A',
                'account_type' => $worker->account_type ?? 'N/A',
                'account_number' => $worker->account_number ?? 'N/A',
            ],
            
            // Devengados (Accruals)
            'accrued' => [
                'salary' => number_format($this->salary, 2, '.', ''),
                'transportation_allowance' => number_format($this->transport_allowance, 2, '.', ''),
                'overtime_surcharge' => number_format($this->overtime, 2, '.', ''),
                'bonuses' => number_format($this->bonuses, 2, '.', ''),
                'commissions' => number_format($this->commissions, 2, '.', ''),
                'severance' => number_format($this->severance, 2, '.', ''),
                'vacation' => number_format($this->vacation, 2, '.', ''),
                'other_concepts' => number_format($this->other_accruals, 2, '.', ''),
            ],
            
            // Deducciones (Deductions)
            'deductions' => [
                'health' => number_format($this->health_contribution, 2, '.', ''),
                'pension' => number_format($this->pension_contribution, 2, '.', ''),
                'solidarity_fund' => number_format($this->unemployment_fund, 2, '.', ''),
                'withholding_source' => number_format($this->tax_withholding, 2, '.', ''),
                'other_deductions' => number_format($this->other_deductions, 2, '.', ''),
            ],
        ];
    }
}
