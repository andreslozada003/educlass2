<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'notas',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detalle) {
            $detalle->subtotal = ($detalle->precio_unitario * $detalle->cantidad) - $detalle->descuento;
        });

        static::created(function ($detalle) {
            // Disminuir stock
            $detalle->producto->disminuirStock(
                $detalle->cantidad,
                'Venta #' . $detalle->venta->folio,
                $detalle->venta
            );
        });
    }

    /**
     * Relación con venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Relación con producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Ganancia del detalle
     */
    public function getGananciaAttribute(): float
    {
        return ($this->precio_unitario - $this->producto->precio_compra) * $this->cantidad;
    }
}
