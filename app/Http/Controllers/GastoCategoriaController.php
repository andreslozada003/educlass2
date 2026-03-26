<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Support\GastosCatalogos;
use Illuminate\Http\Request;

class GastoCategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver gastos')->only(['index']);
        $this->middleware('can:gestionar categorias gastos')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = ExpenseCategory::with(['parent', 'children'])
            ->withCount(['expenses', 'subcategoryExpenses', 'children']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim((string) $request->search) . '%');
        }

        if ($request->filled('grupo')) {
            $query->where('expense_group', $request->grupo);
        }

        if ($request->filled('estado')) {
            $query->where('is_active', $request->estado === 'activo');
        }

        $categorias = $query
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('gastos.categorias.index', [
            'categorias' => $categorias,
            'grupos' => GastosCatalogos::gruposCategorias(),
        ]);
    }

    public function create()
    {
        return view('gastos.categorias.create', [
            'categoria' => null,
            'padres' => ExpenseCategory::active()->main()->orderBy('name')->get(),
            'grupos' => GastosCatalogos::gruposCategorias(),
            'colores' => GastosCatalogos::coloresCategoria(),
            'iconos' => GastosCatalogos::iconosCategoria(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategoria($request);
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['is_active'] = $request->boolean('is_active', true);

        ExpenseCategory::create($validated);

        return redirect()->route('gastos.categorias.index')
            ->with('success', 'Categoria de gasto creada correctamente.');
    }

    public function edit(ExpenseCategory $categoria)
    {
        return view('gastos.categorias.edit', [
            'categoria' => $categoria,
            'padres' => ExpenseCategory::active()
                ->main()
                ->where('id', '!=', $categoria->id)
                ->orderBy('name')
                ->get(),
            'grupos' => GastosCatalogos::gruposCategorias(),
            'colores' => GastosCatalogos::coloresCategoria(),
            'iconos' => GastosCatalogos::iconosCategoria(),
        ]);
    }

    public function update(Request $request, ExpenseCategory $categoria)
    {
        $validated = $this->validateCategoria($request);
        $validated['requires_approval'] = $request->boolean('requires_approval');
        $validated['is_active'] = $request->boolean('is_active', true);

        $categoria->update($validated);

        return redirect()->route('gastos.categorias.index')
            ->with('success', 'Categoria de gasto actualizada correctamente.');
    }

    public function destroy(ExpenseCategory $categoria)
    {
        if ($categoria->expenses()->exists() || $categoria->subcategoryExpenses()->exists() || $categoria->children()->exists()) {
            return back()->with('error', 'No puedes eliminar una categoria con gastos o subcategorias asociadas.');
        }

        $categoria->delete();

        return redirect()->route('gastos.categorias.index')
            ->with('success', 'Categoria eliminada correctamente.');
    }

    protected function validateCategoria(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => 'nullable|string|max:60',
            'parent_id' => 'nullable|exists:expense_categories,id',
            'expense_group' => 'nullable|in:' . implode(',', array_keys(GastosCatalogos::gruposCategorias())),
            'monthly_budget' => 'nullable|numeric|min:0',
        ]);
    }
}
