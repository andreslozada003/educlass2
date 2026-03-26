@extends('layouts.app')

@section('title', 'Historial - ' . $cliente->nombre_completo)

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Historial Completo</h1>
        <p class="text-gray-500">{{ $cliente->nombre_completo }}</p>
    </div>
    <a href="{{ route('clientes.show', $cliente) }}" class="text-gray-600 hover:text-gray-700">
        <i class="fas fa-arrow-left mr-1"></i> Volver al perfil
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Historial de Ventas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-shopping-cart mr-2 text-green-600"></i>Historial de Compras
        </h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($ventas as $venta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $venta->folio }}</a>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-sm text-right font-medium">{{ money($venta->total) }}</td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">No hay compras registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $ventas->links() }}
        </div>
    </div>
    
    <!-- Historial de Reparaciones -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-tools mr-2 text-blue-600"></i>Historial de Reparaciones
        </h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($reparaciones as $reparacion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-primary-600 hover:text-primary-700 font-medium">{{ $reparacion->orden }}</a>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">{{ $reparacion->dispositivo_info }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-{{ $reparacion->estado_color }}-100 text-{{ $reparacion->estado_color }}-800">
                                {{ $reparacion->estado_nombre }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">No hay reparaciones registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $reparaciones->links() }}
        </div>
    </div>
</div>
@endsection
