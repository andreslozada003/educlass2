<?php

namespace App\Support;

use App\Models\Reparacion;
use App\Models\Venta;
use App\Models\VentaCuota;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

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

    public static function syncSaleInstallments(Venta $venta, ?CarbonInterface $today = null): Collection
    {
        $rows = self::buildSaleInstallmentRows($venta, $today);

        if ($rows->isEmpty()) {
            $venta->cuotas()->delete();
            $venta->setRelation('cuotas', collect());

            return collect();
        }

        $numbers = $rows->pluck('numero_cuota')->all();
        $timestamp = now();

        $payload = $rows
            ->map(function (array $row) use ($timestamp) {
                return array_merge($row, [
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            })
            ->all();

        $venta->cuotas()->whereNotIn('numero_cuota', $numbers)->delete();
        $venta->cuotas()->upsert(
            $payload,
            ['venta_id', 'numero_cuota'],
            ['fecha_vencimiento', 'valor_cuota', 'monto_pagado', 'saldo_pendiente', 'estado', 'fecha_pago', 'dias_mora', 'updated_at']
        );

        $freshInstallments = $venta->cuotas()->orderBy('numero_cuota')->get();
        $venta->setRelation('cuotas', $freshInstallments);

        return $freshInstallments;
    }

    public static function saleSummary(Venta $venta, ?CarbonInterface $today = null): array
    {
        $today = Carbon::parse($today ?? now())->startOfDay();
        $baseDate = self::saleCreditBaseDate($venta);
        $commitmentDate = $venta->fecha_compromiso_pago
            ? Carbon::parse($venta->fecha_compromiso_pago)->startOfDay()
            : null;
        $termDays = max(0, (int) ($venta->plazo_acordado_dias ?? 0));
        $saldoPendiente = (float) $venta->saldo_pendiente_mora;
        $cuotas = self::saleInstallmentCollection($venta, $today);
        $currentQuota = $cuotas->first(fn (VentaCuota $cuota) => ! $cuota->esta_pagada);
        $overdueQuota = $cuotas->first(fn (VentaCuota $cuota) => $cuota->esta_en_mora);
        $paidInstallments = $cuotas->filter(fn (VentaCuota $cuota) => $cuota->esta_pagada)->count();
        $installments = $cuotas->count();
        $currentDueDate = $currentQuota?->fecha_vencimiento ?? $commitmentDate ?? $baseDate;
        $currentInstallmentNumber = $currentQuota?->numero_cuota;
        $daysInMora = $overdueQuota?->dias_mora_actual ?? 0;
        $installmentAmount = (float) ($currentQuota?->valor_cuota ?? $cuotas->first()?->valor_cuota ?? 0);
        $schedule = $cuotas
            ->map(function (VentaCuota $cuota) use ($currentQuota) {
                return [
                    'number' => $cuota->numero_cuota,
                    'date' => $cuota->fecha_vencimiento,
                    'is_paid' => $cuota->esta_pagada,
                    'is_current' => $currentQuota && $cuota->numero_cuota === $currentQuota->numero_cuota,
                    'status' => $cuota->estado_calculado,
                    'status_label' => $cuota->estado_etiqueta,
                    'amount' => (float) $cuota->valor_cuota,
                    'days_in_mora' => $cuota->dias_mora_actual,
                ];
            })
            ->values()
            ->all();

        return [
            'base_date' => $baseDate,
            'commitment_date' => $commitmentDate,
            'current_due_date' => $currentDueDate,
            'current_installment_number' => $currentInstallmentNumber,
            'installments' => $installments,
            'installment_amount' => $installmentAmount,
            'covered_installments' => $paidInstallments,
            'term_days' => $termDays,
            'days_in_mora' => $daysInMora,
            'schedule' => $schedule,
            'balance' => $saldoPendiente,
            'current_quota' => $currentQuota,
            'overdue_quota' => $overdueQuota,
            'has_overdue_installment' => $overdueQuota !== null,
            'cuotas' => $cuotas,
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

        if ($summary['has_overdue_installment']) {
            return 'rojo';
        }

        if ($summary['current_due_date']) {
            return 'verde';
        }

        return 'sin_fecha';
    }

    public static function saleStage(Venta $venta, ?CarbonInterface $today = null): string
    {
        return match (self::saleSemaphore($venta, $today)) {
            'al_dia' => 'Al dia',
            'sin_fecha' => 'Pendiente de configurar',
            'verde' => 'Cuota pendiente',
            'amarillo' => 'En seguimiento',
            'rojo' => 'Cuota en mora',
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

        foreach ($summary['cuotas'] as $cuota) {
            $markers[] = [
                'date' => $cuota->fecha_vencimiento,
                'label' => 'C' . $cuota->numero_cuota,
                'kind' => $cuota->estado_calculado,
                'entry_type' => 'installment',
                'client_name' => $venta->cliente?->nombre_completo ?: 'Cliente',
                'amount_label' => money($cuota->valor_cuota),
                'status_label' => $cuota->estado_etiqueta,
                'installment_number' => $cuota->numero_cuota,
                'days_in_mora' => $cuota->dias_mora_actual,
                'badge_classes' => $cuota->estado_badge_classes,
                'card_classes' => match ($cuota->estado_calculado) {
                    'pagada' => 'border-emerald-200 bg-emerald-50/80 text-emerald-700',
                    'vencida' => 'border-rose-200 bg-rose-50/80 text-rose-700',
                    default => 'border-slate-200 bg-white/80 text-slate-700',
                },
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
            $summary['overdue_quota']?->fecha_vencimiento,
            (float) $venta->saldo_pendiente_mora,
            $markers,
            true
        );
    }

    public static function resolveDaySemaphore(
        CarbonInterface $date,
        ?CarbonInterface $fechaInicioMora,
        float $saldoPendiente,
        bool $forceRedFromReference = false
    ): string
    {
        if ($saldoPendiente <= 0 || ! $fechaInicioMora) {
            return 'al_dia';
        }

        $fechaInicio = Carbon::parse($fechaInicioMora)->startOfDay();
        $dia = Carbon::parse($date)->startOfDay();

        if ($dia->lt($fechaInicio)) {
            return 'al_dia';
        }

        if ($forceRedFromReference) {
            return 'rojo';
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
        array $markers = [],
        bool $forceRedFromReference = false
    ): array {
        $calendar = [];
        $cursor = Carbon::parse($visibleMonth)->startOfMonth();
        $months = max(1, min(3, $months));
        $normalizedMarkers = collect($markers)
            ->filter(fn (array $marker) => ! empty($marker['date']))
            ->groupBy(function (array $marker) {
                $date = Carbon::parse($marker['date'])->startOfDay();

                return $date->format('Y-m-d');
            })
            ->map(function (Collection $group) {
                return $group
                    ->map(function (array $marker) {
                        $date = Carbon::parse($marker['date'])->startOfDay();

                        return array_merge($marker, ['date' => $date]);
                    })
                    ->values()
                    ->all();
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
                $daySemaphore = self::resolveDaySemaphore($date, $referenceDate, $saldoPendiente, $forceRedFromReference);
                $entries = $normalizedMarkers[$date->format('Y-m-d')] ?? [];
                $marker = $entries[0] ?? null;

                $days[] = [
                    'blank' => false,
                    'date' => $date->copy(),
                    'is_today' => $date->isToday(),
                    'is_mora_start' => $referenceDate ? $date->isSameDay($referenceDate) : false,
                    'entries' => $entries,
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

    protected static function saleInstallmentCollection(Venta $venta, ?CarbonInterface $today = null): Collection
    {
        $calculatedRows = self::buildSaleInstallmentRows($venta, $today);
        $cuotas = $venta->relationLoaded('cuotas')
            ? $venta->cuotas->sortBy('numero_cuota')->values()
            : $venta->cuotas()->orderBy('numero_cuota')->get();

        if ($cuotas->isNotEmpty() && ! self::installmentsNeedSync($cuotas, $calculatedRows)) {
            return $cuotas->values();
        }

        if ($cuotas->isNotEmpty()) {
            return self::syncSaleInstallments($venta, $today)->values();
        }

        return $calculatedRows
            ->map(function (array $row) {
                $cuota = new VentaCuota($row);
                $cuota->exists = false;

                return $cuota;
            })
            ->values();
    }

    protected static function installmentsNeedSync(Collection $existingRows, Collection $calculatedRows): bool
    {
        if ($existingRows->count() !== $calculatedRows->count()) {
            return true;
        }

        foreach ($calculatedRows as $offset => $row) {
            /** @var VentaCuota $existing */
            $existing = $existingRows[$offset];

            if ((int) $existing->numero_cuota !== (int) $row['numero_cuota']) {
                return true;
            }

            if ($existing->fecha_vencimiento?->toDateString() !== $row['fecha_vencimiento']) {
                return true;
            }

            if (round((float) $existing->valor_cuota, 2) !== round((float) $row['valor_cuota'], 2)) {
                return true;
            }

            if (round((float) $existing->monto_pagado, 2) !== round((float) $row['monto_pagado'], 2)) {
                return true;
            }

            if (round((float) $existing->saldo_pendiente, 2) !== round((float) $row['saldo_pendiente'], 2)) {
                return true;
            }

            if ((string) $existing->estado !== (string) $row['estado']) {
                return true;
            }

            if (($existing->fecha_pago?->toDateTimeString()) !== $row['fecha_pago']) {
                return true;
            }

            if ((int) $existing->dias_mora !== (int) $row['dias_mora']) {
                return true;
            }
        }

        return false;
    }

    protected static function buildSaleInstallmentRows(Venta $venta, ?CarbonInterface $today = null): Collection
    {
        if (! self::shouldTrackSaleInstallments($venta)) {
            return collect();
        }

        $today = Carbon::parse($today ?? now())->startOfDay();
        $openDefinitions = collect(self::saleInstallmentDefinitions($venta))->values();
        $payments = self::salePaymentEvents($venta);
        $remainingBalance = round((float) $venta->total, 2);
        $paidDefinitions = collect();

        foreach ($payments as $payment) {
            if ($remainingBalance <= 0.009 || $openDefinitions->isEmpty()) {
                break;
            }

            $remainingPayment = round(min((float) $payment['monto'], $remainingBalance), 2);

            if ($remainingPayment <= 0) {
                continue;
            }

            $paymentDate = Carbon::parse($payment['fecha_pago'])->startOfDay();

            while ($remainingPayment > 0.009 && $openDefinitions->isNotEmpty() && $remainingBalance > 0.009) {
                $currentValue = self::splitInstallmentAmounts($remainingBalance, $openDefinitions->count())[0] ?? $remainingBalance;
                $currentValue = round((float) $currentValue, 2);

                if ($remainingPayment + 0.009 < $currentValue) {
                    break;
                }

                $currentDefinition = $openDefinitions->shift();

                $paidDefinitions->push([
                    'venta_id' => $currentDefinition['venta_id'],
                    'numero_cuota' => $currentDefinition['numero_cuota'],
                    'fecha_vencimiento' => Carbon::parse($currentDefinition['fecha_vencimiento'])->toDateString(),
                    'valor_cuota' => round((float) $currentValue, 2),
                    'monto_pagado' => round((float) $currentValue, 2),
                    'saldo_pendiente' => 0.0,
                    'estado' => 'pagada',
                    'fecha_pago' => $paymentDate->toDateTimeString(),
                    'dias_mora' => 0,
                ]);

                $remainingBalance = round(max($remainingBalance - $currentValue, 0), 2);
                $remainingPayment = round(max($remainingPayment - $currentValue, 0), 2);
            }

            if ($remainingPayment > 0.009 && $remainingBalance > 0.009) {
                $remainingBalance = round(max($remainingBalance - min($remainingPayment, $remainingBalance), 0), 2);
            }
        }

        if ($openDefinitions->isEmpty() || $remainingBalance <= 0.009) {
            return $paidDefinitions->values();
        }

        $openAmounts = self::splitInstallmentAmounts($remainingBalance, $openDefinitions->count());
        $openRows = $openDefinitions->values()->map(function (array $definition, int $offset) use ($openAmounts, $today) {
            $dueDate = Carbon::parse($definition['fecha_vencimiento'])->startOfDay();
            $value = round((float) ($openAmounts[$offset] ?? 0), 2);
            $isOverdue = $today->gte($dueDate);

            return [
                'venta_id' => $definition['venta_id'],
                'numero_cuota' => $definition['numero_cuota'],
                'fecha_vencimiento' => $dueDate->toDateString(),
                'valor_cuota' => $value,
                'monto_pagado' => 0.0,
                'saldo_pendiente' => $value,
                'estado' => $isOverdue ? 'vencida' : 'pendiente',
                'fecha_pago' => null,
                'dias_mora' => $isOverdue ? $dueDate->diffInDays($today) : 0,
            ];
        });

        return $paidDefinitions
            ->concat($openRows)
            ->sortBy('numero_cuota')
            ->values();
    }

    protected static function shouldTrackSaleInstallments(Venta $venta): bool
    {
        return ((string) $venta->metodo_pago === 'credito'
                || (string) $venta->estado === 'credito'
                || (int) ($venta->numero_cuotas ?? 0) > 0)
            && (float) $venta->total > 0
            && self::saleCreditBaseDate($venta) !== null;
    }

    protected static function saleInstallmentDefinitions(Venta $venta): array
    {
        $installments = max(1, (int) ($venta->numero_cuotas ?: 1));
        $baseDate = self::saleCreditBaseDate($venta) ?? now()->startOfDay();
        $termDays = max(0, (int) ($venta->plazo_acordado_dias ?? 0));
        $amounts = self::splitInstallmentAmounts((float) $venta->total, $installments);

        return collect(range(1, $installments))
            ->map(function (int $number) use ($venta, $baseDate, $installments, $termDays, $amounts) {
                return [
                    'venta_id' => $venta->id,
                    'numero_cuota' => $number,
                    'fecha_vencimiento' => self::saleInstallmentDueDate($baseDate, $number, $installments, $termDays),
                    'valor_cuota' => $amounts[$number - 1] ?? 0,
                ];
            })
            ->all();
    }

    protected static function salePaymentEvents(Venta $venta): Collection
    {
        $abonos = $venta->relationLoaded('moraAbonos')
            ? $venta->moraAbonos
            : $venta->moraAbonos()->orderBy('fecha_pago')->orderBy('id')->get();

        $payments = $abonos
            ->filter(fn ($abono) => (float) $abono->monto > 0)
            ->sortBy(fn ($abono) => ($abono->fecha_pago?->timestamp ?? 0) . '-' . $abono->id)
            ->values()
            ->map(function ($abono) {
                return [
                    'monto' => round((float) $abono->monto, 2),
                    'fecha_pago' => $abono->fecha_pago ?: now(),
                ];
            });

        if ($payments->isNotEmpty()) {
            return $payments;
        }

        if ((float) $venta->monto_pagado <= 0) {
            return collect();
        }

        return collect([[
            'monto' => round((float) $venta->monto_pagado, 2),
            'fecha_pago' => $venta->fecha_venta ?: now(),
        ]]);
    }

    protected static function splitInstallmentAmounts(float $total, int $installments): array
    {
        $installments = max(1, $installments);
        $precision = abs($total - round($total, 0)) < 0.00001 ? 0 : 2;
        $factor = 10 ** $precision;
        $base = floor(($total / $installments) * $factor) / $factor;
        $amounts = array_fill(0, $installments, round($base, $precision));
        $assigned = round($base * ($installments - 1), $precision);
        $amounts[$installments - 1] = round($total - $assigned, $precision);

        return $amounts;
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
