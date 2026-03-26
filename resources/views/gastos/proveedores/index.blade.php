@extends('layouts.app')

@section('title', 'Proveedores de gastos')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Proveedores</h1>
        <p class="text-gray-500">Centraliza empresas, contactos, NIT, categoria frecuente e historial de pagos.</p>
    </div>
    <a href="{{ route('gastos.proveedores.create') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
        <i class="fas fa-plus mr-2"></i>Nuevo proveedor
    </a>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form action="{{ route('gastos.proveedores.index') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar proveedor, contacto, NIT o telefono" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 md:col-span-2">
        <select name="frequent_category_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todas las categorias</option>
            @foreach($categorias as $categoria)
            <option value="{{ $categoria->id }}" {{ request('frequent_category_id') == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Filtrar</button>
            <a href="{{ route('gastos.proveedores.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Limpiar</a>
        </div>
    </form>
</div>

<div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Contacto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoria frecuente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ciudad</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($proveedores as $proveedor)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $proveedor->name }}</p>
                        <p class="text-xs text-gray-500">{{ $proveedor->nit_rut ?: 'Sin NIT/documento' }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $proveedor->contact_name ?: 'Sin contacto' }}
                        <div class="text-xs text-gray-500">{{ $proveedor->phone ?: 'Sin telefono' }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $proveedor->frequentCategory?->name ?? 'No definida' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $proveedor->city ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $proveedor->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $proveedor->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('gastos.proveedores.show', $proveedor) }}" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('gastos.proveedores.edit', $proveedor) }}" class="text-amber-600 hover:text-amber-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('gastos.proveedores.destroy', $proveedor) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700" data-confirm="¿Deseas eliminar este proveedor?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No hay proveedores registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-gray-200 px-4 py-3">
        {{ $proveedores->links() }}
    </div>
</div>
@endsection
