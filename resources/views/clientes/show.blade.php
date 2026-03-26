@extends('layouts.app')

@section('title', $cliente->nombre_completo)

@section('page-header')
<div class="flex flex-col md:flex-row md:items-center md:justify-between">
    <div class="flex items-center">
        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mr-4">
            <span class="text-2xl font-bold text-primary-600">{{ substr($cliente->nombre, 0, 1) }}</span>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $cliente->nombre_completo }}</h1>
            <p class="text-gray-500">Cliente desde {{ $cliente->created_at->format('d/m/Y') }}</p>
        </div>
    </div>
    <div class="mt-4 md:mt-0 flex space-x-3">
        <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
        @if($cliente->telefono)
        <a href="{{ $cliente->whatsapp_link }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <i class="fab fa-whatsapp mr-2"></i>WhatsApp
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Total gastado</p>
        <p class="text-2xl font-bold text-green-600">{{ money($totalCompras) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Compras realizadas</p>
        <p class="text-2xl font-bold text-primary-600">{{ $cliente->ventas->count() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500">Reparaciones</p>
        <p class="text-2xl font-bold text-blue-600">{{ $totalReparaciones }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle mr-2 text-primary-600"></i>Informacion de contacto
            </h3>

            <div class="space-y-3">
                @if($cliente->telefono)
                <div class="flex items-center">
                    <i class="fas fa-phone w-6 text-gray-400"></i>
                    <span class="text-gray-700">{{ $cliente->telefono }}</span>
                </div>
                @endif

                @if($cliente->email)
                <div class="flex items-center">
                    <i class="fas fa-envelope w-6 text-gray-400"></i>
                    <span class="text-gray-700">{{ $cliente->email }}</span>
                </div>
                @endif

                @if($cliente->fecha_nacimiento)
                <div class="flex items-center">
                    <i class="fas fa-birthday-cake w-6 text-gray-400"></i>
                    <span class="text-gray-700">{{ $cliente->fecha_nacimiento->format('d/m/Y') }}</span>
                </div>
                @endif

                @if($cliente->direccion)
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-1"><i class="fas fa-map-marker-alt mr-2"></i>Direccion</p>
                    <p class="text-gray-700">{{ $cliente->direccion }}</p>
                    @if($cliente->ciudad || $cliente->estado)
                    <p class="text-gray-500 text-sm">{{ $cliente->ciudad }}{{ $cliente->ciudad && $cliente->estado ? ', ' : '' }}{{ $cliente->estado }} {{ $cliente->codigo_postal }}</p>
                    @endif
                </div>
                @endif

                @if($cliente->notas)
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-1"><i class="fas fa-sticky-note mr-2"></i>Notas</p>
                    <p class="text-gray-700 text-sm">{{ $cliente->notas }}</p>
                </div>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $cliente->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-file-invoice mr-2 text-emerald-600"></i>Datos fiscales
            </h3>

            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Nombre fiscal</p>
                    <p class="font-medium">{{ $cliente->nombre_fiscal ?: 'No definido' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">RFC</p>
                    <p class="font-medium">{{ $cliente->rfc ?: 'No definido' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Codigo postal</p>
                    <p class="font-medium">{{ $cliente->codigo_postal ?: 'No definido' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Regimen fiscal</p>
                    <p class="font-medium">
                        @if($cliente->regimen_fiscal)
                        {{ $cliente->regimen_fiscal }} - {{ $regimenesFiscales[$cliente->regimen_fiscal] ?? 'Catalogo no identificado' }}
                        @else
                        No definido
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Uso CFDI</p>
                    <p class="font-medium">
                        @if($cliente->uso_cfdi)
                        {{ $cliente->uso_cfdi }} - {{ $usosCfdi[$cliente->uso_cfdi] ?? 'Catalogo no identificado' }}
                        @else
                        No definido
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-shopping-cart mr-2 text-primary-600"></i>Ultimas compras
            </h3>
            <a href="{{ route('clientes.historial', $cliente) }}" class="text-sm text-primary-600 hover:text-primary-700">Ver historial completo</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Folio</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cliente->ventas as $venta)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-primary-600 hover:text-primary-700">{{ $venta->folio }}</a>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-sm font-medium">{{ money($venta->total) }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $venta->estado === 'pagada' ? 'bg-green-100 text-green-700' : ($venta->estado === 'cancelada' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($venta->estado) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">No hay compras registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-tools mr-2 text-primary-600"></i>Ultimas reparaciones
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Costo</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($cliente->reparaciones as $reparacion)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                        <a href="{{ route('reparaciones.show', $reparacion) }}" class="text-primary-600 hover:text-primary-700">{{ $reparacion->orden }}</a>
                    </td>
                    <td class="px-3 py-2 text-sm text-gray-700">{{ $reparacion->dispositivo_info }}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex px-2 py-1 text-xs rounded-full bg-{{ $reparacion->estado_color }}-100 text-{{ $reparacion->estado_color }}-800">
                            {{ $reparacion->estado_nombre }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-sm font-medium">{{ money($reparacion->costo_final ?: $reparacion->costo_estimado) }}</td>
                    <td class="px-3 py-2 text-sm text-gray-500">{{ $reparacion->fecha_recepcion->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 text-center text-gray-500">No hay reparaciones registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
