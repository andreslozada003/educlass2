<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Notificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar dashboard
     */
    public function index()
    {
        // Ventas de hoy
        $ventasHoy = Venta::hoy()->pagadas()->get();
        $totalVentasHoy = $ventasHoy->sum('total');
        $cantidadVentasHoy = $ventasHoy->count();

        // Ventas de la semana
        $ventasSemana = Venta::estaSemana()->pagadas();
        $totalVentasSemana = $ventasSemana->sum('total');

        // Ventas del mes
        $ventasMes = Venta::esteMes()->pagadas();
        $totalVentasMes = $ventasMes->sum('total');

        // Reparaciones
        $reparacionesPendientes = Reparacion::pendientes()->count();
        $reparacionesHoy = Reparacion::hoy()->count();
        $reparacionesListas = Reparacion::listas()->count();

        // Productos con stock bajo
        $productosStockBajo = Producto::stockBajo()->with('categoria')->get();

        // Total de productos
        $totalProductos = Producto::active()->count();
        $totalClientes = Cliente::active()->count();

        // Gráfica de ventas (últimos 7 días)
        $ventasPorDia = Venta::select(
                DB::raw('DATE(fecha_venta) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->where('estado', 'pagada')
            ->where('fecha_venta', '>=', now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Gráfica de reparaciones por estado
        $reparacionesPorEstado = Reparacion::select('estado', DB::raw('COUNT(*) as cantidad'))
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->groupBy('estado')
            ->get()
            ->map(function ($item) {
                $item->estado_nombre = Reparacion::ESTADOS[$item->estado];
                $item->estado_color = Reparacion::ESTADO_COLORES[$item->estado];
                return $item;
            });

        // Productos más vendidos
        $productosMasVendidos = DB::table('venta_detalles')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.imagen_principal',
                DB::raw('SUM(venta_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(venta_detalles.subtotal) as total_ingresos')
            )
            ->where('venta_detalles.created_at', '>=', now()->subDays(30))
            ->groupBy('productos.id', 'productos.nombre', 'productos.imagen_principal')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // Notificaciones recientes
        $notificaciones = Notificacion::paraUsuario(auth()->id())
            ->noLeidas()
            ->latest()
            ->limit(5)
            ->get();

        // Últimas ventas
        $ultimasVentas = Venta::with(['cliente', 'usuario'])
            ->latest()
            ->limit(5)
            ->get();

        // Últimas reparaciones
        $ultimasReparaciones = Reparacion::with(['cliente', 'tecnico'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalVentasHoy',
            'cantidadVentasHoy',
            'totalVentasSemana',
            'totalVentasMes',
            'reparacionesPendientes',
            'reparacionesHoy',
            'reparacionesListas',
            'productosStockBajo',
            'totalProductos',
            'totalClientes',
            'ventasPorDia',
            'reparacionesPorEstado',
            'productosMasVendidos',
            'notificaciones',
            'ultimasVentas',
            'ultimasReparaciones'
        ));
    }

    /**
     * Datos para gráficas AJAX
     */
    public function chartData(Request $request)
    {
        $periodo = $request->get('periodo', 'semana');
        
        $dias = match($periodo) {
            'dia' => 1,
            'semana' => 7,
            'mes' => 30,
            'año' => 365,
            default => 7,
        };

        $ventas = Venta::select(
                DB::raw('DATE(fecha_venta) as fecha'),
                DB::raw('SUM(total) as total')
            )
            ->where('estado', 'pagada')
            ->where('fecha_venta', '>=', now()->subDays($dias))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'labels' => $ventas->pluck('fecha'),
            'data' => $ventas->pluck('total'),
        ]);
    }

    /**
     * Notificaciones en tiempo real
     */
    public function notificaciones()
    {
        $notificaciones = Notificacion::paraUsuario(auth()->id())
            ->noLeidas()
            ->latest()
            ->limit(10)
            ->get();

        $count = Notificacion::paraUsuario(auth()->id())
            ->noLeidas()
            ->count();

        return response()->json([
            'notificaciones' => $notificaciones,
            'count' => $count,
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarNotificacionLeida($id)
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->marcarLeida();

        return response()->json(['success' => true]);
    }
}
