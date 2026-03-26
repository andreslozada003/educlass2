<?php

namespace App\Http\Controllers;

use App\Exports\ReporteInventarioExport;
use App\Exports\ReporteReparacionesExport;
use App\Exports\ReporteVentasExport;
use App\Models\Categoria;
use App\Models\Expense;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver reportes');
    }

    /**
     * Dashboard de reportes
     */
    public function index()
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $ventasMes = Venta::with('detalles.producto')
            ->where('estado', 'pagada')
            ->whereBetween('fecha_venta', [$inicioMes, $finMes])
            ->get();

        $reparacionesMes = Reparacion::where('estado', 'entregado')
            ->whereBetween('fecha_entrega', [$inicioMes, $finMes])
            ->get();

        $costosVentas = $ventasMes->sum(function ($venta) {
            return $venta->detalles->sum(function ($detalle) {
                return ($detalle->producto->precio_compra ?? 0) * $detalle->cantidad;
            });
        });

        $costosReparaciones = $reparacionesMes->sum(fn ($reparacion) => $reparacion->costo_estimado ?? 0);
        $gastosMes = Expense::active()
            ->where('payment_status', '!=', 'cancelled')
            ->whereBetween('expense_date', [$inicioMes, $finMes])
            ->sum('amount');

        $resumen = [
            'ventas_hoy' => Venta::where('estado', 'pagada')
                ->whereDate('fecha_venta', today())
                ->sum('total'),
            'reparaciones_hoy' => Reparacion::whereDate('fecha_recepcion', today())->count(),
            'ganancia' => $ventasMes->sum('total')
                + $reparacionesMes->sum('costo_final')
                - $costosVentas
                - $costosReparaciones
                - $gastosMes,
            'stock_bajo' => Producto::stockBajo()->count(),
            'gastos_mes' => $gastosMes,
        ];

        return view('reportes.index', compact('resumen'));
    }

    /**
     * Reporte de ventas
     */
    public function ventas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');

        [$fechaInicio, $fechaFin] = match ($periodo) {
            'hoy' => [now()->startOfDay(), now()->endOfDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes' => [now()->startOfMonth(), now()->endOfMonth()],
            'año', 'anio', 'ano' => [now()->startOfYear(), now()->endOfYear()],
            'personalizado' => [
                Carbon::parse($request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay(),
                Carbon::parse($request->get('fecha_fin', now()->format('Y-m-d')))->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
            $periodo = 'personalizado';
        }

        $baseQuery = Venta::with(['cliente', 'usuario', 'detalles'])
            ->where('estado', 'pagada')
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);

        $ventas = (clone $baseQuery)
            ->latest('fecha_venta')
            ->paginate(20)
            ->withQueryString();

        $ventasCollection = (clone $baseQuery)->get();

        $stats = [
            'total_ventas' => $ventasCollection->count(),
            'ingresos' => $ventasCollection->sum('total'),
            'ticket_promedio' => $ventasCollection->avg('total') ?? 0,
            'productos_vendidos' => $ventasCollection->sum(
                fn ($venta) => $venta->detalles->sum('cantidad')
            ),
        ];

        $ventasPorDia = $ventasCollection
            ->filter(fn ($venta) => $venta->fecha_venta)
            ->groupBy(fn ($venta) => $venta->fecha_venta->format('Y-m-d'))
            ->map(function ($group) {
                return [
                    'fecha' => $group->first()->fecha_venta->format('Y-m-d'),
                    'total' => $group->sum('total'),
                    'cantidad' => $group->count(),
                ];
            })
            ->values();

        $metodosPago = $ventasCollection
            ->groupBy('metodo_pago')
            ->map(function ($group) {
                return [
                    'metodo_pago' => $group->first()->metodo_pago,
                    'total' => $group->sum('total'),
                    'cantidad' => $group->count(),
                ];
            })
            ->values();

        $topProductos = DB::table('venta_detalles')
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad'),
                DB::raw('SUM(venta_detalles.subtotal) as total')
            )
            ->where('ventas.estado', 'pagada')
            ->whereBetween('ventas.fecha_venta', [$fechaInicio, $fechaFin])
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('cantidad')
            ->limit(10)
            ->get();

        $ventasPorVendedor = $ventasCollection
            ->filter(fn ($venta) => $venta->usuario)
            ->groupBy('user_id')
            ->map(function ($group) {
                return (object) [
                    'name' => $group->first()->usuario->name,
                    'total_ventas' => $group->count(),
                    'total' => $group->sum('total'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return view('reportes.ventas', compact(
            'periodo',
            'fechaInicio',
            'fechaFin',
            'stats',
            'ventas',
            'ventasPorDia',
            'metodosPago',
            'topProductos',
            'ventasPorVendedor'
        ));
    }

    /**
     * Reporte de reparaciones
     */
    public function reparaciones(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');
        $estado = $request->get('estado');

        [$fechaInicio, $fechaFin] = match ($periodo) {
            'hoy' => [now()->startOfDay(), now()->endOfDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes' => [now()->startOfMonth(), now()->endOfMonth()],
            'aÃ±o', 'anio', 'ano' => [now()->startOfYear(), now()->endOfYear()],
            'personalizado' => [
                Carbon::parse($request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay(),
                Carbon::parse($request->get('fecha_fin', now()->format('Y-m-d')))->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
            $periodo = 'personalizado';
        }

        $baseQuery = Reparacion::with(['cliente', 'tecnico'])
            ->whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->when($estado, function ($query) use ($estado) {
                $query->where('estado', $estado);
            });

        $reparaciones = (clone $baseQuery)
            ->latest('fecha_recepcion')
            ->paginate(20)
            ->withQueryString();

        $reparacionesCollection = (clone $baseQuery)->get();

        $estadosCompletados = ['reparado', 'listo', 'entregado'];
        $estadosEnProceso = ['recibido', 'en_diagnostico', 'espera_repuesto', 'en_reparacion'];

        $stats = [
            'total' => $reparacionesCollection->count(),
            'completadas' => $reparacionesCollection->whereIn('estado', $estadosCompletados)->count(),
            'en_proceso' => $reparacionesCollection->whereIn('estado', $estadosEnProceso)->count(),
            'ingresos' => $reparacionesCollection->where('estado', 'entregado')->sum('costo_final'),
            'tiempo_promedio' => round(
                $reparacionesCollection
                    ->where('estado', 'entregado')
                    ->filter(fn ($reparacion) => $reparacion->fecha_recepcion && $reparacion->fecha_entrega)
                    ->map(fn ($reparacion) => $reparacion->fecha_recepcion->diffInHours($reparacion->fecha_entrega))
                    ->avg() ?? 0
            ),
        ];

        $estados = $reparacionesCollection
            ->groupBy('estado')
            ->map(function ($group, $estadoKey) {
                return (object) [
                    'estado' => Reparacion::ESTADOS[$estadoKey] ?? $estadoKey,
                    'total' => $group->count(),
                    'color' => Reparacion::ESTADO_COLORES[$estadoKey] ?? 'gray',
                ];
            })
            ->values();

        $diario = $reparacionesCollection
            ->groupBy(fn ($reparacion) => $reparacion->fecha_recepcion?->format('Y-m-d'))
            ->map(function ($group, $fecha) {
                return (object) [
                    'fecha' => $fecha,
                    'total' => $group->count(),
                ];
            })
            ->sortBy('fecha')
            ->values();

        $marcas = $reparacionesCollection
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

        $fallas = $reparacionesCollection
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

        $tecnicos = $reparacionesCollection
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

        return view('reportes.reparaciones', compact(
            'periodo',
            'fechaInicio',
            'fechaFin',
            'estado',
            'stats',
            'estados',
            'diario',
            'marcas',
            'fallas',
            'tecnicos',
            'reparaciones'
        ));
    }

    /**
     * Reporte de inventario
     */
    public function inventario(Request $request)
    {
        $categoriaId = $request->integer('categoria_id') ?: null;
        $estadoStock = $request->get('estado_stock');

        $categorias = Categoria::active()
            ->orderBy('nombre')
            ->get();

        $productosQuery = Producto::with('categoria')
            ->where('activo', true)
            ->when($categoriaId, function ($query) use ($categoriaId) {
                $query->where('categoria_id', $categoriaId);
            })
            ->when($estadoStock === 'bajo', function ($query) {
                $query->where('es_servicio', false)
                    ->where('stock', '>', 0)
                    ->whereColumn('stock', '<=', 'stock_minimo');
            })
            ->when($estadoStock === 'agotado', function ($query) {
                $query->where('es_servicio', false)
                    ->where('stock', 0);
            })
            ->when($estadoStock === 'disponible', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('es_servicio', true)
                        ->orWhereColumn('stock', '>', 'stock_minimo');
                });
            });

        $productosCollection = (clone $productosQuery)
            ->orderBy('nombre')
            ->get();

        $stats = [
            'total_productos' => $productosCollection->count(),
            'valor_inventario' => $productosCollection->sum(fn ($producto) => $producto->valor_inventario),
            'stock_bajo' => $productosCollection->where('stock_bajo', true)->count(),
            'agotados' => $productosCollection->where('es_servicio', false)->where('stock', 0)->count(),
            'total_unidades' => $productosCollection->sum('stock'),
        ];

        $productosPorCategoria = $productosCollection
            ->groupBy(fn ($producto) => $producto->categoria->nombre ?? 'Sin categoría')
            ->map(function ($group, $nombre) {
                return [
                    'nombre' => $nombre,
                    'total' => $group->count(),
                    'valor' => $group->sum(fn ($producto) => $producto->valor_inventario),
                ];
            })
            ->values();

        $valorPorCategoria = $productosPorCategoria
            ->map(fn ($item) => [
                'nombre' => $item['nombre'],
                'valor' => $item['valor'],
            ])
            ->values();

        $movimientos = InventarioMovimiento::with(['producto', 'usuario'])
            ->when($categoriaId, function ($query) use ($categoriaId) {
                $query->whereHas('producto', function ($productoQuery) use ($categoriaId) {
                    $productoQuery->where('categoria_id', $categoriaId);
                });
            })
            ->latest()
            ->limit(50)
            ->get();

        $productos = (clone $productosQuery)
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('reportes.inventario', compact(
            'categorias',
            'categoriaId',
            'estadoStock',
            'stats',
            'productosPorCategoria',
            'valorPorCategoria',
            'movimientos',
            'productos'
        ));
    }

    /**
     * Reporte financiero
     */
    public function financiero(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');

        [$fechaInicio, $fechaFin] = match ($periodo) {
            'hoy' => [now()->startOfDay(), now()->endOfDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes' => [now()->startOfMonth(), now()->endOfMonth()],
            'aÃ±o', 'anio', 'ano' => [now()->startOfYear(), now()->endOfYear()],
            'personalizado' => [
                Carbon::parse($request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay(),
                Carbon::parse($request->get('fecha_fin', now()->format('Y-m-d')))->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
            $periodo = 'personalizado';
        }

        $ventas = Venta::with(['detalles.producto.categoria'])
            ->where('estado', 'pagada')
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->get();

        $reparaciones = Reparacion::with('tecnico')
            ->where('estado', 'entregado')
            ->whereBetween('fecha_entrega', [$fechaInicio, $fechaFin])
            ->get();

        $costosVentas = $ventas->sum(function ($venta) {
            return $venta->detalles->sum(function ($detalle) {
                return ($detalle->producto->precio_compra ?? 0) * $detalle->cantidad;
            });
        });

        $costosReparaciones = $reparaciones->sum(fn ($reparacion) => $reparacion->costo_estimado ?? 0);
        $ingresosTotales = $ventas->sum('total') + $reparaciones->sum('costo_final');
        $costosTotales = $costosVentas + $costosReparaciones;
        $gananciaBruta = $ingresosTotales - $costosTotales;
        $totalOperaciones = $ventas->count() + $reparaciones->count();

        $stats = [
            'ingresos_ventas' => $ventas->sum('total'),
            'ingresos_reparaciones' => $reparaciones->sum('costo_final'),
            'costos' => $costosVentas,
            'costos_reparaciones' => $costosReparaciones,
            'ganancia_bruta' => $gananciaBruta,
            'margen' => $ingresosTotales > 0 ? ($gananciaBruta / $ingresosTotales) * 100 : 0,
            'total_ventas' => $ventas->count(),
            'total_reparaciones' => $reparaciones->count(),
            'ticket_promedio' => $totalOperaciones > 0 ? $ingresosTotales / $totalOperaciones : 0,
        ];

        $metodosPago = $ventas
            ->groupBy(fn ($venta) => $venta->metodo_pago ?: 'Sin definir')
            ->map(function ($group, $metodoPago) {
                return (object) [
                    'metodo_pago' => $metodoPago,
                    'total' => $group->count(),
                    'monto' => $group->sum('total'),
                ];
            })
            ->sortByDesc('monto')
            ->values();

        $ingresosPorCategoria = $ventas
            ->flatMap(fn ($venta) => $venta->detalles)
            ->groupBy(fn ($detalle) => $detalle->producto->categoria?->nombre ?? 'Sin categoria')
            ->map(function ($group, $nombre) {
                return (object) [
                    'nombre' => $nombre,
                    'total' => $group->sum('subtotal'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        if ($reparaciones->sum('costo_final') > 0) {
            $ingresosPorCategoria->push((object) [
                'nombre' => 'Reparaciones',
                'total' => $reparaciones->sum('costo_final'),
            ]);
        }

        $ventasPorDia = $ventas
            ->groupBy(fn ($venta) => $venta->fecha_venta->format('Y-m-d'))
            ->map(function ($group) {
                return [
                    'ingresos' => $group->sum('total'),
                    'costos' => $group->sum(function ($venta) {
                        return $venta->detalles->sum(function ($detalle) {
                            return ($detalle->producto->precio_compra ?? 0) * $detalle->cantidad;
                        });
                    }),
                ];
            });

        $reparacionesPorDia = $reparaciones
            ->groupBy(fn ($reparacion) => $reparacion->fecha_entrega?->format('Y-m-d'))
            ->map(function ($group) {
                return [
                    'ingresos' => $group->sum('costo_final'),
                    'costos' => $group->sum(fn ($reparacion) => $reparacion->costo_estimado ?? 0),
                ];
            });

        $evolucion = collect();
        $cursor = $fechaInicio->copy()->startOfDay();

        while ($cursor->lte($fechaFin)) {
            $clave = $cursor->format('Y-m-d');
            $ventasDia = $ventasPorDia->get($clave, ['ingresos' => 0, 'costos' => 0]);
            $reparacionesDia = $reparacionesPorDia->get($clave, ['ingresos' => 0, 'costos' => 0]);

            $evolucion->push((object) [
                'fecha' => $clave,
                'ingresos' => $ventasDia['ingresos'] + $reparacionesDia['ingresos'],
                'costos' => $ventasDia['costos'] + $reparacionesDia['costos'],
            ]);

            $cursor->addDay();
        }

        $comparativoMensual = collect();
        $meses = $ventas->pluck('fecha_venta')
            ->filter()
            ->map(fn ($fecha) => $fecha->format('Y-m'))
            ->merge(
                $reparaciones->pluck('fecha_entrega')
                    ->filter()
                    ->map(fn ($fecha) => $fecha->format('Y-m'))
            )
            ->unique()
            ->sort()
            ->values();

        foreach ($meses as $mes) {
            $ventasMes = $ventas->filter(fn ($venta) => $venta->fecha_venta->format('Y-m') === $mes);
            $reparacionesMes = $reparaciones->filter(fn ($reparacion) => $reparacion->fecha_entrega?->format('Y-m') === $mes);
            $ingresosMes = $ventasMes->sum('total') + $reparacionesMes->sum('costo_final');
            $costosMes = $ventasMes->sum(function ($venta) {
                return $venta->detalles->sum(function ($detalle) {
                    return ($detalle->producto->precio_compra ?? 0) * $detalle->cantidad;
                });
            }) + $reparacionesMes->sum(fn ($reparacion) => $reparacion->costo_estimado ?? 0);

            $comparativoMensual->push((object) [
                'mes' => Carbon::createFromFormat('Y-m', $mes)->format('m/Y'),
                'ingresos' => $ingresosMes,
                'costos' => $costosMes,
                'ganancia' => $ingresosMes - $costosMes,
            ]);
        }

        return view('reportes.financiero', compact(
            'periodo',
            'fechaInicio',
            'fechaFin',
            'stats',
            'metodosPago',
            'ingresosPorCategoria',
            'evolucion',
            'comparativoMensual'
        ));
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportarPdf(Request $request, $tipo)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $data = [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'generado' => now(),
        ];

        switch ($tipo) {
            case 'ventas':
                $data['ventas'] = Venta::where('estado', 'pagada')
                    ->whereBetween('fecha_venta', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
                    ->get();
                $pdf = PDF::loadView('reportes.pdf.ventas', $data);
                break;

            case 'reparaciones':
                $periodo = $request->get('periodo', 'mes');
                $estado = $request->get('estado');

                [$fechaInicioReparaciones, $fechaFinReparaciones] = match ($periodo) {
                    'hoy' => [now()->startOfDay(), now()->endOfDay()],
                    'semana' => [now()->startOfWeek(), now()->endOfWeek()],
                    'mes' => [now()->startOfMonth(), now()->endOfMonth()],
                    'aÃ±o', 'anio', 'ano' => [now()->startOfYear(), now()->endOfYear()],
                    'personalizado' => [
                        Carbon::parse($request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay(),
                        Carbon::parse($request->get('fecha_fin', now()->format('Y-m-d')))->endOfDay(),
                    ],
                    default => [now()->startOfMonth(), now()->endOfMonth()],
                };

                $data['fechaInicio'] = $fechaInicioReparaciones->format('Y-m-d');
                $data['fechaFin'] = $fechaFinReparaciones->format('Y-m-d');
                $data['reparaciones'] = Reparacion::with(['cliente', 'tecnico'])
                    ->whereBetween('fecha_recepcion', [$fechaInicioReparaciones, $fechaFinReparaciones])
                    ->when($estado, function ($query) use ($estado) {
                        $query->where('estado', $estado);
                    })
                    ->latest('fecha_recepcion')
                    ->get();
                $pdf = PDF::loadView('reportes.pdf.reparaciones', $data);
                break;

            case 'inventario':
                $data['productos'] = Producto::with('categoria')
                    ->where('activo', true)
                    ->when($request->integer('categoria_id'), function ($query) use ($request) {
                        $query->where('categoria_id', $request->integer('categoria_id'));
                    })
                    ->when($request->get('estado_stock') === 'bajo', function ($query) {
                        $query->where('es_servicio', false)
                            ->where('stock', '>', 0)
                            ->whereColumn('stock', '<=', 'stock_minimo');
                    })
                    ->when($request->get('estado_stock') === 'agotado', function ($query) {
                        $query->where('es_servicio', false)
                            ->where('stock', 0);
                    })
                    ->when($request->get('estado_stock') === 'disponible', function ($query) {
                        $query->where(function ($subQuery) {
                            $subQuery->where('es_servicio', true)
                                ->orWhereColumn('stock', '>', 'stock_minimo');
                        });
                    })
                    ->orderBy('nombre')
                    ->get();
                $pdf = PDF::loadView('reportes.pdf.inventario', $data);
                break;

            case 'financiero':
                $periodo = $request->get('periodo', 'mes');

                [$fechaInicioFinanciero, $fechaFinFinanciero] = match ($periodo) {
                    'hoy' => [now()->startOfDay(), now()->endOfDay()],
                    'semana' => [now()->startOfWeek(), now()->endOfWeek()],
                    'mes' => [now()->startOfMonth(), now()->endOfMonth()],
                    'aÃ±o', 'anio', 'ano' => [now()->startOfYear(), now()->endOfYear()],
                    'personalizado' => [
                        Carbon::parse($request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay(),
                        Carbon::parse($request->get('fecha_fin', now()->format('Y-m-d')))->endOfDay(),
                    ],
                    default => [now()->startOfMonth(), now()->endOfMonth()],
                };

                $ventasFinancieras = Venta::with('detalles.producto')
                    ->where('estado', 'pagada')
                    ->whereBetween('fecha_venta', [$fechaInicioFinanciero, $fechaFinFinanciero])
                    ->get();

                $reparacionesFinancieras = Reparacion::where('estado', 'entregado')
                    ->whereBetween('fecha_entrega', [$fechaInicioFinanciero, $fechaFinFinanciero])
                    ->get();

                $data['fechaInicio'] = $fechaInicioFinanciero->format('Y-m-d');
                $data['fechaFin'] = $fechaFinFinanciero->format('Y-m-d');
                $data['stats'] = [
                    'ingresos_ventas' => $ventasFinancieras->sum('total'),
                    'ingresos_reparaciones' => $reparacionesFinancieras->sum('costo_final'),
                    'costos' => $ventasFinancieras->sum(function ($venta) {
                        return $venta->detalles->sum(function ($detalle) {
                            return ($detalle->producto->precio_compra ?? 0) * $detalle->cantidad;
                        });
                    }),
                    'costos_reparaciones' => $reparacionesFinancieras->sum(fn ($reparacion) => $reparacion->costo_estimado ?? 0),
                ];
                $data['stats']['ganancia_bruta'] = $data['stats']['ingresos_ventas']
                    + $data['stats']['ingresos_reparaciones']
                    - $data['stats']['costos']
                    - $data['stats']['costos_reparaciones'];
                $data['stats']['margen'] = ($data['stats']['ingresos_ventas'] + $data['stats']['ingresos_reparaciones']) > 0
                    ? ($data['stats']['ganancia_bruta'] / ($data['stats']['ingresos_ventas'] + $data['stats']['ingresos_reparaciones'])) * 100
                    : 0;

                $pdf = PDF::loadView('reportes.pdf.financiero', $data);
                break;

            default:
                abort(404);
        }

        return $pdf->download("reporte-{$tipo}-{$fechaInicio}-al-{$fechaFin}.pdf");
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel(Request $request, $tipo)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $filename = "reporte-{$tipo}-{$fechaInicio}-al-{$fechaFin}.xlsx";

        return match ($tipo) {
            'ventas' => Excel::download(new ReporteVentasExport($fechaInicio, $fechaFin), $filename),
            'reparaciones' => Excel::download(new ReporteReparacionesExport($fechaInicio, $fechaFin), $filename),
            'inventario' => Excel::download(new ReporteInventarioExport(), $filename),
            default => abort(404),
        };
    }
}
