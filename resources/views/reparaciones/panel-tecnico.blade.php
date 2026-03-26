@extends('layouts.app')

@section('title', 'Panel de Técnico')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Panel de Técnico</h1>
            <p class="text-gray-600">Bienvenido, {{ auth()->user()->name }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('reparaciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-list mr-2"></i>Ver Todas
            </a>
            <a href="{{ route('reparaciones.estadisticas') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-chart-bar mr-2"></i>Estadísticas
            </a>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pendientes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['pendientes'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Diagnóstico</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['diagnostico'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-stethoscope text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Reparación</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['reparacion'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-tools text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completadas Hoy</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['completadas_hoy'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Reparaciones Asignadas -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-wrench text-blue-600 mr-2"></i>Mis Reparaciones Asignadas
                    </h3>
                    <span class="text-sm text-gray-500">{{ $reparaciones->count() }} activas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($reparaciones as $reparacion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm font-mono font-medium text-gray-900">{{ $reparacion->numero_orden }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $reparacion->fecha_recepcion->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $reparacion->marca }} {{ $reparacion->modelo }}</div>
                                    <div class="text-xs text-gray-500">{{ $reparacion->tipo_dispositivo }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $reparacion->cliente->nombre_completo }}</div>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $reparacion->cliente->telefono) }}" target="_blank" class="text-xs text-green-600 hover:underline">
                                        <i class="fab fa-whatsapp mr-1"></i>{{ $reparacion->cliente->telefono }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $reparacion->estado_color }}-100 text-{{ $reparacion->estado_color }}-800">
                                        {{ $reparacion->estado_nombre }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $dias = $reparacion->fecha_recepcion->diffInDays(now());
                                    @endphp
                                    <span class="text-sm {{ $dias > 3 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                        {{ $dias }} días
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-blue-600 hover:text-blue-900 mx-1" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('reparaciones.edit', $reparacion) }}" class="text-yellow-600 hover:text-yellow-900 mx-1" title="Actualizar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                                    <p>No tienes reparaciones asignadas activas</p>
                                    <p class="text-sm mt-1">¡Buen trabajo!</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reparaciones Recientes Completadas -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-history text-green-600 mr-2"></i>Reparaciones Completadas Recientemente
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entregado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($completadas as $reparacion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono">{{ $reparacion->numero_orden }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $reparacion->marca }} {{ $reparacion->modelo }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $reparacion->cliente->nombre_completo }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $reparacion->fecha_entrega ? $reparacion->fecha_entrega->format('d/m/Y H:i') : 'Pendiente' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No hay reparaciones completadas recientemente
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Mi Rendimiento -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>Mi Rendimiento
                </h3>
                <div class="space-y-4">
                    <div class="text-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg text-white">
                        <p class="text-sm opacity-90">Reparaciones este mes</p>
                        <p class="text-4xl font-bold">{{ $miStats['reparaciones_mes'] }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Promedio/Día</p>
                            <p class="text-xl font-bold text-gray-800">{{ $miStats['promedio_dia'] }}</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Tiempo Prom.</p>
                            <p class="text-xl font-bold text-gray-800">{{ $miStats['tiempo_promedio'] }}h</p>
                        </div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tasa de Éxito</span>
                            <span class="text-lg font-bold text-green-600">{{ $miStats['tasa_exito'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $miStats['tasa_exito'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tareas Prioritarias -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>Prioritarias
                </h3>
                @if($prioritarias->count() > 0)
                <div class="space-y-3">
                    @foreach($prioritarias as $rep)
                    <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded-r-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $rep->numero_orden }}</p>
                                <p class="text-xs text-gray-600">{{ $rep->marca }} {{ $rep->modelo }}</p>
                            </div>
                            <span class="text-xs font-bold text-red-600">{{ $rep->fecha_recepcion->diffInDays(now()) }}d</span>
                        </div>
                        <a href="{{ route('reparaciones.show', $rep) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                            Ver detalle →
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>No hay tareas prioritarias
                </p>
                @endif
            </div>

            <!-- Acceso Rápido -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>Acceso Rápido
                </h3>
                <div class="space-y-2">
                    @can('crear reparaciones')
                    <a href="{{ route('reparaciones.create') }}" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Nueva Reparación
                    </a>
                    @endcan
                    <a href="{{ route('productos.index') }}" class="block w-full text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-box mr-2"></i>Consultar Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
