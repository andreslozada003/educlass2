<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'codigo_barras',
        'nombre',
        'slug',
        'descripcion',
        'categoria_id',
        'marca',
        'modelo',
        'imei',
        'precio_compra',
        'precio_venta',
        'stock',
        'stock_minimo',
        'unidad_medida',
        'clave_prod_serv_sat',
        'clave_unidad_sat',
        'unidad_sat',
        'objeto_impuesto',
        'imagen_principal',
        'imagenes_adicionales',
        'especificaciones_tecnicas',
        'proveedor',
        'garantia',
        'tipo',
        'es_servicio',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'es_servicio' => 'boolean',
        'activo' => 'boolean',
        'imagenes_adicionales' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($producto) {
            if (empty($producto->slug)) {
                $producto->slug = Str::slug($producto->nombre);
            }
            if (empty($producto->codigo)) {
                $producto->codigo = 'PROD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo')
            ->where('es_servicio', false);
    }

    public function scopeEnStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('codigo', 'like', "%{$search}%")
                ->orWhere('codigo_barras', 'like', "%{$search}%")
                ->orWhere('marca', 'like', "%{$search}%")
                ->orWhere('modelo', 'like', "%{$search}%")
                ->orWhere('imei', 'like', "%{$search}%");
        });
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class);
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->stock <= $this->stock_minimo && !$this->es_servicio;
    }

    public function getGananciaAttribute(): float
    {
        return $this->precio_venta - $this->precio_compra;
    }

    public function getMargenGananciaAttribute(): float
    {
        if ($this->precio_compra <= 0) {
            return 0;
        }

        return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
    }

    public function getValorInventarioAttribute(): float
    {
        return $this->stock * $this->precio_compra;
    }

    public function getImagenUrlAttribute(): string
    {
        return $this->imagen_principal
            ? asset('storage/' . $this->imagen_principal)
            : asset('images/producto-default.png');
    }

    public function disminuirStock(int $cantidad, string $motivo = null, $referencia = null): bool
    {
        if ($this->stock < $cantidad && !$this->es_servicio) {
            return false;
        }

        $stockAnterior = $this->stock;
        $this->stock -= $cantidad;
        $this->save();

        InventarioMovimiento::create([
            'producto_id' => $this->id,
            'user_id' => auth()->id(),
            'tipo' => 'salida',
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $this->stock,
            'motivo' => $motivo ?? 'Venta',
            'referencia_tipo' => $referencia ? get_class($referencia) : null,
            'referencia_id' => $referencia?->id,
        ]);

        return true;
    }

    public function aumentarStock(int $cantidad, string $motivo = null, $referencia = null): void
    {
        $stockAnterior = $this->stock;
        $this->stock += $cantidad;
        $this->save();

        InventarioMovimiento::create([
            'producto_id' => $this->id,
            'user_id' => auth()->id(),
            'tipo' => 'entrada',
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $this->stock,
            'motivo' => $motivo ?? 'Compra',
            'referencia_tipo' => $referencia ? get_class($referencia) : null,
            'referencia_id' => $referencia?->id,
        ]);
    }
}
