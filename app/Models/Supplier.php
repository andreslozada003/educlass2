<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'contact_name',
        'nit_rut',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'city',
        'frequent_category_id',
        'website',
        'notes',
        'total_purchases',
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
            'total_purchases' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the inventory movements of this supplier.
     */
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Get the expenses of this supplier.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function frequentCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'frequent_category_id');
    }

    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search suppliers.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('contact_name', 'like', "%{$search}%")
              ->orWhere('nit_rut', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Update total purchases.
     */
    public function updateTotalPurchases(): void
    {
        $this->total_purchases = $this->inventoryMovements()
            ->where('type', 'entry')
            ->sum('total_cost');
        $this->save();
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
