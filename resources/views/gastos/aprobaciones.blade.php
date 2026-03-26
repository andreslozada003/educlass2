@extends('layouts.app')

@section('title', 'Aprobaciones de gastos')

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-800">Aprobaciones</h1>
    <p class="text-gray-500">Revisa gastos pendientes, aprobados o rechazados.</p>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Pendientes</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $resumen['pendientes'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Aprobados</p>
        <p class="mt-2 text-2xl font-bold text-green-600">{{ $resumen['aprobados'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Rechazados</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ $resumen['rechazados'] }}</p>
    </div>
</div>

<div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form action="{{ route('gastos.aprobaciones') }}" method="GET" class="flex flex-wrap gap-3">
        <select name="estado" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos</option>
            <option value="pending" {{ request('estado') === 'pending' ? 'selected' : '' }}>Pendiente</option>
            <option value="approved" {{ request('estado') === 'approved' ? 'selected' : '' }}>Aprobado</option>
            <option value="rejected" {{ request('estado') === 'rejected' ? 'selected' : '' }}>Rechazado</option>
        </select>
        <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Filtrar</button>
    </form>
</div>

<div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Gasto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoria</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Responsable</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($gastos as $gasto)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                        <p class="text-xs text-gray-500">{{ $gasto->expense_number }} · {{ optional($gasto->expense_date)->format('d/m/Y') }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->category?->name ?? 'Sin categoria' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->responsibleUser?->name ?? $gasto->user?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                            {{ $gasto->approval_status === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $gasto->approval_status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $gasto->approval_status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $gasto->approval_status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ money($gasto->amount) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-center gap-2">
                            <a href="{{ route('gastos.show', $gasto) }}" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($gasto->approval_status === 'pending')
                            <form action="{{ route('gastos.aprobar', $gasto) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-700" title="Aprobar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form action="{{ route('gastos.rechazar', $gasto) }}" method="POST" class="w-44 space-y-2">
                                @csrf
                                <textarea name="motivo" rows="2" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs" placeholder="Motivo"></textarea>
                                <button type="submit" class="w-full rounded-lg border border-red-300 bg-red-50 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-100">
                                    Rechazar
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No hay gastos para revisar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-gray-200 px-4 py-3">
        {{ $gastos->links() }}
    </div>
</div>
@endsection
