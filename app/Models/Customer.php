<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'city',
        'birth_date',
        'notes',
        'credit_limit',
        'balance',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'credit_limit' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the sales of this customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the repairs of this customer.
     */
    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search customers.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('document_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Get total purchases amount.
     */
    public function getTotalPurchasesAttribute(): float
    {
        return $this->sales()->where('status', 'completed')->sum('total');
    }

    /**
     * Get total repairs count.
     */
    public function getTotalRepairsAttribute(): int
    {
        return $this->repairs()->count();
    }

    /**
     * Get last purchase date.
     */
    public function getLastPurchaseDateAttribute(): ?string
    {
        $lastSale = $this->sales()->where('status', 'completed')->latest()->first();
        return $lastSale ? $lastSale->created_at->format('Y-m-d') : null;
    }

    /**
     * Check if customer has credit available.
     */
    public function hasCreditAvailable(float $amount): bool
    {
        return ($this->credit_limit - $this->balance) >= $amount;
    }

    /**
     * Add to balance.
     */
    public function addBalance(float $amount): void
    {
        $this->balance += $amount;
        $this->save();
    }

    /**
     * Subtract from balance.
     */
    public function subtractBalance(float $amount): void
    {
        $this->balance -= $amount;
        $this->save();
    }
}
