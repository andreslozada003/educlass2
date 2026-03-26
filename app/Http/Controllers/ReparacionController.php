<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Reparacion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReparacionController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver reparaciones')->only(['index', 'show']);
        $this->middleware('can:crear reparaciones')->only(['create', 'store']);
        $this->middleware('can:editar reparaciones')->only(['edit', 'update']);
        $this->middleware('can:cambiar estado reparaciones')->only(['cambiarEstado']);
    }

    /**
     * Listar reparaciones
     */
    public function index(Request $request)
    {
        $query = Reparacion::with(['cliente', 'tecnico']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            $query->whereNotIn('estado', ['entregado', 'cancelado']);
        }

        if ($request->filled('tecnico_id')) {
            $query->where('tecnico_id', $request->tecnico_id);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('orden', 'like', "%{$search}%")
                    ->orWhere('dispositivo_marca', 'like', "%{$search}%")
                    ->orWhere('dispositivo_modelo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($qc) use ($search) {
                        $qc->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_recepcion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_recepcion', '<=', $request->fecha_hasta);
        }

        $reparaciones = $query->latest('fecha_recepcion')->paginate(20);
        $tecnicos = User::role('tecnico')->active()->get();
        $estados = Reparacion::ESTADOS;

        $pendientes = Reparacion::pendientes()->count();
        $listas = Reparacion::listas()->count();
        $hoy = Reparacion::hoy()->count();

        return view('reparaciones.index', compact(
            'reparaciones',
            'tecnicos',
            'estados',
            'pendientes',
            'listas',
            'hoy'
        ));
    }

    /**
     * Mostrar formulario de creacion
     */
    public function create()
    {
        $clientes = Cliente::active()->get();
        $tecnicos = User::role('tecnico')->active()->get();
        $garantiaDefault = Configuracion::get('reparaciones.garantia_default', 30);
        $diasEstimados = Configuracion::get('reparaciones.dias_estimados_default', 3);

        return view('reparaciones.create', compact(
            'clientes',
            'tecnicos',
            'garantiaDefault',
            'diasEstimados'
        ));
    }

    /**
     * Guardar reparacion
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'dispositivo_tipo' => 'required|string|max:50',
            'dispositivo_marca' => 'required|string|max:100',
            'dispositivo_modelo' => 'required|string|max:100',
            'dispositivo_color' => 'nullable|string|max:50',
            'dispositivo_imei' => 'nullable|string|max:50',
            'dispositivo_serial' => 'nullable|string|max:50',
            'dispositivo_contrasena' => 'nullable|string|max:100',
            'problema_reportado' => 'required|string',
            'costo_estimado' => 'nullable|numeric|min:0',
            'adelanto' => 'nullable|numeric|min:0',
            'fecha_estimada_entrega' => 'nullable|date',
            'fecha_inicio_mora' => 'nullable|date',
            'garantia_dias' => 'nullable|integer|min:0',
            'accesorios_incluidos' => 'nullable|string',
            'condiciones_previas' => 'nullable|string',
            'notas_cliente' => 'nullable|string',
            'mora_observaciones' => 'nullable|string',
            'foto_antes_1' => 'nullable|image|max:2048',
            'foto_antes_2' => 'nullable|image|max:2048',
            'foto_antes_3' => 'nullable|image|max:2048',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $field = "foto_antes_{$i}";
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('reparaciones/fotos', 'public');
            }
        }

        $validated['user_id'] = auth()->id();
        $validated['estado'] = 'recibido';

        $reparacion = Reparacion::create($validated);

        \App\Models\ReparacionHistorial::create([
            'reparacion_id' => $reparacion->id,
            'user_id' => auth()->id(),
            'estado_anterior' => 'recibido',
            'estado_nuevo' => 'recibido',
            'comentario' => 'Orden creada',
        ]);

        if ((float) $reparacion->adelanto > 0) {
            $reparacion->moraAbonos()->create([
                'cliente_id' => $reparacion->cliente_id,
                'user_id' => auth()->id(),
                'tipo' => 'abono',
                'monto' => (float) $reparacion->adelanto,
                'metodo_pago' => 'inicial',
                'origen' => 'reparacion_create',
                'fecha_pago' => now(),
                'notas' => 'Adelanto inicial registrado al crear la reparacion.',
            ]);
        }

        return redirect()->route('reparaciones.show', $reparacion)
            ->with('success', 'Reparacion creada correctamente. Orden: ' . $reparacion->orden);
    }

    /**
     * Mostrar reparacion
     */
    public function show(Reparacion $reparacion)
    {
        $reparacion->load(['cliente', 'usuario', 'tecnico', 'historial.usuario']);

        $tecnicos = User::role('tecnico')->active()->get();
        $estados = Reparacion::ESTADOS;

        return view('reparaciones.show', compact('reparacion', 'tecnicos', 'estados'));
    }

    /**
     * Mostrar formulario de edicion
     */
    public function edit(Reparacion $reparacion)
    {
        $clientes = Cliente::active()->get();
        $tecnicos = User::role('tecnico')->active()->get();

        return view('reparaciones.edit', compact('reparacion', 'clientes', 'tecnicos'));
    }

    /**
     * Actualizar reparacion
     */
    public function update(Request $request, Reparacion $reparacion)
    {
        $adelantoAnterior = (float) $reparacion->adelanto;

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'dispositivo_tipo' => 'required|string|max:50',
            'dispositivo_marca' => 'required|string|max:100',
            'dispositivo_modelo' => 'required|string|max:100',
            'dispositivo_color' => 'nullable|string|max:50',
            'dispositivo_imei' => 'nullable|string|max:50',
            'dispositivo_serial' => 'nullable|string|max:50',
            'dispositivo_contrasena' => 'nullable|string|max:100',
            'problema_reportado' => 'required|string',
            'diagnostico' => 'nullable|string',
            'solucion' => 'nullable|string',
            'costo_estimado' => 'nullable|numeric|min:0',
            'costo_final' => 'nullable|numeric|min:0',
            'adelanto' => 'nullable|numeric|min:0',
            'fecha_estimada_entrega' => 'nullable|date',
            'fecha_inicio_mora' => 'nullable|date',
            'garantia_dias' => 'nullable|integer|min:0',
            'accesorios_incluidos' => 'nullable|string',
            'condiciones_previas' => 'nullable|string',
            'notas_cliente' => 'nullable|string',
            'notas_tecnico' => 'nullable|string',
            'mora_observaciones' => 'nullable|string',
        ]);

        $reparacion->update($validated);

        $adelantoActual = (float) $reparacion->adelanto;
        $diferenciaAbono = round($adelantoActual - $adelantoAnterior, 2);

        if ($diferenciaAbono !== 0.0) {
            $reparacion->moraAbonos()->create([
                'cliente_id' => $reparacion->cliente_id,
                'user_id' => auth()->id(),
                'tipo' => $diferenciaAbono > 0 ? 'abono' : 'ajuste',
                'monto' => $diferenciaAbono,
                'metodo_pago' => 'ajuste',
                'origen' => 'reparacion_edit',
                'fecha_pago' => now(),
                'notas' => $diferenciaAbono > 0
                    ? 'Abono agregado desde la edicion de la reparacion.'
                    : 'Ajuste correctivo del adelanto desde la edicion de la reparacion.',
            ]);
        }

        return redirect()->route('reparaciones.show', $reparacion)
            ->with('success', 'Reparacion actualizada correctamente.');
    }

    /**
     * Cambiar estado de reparacion
     */
    public function cambiarEstado(Request $request, Reparacion $reparacion)
    {
        $validated = $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Reparacion::ESTADOS)),
            'comentario' => 'nullable|string',
        ]);

        $reparacion->cambiarEstado($validated['estado'], $validated['comentario'] ?? null);

        return back()->with('success', 'Estado actualizado a: ' . Reparacion::ESTADOS[$validated['estado']]);
    }

    /**
     * Subir fotos despues
     */
    public function subirFotosDespues(Request $request, Reparacion $reparacion)
    {
        $request->validate([
            'foto_despues_1' => 'nullable|image|max:2048',
            'foto_despues_2' => 'nullable|image|max:2048',
            'foto_despues_3' => 'nullable|image|max:2048',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $field = "foto_despues_{$i}";
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('reparaciones/fotos', 'public');
                $reparacion->update([$field => $path]);
            }
        }

        return back()->with('success', 'Fotos subidas correctamente.');
    }

    /**
     * Notificar al cliente
     */
    public function notificar(Reparacion $reparacion)
    {
        if (! $reparacion->cliente->telefono) {
            return back()->with('error', 'El cliente no tiene telefono registrado.');
        }

        $reparacion->marcarNotificado();

        return back()->with('success', 'Cliente notificado correctamente.');
    }

    /**
     * Imprimir orden
     */
    public function imprimirOrden(Reparacion $reparacion)
    {
        $reparacion->load(['cliente', 'usuario', 'tecnico']);

        $empresa = [
            'nombre' => Configuracion::get('empresa.nombre', 'CellFix Pro'),
            'direccion' => Configuracion::get('empresa.direccion', ''),
            'telefono' => Configuracion::get('empresa.telefono', ''),
            'rfc' => Configuracion::get('empresa.rfc', ''),
        ];

        $pdf = Pdf::loadView('reparaciones.orden', compact('reparacion', 'empresa'));

        return $pdf->stream("orden-{$reparacion->orden}.pdf");
    }

    /**
     * Imprimir ticket de entrega
     */
    public function ticketEntrega(Reparacion $reparacion)
    {
        $reparacion->load(['cliente', 'usuario', 'tecnico']);

        $empresa = [
            'nombre' => Configuracion::get('empresa.nombre', 'CellFix Pro'),
            'direccion' => Configuracion::get('empresa.direccion', ''),
            'telefono' => Configuracion::get('empresa.telefono', ''),
            'rfc' => Configuracion::get('empresa.rfc', ''),
        ];

        $pdf = Pdf::loadView('reparaciones.ticket', compact('reparacion', 'empresa'));
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream("ticket-{$reparacion->orden}.pdf");
    }

    /**
     * Panel del tecnico
     */
    public function panelTecnico()
    {
        $tecnicoId = auth()->id();

        $reparaciones = Reparacion::with('cliente')
            ->where('tecnico_id', $tecnicoId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->latest('fecha_recepcion')
            ->paginate(20);

        $misReparacionesMes = Reparacion::where('tecnico_id', $tecnicoId)
            ->whereMonth('fecha_recepcion', now()->month)
            ->whereYear('fecha_recepcion', now()->year)
            ->count();

        $reparacionesCompletadas = Reparacion::where('tecnico_id', $tecnicoId)
            ->where('estado', 'entregado')
            ->whereMonth('fecha_entrega', now()->month)
            ->whereYear('fecha_entrega', now()->year)
            ->count();

        $stats = [
            'pendientes' => Reparacion::where('tecnico_id', $tecnicoId)
                ->whereIn('estado', ['recibido', 'espera_repuesto'])
                ->count(),
            'diagnostico' => Reparacion::where('tecnico_id', $tecnicoId)
                ->where('estado', 'en_diagnostico')
                ->count(),
            'reparacion' => Reparacion::where('tecnico_id', $tecnicoId)
                ->where('estado', 'en_reparacion')
                ->count(),
            'completadas_hoy' => Reparacion::where('tecnico_id', $tecnicoId)
                ->where('estado', 'entregado')
                ->whereDate('fecha_entrega', today())
                ->count(),
        ];

        $completadas = Reparacion::with('cliente')
            ->where('tecnico_id', $tecnicoId)
            ->where('estado', 'entregado')
            ->latest('fecha_entrega')
            ->limit(10)
            ->get();

        $reparacionesEntregadasMes = Reparacion::where('tecnico_id', $tecnicoId)
            ->where('estado', 'entregado')
            ->whereMonth('fecha_entrega', now()->month)
            ->whereYear('fecha_entrega', now()->year)
            ->get();

        $miStats = [
            'reparaciones_mes' => $misReparacionesMes,
            'promedio_dia' => round($misReparacionesMes / max(1, now()->day), 1),
            'tiempo_promedio' => round(
                $reparacionesEntregadasMes
                    ->filter(fn ($reparacion) => $reparacion->fecha_recepcion && $reparacion->fecha_entrega)
                    ->map(fn ($reparacion) => $reparacion->fecha_recepcion->diffInHours($reparacion->fecha_entrega))
                    ->avg() ?? 0
            ),
            'tasa_exito' => $misReparacionesMes > 0
                ? round(($reparacionesCompletadas / $misReparacionesMes) * 100, 1)
                : 0,
        ];

        $prioritarias = Reparacion::with('cliente')
            ->where('tecnico_id', $tecnicoId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->whereDate('fecha_recepcion', '<=', now()->subDays(3))
            ->oldest('fecha_recepcion')
            ->limit(5)
            ->get();

        return view('reparaciones.panel-tecnico', compact(
            'reparaciones',
            'stats',
            'completadas',
            'miStats',
            'prioritarias',
            'misReparacionesMes',
            'reparacionesCompletadas'
        ));
    }

    /**
     * Estadisticas de reparaciones
     */
    public function estadisticas(Request $request)
    {
        $fechaInicio = $request->filled('fecha_inicio')
            ? \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay()
            : now()->startOfMonth();
        $fechaFin = $request->filled('fecha_fin')
            ? \Carbon\Carbon::parse($request->fecha_fin)->endOfDay()
            : now()->endOfDay();

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $reparaciones = Reparacion::with(['cliente', 'tecnico'])
            ->whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->get();

        $estadosCompletados = ['reparado', 'listo', 'entregado'];
        $estadosEnProceso = ['recibido', 'en_diagnostico', 'espera_repuesto', 'en_reparacion'];

        $stats = [
            'total' => $reparaciones->count(),
            'completadas' => $reparaciones->whereIn('estado', $estadosCompletados)->count(),
            'en_proceso' => $reparaciones->whereIn('estado', $estadosEnProceso)->count(),
            'ingresos' => $reparaciones->where('estado', 'entregado')->sum('costo_final'),
            'tiempo_promedio' => round(
                $reparaciones->where('estado', 'entregado')
                    ->filter(fn ($reparacion) => $reparacion->fecha_recepcion && $reparacion->fecha_entrega)
                    ->map(fn ($reparacion) => $reparacion->fecha_recepcion->diffInHours($reparacion->fecha_entrega))
                    ->avg() ?? 0
            ),
        ];

        $estados = $reparaciones
            ->groupBy('estado')
            ->map(function ($group, $estadoKey) {
                return (object) [
                    'estado' => Reparacion::ESTADOS[$estadoKey] ?? $estadoKey,
                    'total' => $group->count(),
                    'color' => Reparacion::ESTADO_COLORES[$estadoKey] ?? 'gray',
                ];
            })
            ->values();

        $diario = $reparaciones
            ->groupBy(fn ($reparacion) => optional($reparacion->fecha_recepcion)->format('Y-m-d'))
            ->map(function ($group, $fecha) {
                return (object) [
                    'fecha' => $fecha,
                    'total' => $group->count(),
                ];
            })
            ->sortBy('fecha')
            ->values();

        $marcas = $reparaciones
            ->groupBy(fn ($reparacion) => $reparacion->dispositivo_marca ?: 'Sin marca')
            ->map(function ($group, $marca) {
                return (object) [
                    'marca' => $marca,
                    'total' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $fallas = $reparaciones
            ->groupBy(fn ($reparacion) => $reparacion->problema_reportado ?: 'Sin especificar')
            ->map(function ($group, $falla) {
                return (object) [
                    'tipo_falla' => $falla,
                    'total' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $tecnicos = $reparaciones
            ->whereNotNull('tecnico_id')
            ->filter(fn ($reparacion) => $reparacion->tecnico)
            ->groupBy('tecnico_id')
            ->map(function ($group) use ($estadosCompletados, $estadosEnProceso) {
                return (object) [
                    'name' => $group->first()->tecnico->name,
                    'total_reparaciones' => $group->count(),
                    'completadas' => $group->whereIn('estado', $estadosCompletados)->count(),
                    'en_proceso' => $group->whereIn('estado', $estadosEnProceso)->count(),
                    'tiempo_promedio' => round(
                        $group->where('estado', 'entregado')
                            ->filter(fn ($reparacion) => $reparacion->fecha_recepcion && $reparacion->fecha_entrega)
                            ->map(fn ($reparacion) => $reparacion->fecha_recepcion->diffInHours($reparacion->fecha_entrega))
                            ->avg() ?? 0
                    ),
                    'ingresos' => $group->where('estado', 'entregado')->sum('costo_final'),
                ];
            })
            ->sortByDesc('total_reparaciones')
            ->values();

        $ingresos = $reparaciones
            ->where('estado', 'entregado')
            ->filter(fn ($reparacion) => $reparacion->fecha_entrega)
            ->groupBy(fn ($reparacion) => $reparacion->fecha_entrega->format('Y-m-d'))
            ->map(function ($group, $fecha) {
                return (object) [
                    'fecha' => $fecha,
                    'total' => $group->sum('costo_final'),
                ];
            })
            ->sortBy('fecha')
            ->values();

        return view('reparaciones.estadisticas', compact(
            'fechaInicio',
            'fechaFin',
            'stats',
            'estados',
            'diario',
            'marcas',
            'fallas',
            'tecnicos',
            'ingresos'
        ));
    }
}
