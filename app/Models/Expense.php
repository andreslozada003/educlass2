<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_number',
        'user_id',
        'category_id',
        'subcategory_id',
        'description',
        'expense_type',
        'amount',
        'payment_status',
        'paid_amount',
        'payment_method',
        'payment_source',
        'reference_number',
        'receipt_number',
        'invoice_number',
        'receipt_image',
        'expense_date',
        'due_date',
        'paid_date',
        'supplier_id',
        'branch_name',
        'responsible_user_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'notes',
        'is_recurring',
        'recurring_period',
        'next_due_date',
        'recurring_source_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'expense_date' => 'date',
            'due_date' => 'date',
            'paid_date' => 'date',
            'approved_at' => 'datetime',
            'next_due_date' => 'date',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateExpenseNumber();
            }
            if (empty($expense->expense_date)) {
                $expense->expense_date = now();
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $prefix = 'GAS';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = $prefix . '-' . $date . '-' . $random;

        while (self::where('expense_number', $number)->exists()) {
            $random = strtoupper(Str::random(4));
            $number = $prefix . '-' . $date . '-' . $random;
        }

        return $number;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'subcategory_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recurringSource()
    {
        return $this->belongsTo(self::class, 'recurring_source_id');
    }

    public function generatedExpenses()
    {
        return $this->hasMany(self::class, 'recurring_source_id');
    }

    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopePendingPayment($query)
    {
        return $query->whereIn('payment_status', ['pending', 'partial', 'overdue']);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_recurring', true);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'check' => 'Cheque',
            default => 'Otro',
        };
    }

    public function getPaymentSourceLabelAttribute(): string
    {
        return match ($this->payment_source) {
            'caja_general' => 'Caja general',
            'banco' => 'Banco',
            'transferencia' => 'Transferencia',
            'nequi_daviplata' => 'Nequi / Daviplata',
            'tarjeta' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            'credito_proveedor' => 'Credito proveedor',
            default => $this->payment_source ?: 'No definido',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'partial' => 'Parcial',
            'cancelled' => 'Anulado',
            'overdue' => 'Vencido',
            default => $this->payment_status,
        };
    }

    public function getApprovalStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            default => 'No requerido',
        };
    }

    public function getExpenseTypeLabelAttribute(): string
    {
        return match ($this->expense_type) {
            'fixed' => 'Fijo',
            default => 'Variable',
        };
    }

    public function getReceiptImageUrlAttribute(): ?string
    {
        return $this->receipt_image ? asset('storage/' . $this->receipt_image) : null;
    }

    public function getPendingBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
