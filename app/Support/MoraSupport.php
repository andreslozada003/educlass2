<?php

namespace App\Support;

use App\Models\Reparacion;
use App\Models\Venta;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class MoraSupport
{
    public const ALERT_MILESTONES = [7, 15, 30];

    public static function resolveSemaphore(?CarbonInterface $fechaInicioMora, float $saldoPendiente): string
    {
        if ($saldoPendiente <= 0) {
            return 'al_dia';
        }

        if (! $fechaInicioMora || $fechaInicioMora->isFuture()) {
            return 'sin_fecha';
        }

        $dias = self::daysInMora($fechaInicioMora, $saldoPendiente);

        if ($dias <= 7) {
            return 'verde';
        }

        if ($dias <= 29) {
            return 'amarillo';
        }

        return 'rojo';
    }

    public static function resolveStage(?CarbonInterface $fechaInicioMora, float $saldoPendiente): string
    {
        $semaforo = self::resolveSemaphore($fechaInicioMora, $saldoPendiente);

        return match ($semaforo) {
            'al_dia' => 'Al dia',
            'sin_fecha' => 'Pendiente de activar',
            'verde' => 'Mora temprana',
            'amarillo' => 'En seguimiento',
            'rojo' => 'Mora critica',
            default => 'En seguimiento',
        };
    }

    public static function daysInMora(?CarbonInterface $fechaInicioMora, float $saldoPendiente, ?CarbonInterface $today = null): int
    {
        if ($saldoPendiente <= 0 || ! $fechaInicioMora) {
            return 0;
        }

        $today ??= now();
        $fechaInicio = Carbon::parse($fechaInicioMora)->startOfDay();
        $fechaActual = Carbon::parse($today)->startOfDay();

        if ($fechaInicio->isFuture()) {
            return 0;
        }

        return $fechaInicio->diffInDays($fechaActual);
    }

    public static function priority(string $semaforo): int
    {
        return match ($semaforo) {
            'rojo' => 4,
            'sin_fecha' => 3,
            'amarillo' => 2,
            'verde' => 1,
            default => 0,
        };
    }

    public static function palette(string $semaforo): array
    {
        $map = [
            'al_dia' => [
                'label' => 'Al dia',
                'badge' => 'bg-emerald-100 text-emerald-700 ring-1 ring-inset ring-emerald-200',
                'dot' => 'bg-emerald-500',
                'surface' => 'bg-emerald-50 border-emerald-200',
                'calendar' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ],
            'sin_fecha' => [
                'label' => 'Sin fecha',
                'badge' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
                'dot' => 'bg-slate-500',
                'surface' => 'bg-slate-50 border-slate-200',
                'calendar' => 'bg-slate-100 text-slate-700 border-slate-200',
            ],
            'verde' => [
                'label' => 'Verde',
                'badge' => 'bg-lime-100 text-lime-800 ring-1 ring-inset ring-lime-200',
                'dot' => 'bg-lime-500',
                'surface' => 'bg-lime-50 border-lime-200',
                'calendar' => 'bg-lime-100 text-lime-800 border-lime-200',
            ],
            'amarillo' => [
                'label' => 'Amarillo',
                'badge' => 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-200',
                'dot' => 'bg-amber-500',
                'surface' => 'bg-amber-50 border-amber-200',
                'calendar' => 'bg-amber-100 text-amber-800 border-amber-200',
            ],
            'rojo' => [
                'label' => 'Rojo',
                'badge' => 'bg-rose-100 text-rose-800 ring-1 ring-inset ring-rose-200',
                'dot' => 'bg-rose-500',
                'surface' => 'bg-rose-50 border-rose-200',
                'calendar' => 'bg-rose-100 text-rose-800 border-rose-200',
            ],
        ];

        return $map[$semaforo] ?? $map['sin_fecha'];
    }

    public static function milestoneSchedule(?CarbonInterface $fechaInicioMora, float $saldoPendiente): array
    {
        $baseDate = $fechaInicioMora ? Carbon::parse($fechaInicioMora)->startOfDay() : null;
        $diasActuales = self::daysInMora($fechaInicioMora, $saldoPendiente);

        return collect(self::ALERT_MILESTONES)
            ->map(function (int $day) use ($baseDate, $diasActuales) {
                return [
                    'day' => $day,
                    'label' => match ($day) {
                        7 => 'Primer aviso',
                        15 => 'Segundo aviso',
                        30 => 'Aviso critico',
                        default => "Dia {$day}",
                    },
                    'date' => $baseDate?->copy()->addDays($day),
                    'reached' => $baseDate ? $diasActuales >= $day : false,
                ];
            })
            ->all();
    }

    public static function buildCalendar(CarbonInterface $visibleMonth, int $months, ?CarbonInterface $fechaInicioMora, float $saldoPendiente): array
    {
        $calendar = [];
        $cursor = Carbon::parse($visibleMonth)->startOfMonth();
        $months = max(1, min(3, $months));

        for ($index = 0; $index < $months; $index++) {
            $month = $cursor->copy()->addMonths($index);
            $firstDay = $month->copy()->startOfMonth();
            $lastDay = $month->copy()->endOfMonth();
            $days = [];

            for ($blank = 1; $blank < $firstDay->dayOfWeekIso; $blank++) {
                $days[] = ['blank' => true];
            }

            for ($date = $firstDay->copy(); $date->lte($lastDay); $date->addDay()) {
                $daySemaphore = self::resolveDaySemaphore($date, $fechaInicioMora, $saldoPendiente);

                $days[] = [
                    'blank' => false,
                    'date' => $date->copy(),
                    'is_today' => $date->isToday(),
                    'is_mora_start' => $fechaInicioMora ? $date->isSameDay($fechaInicioMora) : false,
                    'semaphore' => $daySemaphore,
                    'palette' => self::palette($daySemaphore),
                ];
            }

            $calendar[] = [
                'key' => $month->format('Y-m'),
                'title' => ucfirst($month->translatedFormat('F Y')),
                'days' => $days,
            ];
        }

        return $calendar;
    }

    public static function resolveDaySemaphore(CarbonInterface $date, ?CarbonInterface $fechaInicioMora, float $saldoPendiente): string
    {
        if ($saldoPendiente <= 0 || ! $fechaInicioMora) {
            return 'al_dia';
        }

        $fechaInicio = Carbon::parse($fechaInicioMora)->startOfDay();
        $dia = Carbon::parse($date)->startOfDay();

        if ($dia->lt($fechaInicio)) {
            return 'al_dia';
        }

        $dias = $fechaInicio->diffInDays($dia);

        if ($dias <= 7) {
            return 'verde';
        }

        if ($dias <= 29) {
            return 'amarillo';
        }

        return 'rojo';
    }

    public static function whatsappUrl(?string $telefono, string $mensaje): ?string
    {
        $cleanPhone = preg_replace('/\D+/', '', (string) $telefono);

        if ($cleanPhone === '') {
            return null;
        }

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($mensaje);
    }

    public static function notificationLevel(int $diasEnMora): string
    {
        return match (true) {
            $diasEnMora >= 30 => 'critica',
            $diasEnMora >= 15 => 'seguimiento',
            $diasEnMora >= 7 => 'recordatorio',
            default => 'preventiva',
        };
    }

    public static function notificationTemplateKey(string $tipo, int $diasEnMora): string
    {
        return sprintf('%s_mora_%s', $tipo, self::notificationLevel($diasEnMora));
    }

    public static function saleMessage(Venta $venta): string
    {
        $cliente = $venta->cliente?->nombre_completo ?: 'cliente';
        $saldo = money($venta->saldo_pendiente_mora);
        $dias = $venta->dias_en_mora;

        return "Hola, {$cliente}. Te informamos que presentas un saldo pendiente de {$saldo} por tu compra {$venta->folio}. Actualmente registras {$dias} dias de mora. Por favor comunicate con nosotros para regularizar tu pago.";
    }

    public static function repairMessage(Reparacion $reparacion): string
    {
        $cliente = $reparacion->cliente?->nombre_completo ?: 'cliente';
        $saldo = money($reparacion->saldo_pendiente_mora);
        $dias = $reparacion->dias_en_mora;

        return "Hola, {$cliente}. Te informamos que tu reparacion {$reparacion->orden} presenta un saldo pendiente de {$saldo} y {$dias} dias de mora. Por favor comunicate con nosotros para coordinar el pago y la entrega del equipo.";
    }
}
