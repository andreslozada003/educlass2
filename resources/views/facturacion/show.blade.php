@extends('layouts.app')

@section('title', 'Factura ' . $factura->folio_interno)

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Factura {{ $factura->folio_interno }}</h1>
        <p class="text-gray-500">Venta relacionada: {{ $factura->venta?->folio }}</p>
    </div>
    <div class="mt-4 md:mt-0 flex gap-3">
        <a href="{{ route('facturacion.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
        <form action="{{ route('ventas.facturacion.preparar', $factura->venta) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-rotate mr-2"></i>Actualizar borrador
            </button>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Resumen CFDI</h3>
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full
                    {{ $factura->estado === 'lista_para_timbrar' ? 'bg-amber-100 text-amber-700' : ($factura->estado === 'timbrada' ? 'bg-green-100 text-green-700' : ($factura->estado === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                    {{ str_replace('_', ' ', ucfirst($factura->estado)) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Serie / folio</p>
                    <p class="font-semibold text-gray-800">{{ $factura->serie ?: 'Sin serie' }} {{ $factura->folio ?: '' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Metodo / forma de pago</p>
                    <p class="font-semibold text-gray-800">{{ $factura->metodo_pago_sat }} / {{ $factura->forma_pago }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $metodosPago[$factura->metodo_pago_sat] ?? 'Sin definir' }} - {{ $formasPago[$factura->forma_pago] ?? 'Sin definir' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">UUID</p>
                    <p class="font-semibold text-gray-800 break-all">{{ $factura->uuid ?: 'Pendiente de timbrado' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <p class="text-sm text-gray-500">Version CFDI</p>
                    <p class="font-medium">{{ $factura->cfdi_version }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Exportacion</p>
                    <p class="font-medium">{{ $factura->exportacion }} - {{ $exportaciones[$factura->exportacion] ?? 'No definida' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Lugar expedicion</p>
                    <p class="font-medium">{{ $factura->lugar_expedicion ?: 'No definido' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">PAC</p>
                    <p class="font-medium">{{ $factura->pac_driver ?: 'No configurado' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Emisor y receptor</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-3">Emisor</p>
                    <div class="space-y-2 text-sm text-gray-700">
                        <p><span class="text-gray-500">Nombre:</span> {{ $factura->emisor_datos['nombre'] ?? 'No definido' }}</p>
                        <p><span class="text-gray-500">RFC:</span> {{ $factura->emisor_datos['rfc'] ?? 'No definido' }}</p>
                        <p><span class="text-gray-500">Regimen:</span> {{ $factura->regimen_fiscal_emisor ?: 'No definido' }} {{ $factura->regimen_fiscal_emisor ? '- ' . ($regimenesFiscales[$factura->regimen_fiscal_emisor] ?? 'Catalogo no identificado') : '' }}</p>
                        <p><span class="text-gray-500">Lugar expedicion:</span> {{ $factura->lugar_expedicion ?: 'No definido' }}</p>
                    </div>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-3">Receptor</p>
                    <div class="space-y-2 text-sm text-gray-700">
                        <p><span class="text-gray-500">Nombre:</span> {{ $factura->receptor_datos['nombre'] ?? 'No definido' }}</p>
                        <p><span class="text-gray-500">RFC:</span> {{ $factura->receptor_datos['rfc'] ?? 'No definido' }}</p>
                        <p><span class="text-gray-500">Codigo postal:</span> {{ $factura->receptor_datos['codigo_postal'] ?? 'No definido' }}</p>
                        <p><span class="text-gray-500">Regimen:</span> {{ $factura->regimen_fiscal_receptor ?: 'No definido' }} {{ $factura->regimen_fiscal_receptor ? '- ' . ($regimenesFiscales[$factura->regimen_fiscal_receptor] ?? 'Catalogo no identificado') : '' }}</p>
                        <p><span class="text-gray-500">Uso CFDI:</span> {{ $factura->uso_cfdi ?: 'No definido' }} {{ $factura->uso_cfdi ? '- ' . ($usosCfdi[$factura->uso_cfdi] ?? 'Catalogo no identificado') : '' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Conceptos</h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripcion</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">SAT</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cant.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor unit.</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Importe</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach(($factura->conceptos ?? []) as $concepto)
                        <tr>
                            <td class="px-3 py-3">
                                <p class="font-medium text-gray-800">{{ $concepto['descripcion'] }}</p>
                                <p class="text-xs text-gray-500">Unidad: {{ $concepto['unidad'] }} | Objeto imp.: {{ $concepto['objeto_impuesto'] }} - {{ $objetosImpuesto[$concepto['objeto_impuesto']] ?? 'No identificado' }}</p>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $concepto['clave_prod_serv'] }} / {{ $concepto['clave_unidad'] }}</td>
                            <td class="px-3 py-3 text-sm text-center">{{ $concepto['cantidad'] }}</td>
                            <td class="px-3 py-3 text-sm text-right">{{ money($concepto['valor_unitario']) }}</td>
                            <td class="px-3 py-3 text-sm text-right font-medium">{{ money($concepto['importe']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-3 py-2 text-right text-sm text-gray-600">Subtotal:</td>
                            <td class="px-3 py-2 text-right text-sm font-medium">{{ money($factura->subtotal) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-3 py-2 text-right text-sm text-gray-600">Impuestos:</td>
                            <td class="px-3 py-2 text-right text-sm font-medium">{{ money($factura->impuestos) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-3 py-3 text-right text-lg font-bold text-gray-800">Total:</td>
                            <td class="px-3 py-3 text-right text-lg font-bold text-primary-600">{{ money($factura->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Revision de requisitos</h3>

            @if(empty($revision['errores']))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 mb-4">
                <p class="text-sm font-semibold text-green-700">Este borrador ya esta listo para timbrar.</p>
            </div>
            @endif

            @if(!empty($revision['errores']))
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 mb-4">
                <p class="text-sm font-semibold text-red-700 mb-2">Faltantes</p>
                <ul class="space-y-1 text-sm text-red-700">
                    @foreach($revision['errores'] as $error)
                    <li><i class="fas fa-circle-exclamation mr-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($revision['advertencias']))
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-700 mb-2">Advertencias</p>
                <ul class="space-y-1 text-sm text-amber-700">
                    @foreach($revision['advertencias'] as $advertencia)
                    <li><i class="fas fa-triangle-exclamation mr-2"></i>{{ $advertencia }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Archivos y timbrado</h3>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">XML:</span>
                    <span class="font-medium">{{ $factura->xml_path ?: 'Pendiente' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">PDF:</span>
                    <span class="font-medium">{{ $factura->pdf_path ?: 'Pendiente' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Fecha timbrado:</span>
                    <span class="font-medium">{{ $factura->fecha_timbrado?->format('d/m/Y H:i') ?: 'Pendiente' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Intentos:</span>
                    <span class="font-medium">{{ $factura->intentos_timbrado }}</span>
                </div>
            </div>

            @if($factura->error_mensaje)
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-sm font-semibold text-red-700 mb-1">Ultimo error</p>
                <p class="text-sm text-red-700">{{ $factura->error_mensaje }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
