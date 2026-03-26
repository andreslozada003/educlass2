@extends('layouts.app')

@section('title', 'Reporte de Reparaciones')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reporte de Reparaciones</h1>
        <div class="flex space-x-2">
            <form action="{{ route('reportes.reparaciones') }}" method="GET" class="flex space-x-2">
                <select name="periodo" class="border rounded-lg px-3 py-2">
                    <option value="hoy" {{ $periodo == 'hoy' ? 'selected' : '' }}>Hoy</option>
                    <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="mes" {{ $periodo == 'mes' ? 'selected' : '' }}>Este Mes</option>
                    <option value="anio" {{ in_array($periodo, ['aÃ±o', 'anio', 'ano']) ? 'selected' : '' }}>Este Ano</option>
                    <option value="personalizado" {{ $periodo == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                </select>
                @if($periodo == 'personalizado')
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio->format('Y-m-d') }}" class="border rounded-lg px-3 py-2">
                <input type="date" name="fecha_fin" value="{{ $fechaFin->format('Y-m-d') }}" class="border rounded-lg px-3 py-2">
                @endif
                <select name="estado" class="border rounded-lg px-3 py-2">
                    <option value="">Todos los estados</option>
                    <option value="recibido" {{ $estado == 'recibido' ? 'selected' : '' }}>Recibido</option>
                    <option value="en_diagnostico" {{ $estado == 'en_diagnostico' ? 'selected' : '' }}>En Diagnostico</option>
                    <option value="espera_repuesto" {{ $estado == 'espera_repuesto' ? 'selected' : '' }}>Espera de Repuesto</option>
                    <option value="en_reparacion" {{ $estado == 'en_reparacion' ? 'selected' : '' }}>En Reparacion</option>
                    <option value="listo" {{ $estado == 'listo' ? 'selected' : '' }}>Listo</option>
                    <option value="entregado" {{ $estado == 'entregado' ? 'selected' : '' }}>Entregado</option>
                    <option value="cancelado" {{ $estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
            </form>
            <a href="{{ route('reportes.exportar.pdf', array_merge(['tipo' => 'reparaciones'], request()->except('page'))) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf mr-2"></i>PDF
            </a>
            <a href="{{ route('reportes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Reparaciones</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-wrench text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completadas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completadas'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Proceso</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['en_proceso'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Ingresos</p>
                    <p class="text-2xl font-bold text-purple-600">{{ money($stats['ingresos']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Tiempo Prom.</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['tiempo_promedio'] }}h</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>Reparaciones por Estado
            </h3>
            <canvas id="estadoChart" height="250"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line text-green-600 mr-2"></i>Reparaciones por Dia
            </h3>
            <canvas id="diarioChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-mobile-alt text-purple-600 mr-2"></i>Marcas Mas Reparadas
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($marcas as $marca)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $marca->marca }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $marca->total }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">
                                <div class="flex items-center justify-end">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $stats['total'] > 0 ? ($marca->total / $stats['total']) * 100 : 0 }}%"></div>
                                    </div>
                                    {{ $stats['total'] > 0 ? round(($marca->total / $stats['total']) * 100, 1) : 0 }}%
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Fallas Mas Comunes
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo de Falla</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($fallas as $falla)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $falla->tipo_falla }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $falla->total }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">
                                <div class="flex items-center justify-end">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ $stats['total'] > 0 ? ($falla->total / $stats['total']) * 100 : 0 }}%"></div>
                                    </div>
                                    {{ $stats['total'] > 0 ? round(($falla->total / $stats['total']) * 100, 1) : 0 }}%
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-users text-orange-600 mr-2"></i>Rendimiento por Tecnico
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tecnico</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completadas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">En Proceso</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tiempo Prom.</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingresos</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tecnicos as $tecnico)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-blue-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $tecnico->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">{{ $tecnico->total_reparaciones }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ $tecnico->completadas }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">{{ $tecnico->en_proceso }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">{{ $tecnico->tiempo_promedio }}h</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">{{ money($tecnico->ingresos) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list text-blue-600 mr-2"></i>Detalle de Reparaciones
            </h3>
            <span class="text-sm text-gray-500">{{ $reparaciones->total() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reparaciones as $reparacion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900">{{ $reparacion->orden }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $reparacion->fecha_recepcion->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $reparacion->dispositivo_marca }} {{ $reparacion->dispositivo_modelo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $reparacion->cliente->nombre_completo }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $reparacion->estado_color }}-100 text-{{ $reparacion->estado_color }}-800">{{ $reparacion->estado_nombre }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-green-600">{{ money($reparacion->costo_final) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($reparaciones->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reparaciones->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const estadoCtx = document.getElementById('estadoChart').getContext('2d');
    new Chart(estadoCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($estados->pluck('estado')) !!},
            datasets: [{
                data: {!! json_encode($estados->pluck('total')) !!},
                backgroundColor: ['#fbbf24', '#3b82f6', '#f97316', '#8b5cf6', '#10b981', '#ef4444', '#6b7280']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    const diarioCtx = document.getElementById('diarioChart').getContext('2d');
    new Chart(diarioCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($diario->pluck('fecha')) !!},
            datasets: [{
                label: 'Reparaciones',
                data: {!! json_encode($diario->pluck('total')) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
