@extends('layouts.app')

@section('title', 'Detalle de proveedor')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $proveedor->name }}</h1>
        <p class="text-gray-500">Historial de gastos y datos del proveedor.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('gastos.proveedores.edit', $proveedor) }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
        <a href="{{ route('gastos.proveedores.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Volver
        </a>
    </div>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Datos del proveedor</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Contacto</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $proveedor->contact_name ?: 'Sin contacto' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">NIT / documento</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $proveedor->nit_rut ?: 'No registrado' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Telefono</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $proveedor->phone ?: 'No registrado' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Correo</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $proveedor->email ?: 'No registrado' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Direccion</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $proveedor->address ?: 'No registrada' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Categoria frecuente</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $proveedor->frequentCategory?->name ?? 'No definida' }}</p>
                </div>
            </div>

            @if($proveedor->notes)
            <div class="mt-6 rounded-lg bg-gray-50 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Observaciones</p>
                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $proveedor->notes }}</p>
            </div>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Historial de gastos</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Descripcion</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Responsable</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($gastos as $gasto)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ optional($gasto->expense_date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('gastos.show', $gasto) }}" class="font-medium text-primary-600 hover:text-primary-700">{{ $gasto->description }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->category?->name ?? 'Sin categoria' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->responsibleUser?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ money($gasto->amount) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Este proveedor aun no tiene gastos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $gastos->links() }}
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Resumen</h2>
            <div class="mt-4 space-y-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-500">Total gastado</p>
                    <p class="mt-2 text-2xl font-bold text-primary-600">{{ money($resumen['total_gastado']) }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-500">Por pagar</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ money($resumen['por_pagar']) }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-sm text-gray-500">Pendientes</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $resumen['pendientes'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
