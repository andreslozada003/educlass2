<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Repair extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'repair_code',
        'customer_id',
        'user_id',
        'technician_id',
        'device_type',
        'brand',
        'model',
        'imei',
        'serial_number',
        'color',
        'storage',
        'reported_issue',
        'diagnosis',
        'solution',
        'status',
        'priority',
        'estimated_cost',
        'parts_cost',
        'labor_cost',
        'total_cost',
        'advance_payment',
        'balance',
        'has_warranty',
        'warranty_days',
        'warranty_starts_at',
        'warranty_expires_at',
        'warranty_code',
        'received_at',
        'diagnosed_at',
        'repaired_at',
        'delivered_at',
        'estimated_delivery_date',
        'device_condition',
        'accessories_received',
        'notes',
        'customer_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'advance_payment' => 'decimal:2',
            'balance' => 'decimal:2',
            'has_warranty' => 'boolean',
            'warranty_days' => 'integer',
            'warranty_starts_at' => 'date',
            'warranty_expires_at' => 'date',
            'device_condition' => 'array',
            'received_at' => 'datetime',
            'diagnosed_at' => 'datetime',
            'repaired_at' => 'datetime',
            'delivered_at' => 'datetime',
            'estimated_delivery_date' => 'date',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($repair) {
            if (empty($repair->repair_code)) {
                $repair->repair_code = self::generateRepairCode();
            }
            if (empty($repair->received_at)) {
                $repair->received_at = now();
            }
        });

        static::updating(function ($repair) {
            // Calculate balance
            $repair->balance = $repair->total_cost - $repair->advance_payment;
            
            // Update timestamps based on status
            if ($repair->isDirty('status')) {
                switch ($repair->status) {
                    case 'diagnosing':
                        if (empty($repair->diagnosed_at)) {
                            $repair->diagnosed_at = now();
                        }
                        break;
                    case 'repaired':
                        if (empty($repair->repaired_at)) {
                            $repair->repaired_at = now();
                        }
                        break;
                    case 'delivered':
                        if (empty($repair->delivered_at)) {
                            $repair->delivered_at = now();
                        }
                        // Start warranty if applicable
                        if ($repair->has_warranty && empty($repair->warranty_starts_at)) {
                            $repair->warranty_starts_at = now();
                            $repair->warranty_expires_at = now()->addDays($repair->warranty_days);
                            $repair->warranty_code = self::generateWarrantyCode();
                        }
                        break;
                }
            }
        });
    }

    /**
     * Generate unique repair code.
     */
    public static function generateRepairCode(): string
    {
        $prefix = 'REP';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $code = $prefix . '-' . $date . '-' . $random;
        
        while (self::where('repair_code', $code)->exists()) {
            $random = strtoupper(Str::random(4));
            $code = $prefix . '-' . $date . '-' . $random;
        }
        
        return $code;
    }

    /**
     * Generate unique warranty code.
     */
    public static function generateWarrantyCode(): string
    {
        $prefix = 'GAR-REP';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        $code = $prefix . '-' . $date . '-' . $random;
        
        while (self::where('warranty_code', $code)->exists()) {
            $random = strtoupper(Str::random(6));
            $code = $prefix . '-' . $date . '-' . $random;
        }
        
        return $code;
    }

    /**
     * Get the customer of this repair.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this repair.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the technician assigned to this repair.
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get pending repairs.
     */
    public function scopePending($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }

    /**
     * Scope a query to get repairs ready for delivery.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Scope a query to get repairs by priority.
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to get repairs assigned to a technician.
     */
    public function scopeAssignedTo($query, int $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Scope a query to get overdue repairs.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled'])
                     ->whereDate('estimated_delivery_date', '<', today());
    }

    /**
     * Scope a query to search repairs.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('repair_code', 'like', "%{$search}%")
              ->orWhere('imei', 'like', "%{$search}%")
              ->orWhere('serial_number', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%");
        });
    }

    /**
     * Check if repair is pending.
     */
    public function isPending(): bool
    {
        return !in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * Check if repair is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if warranty is valid.
     */
    public function isWarrantyValid(): bool
    {
        if (!$this->has_warranty || empty($this->warranty_expires_at)) {
            return false;
        }
        return now()->lessThanOrEqualTo($this->warranty_expires_at);
    }

    /**
     * Calculate total cost.
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->parts_cost + $this->labor_cost;
        $this->balance = $this->total_cost - $this->advance_payment;
        $this->save();
    }

    /**
     * Add advance payment.
     */
    public function addAdvancePayment(float $amount): void
    {
        $this->advance_payment += $amount;
        $this->balance = $this->total_cost - $this->advance_payment;
        $this->save();
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'received' => 'Recibido',
            'diagnosing' => 'Diagnosticando',
            'waiting_parts' => 'Esperando repuestos',
            'in_repair' => 'En reparación',
            'repaired' => 'Reparado',
            'ready' => 'Listo para entregar',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'received' => 'info',
            'diagnosing' => 'primary',
            'waiting_parts' => 'warning',
            'in_repair' => 'warning',
            'repaired' => 'success',
            'ready' => 'success',
            'delivered' => 'secondary',
            'cancelled' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            'low' => 'Baja',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Get priority color.
     */
    public function getPriorityColorAttribute(): string
    {
        $colors = [
            'low' => 'success',
            'normal' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    /**
     * Get device type label.
     */
    public function getDeviceTypeLabelAttribute(): string
    {
        $labels = [
            'iphone' => 'iPhone',
            'android' => 'Android',
            'tablet' => 'Tablet',
            'other' => 'Otro',
        ];

        return $labels[$this->device_type] ?? $this->device_type;
    }

    /**
     * Get warranty status.
     */
    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->has_warranty) {
            return 'Sin garantía';
        }
        
        if (empty($this->warranty_expires_at)) {
            return 'Garantía no iniciada';
        }
        
        if ($this->isWarrantyValid()) {
            $daysLeft = now()->diffInDays($this->warranty_expires_at);
            return "Válida ({$daysLeft} días restantes)";
        }
        
        return 'Vencida';
    }
}
