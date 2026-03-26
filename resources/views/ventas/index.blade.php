@extends('layouts.app')

@section('title', 'Ventas')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Ventas</h1>
        <p class="text-gray-500">Historial de ventas</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('ventas.pos') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Nueva Venta
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Resumen -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Total Filtrado</p>
        <p class="text-2xl font-bold text-green-600">{{ money($totalVentas) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Ganancia Estimada</p>
        <p class="text-2xl font-bold text-blue-600">{{ money($totalGanancia) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Cantidad de Ventas</p>
        <p class="text-2xl font-bold text-primary-600">{{ $ventas->total() }}</p>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ route('ventas.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="md:col-span-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Buscar folio o cliente...">
        </div>
        <div>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Desde">
        </div>
        <div>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Hasta">
        </div>
        <div>
            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Todos los estados</option>
                <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($ventas as $venta)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('ventas.show', $venta) }}" class="text-primary-600 font-medium hover:text-primary-700">{{ $venta->folio }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $venta->cliente?->nombre_completo ?: 'Cliente General' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $venta->usuario?->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700 capitalize">{{ $venta->metodo_pago }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium">{{ money($venta->total) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $venta->estado === 'pagada' ? 'bg-green-100 text-green-700' : ($venta->estado === 'cancelada' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('ventas.ticket', $venta) }}" target="_blank" class="text-gray-600 hover:text-gray-700" title="Ticket">
                                <i class="fas fa-receipt"></i>
                            </a>
                            @if($venta->estado !== 'cancelada')
                            <form action="{{ route('ventas.cancelar', $venta) }}" method="POST" class="inline" onsubmit="return confirm('¿Cancelar esta venta?')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-700" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl mb-3 text-gray-300"></i>
                        <p>No hay ventas registradas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $ventas->withQueryString()->links() }}
    </div>
</div>
@endsection
