<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SaleItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'unit_cost',
        'discount',
        'subtotal',
        'total',
        'profit',
        'warranty_code',
        'warranty_expires_at',
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
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'profit' => 'decimal:2',
            'warranty_expires_at' => 'date',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calculate subtotal
            $item->subtotal = $item->unit_price * $item->quantity;
            
            // Calculate total with discount
            $item->total = $item->subtotal - $item->discount;
            
            // Calculate profit
            $item->profit = ($item->unit_price - $item->unit_cost) * $item->quantity - $item->discount;
            
            // Generate warranty code if product has warranty
            if (empty($item->warranty_code) && $item->product && $item->product->has_warranty) {
                $item->warranty_code = self::generateWarrantyCode();
                $item->warranty_expires_at = now()->addDays($item->product->warranty_days);
            }
        });
    }

    /**
     * Generate unique warranty code.
     */
    public static function generateWarrantyCode(): string
    {
        $prefix = 'GAR';
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
     * Get the sale of this item.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product of this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if warranty is valid.
     */
    public function isWarrantyValid(): bool
    {
        if (empty($this->warranty_expires_at)) {
            return false;
        }
        return now()->lessThanOrEqualTo($this->warranty_expires_at);
    }

    /**
     * Get warranty status.
     */
    public function getWarrantyStatusAttribute(): string
    {
        if (empty($this->warranty_expires_at)) {
            return 'Sin garantía';
        }
        
        if ($this->isWarrantyValid()) {
            $daysLeft = now()->diffInDays($this->warranty_expires_at);
            return "Válida ({$daysLeft} días restantes)";
        }
        
        return 'Vencida';
    }
}
