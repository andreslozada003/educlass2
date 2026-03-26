<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Herramienta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'marca',
        'modelo',
        'ubicacion',
        'fecha_compra',
        'costo_compra',
        'cantidad',
        'cantidad_danada',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'costo_compra' => 'decimal:2',
        'cantidad' => 'integer',
        'cantidad_danada' => 'integer',
        'activo' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($herramienta) {
            if (empty($herramienta->codigo)) {
                $herramienta->codigo = 'HER-' . strtoupper(Str::random(8));
            }
        });
    }

    public function getCantidadDisponibleAttribute(): int
    {
        return max($this->cantidad - $this->cantidad_danada, 0);
    }

    public function getEstadoGeneralAttribute(): string
    {
        if (!$this->activo) {
            return 'Inactiva';
        }

        if ($this->cantidad_danada >= $this->cantidad) {
            return 'Dañada';
        }

        if ($this->cantidad_danada > 0) {
            return 'Con daños';
        }

        return 'Operativa';
    }

    public function getValorInventarioAttribute(): float
    {
        return $this->cantidad * $this->costo_compra;
    }
}
