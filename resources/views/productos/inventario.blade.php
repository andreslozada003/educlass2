@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Movimientos de Inventario</h1>
        <p class="text-gray-500">Historial de entradas, salidas y ajustes</p>
    </div>
    <div class="mt-4 md:mt-0">
        <a href="{{ route('productos.index') }}" class="text-gray-600 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Productos
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Stock Bajo -->
@if($stockBajo->count() > 0)
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <h3 class="text-lg font-semibold text-red-800 mb-2">
        <i class="fas fa-exclamation-triangle mr-2"></i>Productos con Stock Bajo
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach($stockBajo as $producto)
        <div class="bg-white rounded-lg p-3 border border-red-200">
            <p class="font-medium text-gray-800">{{ $producto->nombre }}</p>
            <p class="text-sm text-red-600">Stock: {{ $producto->stock }} / Mín: {{ $producto->stock_minimo }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Movimientos -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Ant.</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Nuevo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($movimientos as $movimiento)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('productos.show', $movimiento->producto) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                            {{ $movimiento->producto?->nombre ?: 'Producto eliminado' }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $movimiento->tipo_color }}-100 text-{{ $movimiento->tipo_color }}-800">
                            {{ $movimiento->tipo_nombre }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-right">{{ $movimiento->cantidad }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $movimiento->stock_anterior }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">{{ $movimiento->stock_nuevo }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $movimiento->motivo ?: '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $movimiento->usuario?->name ?: 'Sistema' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
                        <p>No hay movimientos registrados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $movimientos->links() }}
    </div>
</div>
@endsection
