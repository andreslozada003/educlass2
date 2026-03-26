<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'document_number',
        'supplier_id',
        'unit_cost',
        'total_cost',
        'reason',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Get the product of this movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier of this movement.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to get entries.
     */
    public function scopeEntries($query)
    {
        return $query->where('type', 'entry');
    }

    /**
     * Scope a query to get exits.
     */
    public function scopeExits($query)
    {
        return $query->where('type', 'exit');
    }

    /**
     * Scope a query to get adjustments.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'entry' => 'Entrada',
            'exit' => 'Salida',
            'adjustment' => 'Ajuste',
            'return' => 'Devolución',
            'transfer' => 'Transferencia',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Get type color.
     */
    public function getTypeColorAttribute(): string
    {
        $colors = [
            'entry' => 'success',
            'exit' => 'danger',
            'adjustment' => 'warning',
            'return' => 'info',
            'transfer' => 'primary',
        ];

        return $colors[$this->type] ?? 'secondary';
    }

    /**
     * Calculate total cost before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movement) {
            if ($movement->unit_cost && $movement->quantity) {
                $movement->total_cost = $movement->unit_cost * $movement->quantity;
            }
        });
    }
}
