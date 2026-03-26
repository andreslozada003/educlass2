@extends('layouts.app')

@section('title', 'Reporte Financiero')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reporte Financiero</h1>
        <div class="flex space-x-2">
            <form action="{{ route('reportes.financiero') }}" method="GET" class="flex space-x-2">
                <select name="periodo" class="border rounded-lg px-3 py-2">
                    <option value="hoy" {{ $periodo == 'hoy' ? 'selected' : '' }}>Hoy</option>
                    <option value="semana" {{ $periodo == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="mes" {{ $periodo == 'mes' ? 'selected' : '' }}>Este Mes</option>
                    <option value="año" {{ $periodo == 'año' ? 'selected' : '' }}>Este Año</option>
                    <option value="personalizado" {{ $periodo == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                </select>
                @if($periodo == 'personalizado')
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio->format('Y-m-d') }}" class="border rounded-lg px-3 py-2">
                <input type="date" name="fecha_fin" value="{{ $fechaFin->format('Y-m-d') }}" class="border rounded-lg px-3 py-2">
                @endif
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
            </form>
            <a href="{{ route('reportes.exportar.pdf', array_merge(['tipo' => 'financiero'], request()->except('page'))) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf mr-2"></i>PDF
            </a>
            <a href="{{ route('reportes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Ingresos Totales</p>
                    <p class="text-2xl font-bold">{{ money($stats['ingresos_ventas'] + $stats['ingresos_reparaciones']) }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-arrow-up text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Costos Totales</p>
                    <p class="text-2xl font-bold">{{ money($stats['costos']) }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-arrow-down text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Ganancia Bruta</p>
                    <p class="text-2xl font-bold">{{ money($stats['ganancia_bruta']) }}</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br {{ $stats['margen'] >= 0 ? 'from-purple-500 to-purple-600' : 'from-gray-500 to-gray-600' }} rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Margen</p>
                    <p class="text-2xl font-bold">{{ number_format($stats['margen'], 1) }}%</p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-percentage text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Desglose de Ingresos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-600 mb-1">Ventas de Productos</p>
            <p class="text-xl font-bold text-gray-800">{{ money($stats['ingresos_ventas']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stats['total_ventas'] }} transacciones</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <p class="text-sm text-gray-600 mb-1">Servicios de Reparación</p>
            <p class="text-xl font-bold text-gray-800">{{ money($stats['ingresos_reparaciones']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stats['total_reparaciones'] }} servicios</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-600 mb-1">Ticket Promedio</p>
            <p class="text-xl font-bold text-gray-800">{{ money($stats['ticket_promedio']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Ventas + Reparaciones</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Ingresos vs Costos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Ingresos vs Costos
            </h3>
            <canvas id="financieroChart" height="250"></canvas>
        </div>

        <!-- Gráfico de Evolución -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line text-green-600 mr-2"></i>Evolución Financiera
            </h3>
            <canvas id="evolucionChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Ingresos por Categoría -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-purple-600 mr-2"></i>Ingresos por Categoría
            </h3>
            <canvas id="categoriasChart" height="250"></canvas>
        </div>

        <!-- Métodos de Pago -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-credit-card text-orange-600 mr-2"></i>Ingresos por Método de Pago
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Transacciones</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($metodosPago as $metodo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $metodo->metodo_pago }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $metodo->total }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-green-600">{{ money($metodo->monto) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">
                                @php
                                    $totalIngresos = $stats['ingresos_ventas'] + $stats['ingresos_reparaciones'];
                                    $porcentaje = $totalIngresos > 0 ? round(($metodo->monto / $totalIngresos) * 100, 1) : 0;
                                @endphp
                                {{ $porcentaje }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comparativo Mensual -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Comparativo Mensual
        </h3>
        <canvas id="mensualChart" height="100"></canvas>
    </div>

    <!-- Resumen Detallado -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i>Resumen Detallado
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Ingresos -->
                <div>
                    <h4 class="text-lg font-semibold text-green-600 mb-4 border-b pb-2">INGRESOS</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ventas de Productos</span>
                            <span class="font-medium">{{ money($stats['ingresos_ventas']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Servicios de Reparación</span>
                            <span class="font-medium">{{ money($stats['ingresos_reparaciones']) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-semibold text-gray-800">TOTAL INGRESOS</span>
                            <span class="font-bold text-green-600">{{ money($stats['ingresos_ventas'] + $stats['ingresos_reparaciones']) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Costos y Ganancias -->
                <div>
                    <h4 class="text-lg font-semibold text-red-600 mb-4 border-b pb-2">COSTOS Y GANANCIAS</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Costo de Productos Vendidos</span>
                            <span class="font-medium">{{ money($stats['costos']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Costos de Reparación (repuestos)</span>
                            <span class="font-medium">{{ money($stats['costos_reparaciones']) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-semibold text-gray-800">GANANCIA BRUTA</span>
                            <span class="font-bold text-blue-600">{{ money($stats['ganancia_bruta']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-800">MARGEN DE GANANCIA</span>
                            <span class="font-bold {{ $stats['margen'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($stats['margen'], 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Ingresos vs Costos
    const financieroCtx = document.getElementById('financieroChart').getContext('2d');
    new Chart(financieroCtx, {
        type: 'bar',
        data: {
            labels: ['Ventas', 'Reparaciones', 'Total Ingresos', 'Costos', 'Ganancia'],
            datasets: [{
                label: 'Monto ($)',
                data: [
                    {{ $stats['ingresos_ventas'] }},
                    {{ $stats['ingresos_reparaciones'] }},
                    {{ $stats['ingresos_ventas'] + $stats['ingresos_reparaciones'] }},
                    {{ $stats['costos'] + $stats['costos_reparaciones'] }},
                    {{ $stats['ganancia_bruta'] }}
                ],
                backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981', '#ef4444', '#06b6d4']
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

    // Gráfico de Evolución
    const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');
    new Chart(evolucionCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($evolucion->pluck('fecha')) !!},
            datasets: [{
                label: 'Ingresos',
                data: {!! json_encode($evolucion->pluck('ingresos')) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Costos',
                data: {!! json_encode($evolucion->pluck('costos')) !!},
                borderColor: '#ef4444',
                backgroundColor: 'transparent',
                tension: 0.4
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

    // Gráfico de Categorías
    const categoriasCtx = document.getElementById('categoriasChart').getContext('2d');
    new Chart(categoriasCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($ingresosPorCategoria->pluck('nombre')) !!},
            datasets: [{
                data: {!! json_encode($ingresosPorCategoria->pluck('total')) !!},
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            }
        }
    });

    // Gráfico Mensual
    const mensualCtx = document.getElementById('mensualChart').getContext('2d');
    new Chart(mensualCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($comparativoMensual->pluck('mes')) !!},
            datasets: [{
                label: 'Ingresos',
                data: {!! json_encode($comparativoMensual->pluck('ingresos')) !!},
                backgroundColor: '#10b981'
            }, {
                label: 'Costos',
                data: {!! json_encode($comparativoMensual->pluck('costos')) !!},
                backgroundColor: '#ef4444'
            }, {
                label: 'Ganancia',
                data: {!! json_encode($comparativoMensual->pluck('ganancia')) !!},
                backgroundColor: '#3b82f6'
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
