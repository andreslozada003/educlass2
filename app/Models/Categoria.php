<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'icono',
        'color',
        'parent_id',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Boot method para generar slug automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para categorías principales
     */
    public function scopePadres($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Relación con categoría padre
     */
    public function parent()
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    /**
     * Relación con subcategorías
     */
    public function children()
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    /**
     * Relación con productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Contar productos en la categoría
     */
    public function getProductosCountAttribute(): int
    {
        return $this->productos()->count();
    }
}
