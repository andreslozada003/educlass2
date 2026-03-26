<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver usuarios')->only(['index', 'show']);
        $this->middleware('can:crear usuarios')->only(['create', 'store']);
        $this->middleware('can:editar usuarios')->only(['edit', 'update']);
        $this->middleware('can:eliminar usuarios')->only(['destroy']);
    }

    /**
     * Listar usuarios
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Búsqueda
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->role($request->rol);
        }

        // Filtro de estado
        if ($request->has('inactivos')) {
            $query->where('is_active', false);
        } else {
            $query->where('is_active', true);
        }

        $usuarios = $query->latest()->paginate(20);
        $roles = Role::all();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Guardar usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // Procesar avatar
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $usuario = User::create($validated);
        $usuario->assignRole($validated['role']);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Mostrar usuario
     */
    public function show(User $usuario)
    {
        $usuario->load(['roles', 'ventas' => function($q) {
            $q->latest()->limit(10);
        }, 'reparacionesAsTecnico' => function($q) {
            $q->latest()->limit(10);
        }]);

        // Estadísticas
        $ventasMes = $usuario->ventas()
            ->where('estado', 'pagada')
            ->whereMonth('fecha_venta', now()->month)
            ->sum('total');

        $reparacionesMes = $usuario->reparacionesAsTecnico()
            ->whereMonth('fecha_recepcion', now()->month)
            ->count();

        return view('usuarios.show', compact('usuario', 'ventasMes', 'reparacionesMes'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,name',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        // Procesar avatar
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Actualizar contraseña solo si se proporcionó
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $usuario->update($validated);

        // Actualizar rol
        $usuario->syncRoles([$validated['role']]);

        return redirect()->route('usuarios.show', $usuario)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Desactivar usuario
     */
    public function destroy(User $usuario)
    {
        // No permitir desactivarse a sí mismo
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['is_active' => false]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    /**
     * Activar usuario
     */
    public function activar(User $usuario)
    {
        $usuario->update(['is_active' => true]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario activado correctamente.');
    }
}
