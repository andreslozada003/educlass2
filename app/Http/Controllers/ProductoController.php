<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Support\FacturacionCatalogos;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver productos')->only(['index', 'show']);
        $this->middleware('can:crear productos')->only(['create', 'store']);
        $this->middleware('can:editar productos')->only(['edit', 'update']);
        $this->middleware('can:eliminar productos')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('stock_bajo')) {
            $query->stockBajo();
        }

        if ($request->has('inactivos')) {
            $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        $productos = $query->latest()->paginate(20);
        $categorias = Categoria::active()->get();

        $totalProductos = Producto::where('activo', true)->count();
        $stockBajoCount = Producto::stockBajo()->count();
        $sinStock = Producto::where('stock', 0)->where('es_servicio', false)->count();

        return view('productos.index', compact(
            'productos',
            'categorias',
            'totalProductos',
            'stockBajoCount',
            'sinStock'
        ));
    }

    public function create()
    {
        $categorias = Categoria::active()->get();
        $facturacionDefaults = $this->facturacionDefaults();
        $objetosImpuesto = FacturacionCatalogos::objetosImpuesto();

        return view('productos.create', compact('categorias', 'facturacionDefaults', 'objetosImpuesto'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|unique:productos',
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:50',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'integer|min:0',
            'unidad_medida' => 'nullable|string|max:50',
            'clave_prod_serv_sat' => 'nullable|string|max:20',
            'clave_unidad_sat' => 'nullable|string|max:10',
            'unidad_sat' => 'nullable|string|max:50',
            'objeto_impuesto' => 'nullable|in:01,02,03,04',
            'imagen_principal' => 'nullable|image|max:2048',
            'especificaciones_tecnicas' => 'nullable|string',
            'proveedor' => 'nullable|string|max:100',
            'garantia' => 'nullable|string|max:50',
            'tipo' => 'required|in:celular,accesorio,repuesto,servicio',
            'es_servicio' => 'boolean',
        ]);

        if ($request->hasFile('imagen_principal')) {
            $validated['imagen_principal'] = $request->file('imagen_principal')->store('productos', 'public');
        }

        $validated['es_servicio'] = $request->boolean('es_servicio');
        $validated = $this->aplicarDefaultsFacturacion($validated);

        $producto = Producto::create($validated);

        if ($producto->stock > 0) {
            InventarioMovimiento::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'entrada',
                'cantidad' => $producto->stock,
                'stock_anterior' => 0,
                'stock_nuevo' => $producto->stock,
                'motivo' => 'Stock inicial',
                'costo_unitario' => $producto->precio_compra,
            ]);
        }

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load([
            'categoria',
            'movimientos' => function ($q) {
                $q->latest()->limit(20);
            },
        ]);

        $ventasMes = $producto->ventaDetalles()
            ->whereHas('venta', function ($q) {
                $q->where('estado', 'pagada')
                    ->where('fecha_venta', '>=', now()->subDays(30));
            })
            ->sum('cantidad');

        $ventasTotal = $producto->ventaDetalles()
            ->whereHas('venta', function ($q) {
                $q->where('estado', 'pagada');
            })
            ->sum('cantidad');

        $objetosImpuesto = FacturacionCatalogos::objetosImpuesto();

        return view('productos.show', compact('producto', 'ventasMes', 'ventasTotal', 'objetosImpuesto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::active()->get();
        $facturacionDefaults = $this->facturacionDefaults();
        $objetosImpuesto = FacturacionCatalogos::objetosImpuesto();

        return view('productos.edit', compact('producto', 'categorias', 'facturacionDefaults', 'objetosImpuesto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|unique:productos,codigo,' . $producto->id,
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras,' . $producto->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:50',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'integer|min:0',
            'unidad_medida' => 'nullable|string|max:50',
            'clave_prod_serv_sat' => 'nullable|string|max:20',
            'clave_unidad_sat' => 'nullable|string|max:10',
            'unidad_sat' => 'nullable|string|max:50',
            'objeto_impuesto' => 'nullable|in:01,02,03,04',
            'imagen_principal' => 'nullable|image|max:2048',
            'especificaciones_tecnicas' => 'nullable|string',
            'proveedor' => 'nullable|string|max:100',
            'garantia' => 'nullable|string|max:50',
            'tipo' => 'required|in:celular,accesorio,repuesto,servicio',
            'es_servicio' => 'boolean',
            'activo' => 'boolean',
        ]);

        if ($request->hasFile('imagen_principal')) {
            $validated['imagen_principal'] = $request->file('imagen_principal')->store('productos', 'public');
        }

        $validated['es_servicio'] = $request->boolean('es_servicio');
        $validated['activo'] = $request->boolean('activo', true);
        $validated = $this->aplicarDefaultsFacturacion($validated);

        $producto->update($validated);

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->ventaDetalles()->count() > 0) {
            $producto->update(['activo' => false]);

            return redirect()->route('productos.index')
                ->with('success', 'Producto desactivado correctamente.');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function ajustarStock(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nuevo_stock' => 'required|integer|min:0',
            'motivo' => 'required|string',
        ]);

        $stockAnterior = $producto->stock;
        $diferencia = $validated['nuevo_stock'] - $stockAnterior;

        if ($diferencia !== 0) {
            $producto->update(['stock' => $validated['nuevo_stock']]);

            InventarioMovimiento::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'ajuste',
                'cantidad' => abs($diferencia),
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $validated['nuevo_stock'],
                'motivo' => $validated['motivo'],
            ]);
        }

        return back()->with('success', 'Stock ajustado correctamente.');
    }

    public function entradaInventario(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1',
            'costo_unitario' => 'nullable|numeric|min:0',
            'proveedor' => 'nullable|string',
            'motivo' => 'nullable|string',
        ]);

        $producto->aumentarStock(
            $validated['cantidad'],
            $validated['motivo'] ?? 'Entrada de inventario',
            null
        );

        if (!empty($validated['costo_unitario'])) {
            $producto->update(['precio_compra' => $validated['costo_unitario']]);
        }

        return back()->with('success', 'Entrada registrada correctamente.');
    }

    public function buscar(Request $request)
    {
        $search = $request->get('q');

        $productos = Producto::search($search)
            ->where('activo', true)
            ->where(function ($q) {
                $q->where('stock', '>', 0)
                    ->orWhere('es_servicio', true);
            })
            ->limit(10)
            ->get(['id', 'nombre', 'codigo', 'codigo_barras', 'precio_venta', 'stock', 'imagen_principal']);

        return response()->json($productos);
    }

    public function inventario()
    {
        $movimientos = InventarioMovimiento::with(['producto', 'usuario'])
            ->latest()
            ->paginate(50);

        $stockBajo = Producto::stockBajo()->with('categoria')->get();

        return view('productos.inventario', compact('movimientos', 'stockBajo'));
    }

    protected function facturacionDefaults(): array
    {
        return [
            'clave_prod_serv_sat' => Configuracion::get('facturacion.clave_prod_serv_default', '01010101'),
            'clave_unidad_sat' => Configuracion::get('facturacion.clave_unidad_default', 'H87'),
            'unidad_sat' => Configuracion::get('facturacion.unidad_default', 'Pieza'),
            'objeto_impuesto' => Configuracion::get('facturacion.objeto_impuesto_default', '02'),
        ];
    }

    protected function aplicarDefaultsFacturacion(array $validated): array
    {
        $defaults = $this->facturacionDefaults();

        $validated['clave_prod_serv_sat'] = ($validated['clave_prod_serv_sat'] ?? null) ?: $defaults['clave_prod_serv_sat'];
        $validated['clave_unidad_sat'] = ($validated['clave_unidad_sat'] ?? null) ?: $defaults['clave_unidad_sat'];
        $validated['unidad_sat'] = ($validated['unidad_sat'] ?? null) ?: $defaults['unidad_sat'];
        $validated['objeto_impuesto'] = ($validated['objeto_impuesto'] ?? null) ?: $defaults['objeto_impuesto'];

        return $validated;
    }
}
