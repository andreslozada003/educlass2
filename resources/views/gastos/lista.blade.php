@extends('layouts.app')

@section('title', 'Lista de gastos')

@section('page-header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Lista de gastos</h1>
        <p class="text-gray-500">Filtra, edita, duplica, anula y marca pagos en un solo lugar.</p>
    </div>
    <a href="{{ route('gastos.create') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
        <i class="fas fa-plus mr-2"></i>Nuevo gasto
    </a>
</div>
@endsection

@section('content')
@include('gastos.partials.nav')

<div class="grid grid-cols-1 gap-4 md:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Registros</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $resumen['registros'] }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Total</p>
        <p class="mt-2 text-2xl font-bold text-primary-600">{{ money($resumen['total']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Pagado</p>
        <p class="mt-2 text-2xl font-bold text-green-600">{{ money($resumen['pagado']) }}</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-gray-500">Pendiente</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ money($resumen['pendiente']) }}</p>
    </div>
</div>

<div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form action="{{ route('gastos.lista') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4 xl:grid-cols-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por descripcion o codigo" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 xl:col-span-2">
        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
        <select name="category_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todas las categorias</option>
            @foreach($categorias as $categoria)
            <option value="{{ $categoria->id }}" {{ request('category_id') == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
            @endforeach
        </select>
        <select name="subcategory_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todas las subcategorias</option>
            @foreach($subcategorias as $subcategoria)
            <option value="{{ $subcategoria->id }}" {{ request('subcategory_id') == $subcategoria->id ? 'selected' : '' }}>{{ $subcategoria->name }}</option>
            @endforeach
        </select>
        <select name="supplier_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos los proveedores</option>
            @foreach($proveedores as $proveedor)
            <option value="{{ $proveedor->id }}" {{ request('supplier_id') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->name }}</option>
            @endforeach
        </select>
        <select name="responsible_user_id" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos los responsables</option>
            @foreach($responsables as $responsable)
            <option value="{{ $responsable->id }}" {{ request('responsible_user_id') == $responsable->id ? 'selected' : '' }}>{{ $responsable->name }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todos los estados</option>
            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Pagado</option>
            <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Parcial</option>
            <option value="overdue" {{ request('payment_status') === 'overdue' ? 'selected' : '' }}>Vencido</option>
            <option value="cancelled" {{ request('payment_status') === 'cancelled' ? 'selected' : '' }}>Anulado</option>
        </select>
        <select name="approval_status" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todas las aprobaciones</option>
            <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
            <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Aprobado</option>
            <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Rechazado</option>
            <option value="not_required" {{ request('approval_status') === 'not_required' ? 'selected' : '' }}>No requerida</option>
        </select>
        <select name="branch_name" class="rounded-lg border border-gray-300 px-3 py-2 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="">Todas las sucursales</option>
            @foreach($sucursales as $sucursal)
            <option value="{{ $sucursal }}" {{ request('branch_name') === $sucursal ? 'selected' : '' }}>{{ $sucursal }}</option>
            @endforeach
        </select>
        <label class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700">
            <input type="checkbox" name="solo_recurrentes" value="1" {{ request('solo_recurrentes') ? 'checked' : '' }} class="mr-2 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            Solo recurrentes
        </label>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                <i class="fas fa-filter mr-2"></i>Filtrar
            </button>
            <a href="{{ route('gastos.lista') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Descripcion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Categoria</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Proveedor</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Metodo</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Responsable</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($gastos as $gasto)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-700">{{ optional($gasto->expense_date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-xs font-medium text-gray-500">{{ $gasto->expense_number }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $gasto->description }}</p>
                        <p class="text-xs text-gray-500">{{ $gasto->branch_name ?: 'Principal' }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $gasto->category?->name ?? 'Sin categoria' }}
                        @if($gasto->subcategory)
                        <div class="text-xs text-gray-500">{{ $gasto->subcategory->name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->supplier?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->payment_source_label }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                            {{ $gasto->payment_status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $gasto->payment_status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $gasto->payment_status === 'partial' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $gasto->payment_status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $gasto->payment_status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}">
                            {{ $gasto->payment_status_label }}
                        </span>
                        @if($gasto->approval_status !== 'not_required')
                        <div class="mt-1 text-xs text-gray-500">Aprobacion: {{ $gasto->approval_status_label }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ money($gasto->amount) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $gasto->responsibleUser?->name ?? $gasto->user?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <a href="{{ route('gastos.show', $gasto) }}" class="text-blue-600 hover:text-blue-700" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('gastos.edit', $gasto) }}" class="text-amber-600 hover:text-amber-700" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($gasto->payment_status !== 'paid' && $gasto->payment_status !== 'cancelled')
                            <form action="{{ route('gastos.marcar-pagado', $gasto) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-700" title="Marcar pagado">
                                    <i class="fas fa-circle-check"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('gastos.duplicar', $gasto) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-cyan-600 hover:text-cyan-700" title="Duplicar">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            @if($gasto->payment_status !== 'cancelled')
                            <form action="{{ route('gastos.destroy', $gasto) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700" title="Anular" data-confirm="¿Deseas anular este gasto?">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                            @if($gasto->receipt_image)
                            <a href="{{ asset('storage/' . $gasto->receipt_image) }}" target="_blank" class="text-gray-600 hover:text-gray-700" title="Comprobante">
                                <i class="fas fa-paperclip"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-10 text-center text-sm text-gray-500">
                        No se encontraron gastos con los filtros seleccionados.
                    </td>
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
