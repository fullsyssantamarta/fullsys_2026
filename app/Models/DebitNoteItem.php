<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'debit_note_id',
        'product_id',
        'code',
        'description',
        'type_item_identification_id',
        'unit_measure_id',
        'invoiced_quantity',
        'base_quantity',
        'price_amount',
        'line_extension_amount',
        'discount_amount',
        'discount_percent',
        'tax_id',
        'tax_amount',
        'taxable_amount',
        'tax_percent',
        'free_of_charge_indicator',
        'sort_order',
    ];

    protected $casts = [
        'invoiced_quantity' => 'decimal:6',
        'base_quantity' => 'decimal:6',
        'price_amount' => 'decimal:6',
        'line_extension_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'free_of_charge_indicator' => 'boolean',
    ];

    // Relaciones
    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Métodos de negocio
    public function calculateTotals(): void
    {
        // Subtotal = cantidad × precio
        $subtotal = $this->invoiced_quantity * $this->price_amount;

        // Aplicar descuento
        $discount = ($this->discount_percent > 0)
            ? $subtotal * ($this->discount_percent / 100)
            : $this->discount_amount;

        $this->discount_amount = $discount;
        $this->line_extension_amount = $subtotal - $discount;

        // Base gravable = subtotal - descuento
        $this->taxable_amount = $this->line_extension_amount;

        // Calcular IVA
        $this->tax_amount = $this->taxable_amount * ($this->tax_percent / 100);

        $this->save();
    }

    public function populateFromProduct(Product $product): void
    {
        $this->code = $product->code;
        $this->description = $product->name;
        $this->price_amount = $product->price;
        $this->tax_percent = $product->tax_rate;
        $this->save();
    }
}
