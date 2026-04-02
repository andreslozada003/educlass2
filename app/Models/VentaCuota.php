<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaCuota extends Model
{
    use HasFactory;

    protected $table = 'venta_cuotas';

    protected $fillable = [
        'venta_id',
        'numero_cuota',
        'fecha_vencimiento',
        'valor_cuota',
        'monto_pagado',
        'saldo_pendiente',
        'estado',
        'fecha_pago',
        'dias_mora',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'valor_cuota' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'dias_mora' => 'integer',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function getEstaPagadaAttribute(): bool
    {
        return (float) $this->saldo_pendiente <= 0.009 || $this->fecha_pago !== null || $this->estado === 'pagada';
    }

    public function getEstaEnMoraAttribute(): bool
    {
        return ! $this->esta_pagada && $this->fecha_vencimiento && now()->startOfDay()->gte($this->fecha_vencimiento->copy()->startOfDay());
    }

    public function getEstadoCalculadoAttribute(): string
    {
        if ($this->esta_pagada) {
            return 'pagada';
        }

        return $this->esta_en_mora ? 'vencida' : 'pendiente';
    }

    public function getDiasMoraActualAttribute(): int
    {
        if (! $this->esta_en_mora || ! $this->fecha_vencimiento) {
            return 0;
        }

        return $this->fecha_vencimiento->copy()->startOfDay()->diffInDays(Carbon::now()->startOfDay());
    }

    public function getEstadoEtiquetaAttribute(): string
    {
        return match ($this->estado_calculado) {
            'pagada' => 'Pagada',
            'vencida' => 'En mora',
            default => 'Pendiente',
        };
    }

    public function getEstadoBadgeClassesAttribute(): string
    {
        return match ($this->estado_calculado) {
            'pagada' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'vencida' => 'bg-rose-100 text-rose-700 ring-rose-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }
}
