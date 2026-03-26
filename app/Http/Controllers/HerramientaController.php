<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use Illuminate\Http\Request;

class HerramientaController extends Controller
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
        $query = Herramienta::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('ubicacion', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('danadas')) {
            $query->where('cantidad_danada', '>', 0);
        }

        if ($request->boolean('inactivas')) {
            $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        $herramientas = $query->latest()->paginate(20)->withQueryString();

        $totalHerramientas = Herramienta::where('activo', true)->count();
        $unidadesTotales = Herramienta::where('activo', true)->sum('cantidad');
        $unidadesDanadas = Herramienta::where('activo', true)->sum('cantidad_danada');
        $unidadesDisponibles = max($unidadesTotales - $unidadesDanadas, 0);

        return view('herramientas.index', compact(
            'herramientas',
            'totalHerramientas',
            'unidadesTotales',
            'unidadesDanadas',
            'unidadesDisponibles'
        ));
    }

    public function create()
    {
        return view('herramientas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:50|unique:herramientas,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:150',
            'fecha_compra' => 'nullable|date',
            'costo_compra' => 'nullable|numeric|min:0',
            'cantidad' => 'required|integer|min:1',
            'cantidad_danada' => 'nullable|integer|min:0|lte:cantidad',
            'observaciones' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        $validated['cantidad_danada'] = $validated['cantidad_danada'] ?? 0;
        $validated['costo_compra'] = $validated['costo_compra'] ?? 0;
        $validated['activo'] = $request->boolean('activo', true);

        $herramienta = Herramienta::create($validated);

        return redirect()->route('herramientas.show', $herramienta)
            ->with('success', 'Herramienta registrada correctamente.');
    }

    public function show(Herramienta $herramienta)
    {
        return view('herramientas.show', compact('herramienta'));
    }

    public function edit(Herramienta $herramienta)
    {
        return view('herramientas.edit', compact('herramienta'));
    }

    public function update(Request $request, Herramienta $herramienta)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:50|unique:herramientas,codigo,' . $herramienta->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'ubicacion' => 'nullable|string|max:150',
            'fecha_compra' => 'nullable|date',
            'costo_compra' => 'nullable|numeric|min:0',
            'cantidad' => 'required|integer|min:1',
            'cantidad_danada' => 'nullable|integer|min:0|lte:cantidad',
            'observaciones' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        $validated['cantidad_danada'] = $validated['cantidad_danada'] ?? 0;
        $validated['costo_compra'] = $validated['costo_compra'] ?? 0;
        $validated['activo'] = $request->boolean('activo');

        $herramienta->update($validated);

        return redirect()->route('herramientas.show', $herramienta)
            ->with('success', 'Herramienta actualizada correctamente.');
    }

    public function destroy(Herramienta $herramienta)
    {
        $herramienta->delete();

        return redirect()->route('herramientas.index')
            ->with('success', 'Herramienta eliminada correctamente.');
    }
}
