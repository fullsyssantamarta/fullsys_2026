<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'resolution_id', 'customer_id', 'prefix', 'number', 'type_document_id',
        'date', 'time', 'notes', 'establishment_name', 'establishment_address',
        'establishment_phone', 'establishment_municipality', 'head_note', 'foot_note',
        'payment_form_id', 'payment_method_id', 'payment_due_date', 'duration_measure',
        'line_extension_amount', 'tax_exclusive_amount', 'tax_inclusive_amount',
        'payable_amount', 'discount_amount', 'tax_amount', 'tax_percent',
        'cufe', 'qr_code', 'zip_key', 'dian_status', 'dian_response', 'sent_to_dian_at',
        'pdf_url', 'xml_url', 'sendmail', 'sendmailtome', 'emailed_at', 'seze', 'status',
    ];
    
    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i:s',
        'payment_due_date' => 'date',
        'sent_to_dian_at' => 'datetime',
        'emailed_at' => 'datetime',
        'sendmail' => 'boolean',
        'sendmailtome' => 'boolean',
        'line_extension_amount' => 'decimal:2',
        'tax_exclusive_amount' => 'decimal:2',
        'tax_inclusive_amount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'dian_response' => 'array',
    ];
    
    protected $appends = ['full_number', 'is_approved', 'is_rejected', 'is_sent'];
    
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
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }
    
    public function getFullNumberAttribute(): string
    {
        return ($this->prefix ?? '') . $this->number;
    }
    
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }
    
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }
    
    public function getIsSentAttribute(): bool
    {
        return in_array($this->status, ['sent', 'approved']);
    }
    
    public function calculateTotals(): void
    {
        $items = $this->items;
        $this->line_extension_amount = $items->sum('line_extension_amount');
        $this->tax_amount = $items->sum('tax_amount');
        $this->discount_amount = $items->sum('discount_amount');
        $this->tax_exclusive_amount = $this->line_extension_amount;
        $this->tax_inclusive_amount = $this->line_extension_amount + $this->tax_amount;
        $this->payable_amount = $this->tax_inclusive_amount - $this->discount_amount;
        $predominantTax = $items->groupBy('tax_percent')->map(fn($g) => $g->sum('tax_amount'))->sortDesc()->keys()->first();
        $this->tax_percent = $predominantTax ?? '19';
    }
    
    public function toApidianFormat(): array
    {
        return [
            'number' => (int) $this->number,
            'type_document_id' => $this->type_document_id,
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->time->format('H:i:s'),
            'resolution_number' => $this->resolution?->resolution_number,
            'prefix' => $this->prefix,
            'notes' => $this->notes,
            'sendmail' => $this->sendmail,
            'sendmailtome' => $this->sendmailtome,
            'customer' => [
                'identification_number' => $this->customer->document_number,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
                'email' => $this->customer->email,
                'type_document_identification_id' => $this->customer->document_type_id,
                'municipality_id' => $this->customer->city_id,
                'type_regime_id' => $this->customer->tax_regime_id,
            ],
            'payment_form' => [
                'payment_form_id' => $this->payment_form_id,
                'payment_method_id' => $this->payment_method_id,
                'payment_due_date' => $this->payment_due_date?->format('Y-m-d'),
                'duration_measure' => $this->duration_measure,
            ],
            'legal_monetary_totals' => [
                'line_extension_amount' => number_format($this->line_extension_amount, 2, '.', ''),
                'tax_exclusive_amount' => number_format($this->tax_exclusive_amount, 2, '.', ''),
                'tax_inclusive_amount' => number_format($this->tax_inclusive_amount, 2, '.', ''),
                'payable_amount' => number_format($this->payable_amount, 2, '.', ''),
            ],
            'tax_totals' => [[
                'tax_id' => 1,
                'tax_amount' => number_format($this->tax_amount, 2, '.', ''),
                'percent' => $this->tax_percent,
                'taxable_amount' => number_format($this->tax_exclusive_amount, 2, '.', ''),
            ]],
            'invoice_lines' => $this->items->map(function ($item) {
                return [
                    'unit_measure_id' => $item->unit_measure_id,
                    'invoiced_quantity' => number_format($item->invoiced_quantity, 2, '.', ''),
                    'line_extension_amount' => number_format($item->line_extension_amount, 2, '.', ''),
                    'free_of_charge_indicator' => $item->free_of_charge_indicator,
                    'description' => $item->description,
                    'code' => $item->code,
                    'type_item_identification_id' => $item->type_item_identification_id,
                    'price_amount' => number_format($item->price_amount, 2, '.', ''),
                    'base_quantity' => number_format($item->base_quantity, 2, '.', ''),
                    'tax_totals' => [[
                        'tax_id' => $item->tax_id,
                        'tax_amount' => number_format($item->tax_amount, 2, '.', ''),
                        'taxable_amount' => number_format($item->taxable_amount, 2, '.', ''),
                        'percent' => $item->tax_percent,
                    ]],
                ];
            })->toArray(),
        ];
    }
}
