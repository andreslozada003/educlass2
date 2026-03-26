@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reporte de Ventas</h1>
        <div class="flex space-x-2">
            <form action="{{ route('reportes.ventas') }}" method="GET" class="flex space-x-2">
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
            <a href="{{ route('reportes.exportar.pdf', array_merge(['tipo' => 'ventas'], request()->query())) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf mr-2"></i>PDF
            </a>
            <a href="{{ route('reportes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Ventas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_ventas'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Ingresos Totales</p>
                    <p class="text-2xl font-bold text-green-600">{{ money($stats['ingresos']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Ticket Promedio</p>
                    <p class="text-2xl font-bold text-purple-600">{{ money($stats['ticket_promedio']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Productos Vendidos</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['productos_vendidos'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Ventas por Día -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>Ventas por Día
            </h3>
            <canvas id="ventasDiariasChart" height="250"></canvas>
        </div>

        <!-- Gráfico de Métodos de Pago -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-green-600 mr-2"></i>Ventas por Método de Pago
            </h3>
            <canvas id="metodosPagoChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Productos Vendidos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>Top Productos Vendidos
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topProductos as $producto)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                                <div class="text-xs text-gray-500">{{ $producto->codigo }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $producto->cantidad }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-green-600">{{ money($producto->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ventas por Vendedor -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-users text-purple-600 mr-2"></i>Ventas por Vendedor
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ventas</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ventasPorVendedor as $vendedor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ data_get($vendedor, 'name', 'Sin vendedor') }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600">{{ data_get($vendedor, 'total_ventas', 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-green-600">{{ money(data_get($vendedor, 'total', 0)) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Listado de Ventas -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list text-blue-600 mr-2"></i>Detalle de Ventas
            </h3>
            <span class="text-sm text-gray-500">{{ $ventas->total() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Productos</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Método</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ventas as $venta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $venta->fecha_venta?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900">{{ $venta->folio }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $venta->cliente?->nombre_completo ?? 'Cliente General' }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $venta->detalles->count() }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $venta->metodo_pago }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-green-600">{{ money($venta->total) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($ventas->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Ventas Diarias
    const ventasCtx = document.getElementById('ventasDiariasChart').getContext('2d');
    new Chart(ventasCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($ventasPorDia->pluck('fecha')) !!},
            datasets: [{
                label: 'Ventas ($)',
                data: {!! json_encode($ventasPorDia->pluck('total')) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Cantidad',
                data: {!! json_encode($ventasPorDia->pluck('cantidad')) !!},
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                yAxisID: 'y1'
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
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Gráfico de Métodos de Pago
    const metodosCtx = document.getElementById('metodosPagoChart').getContext('2d');
    new Chart(metodosCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($metodosPago->pluck('metodo_pago')) !!},
            datasets: [{
                data: {!! json_encode($metodosPago->pluck('total')) !!},
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
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
</script>
@endpush
@endsection
