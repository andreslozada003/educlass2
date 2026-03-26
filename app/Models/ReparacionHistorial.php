<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReparacionHistorial extends Model
{
    use HasFactory;

    protected $table = 'reparacion_historial';

    protected $fillable = [
        'reparacion_id',
        'user_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con reparación
     */
    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nombre del estado anterior
     */
    public function getEstadoAnteriorNombreAttribute(): string
    {
        return Reparacion::ESTADOS[$this->estado_anterior] ?? $this->estado_anterior;
    }

    /**
     * Nombre del estado nuevo
     */
    public function getEstadoNuevoNombreAttribute(): string
    {
        return Reparacion::ESTADOS[$this->estado_nuevo] ?? $this->estado_nuevo;
    }
}
