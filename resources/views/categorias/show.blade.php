@extends('layouts.app')

@section('title', 'Detalle de Categoría')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Detalle de Categoría</h1>
        <div class="flex space-x-2">
            <a href="{{ route('categorias.edit', $categoria) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="{{ route('categorias.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6" style="border-left: 4px solid {{ $categoria->color ?? '#3b82f6' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-lg flex items-center justify-center text-3xl mr-4" 
                                 style="background-color: {{ $categoria->color ?? '#3b82f6' }}20; color: {{ $categoria->color ?? '#3b82f6' }}">
                                <i class="fas {{ $categoria->icono ?? 'fa-folder' }}"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $categoria->nombre }}</h2>
                                <p class="text-gray-500 mt-1">
                                    @if($categoria->parent)
                                        Subcategoría de: <span class="font-medium">{{ $categoria->parent->nombre }}</span>
                                    @else
                                        Categoría Principal
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $categoria->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    @if($categoria->descripcion)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Descripción</h3>
                        <p class="text-gray-700">{{ $categoria->descripcion }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Productos en esta Categoría -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-box text-blue-600 mr-2"></i>Productos ({{ $productos->total() }})
                    </h3>
                    <a href="{{ route('productos.create') }}?categoria_id={{ $categoria->id }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-plus mr-1"></i>Agregar Producto
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($productos as $producto)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">{{ $producto->codigo }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($producto->imagen)
                                        <img src="{{ Storage::url($producto->imagen) }}" alt="" class="w-10 h-10 rounded object-cover mr-3">
                                        @else
                                        <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center mr-3">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                                            <div class="text-xs text-gray-500">{{ $producto->marca }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $producto->stock <= $producto->stock_minimo ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $producto->stock }} unidades
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ money($producto->precio_venta) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('productos.show', $producto) }}" class="text-blue-600 hover:text-blue-900 mx-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('productos.edit', $producto) }}" class="text-yellow-600 hover:text-yellow-900 mx-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-3"></i>
                                    <p>No hay productos en esta categoría</p>
                                    <a href="{{ route('productos.create') }}?categoria_id={{ $categoria->id }}" class="text-blue-600 hover:underline mt-2 inline-block">
                                        Agregar el primer producto
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($productos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $productos->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Estadísticas -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie text-blue-600 mr-2"></i>Estadísticas
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Total Productos</span>
                        <span class="text-xl font-bold text-gray-800">{{ $stats['total_productos'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Valor Inventario</span>
                        <span class="text-xl font-bold text-green-600">{{ money($stats['valor_inventario']) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Stock Bajo</span>
                        <span class="text-xl font-bold {{ $stats['stock_bajo'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $stats['stock_bajo'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Subcategorías</span>
                        <span class="text-xl font-bold text-gray-800">{{ $stats['subcategorias'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Subcategorías -->
            @if($categoria->children && $categoria->children->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-sitemap text-blue-600 mr-2"></i>Subcategorías
                </h3>
                <div class="space-y-2">
                    @foreach($categoria->children as $sub)
                    <a href="{{ route('categorias.show', $sub) }}" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="w-8 h-8 rounded flex items-center justify-center mr-3" style="background-color: {{ $sub->color ?? '#3b82f6' }}20; color: {{ $sub->color ?? '#3b82f6' }}">
                            <i class="fas {{ $sub->icono ?? 'fa-folder' }} text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-800">{{ $sub->nombre }}</span>
                            <span class="text-xs text-gray-500 block">{{ $sub->productos_count ?? 0 }} productos</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Información del Sistema -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Creado:</span>
                        <span class="text-gray-700">{{ $categoria->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Actualizado:</span>
                        <span class="text-gray-700">{{ $categoria->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">ID:</span>
                        <span class="text-gray-700 font-mono">#{{ $categoria->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Orden:</span>
                        <span class="text-gray-700">{{ $categoria->orden }}</span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones</h3>
                <div class="space-y-2">
                    <a href="{{ route('categorias.create') }}?parent_id={{ $categoria->id }}" class="block w-full text-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i>Nueva Subcategoría
                    </a>
                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition" {{ $categoria->productos_count > 0 ? 'disabled' : '' }}>
                            <i class="fas fa-trash mr-2"></i>Eliminar Categoría
                        </button>
                    </form>
                    @if($categoria->productos_count > 0)
                    <p class="text-xs text-red-500 text-center mt-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>No se puede eliminar: tiene productos asociados
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection