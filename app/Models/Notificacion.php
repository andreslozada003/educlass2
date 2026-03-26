<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensaje',
        'tipo',
        'referencia_tipo',
        'referencia_id',
        'leida',
        'leida_at',
        'icono',
        'color',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_at' => 'datetime',
    ];

    // Tipos de notificación
    const TIPOS = [
        'info' => 'Información',
        'success' => 'Éxito',
        'warning' => 'Advertencia',
        'error' => 'Error',
    ];

    // Colores para los tipos
    const TIPO_COLORES = [
        'info' => 'blue',
        'success' => 'green',
        'warning' => 'yellow',
        'error' => 'red',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notificacion) {
            if (empty($notificacion->color)) {
                $notificacion->color = self::TIPO_COLORES[$notificacion->tipo] ?? 'blue';
            }
        });
    }

    /**
     * Scope para notificaciones no leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    /**
     * Scope para notificaciones del usuario
     */
    public function scopeParaUsuario($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id');
        });
    }

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Marcar como leída
     */
    public function marcarLeida(): void
    {
        $this->leida = true;
        $this->leida_at = now();
        $this->save();
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public static function marcarTodasLeidas($userId = null): void
    {
        $query = self::noLeidas();
        
        if ($userId) {
            $query->paraUsuario($userId);
        }
        
        $query->update([
            'leida' => true,
            'leida_at' => now(),
        ]);
    }

    /**
     * URL de la notificación
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->referencia_tipo && $this->referencia_id) {
            $model = $this->referencia_tipo::find($this->referencia_id);
            if ($model) {
                // Generar URL según el tipo de referencia
                return match($this->referencia_tipo) {
                    Venta::class => route('ventas.show', $model),
                    Reparacion::class => route('reparaciones.show', $model),
                    Producto::class => route('productos.show', $model),
                    default => null,
                };
            }
        }
        return null;
    }

    /**
     * Icono por defecto según tipo
     */
    public function getIconoAttribute($value): string
    {
        return $value ?? match($this->tipo) {
            'info' => 'information-circle',
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'x-circle',
            default => 'bell',
        };
    }
}
