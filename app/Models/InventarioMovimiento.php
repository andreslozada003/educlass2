<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    use HasFactory;

    protected $table = 'inventario_movimientos';

    protected $fillable = [
        'producto_id',
        'user_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'referencia_tipo',
        'referencia_id',
        'motivo',
        'costo_unitario',
        'proveedor',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
        'costo_unitario' => 'decimal:2',
    ];

    // Tipos de movimiento
    const TIPOS = [
        'entrada' => 'Entrada',
        'salida' => 'Salida',
        'ajuste' => 'Ajuste',
        'devolucion' => 'Devolución',
        'venta' => 'Venta',
        'compra' => 'Compra',
    ];

    // Colores para los tipos
    const TIPO_COLORES = [
        'entrada' => 'green',
        'salida' => 'red',
        'ajuste' => 'yellow',
        'devolucion' => 'blue',
        'venta' => 'purple',
        'compra' => 'indigo',
    ];

    /**
     * Relación con producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nombre del tipo
     */
    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Color del tipo
     */
    public function getTipoColorAttribute(): string
    {
        return self::TIPO_COLORES[$this->tipo] ?? 'gray';
    }

    /**
     * Diferencia de stock
     */
    public function getDiferenciaAttribute(): int
    {
        return $this->stock_nuevo - $this->stock_anterior;
    }

    /**
     * Scope por tipo
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para entradas
     */
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    /**
     * Scope para salidas
     */
    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }
}
