<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resolution extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'prefix',
        'resolution_number',
        'from_number',
        'to_number',
        'current_number',
        'date_from',
        'date_to',
        'technical_key',
        'document_type',
        'status',
    ];
    
    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'from_number' => 'integer',
        'to_number' => 'integer',
        'current_number' => 'integer',
    ];
    
    /**
     * Get the next consecutive number
     */
    public function getNextNumber()
    {
        if ($this->current_number >= $this->to_number) {
            throw new \Exception('Resolution has reached maximum number');
        }
        
        $this->increment('current_number');
        
        return $this->current_number;
    }
    
    /**
     * Check if resolution is valid
     */
    public function isValid()
    {
        return $this->status === 'active' 
            && $this->current_number < $this->to_number
            && now()->between($this->date_from, $this->date_to);
    }
    
    /**
     * Scope for active resolutions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
