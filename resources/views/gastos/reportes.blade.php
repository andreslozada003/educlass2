@extends('layouts.app')

@section('title', 'Reportes de gastos')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Reportes de gastos</h1>
        <p class="text-gray-500">Analiza egresos por categoria, proveedor, metodo, sucursal y utilidad aproximada.</p>
    </div>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form action="{{ route('gastos.reportes') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <input type="date" name="fecha_inicio" value="{{ $fechaInicio->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
        <input type="date" name="fecha_fin" value="{{ $fechaFin->format('Y-m-d') }}" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
        <select name="category_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todas las categorias</option>
            @foreach($categorias as $categoria)
            <option value="{{ $categoria->id }}" {{ request('category_id') == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
            @endforeach
        </select>
        <select name="supplier_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos los proveedores</option>
            @foreach($proveedores as $proveedor)
            <option value="{{ $proveedor->id }}" {{ request('supplier_id') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Aplicar</button>
            <a href="{{ route('gastos.reportes') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Limpiar</a>
        </div>
    </form>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Total gastos</p>
        <p class="mt-2 text-2xl font-bold text-primary-600">{{ money($stats['total']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Pagado</p>
        <p class="mt-2 text-2xl font-bold text-green-600">{{ money($stats['pagado']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Pendiente</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ money($stats['pendiente']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Fijos</p>
        <p class="mt-2 text-2xl font-bold text-cyan-600">{{ money($stats['fijos']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Variables</p>
        <p class="mt-2 text-2xl font-bold text-rose-600">{{ money($stats['variables']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Utilidad aprox.</p>
        <p class="mt-2 text-2xl font-bold {{ $utilidadAproximada >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ money($utilidadAproximada) }}</p>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Flujo diario</h2>
        <canvas id="flujoDiarioChart" height="140"></canvas>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Gastos por categoria</h2>
        <canvas id="categoriaChart" height="140"></canvas>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Top 10 gastos mas altos</h2>
        <div class="mt-4 space-y-3">
            @forelse($topGastos as $gasto)
            <a href="{{ route('gastos.show', $gasto) }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                <div>
                    <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                    <p class="text-xs text-gray-500">{{ $gasto->category?->name ?? 'Sin categoria' }}</p>
                </div>
                <span class="font-semibold text-gray-900">{{ money($gasto->amount) }}</span>
            </a>
            @empty
            <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">No hay datos para el periodo seleccionado.</p>
            @endforelse
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Resumen operativo</h2>
        <div class="mt-4 space-y-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-600">Ventas del periodo</span>
                <span class="font-semibold text-gray-900">{{ money($ventasPeriodo) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-600">Reparaciones del periodo</span>
                <span class="font-semibold text-gray-900">{{ money($reparacionesPeriodo) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-600">Gastos del periodo</span>
                <span class="font-semibold text-red-600">{{ money($stats['total']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3">
                <span class="text-sm font-medium text-green-800">Utilidad aproximada</span>
                <span class="text-lg font-bold text-green-700">{{ money($utilidadAproximada) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Por proveedor</h2>
        <div class="mt-4 space-y-3">
            @forelse($porProveedor->take(8) as $item)
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-700">{{ $item->nombre }}</span>
                <span class="font-semibold text-gray-900">{{ money($item->total) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500">Sin datos.</p>
            @endforelse
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Por sucursal</h2>
        <div class="mt-4 space-y-3">
            @forelse($porSucursal as $item)
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-700">{{ $item->nombre }}</span>
                <span class="font-semibold text-gray-900">{{ money($item->total) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500">Sin datos.</p>
            @endforelse
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Por metodo o cuenta</h2>
        <div class="mt-4 space-y-3">
            @forelse($porMetodoPago as $item)
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-700">{{ $item->nombre }}</span>
                <span class="font-semibold text-gray-900">{{ money($item->total) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500">Sin datos.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const flujoDiario = @json($flujoDiario);
    const categorias = @json($porCategoria);

    new Chart(document.getElementById('flujoDiarioChart'), {
        type: 'bar',
        data: {
            labels: flujoDiario.map(item => item.fecha),
            datasets: [{
                label: 'Gastos',
                data: flujoDiario.map(item => item.total),
                backgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('categoriaChart'), {
        type: 'pie',
        data: {
            labels: categorias.map(item => item.nombre),
            datasets: [{
                data: categorias.map(item => item.total),
                backgroundColor: ['#2563eb', '#0f766e', '#d97706', '#dc2626', '#7c3aed', '#0891b2', '#059669']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endpush
