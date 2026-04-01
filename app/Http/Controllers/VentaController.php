<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\Facturacion\FacturaElectronicaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver ventas')->only(['index', 'show']);
        $this->middleware('can:crear ventas')->only(['create', 'store', 'pos']);
        $this->middleware('can:cancelar ventas')->only(['cancelar']);
    }

    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'usuario']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('folio', 'like', "%{$request->search}%")
                    ->orWhereHas('cliente', function ($qc) use ($request) {
                        $qc->where('nombre', 'like', "%{$request->search}%")
                            ->orWhere('apellido', 'like', "%{$request->search}%");
                    });
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        $ventas = $query->latest()->paginate(20);
        $totalVentas = $query->sum('total');
        $totalGanancia = $ventas->sum('ganancia');

        return view('ventas.index', compact('ventas', 'totalVentas', 'totalGanancia'));
    }

    public function pos()
    {
        $productos = Producto::active()
            ->where(function ($q) {
                $q->where('stock', '>', 0)
                    ->orWhere('es_servicio', true);
            })
            ->with('categoria')
            ->get();

        $categorias = Categoria::active()->has('productos')->get();

        return view('ventas.pos', compact('productos', 'categorias'));
    }

    public function create()
    {
        $clientes = Cliente::active()->get();
        $productos = Producto::active()->enStock()->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required_if:metodo_pago,credito|nullable|exists:clientes,id',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,deposito,credito,mixto',
            'pagado_con' => 'nullable|numeric|min:0',
            'notas' => 'nullable|string',
            'fecha_inicio_mora' => 'required_if:metodo_pago,credito|nullable|date',
            'fecha_compromiso_pago' => 'nullable|date|after_or_equal:fecha_inicio_mora',
            'numero_cuotas' => 'nullable|integer|min:1|max:48',
            'plazo_acordado_dias' => 'nullable|integer|min:1|max:365',
        ], [
            'cliente_id.required_if' => 'Debes seleccionar un cliente para registrar una venta a credito.',
            'fecha_inicio_mora.required_if' => 'Debes definir la fecha base del credito para una venta a credito.',
        ]);

        $venta = DB::transaction(function () use ($validated) {
            $timestamp = now();
            $ivaPorcentaje = (float) Configuracion::get('ventas.iva_porcentaje', 16);
            $ivaFactor = $ivaPorcentaje / 100;
            $descuento = (float) ($validated['descuento'] ?? 0);
            $pagadoCon = (float) ($validated['pagado_con'] ?? 0);

            $productosSolicitados = collect($validated['productos']);
            $productos = Producto::whereIn('id', $productosSolicitados->pluck('id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $detalleRows = [];
            $movimientoRows = [];
            $subtotal = 0;

            foreach ($productosSolicitados as $productoData) {
                $producto = $productos->get($productoData['id']);

                if (!$producto) {
                    throw ValidationException::withMessages([
                        'productos' => 'Uno de los productos seleccionados no existe.',
                    ]);
                }

                $cantidad = (int) $productoData['cantidad'];
                $precio = (float) $productoData['precio'];

                if (!$producto->es_servicio && $producto->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => "Stock insuficiente para {$producto->nombre}.",
                    ]);
                }

                $subtotalLinea = round($precio * $cantidad, 2);
                $subtotal += $subtotalLinea;

                $detalleRows[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento' => 0,
                    'subtotal' => $subtotalLinea,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                if (!$producto->es_servicio) {
                    $stockAnterior = $producto->stock;
                    $producto->stock = $stockAnterior - $cantidad;

                    $movimientoRows[] = [
                        'producto_id' => $producto->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'salida',
                        'cantidad' => $cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $producto->stock,
                        'referencia_tipo' => Venta::class,
                        'motivo' => 'Venta',
                        'costo_unitario' => $producto->precio_compra,
                        'proveedor' => null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }

            $impuestos = round($subtotal * $ivaFactor, 2);
            $total = round($subtotal + $impuestos - $descuento, 2);
            $cambio = max(0, round($pagadoCon - $total, 2));
            $esCredito = $validated['metodo_pago'] === 'credito';
            $montoPagado = $esCredito ? min($total, $pagadoCon) : $total;
            $estado = $montoPagado < $total ? 'credito' : 'pagada';

            $venta = Venta::create([
                'cliente_id' => $validated['cliente_id'] ?? null,
                'user_id' => auth()->id(),
                'fecha_venta' => $timestamp,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'impuestos' => $impuestos,
                'total' => $total,
                'monto_pagado' => $montoPagado,
                'metodo_pago' => $validated['metodo_pago'],
                'pagado_con' => $pagadoCon,
                'cambio' => $cambio,
                'notas' => $validated['notas'] ?? null,
                'estado' => $estado,
                'fecha_inicio_mora' => $validated['fecha_inicio_mora'] ?? null,
                'fecha_compromiso_pago' => $validated['fecha_compromiso_pago'] ?? null,
                'numero_cuotas' => $validated['numero_cuotas'] ?? null,
                'plazo_acordado_dias' => $validated['plazo_acordado_dias'] ?? null,
            ]);

            foreach ($detalleRows as &$detalleRow) {
                $detalleRow['venta_id'] = $venta->id;
            }
            unset($detalleRow);

            DB::table('venta_detalles')->insert($detalleRows);

            foreach ($productos as $producto) {
                if ($producto->isDirty('stock')) {
                    $producto->save();
                }
            }

            if (!empty($movimientoRows)) {
                foreach ($movimientoRows as &$movimientoRow) {
                    $movimientoRow['referencia_id'] = $venta->id;
                }
                unset($movimientoRow);

                DB::table('inventario_movimientos')->insert($movimientoRows);
            }

            if ($montoPagado > 0) {
                $venta->moraAbonos()->create([
                    'cliente_id' => $venta->cliente_id,
                    'user_id' => auth()->id(),
                    'tipo' => 'abono',
                    'monto' => $montoPagado,
                    'metodo_pago' => $validated['metodo_pago'],
                    'origen' => 'punto_venta',
                    'fecha_pago' => $timestamp,
                    'notas' => $esCredito
                        ? 'Abono inicial registrado desde el punto de venta.'
                        : 'Pago inicial registrado desde el punto de venta.',
                ]);
            }

            return $venta;
        });

        return response()->json([
            'success' => true,
            'venta_id' => $venta->id,
            'folio' => $venta->folio,
            'total' => $venta->total,
            'cambio' => $venta->cambio,
            'estado' => $venta->estado,
            'saldo_pendiente' => $venta->saldo_pendiente_mora,
            'redirect' => route('ventas.show', $venta),
        ]);
    }

    public function show(Venta $venta, FacturaElectronicaService $facturaElectronicaService)
    {
        $venta->load(['cliente', 'usuario', 'detalles.producto', 'facturaElectronica']);
        $revisionFacturacion = $facturaElectronicaService->revisarVenta($venta);

        return view('ventas.show', compact('venta', 'revisionFacturacion'));
    }

    public function cancelar(Request $request, Venta $venta)
    {
        $request->validate([
            'motivo' => 'required|string',
        ]);

        if ($venta->estado === 'cancelada') {
            return back()->with('error', 'La venta ya estÃ¡ cancelada.');
        }

        $venta->cancelar($request->motivo);

        return redirect()->route('ventas.index')
            ->with('success', 'Venta cancelada correctamente.');
    }

    public function ticket(Venta $venta)
    {
        $venta->load(['cliente', 'usuario', 'detalles.producto']);
        $clienteBalance = 0;

        if ($venta->cliente_id) {
            $clienteBalance = Venta::query()
                ->where('cliente_id', $venta->cliente_id)
                ->where('estado', '!=', 'cancelada')
                ->whereColumn('total', '>', 'monto_pagado')
                ->get()
                ->sum(fn ($item) => max(0, (float) $item->total - (float) $item->monto_pagado));
        }

        $logo = Configuracion::get('empresa.logo', '');
        $logoPath = null;

        if ($logo && Storage::disk('public')->exists($logo)) {
            $logoPath = Storage::disk('public')->path($logo);
        }

        $empresa = [
            'nombre' => Configuracion::get('empresa.nombre', 'CellFix Pro'),
            'direccion' => Configuracion::get('empresa.direccion', ''),
            'telefono' => Configuracion::get('empresa.telefono', ''),
            'email' => Configuracion::get('empresa.email', ''),
            'rfc' => Configuracion::get('empresa.rfc', ''),
            'logo_path' => $logoPath,
            'web' => config('app.url'),
        ];

        $ticketWidthMm = (float) Configuracion::get('impresion.ticket_ancho', 80);
        $paperWidth = max(58, min($ticketWidthMm, 80)) * 2.83465;

        $detailLines = $venta->detalles->count()
            + $venta->detalles->filter(fn ($detalle) => filled($detalle->notas))->count();
        $clientLines = collect([
            $venta->cliente?->telefono,
            $venta->cliente?->email,
            $venta->cliente?->rfc,
            $venta->cliente?->direccion,
            $venta->cliente?->ciudad,
            $venta->cliente?->estado,
        ])->filter()->count();
        $creditLines = $venta->estado === 'credito'
            ? collect([
                $venta->fecha_inicio_mora,
                $venta->fecha_compromiso_pago,
                $venta->numero_cuotas,
                $venta->plazo_acordado_dias,
                $venta->mora_observaciones,
            ])->filter()->count() + 4
            : 0;
        $noteLines = max(
            filled($venta->notas) ? (int) ceil(mb_strlen($venta->notas) / 42) : 0,
            filled($venta->mora_observaciones) ? (int) ceil(mb_strlen($venta->mora_observaciones) / 42) : 0
        );

        $paperHeight = 560
            + ($detailLines * 28)
            + ($clientLines * 12)
            + ($creditLines * 15)
            + ($noteLines * 14)
            + ($logoPath ? 34 : 0)
            + ($venta->descuento > 0 ? 14 : 0)
            + ($venta->estado === 'credito' ? 70 : 0);

        $paperHeight = max(720, min($paperHeight, 1800));

        $pdf = PDF::loadView('ventas.ticket', compact('venta', 'empresa', 'clienteBalance'));
        $pdf->setPaper([0, 0, $paperWidth, $paperHeight], 'portrait');

        return $pdf->stream("ticket-{$venta->folio}.pdf");
    }

    public function comprobante(Venta $venta)
    {
        $venta->load(['cliente', 'usuario', 'detalles.producto']);

        $empresa = [
            'nombre' => Configuracion::get('empresa.nombre', 'CellFix Pro'),
            'direccion' => Configuracion::get('empresa.direccion', ''),
            'telefono' => Configuracion::get('empresa.telefono', ''),
            'rfc' => Configuracion::get('empresa.rfc', ''),
            'logo' => Configuracion::get('empresa.logo', ''),
        ];

        $pdf = PDF::loadView('ventas.comprobante', compact('venta', 'empresa'));

        return $pdf->stream("comprobante-{$venta->folio}.pdf");
    }

    public function estadisticas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');

        $dias = match ($periodo) {
            'dia' => 1,
            'semana' => 7,
            'mes' => 30,
            'aÃ±o' => 365,
            default => 30,
        };

        $fechaInicio = now()->subDays($dias);

        $ventasPorDia = Venta::selectRaw('DATE(fecha_venta) as fecha, SUM(total) as total, COUNT(*) as cantidad')
            ->where('estado', 'pagada')
            ->where('fecha_venta', '>=', $fechaInicio)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $ventasPorMetodo = Venta::selectRaw('metodo_pago, SUM(total) as total, COUNT(*) as cantidad')
            ->where('estado', 'pagada')
            ->where('fecha_venta', '>=', $fechaInicio)
            ->groupBy('metodo_pago')
            ->get();

        $productosTop = DB::table('venta_detalles')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->select('productos.nombre', DB::raw('SUM(venta_detalles.cantidad) as cantidad'), DB::raw('SUM(venta_detalles.subtotal) as total'))
            ->where('ventas.estado', 'pagada')
            ->where('ventas.fecha_venta', '>=', $fechaInicio)
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidad')
            ->limit(10)
            ->get();

        $vendedoresTop = Venta::selectRaw('user_id, SUM(total) as total, COUNT(*) as ventas')
            ->with('usuario')
            ->where('estado', 'pagada')
            ->where('fecha_venta', '>=', $fechaInicio)
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('ventas.estadisticas', compact(
            'ventasPorDia',
            'ventasPorMetodo',
            'productosTop',
            'vendedoresTop',
            'periodo'
        ));
    }
}
