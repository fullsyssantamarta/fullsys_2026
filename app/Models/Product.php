<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'category_id',
        'unit_of_measure',
        'price',
        'cost',
        'tax_percentage',
        'stock',
        'min_stock',
        'max_stock',
        'status',
        'image',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
    ];
    
    /**
     * Get the category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Get low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }
}
