@extends('layouts.app')

@section('title', 'Categorías')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Categorías</h1>
        <p class="text-gray-500">Gestión de categorías de productos</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('categorias.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nueva Categoría
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Productos</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categorias as $categoria)
                <tr class="hover:bg-gray-50 {{ !$categoria->activo ? 'bg-gray-100' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            @if($categoria->icono)
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3" style="background-color: {{ $categoria->color }}20;">
                                <i class="fas fa-{{ $categoria->icono }}" style="color: {{ $categoria->color }}"></i>
                            </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $categoria->nombre }}</p>
                                @if($categoria->parent)
                                <p class="text-xs text-gray-500">Subcategoría de {{ $categoria->parent->nombre }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $categoria->descripcion ?: 'Sin descripción' }}</td>
                    <td class="px-4 py-3 text-center text-sm font-medium">{{ $categoria->productos_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $categoria->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('categorias.show', $categoria) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('categorias.edit', $categoria) }}" class="text-amber-600 hover:text-amber-700" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($categoria->productos_count == 0 && $categoria->children_count == 0)
                            <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-tags text-4xl mb-3 text-gray-300"></i>
                        <p>No hay categorías registradas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
