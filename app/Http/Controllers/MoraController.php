<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\User;
use App\Models\Venta;
use App\Support\MoraSupport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MoraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        [$canViewSales, $canViewRepairs] = $this->resolvePermissions($request);

        $tab = $this->resolveTab($request->string('tab')->toString(), $canViewSales, $canViewRepairs);

        $ventasCollection = $canViewSales
            ? $this->applyComputedFilters($this->ventasBaseQuery($request)->get(), $request)
            : collect();
        $reparacionesCollection = $canViewRepairs
            ? $this->applyComputedFilters($this->reparacionesBaseQuery($request)->get(), $request)
            : collect();

        $ventas = $canViewSales
            ? $this->paginateCollection($ventasCollection, 10, 'ventas_page', $request)
            : null;
        $reparaciones = $canViewRepairs
            ? $this->paginateCollection($reparacionesCollection, 10, 'reparaciones_page', $request)
            : null;

        $asesores = User::query()
            ->active()
            ->orderBy('name')
            ->get();

        return view('mora.index', [
            'tab' => $tab,
            'canViewSales' => $canViewSales,
            'canViewRepairs' => $canViewRepairs,
            'ventas' => $ventas,
            'reparaciones' => $reparaciones,
            'ventasMetrics' => $canViewSales ? $this->buildMetrics($ventasCollection) : null,
            'reparacionesMetrics' => $canViewRepairs ? $this->buildMetrics($reparacionesCollection) : null,
            'asesores' => $asesores,
            'filters' => $request->only(['search', 'color', 'asesor_id', 'fecha_desde', 'fecha_hasta', 'tab']),
            'semaforos' => [
                'sin_fecha' => MoraSupport::palette('sin_fecha'),
                'verde' => MoraSupport::palette('verde'),
                'amarillo' => MoraSupport::palette('amarillo'),
                'rojo' => MoraSupport::palette('rojo'),
            ],
        ]);
    }

    public function showVenta(Request $request, Venta $venta)
    {
        [$canViewSales] = $this->resolvePermissions($request);

        abort_unless($canViewSales, 403);

        $venta->load([
            'cliente',
            'usuario',
            'cuotas',
            'detalles.producto',
            'moraAbonos.usuario',
            'moraNotificaciones.usuario',
            'ultimaMoraNotificacion',
        ]);

        return view('mora.show', $this->buildDetailPayload($request, $venta, 'ventas'));
    }

    public function showReparacion(Request $request, Reparacion $reparacion)
    {
        [, $canViewRepairs] = $this->resolvePermissions($request);

        abort_unless($canViewRepairs, 403);

        $reparacion->load([
            'cliente',
            'usuario',
            'tecnico',
            'historial.usuario',
            'moraAbonos.usuario',
            'moraNotificaciones.usuario',
            'ultimaMoraNotificacion',
        ]);

        return view('mora.show', $this->buildDetailPayload($request, $reparacion, 'reparaciones'));
    }

    public function updateVenta(Request $request, Venta $venta): RedirectResponse
    {
        [$canViewSales] = $this->resolvePermissions($request);

        abort_unless($canViewSales, 403);

        $validated = $request->validate([
            'fecha_inicio_mora' => 'nullable|date',
            'fecha_compromiso_pago' => 'nullable|date|after_or_equal:fecha_inicio_mora',
            'numero_cuotas' => 'nullable|integer|min:1|max:48',
            'plazo_acordado_dias' => 'nullable|integer|min:1|max:365',
            'mora_observaciones' => 'nullable|string|max:2000',
        ]);

        $venta->update($validated);

        return back()->with('success', 'Seguimiento de mora actualizado para la venta.');
    }

    public function updateReparacion(Request $request, Reparacion $reparacion): RedirectResponse
    {
        [, $canViewRepairs] = $this->resolvePermissions($request);

        abort_unless($canViewRepairs, 403);

        $validated = $request->validate([
            'fecha_inicio_mora' => 'nullable|date',
            'mora_observaciones' => 'nullable|string|max:2000',
        ]);

        $reparacion->update($validated);

        return back()->with('success', 'Seguimiento de mora actualizado para la reparacion.');
    }

    public function storeVentaAbono(Request $request, Venta $venta): RedirectResponse
    {
        [$canViewSales] = $this->resolvePermissions($request);

        abort_unless($canViewSales, 403);

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string|max:50',
            'fecha_pago' => 'required|date',
            'notas' => 'nullable|string|max:1000',
        ]);

        if ((float) $validated['monto'] > $venta->saldo_pendiente_mora) {
            return back()->with('error', 'El abono supera el saldo pendiente de la venta.');
        }

        DB::transaction(function () use ($validated, $venta) {
            $venta->moraAbonos()->create([
                'cliente_id' => $venta->cliente_id,
                'user_id' => auth()->id(),
                'tipo' => 'abono',
                'monto' => (float) $validated['monto'],
                'metodo_pago' => $validated['metodo_pago'] ?? 'manual',
                'origen' => 'mora_module',
                'fecha_pago' => $validated['fecha_pago'],
                'notas' => $validated['notas'] ?? 'Abono registrado desde el modulo de mora.',
            ]);

            $venta->sincronizarMontoPagadoDesdeAbonos();
        });

        return back()->with('success', 'Abono registrado correctamente.');
    }

    public function storeReparacionAbono(Request $request, Reparacion $reparacion): RedirectResponse
    {
        [, $canViewRepairs] = $this->resolvePermissions($request);

        abort_unless($canViewRepairs, 403);

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string|max:50',
            'fecha_pago' => 'required|date',
            'notas' => 'nullable|string|max:1000',
        ]);

        if ((float) $validated['monto'] > $reparacion->saldo_pendiente_mora) {
            return back()->with('error', 'El abono supera el saldo pendiente de la reparacion.');
        }

        DB::transaction(function () use ($validated, $reparacion) {
            $reparacion->moraAbonos()->create([
                'cliente_id' => $reparacion->cliente_id,
                'user_id' => auth()->id(),
                'tipo' => 'abono',
                'monto' => (float) $validated['monto'],
                'metodo_pago' => $validated['metodo_pago'] ?? 'manual',
                'origen' => 'mora_module',
                'fecha_pago' => $validated['fecha_pago'],
                'notas' => $validated['notas'] ?? 'Abono registrado desde el modulo de mora.',
            ]);

            $reparacion->adelanto = (float) $reparacion->adelanto + (float) $validated['monto'];
            $reparacion->save();
        });

        return back()->with('success', 'Abono registrado correctamente.');
    }

    public function ventaWhatsapp(Request $request, Venta $venta)
    {
        [$canViewSales] = $this->resolvePermissions($request);

        abort_unless($canViewSales, 403);

        return $this->redirectToWhatsapp(
            $venta,
            $venta->cliente?->telefono,
            MoraSupport::saleMessage($venta),
            'venta'
        );
    }

    public function reparacionWhatsapp(Request $request, Reparacion $reparacion)
    {
        [, $canViewRepairs] = $this->resolvePermissions($request);

        abort_unless($canViewRepairs, 403);

        return $this->redirectToWhatsapp(
            $reparacion,
            $reparacion->cliente?->telefono,
            MoraSupport::repairMessage($reparacion),
            'reparacion'
        );
    }

    protected function redirectToWhatsapp(Model $model, ?string $telefono, string $mensaje, string $tipo)
    {
        $whatsappUrl = MoraSupport::whatsappUrl($telefono, $mensaje);

        if (! $whatsappUrl) {
            return redirect()
                ->to($tipo === 'venta'
                    ? route('mora.ventas.show', $model)
                    : route('mora.reparaciones.show', $model))
                ->with('error', 'El cliente no tiene un numero de WhatsApp valido.');
        }

        $diasEnMora = (int) $model->dias_en_mora;
        $fechaEnvio = now();

        $model->moraNotificaciones()->create([
            'cliente_id' => $model->cliente_id,
            'user_id' => auth()->id(),
            'canal' => 'whatsapp',
            'nivel' => MoraSupport::notificationLevel($diasEnMora),
            'plantilla' => MoraSupport::notificationTemplateKey($tipo, $diasEnMora),
            'telefono' => $telefono,
            'estado_envio' => 'abierto_en_whatsapp',
            'fecha_envio' => $fechaEnvio,
            'mensaje' => $mensaje,
        ]);

        $model->forceFill([
            'ultima_notificacion_mora_at' => $fechaEnvio,
        ])->save();

        return redirect()->away($whatsappUrl);
    }

    protected function buildDetailPayload(Request $request, Model $model, string $type): array
    {
        $visibleMonth = $this->resolveVisibleMonth($request, $model);
        $months = max(1, min(3, (int) $request->integer('months', 2)));
        $creditSummary = null;
        $calendar = [];
        $milestones = [];

        if ($model instanceof Venta) {
            $creditSummary = MoraSupport::saleSummary($model);
            $calendar = MoraSupport::saleBuildCalendar($model, $visibleMonth, $months);
            $milestones = MoraSupport::saleMilestoneSchedule($model);
        } else {
            $saldoPendiente = (float) $model->saldo_pendiente_mora;
            $fechaInicioMora = $model->fecha_inicio_mora;
            $calendar = MoraSupport::buildCalendar($visibleMonth, $months, $fechaInicioMora, $saldoPendiente);
            $milestones = MoraSupport::milestoneSchedule($fechaInicioMora, $saldoPendiente);
        }

        return [
            'type' => $type,
            'record' => $model,
            'months' => $months,
            'visibleMonth' => $visibleMonth,
            'calendar' => $calendar,
            'milestones' => $milestones,
            'palette' => MoraSupport::palette($model->mora_semaforo),
            'backRoute' => route('mora.index', ['tab' => $type]),
            'detailRoute' => $type === 'ventas'
                ? route('mora.ventas.show', $model)
                : route('mora.reparaciones.show', $model),
            'creditSummary' => $creditSummary,
        ];
    }

    protected function ventasBaseQuery(Request $request): Builder
    {
        $query = Venta::query()
            ->with([
                'cliente',
                'usuario',
                'cuotas',
                'detalles.producto',
                'ultimaMoraNotificacion',
            ])
            ->where('estado', '!=', 'cancelada')
            ->whereColumn('total', '>', 'monto_pagado');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('folio', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function (Builder $clienteQuery) use ($search) {
                        $clienteQuery->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%")
                            ->orWhere('rfc', 'like', "%{$search}%");
                    })
                    ->orWhereHas('detalles.producto', function (Builder $detalleQuery) use ($search) {
                        $detalleQuery->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('asesor_id')) {
            $query->where('user_id', $request->asesor_id);
        }

        return $query->latest('fecha_venta');
    }

    protected function reparacionesBaseQuery(Request $request): Builder
    {
        $query = Reparacion::query()
            ->with([
                'cliente',
                'usuario',
                'tecnico',
                'ultimaMoraNotificacion',
            ])
            ->where(function (Builder $builder) {
                $builder
                    ->where(function (Builder $query) {
                        $query->where('costo_final', '>', 0)
                            ->whereColumn('costo_final', '>', 'adelanto');
                    })
                    ->orWhere(function (Builder $query) {
                        $query->where('costo_final', '<=', 0)
                            ->whereColumn('costo_estimado', '>', 'adelanto');
                    });
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('orden', 'like', "%{$search}%")
                    ->orWhere('dispositivo_marca', 'like', "%{$search}%")
                    ->orWhere('dispositivo_modelo', 'like', "%{$search}%")
                    ->orWhere('problema_reportado', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function (Builder $clienteQuery) use ($search) {
                        $clienteQuery->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%")
                            ->orWhere('rfc', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('asesor_id')) {
            $query->where('user_id', $request->asesor_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio_mora', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio_mora', '<=', $request->fecha_hasta);
        }

        return $query->latest('fecha_recepcion');
    }

    protected function applyComputedFilters(Collection $items, Request $request): Collection
    {
        if ($request->filled('fecha_desde')) {
            $fechaDesde = Carbon::parse($request->fecha_desde)->startOfDay();
            $items = $items->filter(function ($item) use ($fechaDesde) {
                $referenceDate = $item instanceof Venta
                    ? $item->fecha_mora_referencia
                    : $item->fecha_inicio_mora;

                return $referenceDate ? Carbon::parse($referenceDate)->startOfDay()->gte($fechaDesde) : false;
            });
        }

        if ($request->filled('fecha_hasta')) {
            $fechaHasta = Carbon::parse($request->fecha_hasta)->startOfDay();
            $items = $items->filter(function ($item) use ($fechaHasta) {
                $referenceDate = $item instanceof Venta
                    ? $item->fecha_mora_referencia
                    : $item->fecha_inicio_mora;

                return $referenceDate ? Carbon::parse($referenceDate)->startOfDay()->lte($fechaHasta) : false;
            });
        }

        if ($request->filled('color')) {
            $items = $items->filter(fn ($item) => $item->mora_semaforo === $request->color);
        }

        return $items
            ->sort(function ($left, $right) {
                $priorityComparison = MoraSupport::priority($right->mora_semaforo) <=> MoraSupport::priority($left->mora_semaforo);

                if ($priorityComparison !== 0) {
                    return $priorityComparison;
                }

                $daysComparison = $right->dias_en_mora <=> $left->dias_en_mora;

                if ($daysComparison !== 0) {
                    return $daysComparison;
                }

                return ($right->updated_at?->timestamp ?? 0) <=> ($left->updated_at?->timestamp ?? 0);
            })
            ->values();
    }

    protected function buildMetrics(Collection $items): array
    {
        return [
            'total' => $items->count(),
            'saldo' => $items->sum(fn ($item) => (float) $item->saldo_pendiente_mora),
            'criticos' => $items->where('mora_semaforo', 'rojo')->count(),
            'porConfigurar' => $items->where('mora_semaforo', 'sin_fecha')->count(),
        ];
    }

    protected function paginateCollection(Collection $items, int $perPage, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $results = $items->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $results,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        );

        return $paginator->appends(collect($request->query())->except($pageName)->all());
    }

    protected function resolvePermissions(Request $request): array
    {
        $user = $request->user();

        $canViewSales = $user->can('ver ventas');
        $canViewRepairs = $user->can('ver reparaciones');

        abort_unless($canViewSales || $canViewRepairs, 403);

        return [$canViewSales, $canViewRepairs];
    }

    protected function resolveTab(string $tab, bool $canViewSales, bool $canViewRepairs): string
    {
        if ($tab === 'reparaciones' && $canViewRepairs) {
            return 'reparaciones';
        }

        if ($tab === 'ventas' && $canViewSales) {
            return 'ventas';
        }

        return $canViewSales ? 'ventas' : 'reparaciones';
    }

    protected function resolveVisibleMonth(Request $request, Model $model): Carbon
    {
        if ($request->filled('month')) {
            try {
                return Carbon::createFromFormat('!Y-m', $request->string('month')->toString())->startOfMonth();
            } catch (\Throwable) {
                // Fallback below.
            }
        }

        if ($model instanceof Venta) {
            if ($model->fecha_mora_referencia) {
                return Carbon::parse($model->fecha_mora_referencia)->startOfMonth();
            }

            if ($model->fecha_venta) {
                return Carbon::parse($model->fecha_venta)->startOfMonth();
            }
        }

        if ($model->fecha_inicio_mora) {
            return Carbon::parse($model->fecha_inicio_mora)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
