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

        return self::milestoneScheduleFromReferenceDate($baseDate, $saldoPendiente);
    }

    public static function buildCalendar(CarbonInterface $visibleMonth, int $months, ?CarbonInterface $fechaInicioMora, float $saldoPendiente): array
    {
        $referenceDate = $fechaInicioMora ? Carbon::parse($fechaInicioMora)->startOfDay() : null;

        return self::buildCalendarFromReferenceDate(
            $visibleMonth,
            $months,
            $referenceDate,
            $saldoPendiente,
            $referenceDate ? [[
                'date' => $referenceDate,
                'label' => 'Inicio',
                'kind' => 'base',
            ]] : []
        );
    }

    public static function saleCreditBaseDate(Venta $venta): ?Carbon
    {
        if ($venta->fecha_inicio_mora) {
            return Carbon::parse($venta->fecha_inicio_mora)->startOfDay();
        }

        if ($venta->fecha_venta) {
            return Carbon::parse($venta->fecha_venta)->startOfDay();
        }

        return null;
    }

    public static function saleSummary(Venta $venta, ?CarbonInterface $today = null): array
    {
        $today = Carbon::parse($today ?? now())->startOfDay();
        $baseDate = self::saleCreditBaseDate($venta);
        $commitmentDate = $venta->fecha_compromiso_pago
            ? Carbon::parse($venta->fecha_compromiso_pago)->startOfDay()
            : null;
        $installments = max(0, (int) ($venta->numero_cuotas ?? 0));
        $termDays = max(0, (int) ($venta->plazo_acordado_dias ?? 0));
        $saldoPendiente = (float) $venta->saldo_pendiente_mora;

        $installmentAmount = $installments > 0
            ? round((float) $venta->total / max(1, $installments), 2)
            : 0.0;

        $coveredInstallments = $installmentAmount > 0
            ? min($installments, (int) floor((((float) $venta->monto_pagado) + 0.00001) / $installmentAmount))
            : 0;

        $schedule = [];
        $currentDueDate = null;
        $currentInstallmentNumber = null;

        if ($baseDate && $installments > 0) {
            foreach (range(1, $installments) as $installmentNumber) {
                $dueDate = self::saleInstallmentDueDate($baseDate, $installmentNumber, $installments, $termDays);

                $schedule[] = [
                    'number' => $installmentNumber,
                    'date' => $dueDate,
                    'is_paid' => $installmentNumber <= $coveredInstallments,
                    'is_current' => false,
                ];
            }

            $currentInstallmentNumber = min($installments, max(1, $coveredInstallments + 1));
            $currentDueDate = $schedule[$currentInstallmentNumber - 1]['date'] ?? null;

            if (isset($schedule[$currentInstallmentNumber - 1])) {
                $schedule[$currentInstallmentNumber - 1]['is_current'] = true;
            }
        } elseif ($baseDate && $termDays > 0) {
            $currentDueDate = $baseDate->copy()->addDays($termDays);
        } elseif ($commitmentDate) {
            $currentDueDate = $commitmentDate;
        } elseif ($baseDate) {
            $currentDueDate = $baseDate->copy();
        }

        $daysInMora = 0;

        if ($saldoPendiente > 0 && $currentDueDate && ! $currentDueDate->isFuture()) {
            $daysInMora = $currentDueDate->diffInDays($today);
        }

        return [
            'base_date' => $baseDate,
            'commitment_date' => $commitmentDate,
            'current_due_date' => $currentDueDate,
            'current_installment_number' => $currentInstallmentNumber,
            'installments' => $installments,
            'installment_amount' => $installmentAmount,
            'covered_installments' => $coveredInstallments,
            'term_days' => $termDays,
            'days_in_mora' => $daysInMora,
            'schedule' => $schedule,
            'balance' => $saldoPendiente,
        ];
    }

    public static function saleCurrentDueDate(Venta $venta, ?CarbonInterface $today = null): ?Carbon
    {
        return self::saleSummary($venta, $today)['current_due_date'];
    }

    public static function saleDaysInMora(Venta $venta, ?CarbonInterface $today = null): int
    {
        return self::saleSummary($venta, $today)['days_in_mora'];
    }

    public static function saleSemaphore(Venta $venta, ?CarbonInterface $today = null): string
    {
        $summary = self::saleSummary($venta, $today);

        if ($summary['balance'] <= 0) {
            return 'al_dia';
        }

        if (! $summary['base_date'] && ! $summary['current_due_date']) {
            return 'sin_fecha';
        }

        if (! $summary['current_due_date'] || $summary['current_due_date']->isFuture()) {
            return 'al_dia';
        }

        if ($summary['days_in_mora'] <= 7) {
            return 'verde';
        }

        if ($summary['days_in_mora'] <= 29) {
            return 'amarillo';
        }

        return 'rojo';
    }

    public static function saleStage(Venta $venta, ?CarbonInterface $today = null): string
    {
        return match (self::saleSemaphore($venta, $today)) {
            'al_dia' => 'Al dia',
            'sin_fecha' => 'Pendiente de configurar',
            'verde' => 'Mora temprana',
            'amarillo' => 'En seguimiento',
            'rojo' => 'Mora critica',
            default => 'En seguimiento',
        };
    }

    public static function saleMilestoneSchedule(Venta $venta, ?CarbonInterface $today = null): array
    {
        $summary = self::saleSummary($venta, $today);

        return self::milestoneScheduleFromReferenceDate(
            $summary['current_due_date'],
            (float) $venta->saldo_pendiente_mora,
            $today
        );
    }

    public static function saleBuildCalendar(Venta $venta, CarbonInterface $visibleMonth, int $months, ?CarbonInterface $today = null): array
    {
        $summary = self::saleSummary($venta, $today);
        $markers = [];

        if ($summary['base_date']) {
            $markers[] = [
                'date' => $summary['base_date'],
                'label' => 'Inicio',
                'kind' => 'base',
            ];
        }

        if ($summary['current_due_date']) {
            $markers[] = [
                'date' => $summary['current_due_date'],
                'label' => $summary['current_installment_number']
                    ? 'C' . $summary['current_installment_number']
                    : 'Vence',
                'kind' => 'due',
            ];
        }

        if ($summary['commitment_date']
            && (! $summary['current_due_date'] || ! $summary['commitment_date']->isSameDay($summary['current_due_date']))
        ) {
            $markers[] = [
                'date' => $summary['commitment_date'],
                'label' => 'Comp.',
                'kind' => 'commitment',
            ];
        }

        return self::buildCalendarFromReferenceDate(
            $visibleMonth,
            $months,
            $summary['current_due_date'],
            (float) $venta->saldo_pendiente_mora,
            $markers
        );
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

    protected static function milestoneScheduleFromReferenceDate(?CarbonInterface $referenceDate, float $saldoPendiente, ?CarbonInterface $today = null): array
    {
        $baseDate = $referenceDate ? Carbon::parse($referenceDate)->startOfDay() : null;
        $diasActuales = self::daysInMora($baseDate, $saldoPendiente, $today);

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

    protected static function buildCalendarFromReferenceDate(
        CarbonInterface $visibleMonth,
        int $months,
        ?CarbonInterface $referenceDate,
        float $saldoPendiente,
        array $markers = []
    ): array {
        $calendar = [];
        $cursor = Carbon::parse($visibleMonth)->startOfMonth();
        $months = max(1, min(3, $months));
        $normalizedMarkers = collect($markers)
            ->filter(fn (array $marker) => ! empty($marker['date']))
            ->mapWithKeys(function (array $marker) {
                $date = Carbon::parse($marker['date'])->startOfDay();

                return [$date->format('Y-m-d') => array_merge($marker, ['date' => $date])];
            })
            ->all();

        for ($index = 0; $index < $months; $index++) {
            $month = $cursor->copy()->addMonths($index);
            $firstDay = $month->copy()->startOfMonth();
            $lastDay = $month->copy()->endOfMonth();
            $days = [];

            for ($blank = 1; $blank < $firstDay->dayOfWeekIso; $blank++) {
                $days[] = ['blank' => true];
            }

            for ($date = $firstDay->copy(); $date->lte($lastDay); $date->addDay()) {
                $daySemaphore = self::resolveDaySemaphore($date, $referenceDate, $saldoPendiente);
                $marker = $normalizedMarkers[$date->format('Y-m-d')] ?? null;

                $days[] = [
                    'blank' => false,
                    'date' => $date->copy(),
                    'is_today' => $date->isToday(),
                    'is_mora_start' => $referenceDate ? $date->isSameDay($referenceDate) : false,
                    'marker' => $marker,
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

    protected static function saleInstallmentDueDate(Carbon $baseDate, int $installmentNumber, int $installments, int $termDays): Carbon
    {
        if ($installments <= 1 && $termDays > 0) {
            return $baseDate->copy()->addDays($termDays);
        }

        return $baseDate->copy()->addMonthsNoOverflow($installmentNumber);
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
