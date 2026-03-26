@extends('layouts.app')

@section('title', $usuario->name)

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div class="flex items-center">
        <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center mr-4 overflow-hidden">
            @if($usuario->avatar)
                <img src="{{ asset('storage/' . $usuario->avatar) }}" alt="" class="w-full h-full object-cover">
            @else
                <span class="text-2xl font-bold text-primary-600">{{ substr($usuario->name, 0, 1) }}</span>
            @endif
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $usuario->name }}</h1>
            <p class="text-gray-500">{{ ucfirst($usuario->getRoleNames()->first()) }}</p>
        </div>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('usuarios.edit', $usuario) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Información -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de Contacto</h3>
        
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Email:</span>
                <span class="font-medium">{{ $usuario->email }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Teléfono:</span>
                <span class="font-medium">{{ $usuario->phone ?: 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Rol:</span>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $usuario->isAdmin() ? 'bg-red-100 text-red-700' : ($usuario->isTecnico() ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                    {{ ucfirst($usuario->getRoleNames()->first()) }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Estado:</span>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $usuario->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $usuario->is_active ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Registrado:</span>
                <span class="font-medium">{{ $usuario->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas del Mes</h3>
        
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-500">Ventas Realizadas</p>
                <p class="text-2xl font-bold text-green-600">{{ money($ventasMes) }}</p>
            </div>
            @if($usuario->isTecnico())
            <div>
                <p class="text-sm text-gray-500">Reparaciones Completadas</p>
                <p class="text-2xl font-bold text-blue-600">{{ $reparacionesMes }}</p>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Últimas Ventas -->
    <div class="lg:col-span-3 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Últimas Ventas</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($usuario->ventas->take(10) as $venta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-primary-600 hover:text-primary-700">{{ $venta->folio }}</a>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-sm text-gray-700">{{ $venta->cliente?->nombre_completo ?: 'Cliente General' }}</td>
                        <td class="px-3 py-2 text-sm text-right font-medium">{{ money($venta->total) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">No hay ventas registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
