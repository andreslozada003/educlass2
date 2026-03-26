<?php

namespace App\Models;

use App\Support\MoraSupport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reparacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reparaciones';

    protected $fillable = [
        'orden',
        'cliente_id',
        'user_id',
        'tecnico_id',
        'dispositivo_tipo',
        'dispositivo_marca',
        'dispositivo_modelo',
        'dispositivo_color',
        'dispositivo_imei',
        'dispositivo_serial',
        'dispositivo_contrasena',
        'problema_reportado',
        'diagnostico',
        'solucion',
        'estado',
        'costo_estimado',
        'costo_final',
        'adelanto',
        'ganancia',
        'fecha_recepcion',
        'fecha_estimada_entrega',
        'fecha_entrega',
        'fecha_inicio_mora',
        'notas_tecnico',
        'notas_cliente',
        'mora_observaciones',
        'garantia_dias',
        'accesorios_incluidos',
        'condiciones_previas',
        'foto_antes_1',
        'foto_antes_2',
        'foto_antes_3',
        'foto_despues_1',
        'foto_despues_2',
        'foto_despues_3',
        'notificado_listo',
        'fecha_notificacion',
        'ultima_notificacion_mora_at',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
        'fecha_estimada_entrega' => 'datetime',
        'fecha_entrega' => 'datetime',
        'fecha_inicio_mora' => 'date',
        'fecha_notificacion' => 'datetime',
        'ultima_notificacion_mora_at' => 'datetime',
        'costo_estimado' => 'decimal:2',
        'costo_final' => 'decimal:2',
        'adelanto' => 'decimal:2',
        'ganancia' => 'decimal:2',
        'notificado_listo' => 'boolean',
    ];

    // Estados posibles
    const ESTADOS = [
        'recibido' => 'Recibido',
        'en_diagnostico' => 'En Diagnóstico',
        'espera_repuesto' => 'Espera de Repuesto',
        'en_reparacion' => 'En Reparación',
        'reparado' => 'Reparado',
        'listo' => 'Listo para Entregar',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    // Colores para los estados
    const ESTADO_COLORES = [
        'recibido' => 'gray',
        'en_diagnostico' => 'yellow',
        'espera_repuesto' => 'orange',
        'en_reparacion' => 'blue',
        'reparado' => 'indigo',
        'listo' => 'green',
        'entregado' => 'emerald',
        'cancelado' => 'red',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reparacion) {
            if (empty($reparacion->orden)) {
                $reparacion->orden = 'R-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
            if (empty($reparacion->fecha_recepcion)) {
                $reparacion->fecha_recepcion = now();
            }
        });

        static::updated(function ($reparacion) {
            // Si cambió a estado "listo", crear notificación
            if ($reparacion->isDirty('estado') && $reparacion->estado === 'listo' && !$reparacion->notificado_listo) {
                Notificacion::create([
                    'titulo' => 'Reparación lista',
                    'mensaje' => "Orden #{$reparacion->orden} está lista para entregar",
                    'tipo' => 'success',
                    'referencia_tipo' => self::class,
                    'referencia_id' => $reparacion->id,
                    'icono' => 'wrench',
                    'color' => 'green',
                ]);
            }
        });
    }

    /**
     * Scope para reparaciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->whereNotIn('estado', ['entregado', 'cancelado']);
    }

    /**
     * Scope para reparaciones listas
     */
    public function scopeListas($query)
    {
        return $query->where('estado', 'listo');
    }

    /**
     * Scope para reparaciones de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_recepcion', today());
    }

    /**
     * Scope por técnico
     */
    public function scopePorTecnico($query, $tecnicoId)
    {
        return $query->where('tecnico_id', $tecnicoId);
    }

    /**
     * Relación con cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación con usuario que recibió
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con técnico
     */
    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    /**
     * Relación con historial
     */
    public function historial()
    {
        return $this->hasMany(ReparacionHistorial::class);
    }

    public function moraAbonos(): MorphMany
    {
        return $this->morphMany(MoraAbono::class, 'mora_abonable');
    }

    public function ultimoMoraAbono(): MorphOne
    {
        return $this->morphOne(MoraAbono::class, 'mora_abonable')->latestOfMany('fecha_pago');
    }

    public function moraNotificaciones(): MorphMany
    {
        return $this->morphMany(MoraNotificacion::class, 'mora_notificable');
    }

    public function ultimaMoraNotificacion(): MorphOne
    {
        return $this->morphOne(MoraNotificacion::class, 'mora_notificable')->latestOfMany('fecha_envio');
    }

    /**
     * Cambiar estado
     */
    public function cambiarEstado(string $nuevoEstado, ?string $comentario = null): bool
    {
        if (!array_key_exists($nuevoEstado, self::ESTADOS)) {
            return false;
        }

        $estadoAnterior = $this->estado;
        
        $this->estado = $nuevoEstado;
        
        if ($nuevoEstado === 'entregado') {
            $this->fecha_entrega = now();
        }
        
        $this->save();

        // Registrar en historial
        ReparacionHistorial::create([
            'reparacion_id' => $this->id,
            'user_id' => auth()->id(),
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado,
            'comentario' => $comentario,
        ]);

        return true;
    }

    /**
     * Marcar como notificado
     */
    public function marcarNotificado(): void
    {
        $this->notificado_listo = true;
        $this->fecha_notificacion = now();
        $this->save();
    }

    /**
     * Calcular ganancia
     */
    public function calcularGanancia(): void
    {
        // Aquí se puede agregar lógica para calcular ganancia basada en repuestos usados
        $this->ganancia = $this->costo_final - $this->costo_estimado;
        $this->save();
    }

    /**
     * Nombre del estado
     */
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    /**
     * Alias para compatibilidad con vistas antiguas.
     */
    public function getNumeroOrdenAttribute(): string
    {
        return $this->orden;
    }

    /**
     * Alias para compatibilidad con vistas antiguas.
     */
    public function getMarcaAttribute(): ?string
    {
        return $this->dispositivo_marca;
    }

    /**
     * Alias para compatibilidad con vistas antiguas.
     */
    public function getModeloAttribute(): ?string
    {
        return $this->dispositivo_modelo;
    }

    /**
     * Alias para compatibilidad con vistas antiguas.
     */
    public function getTipoDispositivoAttribute(): ?string
    {
        return $this->dispositivo_tipo;
    }

    /**
     * Color del estado
     */
    public function getEstadoColorAttribute(): string
    {
        return self::ESTADO_COLORES[$this->estado] ?? 'gray';
    }

    /**
     * Saldo pendiente
     */
    public function getSaldoPendienteAttribute(): float
    {
        return max(0, $this->costo_final - $this->adelanto);
    }

    public function getValorOperacionMoraAttribute(): float
    {
        return (float) ($this->costo_final > 0 ? $this->costo_final : $this->costo_estimado);
    }

    public function getSaldoPendienteMoraAttribute(): float
    {
        return max(0, $this->valor_operacion_mora - (float) $this->adelanto);
    }

    public function getDiasEnMoraAttribute(): int
    {
        return MoraSupport::daysInMora($this->fecha_inicio_mora, $this->saldo_pendiente_mora);
    }

    public function getMoraSemaforoAttribute(): string
    {
        return MoraSupport::resolveSemaphore($this->fecha_inicio_mora, $this->saldo_pendiente_mora);
    }

    public function getMoraEtapaAttribute(): string
    {
        return MoraSupport::resolveStage($this->fecha_inicio_mora, $this->saldo_pendiente_mora);
    }

    /**
     * ¿Está pagada?
     */
    public function getEstaPagadaAttribute(): bool
    {
        return $this->adelanto >= $this->costo_final;
    }

    /**
     * Días en taller
     */
    public function getDiasEnTallerAttribute(): int
    {
        $fechaFin = $this->fecha_entrega ?? now();
        return $this->fecha_recepcion->diffInDays($fechaFin);
    }

    /**
     * Info del dispositivo
     */
    public function getDispositivoInfoAttribute(): string
    {
        return "{$this->dispositivo_marca} {$this->dispositivo_modelo}";
    }

    public function sincronizarAdelantoDesdeAbonos(): void
    {
        $this->adelanto = (float) $this->moraAbonos()->sum('monto');
        $this->save();
    }
}
