@extends('layouts.app')

@section('title', 'Gastos')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Resumen de gastos</h1>
        <p class="text-gray-500">Visualiza egresos, pendientes, recurrencias, alertas y utilidad operativa.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('gastos.create') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
            <i class="fas fa-plus mr-2"></i>Registrar gasto
        </a>
        <a href="{{ route('gastos.reportes') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-chart-line mr-2"></i>Ver reportes
        </a>
    </div>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">Gastos del dia</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ money($gastosHoy) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">Gastos del mes</p>
        <p class="mt-2 text-2xl font-bold text-primary-600">{{ money($totalMes) }}</p>
        <p class="mt-2 text-xs {{ $comparativo['diferencia'] > 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ $comparativo['diferencia'] > 0 ? '+' : '' }}{{ number_format($comparativo['porcentaje'], 1) }}% vs mes anterior
        </p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">Pendientes</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pendientes }}</p>
        <p class="mt-2 text-xs text-gray-500">Total por pagar: {{ money($totalPorPagar) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">Recurrentes proximos</p>
        <p class="mt-2 text-2xl font-bold text-cyan-600">{{ $recurrentesProximos->count() }}</p>
        <p class="mt-2 text-xs text-gray-500">En los proximos 15 dias</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">Utilidad operativa</p>
        <p class="mt-2 text-2xl font-bold {{ $resumenOperacion['utilidad'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ money($resumenOperacion['utilidad']) }}
        </p>
        <p class="mt-2 text-xs text-gray-500">Ventas + reparaciones - gastos</p>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Comportamiento del mes</h2>
            <p class="text-sm text-gray-500">Evolucion diaria de los gastos.</p>
        </div>
        <canvas id="gastosDiariosChart" height="120"></canvas>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Alertas</h2>
        <div class="mt-4 space-y-3">
            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-3">
                <span class="text-sm text-amber-800">Servicios por vencer</span>
                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">{{ $alertas['por_vencer'] }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-rose-50 px-3 py-3">
                <span class="text-sm text-rose-800">Gastos sin comprobante</span>
                <span class="rounded-full bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700">{{ $alertas['sin_comprobante'] }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-3">
                <span class="text-sm text-blue-800">Pendientes de aprobacion</span>
                <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">{{ $alertas['sin_aprobar'] }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg bg-violet-50 px-3 py-3">
                <span class="text-sm text-violet-800">Fuera de presupuesto</span>
                <span class="rounded-full bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700">{{ $alertas['fuera_presupuesto'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Gastos por categoria</h2>
            <p class="text-sm text-gray-500">En que se esta yendo mas dinero.</p>
        </div>
        <canvas id="gastosCategoriaChart" height="180"></canvas>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Operacion del mes</h2>
        <div class="mt-4 space-y-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-600">Ventas</span>
                <span class="font-semibold text-gray-900">{{ money($resumenOperacion['ventas']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-600">Reparaciones</span>
                <span class="font-semibold text-gray-900">{{ money($resumenOperacion['reparaciones']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm text-gray-600">Gastos</span>
                <span class="font-semibold text-red-600">{{ money($resumenOperacion['gastos']) }}</span>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-green-200 bg-green-50 px-4 py-3">
                <span class="text-sm font-medium text-green-800">Utilidad aproximada</span>
                <span class="text-lg font-bold text-green-700">{{ money($resumenOperacion['utilidad']) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Ultimos gastos registrados</h2>
        <div class="mt-4 space-y-3">
            @forelse($ultimosGastos as $gasto)
            <a href="{{ route('gastos.show', $gasto) }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                <div>
                    <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                    <p class="text-xs text-gray-500">{{ $gasto->expense_number }} · {{ $gasto->category?->name ?? 'Sin categoria' }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-900">{{ money($gasto->amount) }}</p>
                    <p class="text-xs text-gray-500">{{ optional($gasto->expense_date)->format('d/m/Y') }}</p>
                </div>
            </a>
            @empty
            <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">No hay gastos registrados aun.</p>
            @endforelse
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800">Categorias fuera de presupuesto</h2>
        <div class="mt-4 space-y-3">
            @forelse($categoriasFueraPresupuesto as $categoria)
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3">
                <div class="flex items-center justify-between">
                    <p class="font-medium text-rose-900">{{ $categoria->nombre }}</p>
                    <span class="text-sm font-semibold text-rose-700">{{ money($categoria->total) }}</span>
                </div>
                <p class="mt-1 text-xs text-rose-700">Presupuesto: {{ money($categoria->presupuesto) }}</p>
            </div>
            @empty
            <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">No hay categorias fuera de presupuesto este mes.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const gastosDiarios = @json($gastosDiarios);
    const gastosCategorias = @json($gastosPorCategoria);

    new Chart(document.getElementById('gastosDiariosChart'), {
        type: 'line',
        data: {
            labels: gastosDiarios.map(item => item.fecha),
            datasets: [{
                label: 'Gastos',
                data: gastosDiarios.map(item => item.total),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('gastosCategoriaChart'), {
        type: 'doughnut',
        data: {
            labels: gastosCategorias.map(item => item.nombre),
            datasets: [{
                data: gastosCategorias.map(item => item.total),
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
