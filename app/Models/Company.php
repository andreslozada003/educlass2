<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'legal_name',
        'nit_rut',
        'logo',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        'currency',
        'currency_symbol',
        'tax_rate',
        'tax_name',
        'invoice_footer',
        'terms_conditions',
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
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get logo URL.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/default-logo.png');
    }

    /**
     * Format currency.
     */
    public function formatCurrency(float $amount): string
    {
        return money($amount, 0, $this->currency_symbol ?: '$');
    }

    /**
     * Calculate tax amount.
     */
    public function calculateTax(float $amount): float
    {
        return $amount * ($this->tax_rate / 100);
    }

    /**
     * Get the first active company.
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }
}
