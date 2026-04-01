<?php

namespace App\Models;

use App\Support\MoraSupport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'folio',
        'cliente_id',
        'user_id',
        'fecha_venta',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'monto_pagado',
        'metodo_pago',
        'pagado_con',
        'cambio',
        'notas',
        'estado',
        'comprobante',
        'fecha_inicio_mora',
        'fecha_compromiso_pago',
        'numero_cuotas',
        'plazo_acordado_dias',
        'mora_observaciones',
        'ultima_notificacion_mora_at',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'pagado_con' => 'decimal:2',
        'cambio' => 'decimal:2',
        'fecha_inicio_mora' => 'date',
        'fecha_compromiso_pago' => 'date',
        'ultima_notificacion_mora_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venta) {
            if (empty($venta->folio)) {
                $venta->folio = 'V-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
            if (empty($venta->fecha_venta)) {
                $venta->fecha_venta = now();
            }
        });

        static::created(function ($venta) {
            Notificacion::create([
                'titulo' => 'Nueva venta registrada',
                'mensaje' => "Venta #{$venta->folio} por " . money($venta->total),
                'tipo' => 'success',
                'referencia_tipo' => self::class,
                'referencia_id' => $venta->id,
                'icono' => 'shopping-cart',
                'color' => 'green',
            ]);
        });
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_venta', today());
    }

    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('fecha_venta', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeEsteMes($query)
    {
        return $query->whereMonth('fecha_venta', now()->month)
            ->whereYear('fecha_venta', now()->year);
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function facturaElectronica()
    {
        return $this->hasOne(FacturaElectronica::class);
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

    public function calcularTotales(): void
    {
        $this->subtotal = $this->detalles->sum('subtotal');
        $this->impuestos = $this->subtotal * 0.16;
        $this->total = $this->subtotal + $this->impuestos - $this->descuento;
        $this->save();
    }

    public function calcularCambio(): void
    {
        $this->cambio = max(0, $this->pagado_con - $this->total);
        $this->save();
    }

    public function cancelar(string $motivo = null): bool
    {
        if ($this->estado === 'cancelada') {
            return false;
        }

        foreach ($this->detalles as $detalle) {
            $detalle->producto->aumentarStock(
                $detalle->cantidad,
                'CancelaciÃ³n de venta #' . $this->folio,
                $this
            );
        }

        $this->estado = 'cancelada';
        $this->notas = $motivo ? $this->notas . "\nCancelado: {$motivo}" : $this->notas;
        $this->save();

        return true;
    }

    public function getGananciaAttribute(): float
    {
        return $this->detalles->sum(function ($detalle) {
            return ($detalle->precio_unitario - $detalle->producto->precio_compra) * $detalle->cantidad;
        });
    }

    public function getTotalArticulosAttribute(): int
    {
        return $this->detalles->sum('cantidad');
    }

    public function getSaldoPendienteMoraAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->monto_pagado);
    }

    public function getDiasEnMoraAttribute(): int
    {
        return MoraSupport::saleDaysInMora($this);
    }

    public function getMoraSemaforoAttribute(): string
    {
        return MoraSupport::saleSemaphore($this);
    }

    public function getMoraEtapaAttribute(): string
    {
        return MoraSupport::saleStage($this);
    }

    public function getFechaMoraReferenciaAttribute()
    {
        return MoraSupport::saleCurrentDueDate($this);
    }

    public function getResumenMoraCreditoAttribute(): array
    {
        return MoraSupport::saleSummary($this);
    }

    public function getResumenEquipoMoraAttribute(): string
    {
        $productos = $this->relationLoaded('detalles')
            ? $this->detalles
            : $this->detalles()->with('producto')->get();

        $nombres = $productos
            ->pluck('producto.nombre')
            ->filter()
            ->unique()
            ->values();

        if ($nombres->isEmpty()) {
            return 'Sin detalle de equipo';
        }

        return $nombres->take(3)->implode(', ');
    }

    public function sincronizarMontoPagadoDesdeAbonos(): void
    {
        $this->monto_pagado = min((float) $this->total, (float) $this->moraAbonos()->sum('monto'));
        $this->estado = $this->saldo_pendiente_mora > 0 ? 'credito' : 'pagada';
        $this->save();
    }
}
