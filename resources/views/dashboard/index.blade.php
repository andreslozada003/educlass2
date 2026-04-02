@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500">Resumen de tu negocio - {{ now()->format('d \d\e F, Y') }}</p>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('ventas.pos') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nueva Venta
        </a>
        <a href="{{ route('reparaciones.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-tools mr-2"></i>
            Nueva Reparación
        </a>
    </div>
</div>
@endsection      

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Ventas Hoy -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Ventas Hoy</p>
                <p class="text-2xl font-bold text-gray-800">{{ money($totalVentasHoy) }}</p>
                <p class="text-sm text-gray-400">{{ $cantidadVentasHoy }} ventas</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Reparaciones Pendientes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Reparaciones Pendientes</p>
                <p class="text-2xl font-bold text-gray-800">{{ $reparacionesPendientes }}</p>
                <p class="text-sm text-gray-400">{{ $reparacionesListas }} listas para entregar</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tools text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Productos Stock Bajo -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Stock Bajo</p>
                <p class="text-2xl font-bold {{ $productosStockBajo->count() > 0 ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $productosStockBajo->count() }}
                </p>
                <p class="text-sm text-gray-400">productos por reabastecer</p>
            </div>
            <div class="w-12 h-12 {{ $productosStockBajo->count() > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle {{ $productosStockBajo->count() > 0 ? 'text-red-600' : 'text-green-600' }} text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Clientes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Clientes</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalClientes }}</p>
                <p class="text-sm text-gray-400">{{ $totalProductos }} productos</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Gráfica de Ventas -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Ventas de los Últimos 7 Días</h3>
            <select id="periodo-chart" class="text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                <option value="semana">Esta Semana</option>
                <option value="mes">Este Mes</option>
            </select>
        </div>
        <div class="relative h-80">
            <canvas id="ventasChart"></canvas>
        </div>
    </div>
    
    <!-- Reparaciones por Estado -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Reparaciones por Estado</h3>
        <div class="relative h-80">
            <canvas id="reparacionesChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Productos Más Vendidos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Productos Más Vendidos</h3>
        <div class="space-y-3">
            @forelse($productosMasVendidos as $producto)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                        @if($producto->imagen_principal)
                            <img src="{{ asset('storage/' . $producto->imagen_principal) }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <i class="fas fa-box text-gray-400"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $producto->nombre }}</p>
                        <p class="text-xs text-gray-500">{{ $producto->total_vendido }} vendidos</p>
                    </div>
                </div>
                <span class="text-sm font-semibold text-green-600">{{ money($producto->total_ingresos) }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No hay ventas registradas</p>
            @endforelse
        </div>
    </div>
    
    <!-- Últimas Ventas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Últimas Ventas</h3>
            <a href="{{ route('ventas.index') }}" class="text-sm text-primary-600 hover:text-primary-700">Ver todas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="pb-3">Folio</th>
                        <th class="pb-3">Cliente</th>
                        <th class="pb-3">Total</th>
                        <th class="pb-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ultimasVentas as $venta)
                    <tr>
                        <td class="py-3 text-sm">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-primary-600 hover:text-primary-700">
                                {{ $venta->folio }}
                            </a>
                        </td>
                        <td class="py-3 text-sm text-gray-700">
                            {{ $venta->cliente?->nombre_completo ?? 'Cliente General' }}
                        </td>
                        <td class="py-3 text-sm font-medium">{{ money($venta->total) }}</td>
                        <td class="py-3">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $venta->estado === 'pagada' ? 'bg-green-100 text-green-700' : ($venta->estado === 'cancelada' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-500">No hay ventas recientes</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Alertas de Stock Bajo -->
@if($productosStockBajo->count() > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-red-600">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Alertas de Stock Bajo
        </h3>
        <a href="{{ route('productos.index', ['stock_bajo' => 1]) }}" class="text-sm text-primary-600 hover:text-primary-700">Ver todos</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($productosStockBajo->take(6) as $producto)
        <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-lg">
            <div>
                <p class="font-medium text-gray-800">{{ $producto->nombre }}</p>
                <p class="text-sm text-gray-500">{{ $producto->categoria?->nombre }}</p>
                <p class="text-sm text-red-600 mt-1">
                    Stock: <strong>{{ $producto->stock }}</strong> / Mín: {{ $producto->stock_minimo }}
                </p>
            </div>
            <a href="{{ route('productos.show', $producto) }}" class="text-primary-600 hover:text-primary-700">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Gráfica de Ventas
document.addEventListener('DOMContentLoaded', () => {
const ventasCanvas = document.getElementById('ventasChart');
const reparacionesCanvas = document.getElementById('reparacionesChart');
const periodoSelect = document.getElementById('periodo-chart');

if (!ventasCanvas || !reparacionesCanvas || typeof Chart === 'undefined') {
    return;
}

const ventasChart = new Chart(ventasCanvas.getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode($ventasPorDia->pluck('fecha')) !!},
        datasets: [{
            label: 'Ventas',
            data: {!! json_encode($ventasPorDia->pluck('total')) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
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

// Gráfica de Reparaciones
new Chart(reparacionesCanvas.getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($reparacionesPorEstado->pluck('estado_nombre')) !!},
        datasets: [{
            data: {!! json_encode($reparacionesPorEstado->pluck('cantidad')) !!},
            backgroundColor: {!! json_encode($reparacionesPorEstado->map(fn($r) => [
                'gray' => '#9ca3af',
                'yellow' => '#fbbf24',
                'orange' => '#f97316',
                'blue' => '#3b82f6',
                'indigo' => '#6366f1',
                'green' => '#22c55e',
                'emerald' => '#10b981',
                'red' => '#ef4444'
            ][$r->estado_color])) !!}
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});

// Cambiar período de gráfica
periodoSelect?.addEventListener('change', function() {
    fetch(`{{ route('dashboard.chart') }}?periodo=${this.value}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('No se pudo cargar la grafica.');
            }

            return response.json();
        })
        .then(data => {
            ventasChart.data.labels = data.labels;
            ventasChart.data.datasets[0].data = data.data;
            ventasChart.update();
        })
        .catch(error => {
            console.error(error);
        });
});

});
</script>
@endpush
