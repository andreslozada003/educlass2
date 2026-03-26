<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver categorias')->only(['index', 'show']);
        $this->middleware('can:crear categorias')->only(['create', 'store']);
        $this->middleware('can:editar categorias')->only(['edit', 'update']);
        $this->middleware('can:eliminar categorias')->only(['destroy']);
    }

    /**
     * Listar categorías
     */
    public function index()
    {
        $categorias = Categoria::withCount('productos')
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('orden')
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $categoriasPadre = Categoria::whereNull('parent_id')->where('activo', true)->get();
        return view('categorias.create', compact('categoriasPadre'));
    }

    /**
     * Guardar categoría
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:categorias,id',
            'orden' => 'integer|min:0',
        ]);

        Categoria::create($validated);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Mostrar categoría
     */
    public function show(Categoria $categoria)
    {
        $categoria->load(['children', 'productos' => function($q) {
            $q->where('activo', true)->limit(20);
        }]);

        return view('categorias.show', compact('categoria'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Categoria $categoria)
    {
        $categoriasPadre = Categoria::whereNull('parent_id')
            ->where('id', '!=', $categoria->id)
            ->where('activo', true)
            ->get();

        return view('categorias.edit', compact('categoria', 'categoriasPadre'));
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'icono' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'parent_id' => 'nullable|exists:categorias,id',
            'orden' => 'integer|min:0',
            'activo' => 'boolean',
        ]);

        // Evitar que una categoría sea padre de sí misma
        if ($validated['parent_id'] == $categoria->id) {
            $validated['parent_id'] = null;
        }

        $validated['activo'] = $request->boolean('activo', true);

        $categoria->update($validated);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Eliminar categoría
     */
    public function destroy(Categoria $categoria)
    {
        // Verificar si tiene productos
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        // Verificar si tiene subcategorías
        if ($categoria->children()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene subcategorías.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
