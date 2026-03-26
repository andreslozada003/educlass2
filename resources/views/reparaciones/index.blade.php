@extends('layouts.app')

@section('title', 'Reparaciones')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reparaciones</h1>
        <p class="text-gray-500">Gestión de órdenes de reparación</p>
    </div>
    @can('crear reparaciones')
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('reparaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Orden
        </a>
    </div>
    @endcan
</div>
@endsection

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pendientes</p>
                <p class="text-2xl font-bold text-blue-600">{{ $pendientes }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Listas</p>
                <p class="text-2xl font-bold text-green-600">{{ $listas }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Hoy</p>
                <p class="text-2xl font-bold text-purple-600">{{ $hoy }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
    <form action="{{ route('reparaciones.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Orden, cliente, marca...">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Todos los estados</option>
                @foreach($estados as $key => $nombre)
                <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Técnico</label>
            <select name="tecnico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">Todos los técnicos</option>
                @foreach($tecnicos as $tecnico)
                <option value="{{ $tecnico->id }}" {{ request('tecnico_id') == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orden</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Técnico</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($reparaciones as $reparacion)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-primary-600 font-medium hover:text-primary-700">
                            {{ $reparacion->orden }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">{{ $reparacion->cliente->nombre_completo }}</div>
                        <div class="text-sm text-gray-500">{{ $reparacion->cliente->telefono }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-900">{{ $reparacion->dispositivo_info }}</div>
                        <div class="text-sm text-gray-500">{{ $reparacion->dispositivo_tipo }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $reparacion->estado_color }}-100 text-{{ $reparacion->estado_color }}-800">
                            {{ $reparacion->estado_nombre }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $reparacion->tecnico?->name ?? 'Sin asignar' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ money($reparacion->costo_final ?: $reparacion->costo_estimado) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">
                        {{ $reparacion->fecha_recepcion->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('reparaciones.imprimir-orden', $reparacion) }}" class="text-gray-600 hover:text-gray-700" title="Imprimir" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                            @if($reparacion->estado === 'listo' && !$reparacion->notificado_listo)
                            <a href="{{ route('reparaciones.notificar', $reparacion) }}" class="text-green-600 hover:text-green-700" title="Notificar">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-tools text-4xl mb-3 text-gray-300"></i>
                        <p>No hay reparaciones registradas</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $reparaciones->withQueryString()->links() }}
    </div>
</div>
@endsection
