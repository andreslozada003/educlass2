@extends('layouts.app')

@section('title', 'Productos')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Productos</h1>
        <p class="text-gray-500">Gestion de inventario</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('productos.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nuevo Producto
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total productos</p>
                <p class="text-2xl font-bold text-primary-600">{{ $totalProductos }}</p>
            </div>
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-primary-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Stock bajo</p>
                <p class="text-2xl font-bold {{ $stockBajoCount > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $stockBajoCount }}</p>
            </div>
            <div class="w-12 h-12 {{ $stockBajoCount > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle {{ $stockBajoCount > 0 ? 'text-red-600' : 'text-green-600' }} text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Sin stock</p>
                <p class="text-2xl font-bold {{ $sinStock > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $sinStock }}</p>
            </div>
            <div class="w-12 h-12 {{ $sinStock > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                <i class="fas fa-times-circle {{ $sinStock > 0 ? 'text-red-600' : 'text-green-600' }} text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ route('productos.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar por nombre, codigo o barras...">
        </div>
        <div>
            <select name="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Todas las categorias</option>
                @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Todos los tipos</option>
                <option value="celular" {{ request('tipo') == 'celular' ? 'selected' : '' }}>Celulares</option>
                <option value="accesorio" {{ request('tipo') == 'accesorio' ? 'selected' : '' }}>Accesorios</option>
                <option value="repuesto" {{ request('tipo') == 'repuesto' ? 'selected' : '' }}>Repuestos</option>
                <option value="servicio" {{ request('tipo') == 'servicio' ? 'selected' : '' }}>Servicios</option>
            </select>
        </div>
        <div class="flex gap-2">
            <label class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="stock_bajo" value="1" {{ request('stock_bajo') ? 'checked' : '' }} class="mr-2">
                <span class="text-sm">Stock bajo</span>
            </label>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barras</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($productos as $producto)
                <tr class="hover:bg-gray-50 {{ $producto->stock_bajo ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3 overflow-hidden">
                                @if($producto->imagen_principal)
                                    <img src="{{ asset('storage/' . $producto->imagen_principal) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-box text-gray-400"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $producto->nombre }}</p>
                                <p class="text-xs text-gray-500">{{ trim(($producto->marca ?: '') . ' ' . ($producto->modelo ?: '')) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $producto->codigo }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $producto->codigo_barras ?: '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $producto->categoria?->nombre }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">{{ money($producto->precio_venta) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($producto->es_servicio)
                            <span class="text-xs text-green-600">Servicio</span>
                        @else
                            <span class="text-sm {{ $producto->stock_bajo ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                {{ $producto->stock }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $producto->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('productos.show', $producto) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('productos.edit', $producto) }}" class="text-amber-600 hover:text-amber-700" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-box text-4xl mb-3 text-gray-300"></i>
                        <p>No hay productos registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-gray-200">
        {{ $productos->withQueryString()->links() }}
    </div>
</div>
@endsection
