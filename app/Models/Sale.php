<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sale_number',
        'customer_id',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'paid_amount',
        'change_amount',
        'profit',
        'notes',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'profit' => 'decimal:2',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->sale_number)) {
                $sale->sale_number = self::generateSaleNumber();
            }
        });

        static::updating(function ($sale) {
            if ($sale->isDirty('status')) {
                if ($sale->status === 'completed' && empty($sale->completed_at)) {
                    $sale->completed_at = now();
                }
                if ($sale->status === 'cancelled' && empty($sale->cancelled_at)) {
                    $sale->cancelled_at = now();
                }
            }
        });
    }

    /**
     * Generate unique sale number.
     */
    public static function generateSaleNumber(): string
    {
        $prefix = 'VTA';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $saleNumber = $prefix . '-' . $date . '-' . $random;
        
        while (self::where('sale_number', $saleNumber)->exists()) {
            $random = strtoupper(Str::random(4));
            $saleNumber = $prefix . '-' . $date . '-' . $random;
        }
        
        return $saleNumber;
    }

    /**
     * Get the customer of this sale.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items of this sale.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the inventory movements related to this sale.
     */
    public function inventoryMovements()
    {
        return $this->morphMany(InventoryMovement::class, 'reference');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get completed sales.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to get pending sales.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to get today's sales.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to get this week's sales.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query to get this month's sales.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if sale is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if sale is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if sale is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if sale is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Calculate totals from items.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->discount_amount = $this->items->sum('discount');
        $this->total = $this->items->sum('total');
        $this->profit = $this->items->sum('profit');
        
        // Calculate tax if company has tax
        $company = Company::getActive();
        if ($company && $company->tax_rate > 0) {
            $this->tax_amount = $company->calculateTax($this->subtotal - $this->discount_amount);
            $this->total += $this->tax_amount;
        }
        
        $this->save();
    }

    /**
     * Complete the sale.
     */
    public function complete(): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Solo ventas pendientes pueden ser completadas.');
        }

        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        // Update inventory
        foreach ($this->items as $item) {
            $item->product->updateStock($item->quantity, 'exit', 'Venta #' . $this->sale_number);
        }

        // Update customer balance if credit
        if ($this->payment_method === 'credit' && $this->customer) {
            $this->customer->addBalance($this->total);
        }
    }

    /**
     * Cancel the sale.
     */
    public function cancel(string $reason): void
    {
        if ($this->status === 'completed') {
            // Return items to inventory
            foreach ($this->items as $item) {
                $item->product->updateStock($item->quantity, 'entry', 'Cancelación venta #' . $this->sale_number);
            }
        }

        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->save();

        // Update customer balance if credit
        if ($this->payment_method === 'credit' && $this->customer) {
            $this->customer->subtractBalance($this->total);
        }
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'refunded' => 'Reembolsada',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'info',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        $labels = [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'credit' => 'Crédito',
            'mixed' => 'Mixto',
        ];

        return $labels[$this->payment_method] ?? $this->payment_method;
    }
}
