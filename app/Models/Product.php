<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'model',
        'imei',
        'purchase_price',
        'sale_price',
        'wholesale_price',
        'stock_quantity',
        'min_stock',
        'max_stock',
        'unit',
        'image',
        'gallery',
        'specifications',
        'has_warranty',
        'warranty_days',
        'is_service',
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
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'gallery' => 'array',
            'specifications' => 'array',
            'has_warranty' => 'boolean',
            'warranty_days' => 'integer',
            'is_service' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = self::generateSku();
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Generate unique SKU.
     */
    public static function generateSku(): string
    {
        $prefix = 'PROD';
        $random = strtoupper(Str::random(6));
        $sku = $prefix . '-' . $random;
        
        while (self::where('sku', $sku)->exists()) {
            $random = strtoupper(Str::random(6));
            $sku = $prefix . '-' . $random;
        }
        
        return $sku;
    }

    /**
     * Get the category of this product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand of this product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the inventory movements of this product.
     */
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Get the sale items of this product.
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include services.
     */
    public function scopeServices($query)
    {
        return $query->where('is_service', true);
    }

    /**
     * Scope a query to only include physical products.
     */
    public function scopePhysical($query)
    {
        return $query->where('is_service', false);
    }

    /**
     * Scope a query to only include products with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock')
                     ->where('is_service', false);
    }

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to search products.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Check if product has low stock.
     */
    public function isLowStock(): bool
    {
        return !$this->is_service && $this->stock_quantity <= $this->min_stock;
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->is_service || $this->stock_quantity > 0;
    }

    /**
     * Calculate profit margin.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->purchase_price <= 0) {
            return 0;
        }
        return (($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    /**
     * Calculate profit per unit.
     */
    public function getProfitPerUnitAttribute(): float
    {
        return $this->sale_price - $this->purchase_price;
    }

    /**
     * Get image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/default-product.png');
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(int $quantity, string $type, ?string $reason = null): InventoryMovement
    {
        $stockBefore = $this->stock_quantity;
        
        if ($type === 'entry') {
            $this->stock_quantity += $quantity;
        } elseif ($type === 'exit') {
            $this->stock_quantity -= $quantity;
        } elseif ($type === 'adjustment') {
            $this->stock_quantity = $quantity;
        }
        
        $this->save();

        return InventoryMovement::create([
            'product_id' => $this->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $this->stock_quantity,
            'reason' => $reason,
        ]);
    }
}
