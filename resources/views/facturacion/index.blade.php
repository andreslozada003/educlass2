@extends('layouts.app')

@section('title', 'Facturacion electronica')

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Facturacion electronica</h1>
        <p class="text-gray-500">Prepara el CFDI de tus ventas y deja listo el sistema para conectar un PAC.</p>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Listas para timbrar</p>
        <p class="text-2xl font-bold text-amber-600">{{ $estadisticas['listas'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Timbradas</p>
        <p class="text-2xl font-bold text-green-600">{{ $estadisticas['timbradas'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Con error</p>
        <p class="text-2xl font-bold text-red-600">{{ $estadisticas['errores'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Ventas pendientes</p>
        <p class="text-2xl font-bold text-primary-600">{{ $estadisticas['pendientes'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Configuracion de emisor y PAC</h3>
                    <p class="text-sm text-gray-500">Aqui dejas capturado lo necesario para timbrar cuando ya tengas proveedor.</p>
                </div>
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $configuracion['facturacion']['activo'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $configuracion['facturacion']['activo'] ? 'Activo' : 'En preparacion' }}
                </span>
            </div>

            <form action="{{ route('facturacion.configuracion.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="empresa_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del emisor</label>
                        <input type="text" name="empresa_nombre" id="empresa_nombre" value="{{ old('empresa_nombre', $configuracion['empresa']['nombre']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="empresa_rfc" class="block text-sm font-medium text-gray-700 mb-1">RFC del emisor</label>
                        <input type="text" name="empresa_rfc" id="empresa_rfc" value="{{ old('empresa_rfc', $configuracion['empresa']['rfc']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="empresa_telefono" class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                        <input type="text" name="empresa_telefono" id="empresa_telefono" value="{{ old('empresa_telefono', $configuracion['empresa']['telefono']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="empresa_email" class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                        <input type="email" name="empresa_email" id="empresa_email" value="{{ old('empresa_email', $configuracion['empresa']['email']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="empresa_direccion" class="block text-sm font-medium text-gray-700 mb-1">Direccion fiscal o comercial</label>
                        <textarea name="empresa_direccion" id="empresa_direccion" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('empresa_direccion', $configuracion['empresa']['direccion']) }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="facturacion_activo" value="1" {{ old('facturacion_activo', $configuracion['facturacion']['activo']) ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Habilitar modulo de facturacion electronica</span>
                        </label>
                    </div>
                    <div>
                        <label for="facturacion_cfdi_version" class="block text-sm font-medium text-gray-700 mb-1">Version CFDI</label>
                        <input type="text" name="facturacion_cfdi_version" id="facturacion_cfdi_version" value="{{ old('facturacion_cfdi_version', $configuracion['facturacion']['cfdi_version']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_serie" class="block text-sm font-medium text-gray-700 mb-1">Serie</label>
                        <input type="text" name="facturacion_serie" id="facturacion_serie" value="{{ old('facturacion_serie', $configuracion['facturacion']['serie']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_lugar_expedicion" class="block text-sm font-medium text-gray-700 mb-1">Lugar de expedicion</label>
                        <input type="text" name="facturacion_lugar_expedicion" id="facturacion_lugar_expedicion" value="{{ old('facturacion_lugar_expedicion', $configuracion['facturacion']['lugar_expedicion']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_regimen_fiscal_emisor" class="block text-sm font-medium text-gray-700 mb-1">Regimen fiscal del emisor</label>
                        <select name="facturacion_regimen_fiscal_emisor" id="facturacion_regimen_fiscal_emisor"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Seleccionar regimen...</option>
                            @foreach($regimenesFiscales as $clave => $descripcion)
                            <option value="{{ $clave }}" {{ old('facturacion_regimen_fiscal_emisor', $configuracion['facturacion']['regimen_fiscal_emisor']) === $clave ? 'selected' : '' }}>{{ $clave }} - {{ $descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="facturacion_exportacion" class="block text-sm font-medium text-gray-700 mb-1">Exportacion</label>
                        <select name="facturacion_exportacion" id="facturacion_exportacion"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach($exportaciones as $clave => $descripcion)
                            <option value="{{ $clave }}" {{ old('facturacion_exportacion', $configuracion['facturacion']['exportacion']) === $clave ? 'selected' : '' }}>{{ $clave }} - {{ $descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="facturacion_pac_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre del PAC</label>
                        <input type="text" name="facturacion_pac_nombre" id="facturacion_pac_nombre" value="{{ old('facturacion_pac_nombre', $configuracion['facturacion']['pac_nombre']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Ej: Facturama, SW Sapien, Finkok...">
                    </div>
                    <div>
                        <label for="facturacion_pac_modo" class="block text-sm font-medium text-gray-700 mb-1">Modo PAC</label>
                        <select name="facturacion_pac_modo" id="facturacion_pac_modo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach($modosPac as $clave => $descripcion)
                            <option value="{{ $clave }}" {{ old('facturacion_pac_modo', $configuracion['facturacion']['pac_modo']) === $clave ? 'selected' : '' }}>{{ $descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="facturacion_pac_url" class="block text-sm font-medium text-gray-700 mb-1">URL o endpoint del PAC</label>
                        <input type="text" name="facturacion_pac_url" id="facturacion_pac_url" value="{{ old('facturacion_pac_url', $configuracion['facturacion']['pac_url']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_pac_usuario" class="block text-sm font-medium text-gray-700 mb-1">Usuario PAC</label>
                        <input type="text" name="facturacion_pac_usuario" id="facturacion_pac_usuario" value="{{ old('facturacion_pac_usuario', $configuracion['facturacion']['pac_usuario']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_pac_password" class="block text-sm font-medium text-gray-700 mb-1">Password PAC</label>
                        <input type="text" name="facturacion_pac_password" id="facturacion_pac_password" value="{{ old('facturacion_pac_password', $configuracion['facturacion']['pac_password']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_pac_token" class="block text-sm font-medium text-gray-700 mb-1">Token PAC</label>
                        <input type="text" name="facturacion_pac_token" id="facturacion_pac_token" value="{{ old('facturacion_pac_token', $configuracion['facturacion']['pac_token']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label for="facturacion_certificado_cer" class="block text-sm font-medium text-gray-700 mb-1">Archivo .cer</label>
                        <input type="text" name="facturacion_certificado_cer" id="facturacion_certificado_cer" value="{{ old('facturacion_certificado_cer', $configuracion['facturacion']['certificado_cer']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Ruta o nombre del certificado">
                    </div>
                    <div>
                        <label for="facturacion_certificado_key" class="block text-sm font-medium text-gray-700 mb-1">Archivo .key</label>
                        <input type="text" name="facturacion_certificado_key" id="facturacion_certificado_key" value="{{ old('facturacion_certificado_key', $configuracion['facturacion']['certificado_key']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Ruta o nombre de la llave privada">
                    </div>
                    <div>
                        <label for="facturacion_certificado_password" class="block text-sm font-medium text-gray-700 mb-1">Password de certificados</label>
                        <input type="text" name="facturacion_certificado_password" id="facturacion_certificado_password" value="{{ old('facturacion_certificado_password', $configuracion['facturacion']['certificado_password']) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Defaults fiscales para productos</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="facturacion_clave_prod_serv_default" class="block text-sm font-medium text-gray-700 mb-1">ClaveProdServ por defecto</label>
                            <input type="text" name="facturacion_clave_prod_serv_default" id="facturacion_clave_prod_serv_default" value="{{ old('facturacion_clave_prod_serv_default', $configuracion['defaults_producto']['clave_prod_serv_sat']) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="facturacion_clave_unidad_default" class="block text-sm font-medium text-gray-700 mb-1">ClaveUnidad por defecto</label>
                            <input type="text" name="facturacion_clave_unidad_default" id="facturacion_clave_unidad_default" value="{{ old('facturacion_clave_unidad_default', $configuracion['defaults_producto']['clave_unidad_sat']) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="facturacion_unidad_default" class="block text-sm font-medium text-gray-700 mb-1">Unidad por defecto</label>
                            <input type="text" name="facturacion_unidad_default" id="facturacion_unidad_default" value="{{ old('facturacion_unidad_default', $configuracion['defaults_producto']['unidad_sat']) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="facturacion_objeto_impuesto_default" class="block text-sm font-medium text-gray-700 mb-1">Objeto de impuesto por defecto</label>
                            <select name="facturacion_objeto_impuesto_default" id="facturacion_objeto_impuesto_default"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @foreach($objetosImpuesto as $clave => $descripcion)
                                <option value="{{ $clave }}" {{ old('facturacion_objeto_impuesto_default', $configuracion['defaults_producto']['objeto_impuesto']) === $clave ? 'selected' : '' }}>{{ $clave }} - {{ $descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Guardar configuracion
                    </button>
                </div>

                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <p class="font-semibold mb-1">Importante</p>
                    <p>Guardar esta configuracion no crea un CFDI. Despues de guardar, debes preparar la factura desde una venta pagada en el panel derecho o desde el detalle de la venta.</p>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Facturas preparadas</h3>
                    <p class="text-sm text-gray-500">Borradores que ya podras timbrar cuando conectes tu PAC.</p>
                </div>
                <form method="GET" action="{{ route('facturacion.index') }}" class="flex flex-col md:flex-row gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por folio, cliente o RFC"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <select name="estado" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $clave => $descripcion)
                        <option value="{{ $clave }}" {{ request('estado') === $clave ? 'selected' : '' }}>{{ $descripcion }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">Filtrar</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Venta</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($facturas as $factura)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 text-sm font-medium text-gray-800">{{ $factura->folio_interno }}</td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $factura->venta?->folio }}</td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $factura->cliente?->nombre_fiscal }}</td>
                            <td class="px-3 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $factura->estado === 'lista_para_timbrar' ? 'bg-amber-100 text-amber-700' : ($factura->estado === 'timbrada' ? 'bg-green-100 text-green-700' : ($factura->estado === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ $estados[$factura->estado] ?? $factura->estado }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-sm text-right font-medium">{{ money($factura->total) }}</td>
                            <td class="px-3 py-3 text-right">
                                <a href="{{ route('facturacion.show', $factura) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Ver detalle</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8">
                                <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">
                                        <i class="fas fa-file-invoice text-xl"></i>
                                    </div>
                                    <h4 class="text-base font-semibold text-gray-800">Todavia no hay facturas preparadas</h4>
                                    <p class="mt-2 text-sm text-gray-500">Guardar la configuracion solo deja listo el modulo. Para que una factura aparezca aqui, primero debes preparar el CFDI desde una venta pagada.</p>
                                    <p class="mt-3 text-sm font-medium text-primary-600">Ventas pendientes por preparar: {{ $estadisticas['pendientes'] }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $facturas->links() }}
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas listas para preparar</h3>

            <div class="space-y-3">
                @forelse($ventasPendientes as $venta)
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $venta->folio }}</p>
                            <p class="text-sm text-gray-500">{{ $venta->cliente?->nombre_fiscal ?: 'Sin cliente' }}</p>
                            <p class="text-sm text-gray-500">{{ $venta->fecha_venta?->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="text-sm font-semibold text-primary-600">{{ money($venta->total) }}</span>
                    </div>
                    <form action="{{ route('ventas.facturacion.preparar', $venta) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-file-circle-plus mr-2"></i>Preparar CFDI
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-500">No hay ventas pagadas pendientes por preparar.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-amber-200 bg-amber-50 p-6">
            <h3 class="text-lg font-semibold text-amber-800 mb-3">Que queda listo desde hoy</h3>
            <ul class="space-y-2 text-sm text-amber-800">
                <li><i class="fas fa-check-circle mr-2"></i>Datos fiscales del cliente y del emisor.</li>
                <li><i class="fas fa-check-circle mr-2"></i>Claves SAT por producto o servicio.</li>
                <li><i class="fas fa-check-circle mr-2"></i>Borrador CFDI por venta con conceptos y totales.</li>
                <li><i class="fas fa-check-circle mr-2"></i>Campos del PAC, modo, endpoint y certificados.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
