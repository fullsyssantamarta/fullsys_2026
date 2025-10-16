<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebitNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'resolution_id',
        'prefix',
        'number',
        'type_document_id',
        'date',
        'time',
        'billing_reference_number',
        'billing_reference_uuid',
        'billing_reference_issue_date',
        'discrepancy_response_code',
        'discrepancy_response_description',
        'line_extension_amount',
        'tax_exclusive_amount',
        'tax_inclusive_amount',
        'payable_amount',
        'cude',
        'qr_code',
        'zip_key',
        'dian_status',
        'dian_response',
        'sent_to_dian_at',
        'pdf_url',
        'xml_url',
        'status',
        'sendmail',
        'sendmailtome',
    ];

    protected $casts = [
        'date' => 'date',
        'billing_reference_issue_date' => 'date',
        'sent_to_dian_at' => 'datetime',
        'dian_response' => 'array',
        'sendmail' => 'boolean',
        'sendmailtome' => 'boolean',
        'line_extension_amount' => 'decimal:2',
        'tax_exclusive_amount' => 'decimal:2',
        'tax_inclusive_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
    ];

    // Relaciones
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(Resolution::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DebitNoteItem::class);
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
        $subtotal = 0;
        $totalTax = 0;

        foreach ($this->items as $item) {
            $item->calculateTotals();
            $subtotal += $item->line_extension_amount;
            $totalTax += $item->tax_amount;
        }

        $this->line_extension_amount = $subtotal;
        $this->tax_exclusive_amount = $subtotal;
        $this->tax_inclusive_amount = $subtotal + $totalTax;
        $this->payable_amount = $subtotal + $totalTax;
        $this->save();
    }

    public function toApidianFormat(): array
    {
        return [
            'number' => (int) $this->number,
            'type_document_id' => $this->type_document_id,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->time,
            'resolution_number' => $this->resolution->resolution_number,
            'prefix' => $this->prefix,
            'notes' => $this->discrepancy_response_description,
            'sendmail' => $this->sendmail,
            'sendmailtome' => $this->sendmailtome,
            
            // Cliente
            'customer' => [
                'identification_number' => $this->customer->identification_number,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
                'email' => $this->customer->email,
                'merchant_registration' => $this->customer->merchant_registration ?? '0000000-00',
                'type_document_identification_id' => $this->customer->type_document_id,
                'type_organization_id' => $this->customer->type_organization_id,
                'type_liability_id' => $this->customer->type_liability_id,
                'type_regime_id' => $this->customer->type_regime_id,
                'municipality_id' => $this->customer->municipality_id,
            ],
            
            // Referencia a factura original (Billing Reference)
            'billing_reference' => [
                'number' => $this->billing_reference_number,
                'uuid' => $this->billing_reference_uuid,
                'issue_date' => $this->billing_reference_issue_date->format('Y-m-d'),
            ],
            
            // Razón de la nota débito (Discrepancy Response)
            'discrepancy_response' => [
                'code' => $this->discrepancy_response_code,
                'description' => $this->discrepancy_response_description,
            ],
            
            // Totales monetarios
            'legal_monetary_totals' => [
                'line_extension_amount' => number_format($this->line_extension_amount, 2, '.', ''),
                'tax_exclusive_amount' => number_format($this->tax_exclusive_amount, 2, '.', ''),
                'tax_inclusive_amount' => number_format($this->tax_inclusive_amount, 2, '.', ''),
                'payable_amount' => number_format($this->payable_amount, 2, '.', ''),
            ],
            
            // Totales de impuestos
            'tax_totals' => [
                [
                    'tax_id' => 1, // IVA
                    'tax_amount' => number_format($this->tax_inclusive_amount - $this->tax_exclusive_amount, 2, '.', ''),
                    'taxable_amount' => number_format($this->tax_exclusive_amount, 2, '.', ''),
                ],
            ],
            
            // Líneas de la nota
            'debit_note_lines' => $this->items->map(function ($item, $index) {
                return [
                    'unit_measure_id' => $item->unit_measure_id,
                    'invoiced_quantity' => number_format($item->invoiced_quantity, 6, '.', ''),
                    'line_extension_amount' => number_format($item->line_extension_amount, 2, '.', ''),
                    'free_of_charge_indicator' => $item->free_of_charge_indicator,
                    'tax_totals' => [
                        [
                            'tax_id' => $item->tax_id,
                            'tax_amount' => number_format($item->tax_amount, 2, '.', ''),
                            'taxable_amount' => number_format($item->taxable_amount, 2, '.', ''),
                            'percent' => number_format($item->tax_percent, 2, '.', ''),
                        ],
                    ],
                    'description' => $item->description,
                    'code' => $item->code,
                    'type_item_identification_id' => $item->type_item_identification_id,
                    'price_amount' => number_format($item->price_amount, 6, '.', ''),
                    'base_quantity' => number_format($item->base_quantity, 6, '.', ''),
                ];
            })->toArray(),
        ];
    }
}
