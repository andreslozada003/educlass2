@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Reportes y estadisticas</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
        <a href="{{ route('reportes.ventas') }}" class="block">
            <div class="rounded-lg border-l-4 border-blue-500 bg-white p-6 shadow-md transition hover:shadow-lg">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100">
                        <i class="fas fa-shopping-cart text-2xl text-blue-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Reporte de ventas</h3>
                <p class="text-sm text-gray-600">Analisis de ventas, productos mas vendidos y rendimiento comercial.</p>
            </div>
        </a>

        <a href="{{ route('reportes.reparaciones') }}" class="block">
            <div class="rounded-lg border-l-4 border-orange-500 bg-white p-6 shadow-md transition hover:shadow-lg">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-100">
                        <i class="fas fa-tools text-2xl text-orange-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Reporte de reparaciones</h3>
                <p class="text-sm text-gray-600">Estados, tecnicos, marcas, tiempos de servicio e ingresos del taller.</p>
            </div>
        </a>

        <a href="{{ route('reportes.inventario') }}" class="block">
            <div class="rounded-lg border-l-4 border-green-500 bg-white p-6 shadow-md transition hover:shadow-lg">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-box text-2xl text-green-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Reporte de inventario</h3>
                <p class="text-sm text-gray-600">Valor del stock, rotacion, movimientos y alertas de inventario.</p>
            </div>
        </a>

        <a href="{{ route('reportes.financiero') }}" class="block">
            <div class="rounded-lg border-l-4 border-purple-500 bg-white p-6 shadow-md transition hover:shadow-lg">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-purple-100">
                        <i class="fas fa-chart-line text-2xl text-purple-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Reporte financiero</h3>
                <p class="text-sm text-gray-600">Ingresos, costos, rentabilidad y comparativos del negocio.</p>
            </div>
        </a>

        <a href="{{ route('gastos.reportes') }}" class="block">
            <div class="rounded-lg border-l-4 border-red-500 bg-white p-6 shadow-md transition hover:shadow-lg">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-wallet text-2xl text-red-600"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400"></i>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Reporte de gastos</h3>
                <p class="text-sm text-gray-600">Egresos por categoria, proveedor, sucursal y utilidad operativa.</p>
            </div>
        </a>
    </div>

    <div class="mt-8">
        <h2 class="mb-4 text-xl font-semibold text-gray-800">Resumen del periodo actual</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="flex items-center">
                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Ventas hoy</p>
                        <p class="text-xl font-bold text-gray-800">{{ money($resumen['ventas_hoy']) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="flex items-center">
                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-orange-100">
                        <i class="fas fa-wrench text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Reparaciones hoy</p>
                        <p class="text-xl font-bold text-gray-800">{{ $resumen['reparaciones_hoy'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="flex items-center">
                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-chart-pie text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Ganancia estimada</p>
                        <p class="text-xl font-bold text-green-600">{{ money($resumen['ganancia']) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="flex items-center">
                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                        <i class="fas fa-wallet text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Gastos del mes</p>
                        <p class="text-xl font-bold text-red-600">{{ money($resumen['gastos_mes']) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="flex items-center">
                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Stock bajo</p>
                        <p class="text-xl font-bold text-amber-600">{{ $resumen['stock_bajo'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="mb-4 text-xl font-semibold text-gray-800">Acciones rapidas</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('ventas.pos') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700">
                <i class="fas fa-cash-register mr-2"></i>Nueva venta
            </a>
            <a href="{{ route('reparaciones.create') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-white transition hover:bg-orange-700">
                <i class="fas fa-tools mr-2"></i>Nueva reparacion
            </a>
            <a href="{{ route('clientes.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-white transition hover:bg-teal-700">
                <i class="fas fa-user-plus mr-2"></i>Nuevo cliente
            </a>
            <a href="{{ route('productos.create') }}" class="rounded-lg bg-green-600 px-4 py-2 text-white transition hover:bg-green-700">
                <i class="fas fa-box mr-2"></i>Nuevo producto
            </a>
            <a href="{{ route('gastos.create') }}" class="rounded-lg bg-red-600 px-4 py-2 text-white transition hover:bg-red-700">
                <i class="fas fa-wallet mr-2"></i>Registrar gasto
            </a>
        </div>
    </div>
</div>
@endsection
