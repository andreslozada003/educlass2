<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Support\FacturacionCatalogos;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver clientes')->only(['index', 'show']);
        $this->middleware('can:crear clientes')->only(['create', 'store']);
        $this->middleware('can:editar clientes')->only(['edit', 'update']);
        $this->middleware('can:eliminar clientes')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->has('inactivos')) {
            $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        $clientes = $query->latest()->paginate(20);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $regimenesFiscales = FacturacionCatalogos::regimenesFiscales();
        $usosCfdi = FacturacionCatalogos::usosCfdi();

        return view('clientes.create', compact('regimenesFiscales', 'usosCfdi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clientes,email',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'notas' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'rfc' => 'nullable|string|max:13',
            'regimen_fiscal' => 'nullable|string|max:10',
            'uso_cfdi' => 'nullable|string|max:10',
        ]);

        $cliente = Cliente::create($validated);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'ventas' => function ($q) {
                $q->latest()->limit(10);
            },
            'reparaciones' => function ($q) {
                $q->latest()->limit(10);
            },
        ]);

        $totalCompras = $cliente->ventas()->pagadas()->sum('total');
        $totalReparaciones = $cliente->reparaciones()->count();
        $promedioCompra = $cliente->ventas()->pagadas()->avg('total') ?? 0;
        $regimenesFiscales = FacturacionCatalogos::regimenesFiscales();
        $usosCfdi = FacturacionCatalogos::usosCfdi();

        return view('clientes.show', compact(
            'cliente',
            'totalCompras',
            'totalReparaciones',
            'promedioCompra',
            'regimenesFiscales',
            'usosCfdi'
        ));
    }

    public function edit(Cliente $cliente)
    {
        $regimenesFiscales = FacturacionCatalogos::regimenesFiscales();
        $usosCfdi = FacturacionCatalogos::usosCfdi();

        return view('clientes.edit', compact('cliente', 'regimenesFiscales', 'usosCfdi'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clientes,email,' . $cliente->id,
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'notas' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            'rfc' => 'nullable|string|max:13',
            'regimen_fiscal' => 'nullable|string|max:10',
            'uso_cfdi' => 'nullable|string|max:10',
            'activo' => 'boolean',
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->ventas()->count() > 0 || $cliente->reparaciones()->count() > 0) {
            $cliente->update(['activo' => false]);

            return redirect()->route('clientes.index')
                ->with('success', 'Cliente desactivado correctamente.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    public function activar(Cliente $cliente)
    {
        $cliente->update(['activo' => true]);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente activado correctamente.');
    }

    public function buscar(Request $request)
    {
        $search = $request->get('q');

        $clientes = Cliente::search($search)
            ->where('activo', true)
            ->limit(10)
            ->get(['id', 'nombre', 'apellido', 'telefono', 'rfc']);

        return response()->json($clientes);
    }

    public function historial(Cliente $cliente)
    {
        $ventas = $cliente->ventas()->with('detalles.producto')->latest()->paginate(10, ['*'], 'ventas_page');
        $reparaciones = $cliente->reparaciones()->latest()->paginate(10, ['*'], 'reparaciones_page');

        return view('clientes.historial', compact('cliente', 'ventas', 'reparaciones'));
    }
}
