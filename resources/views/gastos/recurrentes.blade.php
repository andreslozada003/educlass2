@extends('layouts.app')

@section('title', 'Gastos recurrentes')

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-800">Gastos recurrentes</h1>
    <p class="text-gray-500">Controla vencimientos, plantillas y gastos generados automaticamente.</p>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm xl:col-span-2">
        <h2 class="text-lg font-semibold text-gray-800">Plantillas recurrentes</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Gasto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoria</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Frecuencia</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Proximo</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recurrentes as $gasto)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                            <p class="text-xs text-gray-500">{{ $gasto->expense_number }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->category?->name ?? 'Sin categoria' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ \App\Support\GastosCatalogos::label(\App\Support\GastosCatalogos::periodosRecurrentes(), $gasto->recurring_period) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ optional($gasto->next_due_date)->format('d/m/Y') ?: 'Sin fecha' }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ money($gasto->amount) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('gastos.show', $gasto) }}" class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('gastos.recurrentes.generar', $gasto) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-cyan-600 hover:text-cyan-700" title="Generar ahora">
                                        <i class="fas fa-arrows-rotate"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No hay gastos recurrentes configurados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $recurrentes->links() }}
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Proximos vencimientos</h2>
            <div class="mt-4 space-y-3">
                @forelse($proximos as $gasto)
                <div class="rounded-lg border border-gray-200 px-4 py-3">
                    <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ optional($gasto->next_due_date)->format('d/m/Y') }} · {{ $gasto->supplier?->name ?? 'Sin proveedor' }}</p>
                </div>
                @empty
                <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">No hay vencimientos cercanos.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Ultimos generados</h2>
            <div class="mt-4 space-y-3">
                @forelse($ultimosGenerados as $gasto)
                <a href="{{ route('gastos.show', $gasto) }}" class="block rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                    <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ optional($gasto->expense_date)->format('d/m/Y') }} · {{ $gasto->recurringSource?->expense_number }}</p>
                </a>
                @empty
                <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">Aun no se han generado gastos.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
