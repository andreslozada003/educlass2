<?php

use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createVentaCuotasTable();
        $this->backfillVentaCuotas();
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_cuotas');
    }

    private function createVentaCuotasTable(): void
    {
        if (Schema::hasTable('venta_cuotas')) {
            return;
        }

        Schema::create('venta_cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->unsignedInteger('numero_cuota');
            $table->date('fecha_vencimiento');
            $table->decimal('valor_cuota', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('saldo_pendiente', 12, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->dateTime('fecha_pago')->nullable();
            $table->unsignedInteger('dias_mora')->default(0);
            $table->timestamps();

            $table->unique(['venta_id', 'numero_cuota']);
            $table->index(['fecha_vencimiento', 'estado']);
        });
    }

    private function backfillVentaCuotas(): void
    {
        if (! Schema::hasTable('venta_cuotas') || DB::table('venta_cuotas')->count() > 0) {
            return;
        }

        DB::table('ventas')
            ->orderBy('id')
            ->chunkById(100, function ($ventas) {
                $rows = [];

                foreach ($ventas as $venta) {
                    if (! $this->shouldGenerateInstallments($venta)) {
                        continue;
                    }

                    array_push($rows, ...$this->buildRowsForVenta($venta));
                }

                if ($rows !== []) {
                    DB::table('venta_cuotas')->insert($rows);
                }
            });
    }

    private function shouldGenerateInstallments(object $venta): bool
    {
        $isCreditSale = ($venta->metodo_pago ?? null) === 'credito'
            || ($venta->estado ?? null) === 'credito'
            || ! empty($venta->numero_cuotas);

        return $isCreditSale
            && (float) ($venta->total ?? 0) > 0
            && ! empty($venta->fecha_inicio_mora ?: $venta->fecha_venta);
    }

    private function buildRowsForVenta(object $venta): array
    {
        $installments = max(1, (int) ($venta->numero_cuotas ?: 1));
        $baseDate = Carbon::parse($venta->fecha_inicio_mora ?: $venta->fecha_venta)->startOfDay();
        $amounts = $this->splitInstallmentAmounts((float) $venta->total, $installments);
        $paymentEvents = $this->paymentEventsForVenta($venta);
        $plans = [];

        foreach (range(1, $installments) as $number) {
            $plans[] = [
                'venta_id' => $venta->id,
                'numero_cuota' => $number,
                'fecha_vencimiento' => $this->calculateDueDate($baseDate, $number, $installments, (int) ($venta->plazo_acordado_dias ?? 0)),
                'valor_cuota' => $amounts[$number - 1],
                'monto_pagado' => 0.0,
                'saldo_pendiente' => $amounts[$number - 1],
                'estado' => 'pendiente',
                'fecha_pago' => null,
                'dias_mora' => 0,
            ];
        }

        foreach ($paymentEvents as $payment) {
            $remaining = round((float) $payment->monto, 2);
            $paymentDate = Carbon::parse($payment->fecha_pago)->startOfDay();

            foreach ($plans as &$plan) {
                $outstanding = round((float) $plan['valor_cuota'] - (float) $plan['monto_pagado'], 2);

                if ($outstanding <= 0 || $remaining <= 0) {
                    continue;
                }

                $applied = min($remaining, $outstanding);
                $plan['monto_pagado'] = round((float) $plan['monto_pagado'] + $applied, 2);
                $remaining = round($remaining - $applied, 2);

                if ($plan['monto_pagado'] + 0.009 >= (float) $plan['valor_cuota']) {
                    $plan['fecha_pago'] = $paymentDate->copy();
                }

                if ($remaining <= 0) {
                    break;
                }
            }

            unset($plan);
        }

        $now = Carbon::now()->startOfDay();
        $timestamp = now();

        return array_map(function (array $plan) use ($now, $timestamp) {
            $dueDate = Carbon::parse($plan['fecha_vencimiento'])->startOfDay();
            $saldoPendiente = round(max((float) $plan['valor_cuota'] - (float) $plan['monto_pagado'], 0), 2);
            $isPaid = $saldoPendiente <= 0.009;
            $isOverdue = ! $isPaid && $now->gte($dueDate);

            $plan['saldo_pendiente'] = $saldoPendiente;
            $plan['estado'] = $isPaid ? 'pagada' : ($isOverdue ? 'vencida' : 'pendiente');
            $plan['dias_mora'] = $isOverdue ? $dueDate->diffInDays($now) : 0;
            $plan['fecha_vencimiento'] = $dueDate->toDateString();
            $plan['fecha_pago'] = $isPaid && $plan['fecha_pago']
                ? Carbon::parse($plan['fecha_pago'])->toDateTimeString()
                : null;
            $plan['created_at'] = $timestamp;
            $plan['updated_at'] = $timestamp;

            return $plan;
        }, $plans);
    }

    private function paymentEventsForVenta(object $venta)
    {
        $payments = DB::table('mora_abonos')
            ->where('mora_abonable_type', Venta::class)
            ->where('mora_abonable_id', $venta->id)
            ->where('monto', '>', 0)
            ->orderBy('fecha_pago')
            ->orderBy('id')
            ->get(['monto', 'fecha_pago']);

        if ($payments->isNotEmpty()) {
            return $payments;
        }

        if ((float) ($venta->monto_pagado ?? 0) <= 0) {
            return collect();
        }

        return collect([
            (object) [
                'monto' => (float) $venta->monto_pagado,
                'fecha_pago' => $venta->fecha_venta,
            ],
        ]);
    }

    private function splitInstallmentAmounts(float $total, int $installments): array
    {
        $installments = max(1, $installments);
        $base = floor(($total / $installments) * 100) / 100;
        $amounts = array_fill(0, $installments, round($base, 2));
        $assigned = round($base * ($installments - 1), 2);
        $amounts[$installments - 1] = round($total - $assigned, 2);

        return $amounts;
    }

    private function calculateDueDate(Carbon $baseDate, int $installmentNumber, int $installments, int $termDays): Carbon
    {
        if ($installments <= 1 && $termDays > 0) {
            return $baseDate->copy()->addDays($termDays);
        }

        return $baseDate->copy()->addMonthsNoOverflow($installmentNumber);
    }
};
