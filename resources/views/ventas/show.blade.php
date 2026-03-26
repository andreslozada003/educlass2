@extends('layouts.app')

@section('title', 'Venta ' . $venta->folio)

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Venta {{ $venta->folio }}</h1>
        <p class="text-gray-500">{{ $venta->fecha_venta->format('d \d\e F, Y H:i') }}</p>
    </div>
    <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
        <a href="{{ route('ventas.ticket', $venta) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-receipt mr-2"></i>Ticket
        </a>
        @if($venta->facturaElectronica)
        <a href="{{ route('facturacion.show', $venta->facturaElectronica) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
            <i class="fas fa-file-invoice-dollar mr-2"></i>Ver factura CFDI
        </a>
        @elseif($venta->estado !== 'cancelada')
        <form action="{{ route('ventas.facturacion.preparar', $venta) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <i class="fas fa-file-circle-plus mr-2"></i>Preparar factura CFDI
            </button>
        </form>
        @endif
        @if($venta->estado !== 'cancelada')
        <form action="{{ route('ventas.cancelar', $venta) }}" method="POST" class="inline" onsubmit="const motivo = prompt('Motivo de cancelacion:'); if (!motivo) return false; this.querySelector('input[name=motivo]').value = motivo; return confirm('¿Estas seguro de cancelar esta venta?');">
            @csrf
            <input type="hidden" name="motivo" value="">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-times mr-2"></i>Cancelar Venta
            </button>
        </form>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalle de la venta</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cant.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($venta->detalles as $detalle)
                        <tr>
                            <td class="px-3 py-3">
                                <p class="font-medium text-gray-800">{{ $detalle->producto?->nombre ?: 'Producto eliminado' }}</p>
                                @if($detalle->notas)
                                <p class="text-xs text-gray-500">{{ $detalle->notas }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right text-sm">{{ money($detalle->precio_unitario) }}</td>
                            <td class="px-3 py-3 text-center text-sm">{{ $detalle->cantidad }}</td>
                            <td class="px-3 py-3 text-right text-sm font-medium">{{ money($detalle->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right text-sm text-gray-600">Subtotal:</td>
                            <td class="px-3 py-2 text-right text-sm font-medium">{{ money($venta->subtotal) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right text-sm text-gray-600">IVA (16%):</td>
                            <td class="px-3 py-2 text-right text-sm font-medium">{{ money($venta->impuestos) }}</td>
                        </tr>
                        @if($venta->descuento > 0)
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right text-sm text-gray-600">Descuento:</td>
                            <td class="px-3 py-2 text-right text-sm font-medium text-red-600">-{{ money($venta->descuento) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-right text-lg font-bold text-gray-800">Total:</td>
                            <td class="px-3 py-3 text-right text-lg font-bold text-primary-600">{{ money($venta->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Facturacion electronica</h3>
                @if($venta->facturaElectronica)
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full
                    {{ $venta->facturaElectronica->estado === 'lista_para_timbrar' ? 'bg-amber-100 text-amber-700' : ($venta->facturaElectronica->estado === 'timbrada' ? 'bg-green-100 text-green-700' : ($venta->facturaElectronica->estado === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                    {{ str_replace('_', ' ', ucfirst($venta->facturaElectronica->estado)) }}
                </span>
                @else
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">Sin preparar</span>
                @endif
            </div>

            @if($venta->facturaElectronica)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Folio interno</p>
                    <p class="font-semibold text-gray-800">{{ $venta->facturaElectronica->folio_interno }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">PAC</p>
                    <p class="font-semibold text-gray-800">{{ $venta->facturaElectronica->pac_driver ?: 'No configurado' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">UUID</p>
                    <p class="font-semibold text-gray-800 break-all">{{ $venta->facturaElectronica->uuid ?: 'Pendiente de timbrado' }}</p>
                </div>
            </div>

            <form action="{{ route('ventas.facturacion.preparar', $venta) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                    <i class="fas fa-rotate mr-2"></i>Actualizar borrador CFDI
                </button>
            </form>
            @elseif($venta->estado !== 'cancelada')
            <form action="{{ route('ventas.facturacion.preparar', $venta) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-file-circle-plus mr-2"></i>Preparar factura CFDI
                </button>
            </form>
            @endif

            @if(!empty($revisionFacturacion['errores']))
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-semibold text-red-700 mb-2">Faltantes para facturar</p>
                <ul class="space-y-1 text-sm text-red-700">
                    @foreach($revisionFacturacion['errores'] as $error)
                    <li><i class="fas fa-circle-exclamation mr-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($revisionFacturacion['advertencias']))
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-700 mb-2">Advertencias</p>
                <ul class="space-y-1 text-sm text-amber-700">
                    @foreach($revisionFacturacion['advertencias'] as $advertencia)
                    <li><i class="fas fa-triangle-exclamation mr-2"></i>{{ $advertencia }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informacion</h3>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Estado:</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $venta->estado === 'pagada' ? 'bg-green-100 text-green-700' : ($venta->estado === 'cancelada' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ ucfirst($venta->estado) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Metodo de pago:</span>
                    <span class="font-medium capitalize">{{ $venta->metodo_pago }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Vendedor:</span>
                    <span class="font-medium">{{ $venta->usuario?->name }}</span>
                </div>
                @if($venta->cliente)
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-1">Cliente:</p>
                    <p class="font-medium">{{ $venta->cliente->nombre_completo }}</p>
                    @if($venta->cliente->telefono)
                    <p class="text-sm text-gray-500">{{ $venta->cliente->telefono }}</p>
                    @endif
                    @if($venta->cliente->rfc)
                    <p class="text-sm text-gray-500">RFC: {{ $venta->cliente->rfc }}</p>
                    @endif
                </div>
                @endif
                @if($venta->pagado_con > 0)
                <div class="pt-3 border-t border-gray-200">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pagado con:</span>
                        <span class="font-medium">{{ money($venta->pagado_con) }}</span>
                    </div>
                    @if($venta->cambio > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cambio:</span>
                        <span class="font-medium text-green-600">{{ money($venta->cambio) }}</span>
                    </div>
                    @endif
                </div>
                @endif
                @if($venta->notas)
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-1">Notas:</p>
                    <p class="text-sm text-gray-700">{{ $venta->notas }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
