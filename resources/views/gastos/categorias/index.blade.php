@extends('layouts.app')

@section('title', 'Categorias de gastos')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Categorias de gastos</h1>
        <p class="text-gray-500">Organiza gastos fijos, variables, subcategorias, presupuestos y aprobaciones.</p>
    </div>
    <a href="{{ route('gastos.categorias.create') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
        <i class="fas fa-plus mr-2"></i>Nueva categoria
    </a>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form action="{{ route('gastos.categorias.index') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar categoria" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
        <select name="grupo" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos los grupos</option>
            @foreach($grupos as $value => $label)
            <option value="{{ $value }}" {{ request('grupo') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="estado" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos los estados</option>
            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Filtrar</button>
            <a href="{{ route('gastos.categorias.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Limpiar</a>
        </div>
    </form>
</div>

<div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoria</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Grupo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Presupuesto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Aprobacion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($categorias as $categoria)
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white" style="background-color: {{ $categoria->color ?: '#2563eb' }}">
                                <i class="{{ $categoria->icon ?: 'fas fa-tag' }}"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-900">{{ $categoria->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $categoria->parent?->name ? 'Subcategoria de ' . $categoria->parent->name : 'Categoria principal' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $grupos[$categoria->expense_group] ?? 'Sin grupo' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $categoria->monthly_budget ? money($categoria->monthly_budget) : 'Sin presupuesto' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $categoria->requires_approval ? 'Requerida' : 'No requerida' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $categoria->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $categoria->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('gastos.categorias.edit', $categoria) }}" class="text-amber-600 hover:text-amber-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('gastos.categorias.destroy', $categoria) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700" data-confirm="¿Deseas eliminar esta categoria?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No hay categorias registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-gray-200 px-4 py-3">
        {{ $categorias->links() }}
    </div>
</div>
@endsection
