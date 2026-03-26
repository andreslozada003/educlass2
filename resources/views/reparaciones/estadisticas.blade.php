@extends('layouts.app')

@section('title', 'Estadísticas de Reparaciones')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Estadísticas de Reparaciones</h1>
        <div class="flex space-x-2">
            <form action="{{ route('reparaciones.estadisticas') }}" method="GET" class="flex space-x-2">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio->format('Y-m-d') }}" class="border rounded-lg px-3 py-2">
                <input type="date" name="fecha_fin" value="{{ $fechaFin->format('Y-m-d') }}" class="border rounded-lg px-3 py-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
            </form>
            <a href="{{ route('reparaciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-wrench text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Reparaciones</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Completadas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completadas'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">En Proceso</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['en_proceso'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Ingresos</p>
                    <p class="text-2xl font-bold text-purple-600">{{ money($stats['ingresos']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tiempo Prom.</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['tiempo_promedio'] }}h</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Reparaciones por Estado -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>Reparaciones por Estado
            </h3>
            <canvas id="estadoChart" height="250"></canvas>
        </div>

        <!-- Gráfico de Reparaciones por Día -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line text-green-600 mr-2"></i>Reparaciones por Día
            </h3>
            <canvas id="diarioChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Marcas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-mobile-alt text-purple-600 mr-2"></i>Top Marcas Reparadas
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($marcas as $marca)
                        <tr>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $marca->marca }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-600">{{ $marca->total }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-600">
                                <div class="flex items-center justify-end">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($marca->total / $stats['total']) * 100 }}%"></div>
                                    </div>
                                    {{ round(($marca->total / $stats['total']) * 100, 1) }}%
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Tipos de Fallas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Tipos de Fallas Más Comunes
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo de Falla</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($fallas as $falla)
                        <tr>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $falla->tipo_falla }}</td>
                            <td class="px-4 py-2 text-sm text-center text-gray-600">{{ $falla->total }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-600">
                                <div class="flex items-center justify-end">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($falla->total / $stats['total']) * 100 }}%"></div>
                                    </div>
                                    {{ round(($falla->total / $stats['total']) * 100, 1) }}%
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rendimiento por Técnico -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-orange-600 mr-2"></i>Rendimiento por Técnico
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completadas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">En Proceso</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tiempo Prom.</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingresos Generados</th>
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

    <!-- Gráfico de Ingresos -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-dollar-sign text-green-600 mr-2"></i>Ingresos por Reparaciones
        </h3>
        <canvas id="ingresosChart" height="100"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Estados
    const estadoCtx = document.getElementById('estadoChart').getContext('2d');
    new Chart(estadoCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($estados->pluck('estado')) !!},
            datasets: [{
                data: {!! json_encode($estados->pluck('total')) !!},
                backgroundColor: [
                    '#fbbf24', // Recibido - amarillo
                    '#3b82f6', // Diagnóstico - azul
                    '#f97316', // En Reparación - naranja
                    '#8b5cf6', // Listo - morado
                    '#10b981', // Entregado - verde
                    '#ef4444'  // Cancelado - rojo
                ]
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

    // Gráfico Diario
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

    // Gráfico de Ingresos
    const ingresosCtx = document.getElementById('ingresosChart').getContext('2d');
    new Chart(ingresosCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($ingresos->pluck('fecha')) !!},
            datasets: [{
                label: 'Ingresos ($)',
                data: {!! json_encode($ingresos->pluck('total')) !!},
                backgroundColor: '#10b981'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection