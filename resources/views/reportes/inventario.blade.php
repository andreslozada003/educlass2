@extends('layouts.app')

@section('title', 'Reporte de Inventario')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reporte de Inventario</h1>
        <div class="flex space-x-2">
            <form action="{{ route('reportes.inventario') }}" method="GET" class="flex space-x-2">
                <select name="categoria_id" class="border rounded-lg px-3 py-2">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                <select name="estado_stock" class="border rounded-lg px-3 py-2">
                    <option value="">Todo el stock</option>
                    <option value="bajo" {{ $estadoStock == 'bajo' ? 'selected' : '' }}>Stock Bajo</option>
                    <option value="agotado" {{ $estadoStock == 'agotado' ? 'selected' : '' }}>Agotado</option>
                    <option value="disponible" {{ $estadoStock == 'disponible' ? 'selected' : '' }}>Disponible</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
            </form>
            <a href="{{ route('reportes.exportar.pdf', array_merge(['tipo' => 'inventario'], request()->except('page'))) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf mr-2"></i>PDF
            </a>
            <a href="{{ route('reportes.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Productos</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_productos'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Valor Inventario</p>
                    <p class="text-2xl font-bold text-green-600">{{ money($stats['valor_inventario']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Stock Bajo</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['stock_bajo'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Agotados</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['agotados'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Unidades</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['total_unidades'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-cubes text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico de Stock por Categoría -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Productos por Categoría
            </h3>
            <canvas id="categoriasChart" height="250"></canvas>
        </div>

        <!-- Gráfico de Valor por Categoría -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-green-600 mr-2"></i>Valor del Inventario por Categoría
            </h3>
            <canvas id="valorChart" height="250"></canvas>
        </div>
    </div>

    <!-- Movimientos Recientes -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-exchange-alt text-purple-600 mr-2"></i>Movimientos Recientes
            </h3>
            <a href="{{ route('inventario') }}" class="text-blue-600 hover:underline text-sm">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($movimientos as $movimiento)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $movimiento->producto->nombre }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full {{ $movimiento->tipo == 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $movimiento->tipo == 'entrada' ? 'Entrada' : 'Salida' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium {{ $movimiento->tipo == 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movimiento->tipo == 'entrada' ? '+' : '-' }}{{ $movimiento->cantidad }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $movimiento->motivo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $movimiento->usuario->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Listado de Productos -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list text-blue-600 mr-2"></i>Detalle de Productos
            </h3>
            <span class="text-sm text-gray-500">{{ $productos->total() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Mínimo</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Venta</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($productos as $producto)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-500">{{ $producto->codigo }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                @if($producto->imagen)
                                <img src="{{ Storage::url($producto->imagen) }}" alt="" class="w-8 h-8 rounded object-cover mr-2">
                                @else
                                <div class="w-8 h-8 rounded bg-gray-200 flex items-center justify-center mr-2">
                                    <i class="fas fa-box text-gray-400 text-xs"></i>
                                </div>
                                @endif
                                <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center text-sm font-medium {{ $producto->stock <= $producto->stock_minimo ? 'text-red-600' : 'text-gray-900' }}">{{ $producto->stock }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $producto->stock_minimo }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-600">{{ money($producto->precio_compra) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-600">{{ money($producto->precio_venta) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-green-600">{{ money($producto->stock * $producto->precio_compra) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($producto->stock == 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Agotado</span>
                            @elseif($producto->stock <= $producto->stock_minimo)
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Bajo</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">OK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($productos->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Productos por Categoría
    const categoriasCtx = document.getElementById('categoriasChart').getContext('2d');
    new Chart(categoriasCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($productosPorCategoria->pluck('nombre')) !!},
            datasets: [{
                label: 'Productos',
                data: {!! json_encode($productosPorCategoria->pluck('total')) !!},
                backgroundColor: '#3b82f6'
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

    // Gráfico de Valor por Categoría
    const valorCtx = document.getElementById('valorChart').getContext('2d');
    new Chart(valorCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($valorPorCategoria->pluck('nombre')) !!},
            datasets: [{
                data: {!! json_encode($valorPorCategoria->pluck('valor')) !!},
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
</script>
@endpush
@endsection
