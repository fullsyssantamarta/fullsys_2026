<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'invoice_id',
        'product_id',
        'code',
        'description',
        'notes',
        'unit_measure_id',
        'invoiced_quantity',
        'base_quantity',
        'price_amount',
        'line_extension_amount',
        'discount_amount',
        'discount_percent',
        'charge_amount',
        'tax_id',
        'tax_amount',
        'taxable_amount',
        'tax_percent',
        'type_item_identification_id',
        'free_of_charge_indicator',
        'sort_order',
    ];
    
    protected $casts = [
        'invoiced_quantity' => 'decimal:4',
        'base_quantity' => 'decimal:4',
        'price_amount' => 'decimal:2',
        'line_extension_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'free_of_charge_indicator' => 'boolean',
    ];
    
    // Relaciones
    
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    // Métodos de negocio
    
    /**
     * Calcular totales del ítem automáticamente
     */
    public function calculateTotals(): void
    {
        // Subtotal = Cantidad × Precio
        $this->line_extension_amount = $this->invoiced_quantity * $this->price_amount;
        
        // Base gravable = Subtotal - Descuento + Cargos
        $this->taxable_amount = $this->line_extension_amount - $this->discount_amount + $this->charge_amount;
        
        // Impuesto = Base gravable × Porcentaje IVA
        $taxPercent = floatval($this->tax_percent ?? 19) / 100;
        $this->tax_amount = $this->taxable_amount * $taxPercent;
    }
    
    /**
     * Calcular desde el producto
     */
    public function populateFromProduct(Product $product, float $quantity = 1): void
    {
        $this->product_id = $product->id;
        $this->code = $product->code;
        $this->description = $product->name;
        $this->price_amount = $product->price;
        $this->invoiced_quantity = $quantity;
        $this->base_quantity = 1;
        $this->unit_measure_id = 70; // Unidad
        $this->tax_percent = $product->tax_rate ?? '19.00';
        $this->type_item_identification_id = 4; // Estándar
        $this->free_of_charge_indicator = false;
        
        $this->calculateTotals();
    }
}
