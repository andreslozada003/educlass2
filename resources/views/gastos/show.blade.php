@extends('layouts.app')

@section('title', 'Detalle de gasto')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Detalle del gasto</h1>
        <p class="text-gray-500">{{ $gasto->expense_number }} · {{ $gasto->description }}</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('gastos.edit', $gasto) }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
        <a href="{{ route('gastos.lista') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
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
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $gasto->description }}</h2>
                    <p class="text-sm text-gray-500">{{ optional($gasto->expense_date)->format('d/m/Y') }} · {{ $gasto->category?->name ?? 'Sin categoria' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-gray-900">{{ money($gasto->amount) }}</p>
                    <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold
                        {{ $gasto->payment_status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $gasto->payment_status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $gasto->payment_status === 'partial' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $gasto->payment_status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $gasto->payment_status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}">
                        {{ $gasto->payment_status_label }}
                    </span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Subcategoria</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->subcategory?->name ?? 'No aplica' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Tipo de gasto</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->expense_type_label }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Metodo de pago</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->payment_method_label }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Caja o cuenta</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->payment_source_label }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Fecha de vencimiento</p>
                    <p class="mt-1 font-medium text-gray-900">{{ optional($gasto->due_date)->format('d/m/Y') ?: 'No definida' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Fecha de pago</p>
                    <p class="mt-1 font-medium text-gray-900">{{ optional($gasto->paid_date)->format('d/m/Y') ?: 'No registrada' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Factura o recibo</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->invoice_number ?: ($gasto->receipt_number ?: 'Sin documento') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Referencia</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->reference_number ?: 'Sin referencia' }}</p>
                </div>
            </div>

            @if($gasto->notes)
            <div class="mt-6 rounded-lg bg-gray-50 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Observaciones</p>
                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $gasto->notes }}</p>
            </div>
            @endif
        </div>

        @if($gasto->generatedExpenses->count())
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Historial generado por recurrencia</h2>
            <div class="mt-4 space-y-3">
                @foreach($gasto->generatedExpenses as $generado)
                <a href="{{ route('gastos.show', $generado) }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-900">{{ $generado->description }}</p>
                        <p class="text-xs text-gray-500">{{ $generado->expense_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ money($generado->amount) }}</p>
                        <p class="text-xs text-gray-500">{{ optional($generado->expense_date)->format('d/m/Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($historialProveedor->count())
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Otros gastos del mismo proveedor</h2>
            <div class="mt-4 space-y-3">
                @foreach($historialProveedor as $item)
                <a href="{{ route('gastos.show', $item) }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-900">{{ $item->description }}</p>
                        <p class="text-xs text-gray-500">{{ $item->category?->name ?? 'Sin categoria' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ money($item->amount) }}</p>
                        <p class="text-xs text-gray-500">{{ optional($item->expense_date)->format('d/m/Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Control</h2>
            <div class="mt-4 space-y-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Responsable</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->responsibleUser?->name ?? $gasto->user?->name ?? '-' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Proveedor</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->supplier?->name ?? 'Sin proveedor' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Aprobacion</p>
                    <p class="mt-1 font-medium text-gray-900">{{ $gasto->approval_status_label }}</p>
                    @if($gasto->approver)
                    <p class="mt-1 text-xs text-gray-500">Por {{ $gasto->approver->name }} el {{ optional($gasto->approved_at)->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Recurrencia</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $gasto->is_recurring ? 'Si, ' . \App\Support\GastosCatalogos::label(\App\Support\GastosCatalogos::periodosRecurrentes(), $gasto->recurring_period) : 'No recurrente' }}
                    </p>
                    @if($gasto->next_due_date)
                    <p class="mt-1 text-xs text-gray-500">Proxima fecha: {{ $gasto->next_due_date->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 space-y-3">
                @if($gasto->payment_status !== 'paid' && $gasto->payment_status !== 'cancelled')
                <form action="{{ route('gastos.marcar-pagado', $gasto) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                        <i class="fas fa-circle-check mr-2"></i>Marcar como pagado
                    </button>
                </form>
                @endif

                <form action="{{ route('gastos.duplicar', $gasto) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-copy mr-2"></i>Duplicar gasto
                    </button>
                </form>

                @if($gasto->is_recurring)
                <form action="{{ route('gastos.recurrentes.generar', $gasto) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-cyan-300 bg-cyan-50 px-4 py-2.5 text-sm font-medium text-cyan-700 hover:bg-cyan-100">
                        <i class="fas fa-arrows-rotate mr-2"></i>Generar siguiente gasto
                    </button>
                </form>
                @endif

                @if($gasto->approval_status === 'pending' && auth()->user()->can('aprobar gastos'))
                <form action="{{ route('gastos.aprobar', $gasto) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-blue-300 bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-700 hover:bg-blue-100">
                        <i class="fas fa-user-check mr-2"></i>Aprobar gasto
                    </button>
                </form>

                <form action="{{ route('gastos.rechazar', $gasto) }}" method="POST" class="space-y-2">
                    @csrf
                    <textarea name="motivo" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200" placeholder="Motivo del rechazo"></textarea>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">
                        <i class="fas fa-ban mr-2"></i>Rechazar gasto
                    </button>
                </form>
                @endif

                @if($gasto->payment_status !== 'cancelled')
                <form action="{{ route('gastos.destroy', $gasto) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-50" data-confirm="¿Deseas anular este gasto?">
                        <i class="fas fa-trash mr-2"></i>Anular gasto
                    </button>
                </form>
                @endif

                @if($gasto->receipt_image)
                <a href="{{ asset('storage/' . $gasto->receipt_image) }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-paperclip mr-2"></i>Ver comprobante
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
