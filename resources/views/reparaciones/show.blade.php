@extends('layouts.app')

@section('title', 'Orden ' . $reparacion->orden)

@section('page-header')
<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Orden {{ $reparacion->orden }}</h1>
        <p class="text-gray-500">
            {{ $reparacion->fecha_recepcion?->format('d \d\e F, Y H:i') ?? 'Sin fecha registrada' }}
        </p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('reparaciones.imprimir-orden', $reparacion) }}" target="_blank" class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700">
            <i class="fas fa-print mr-2"></i>Imprimir
        </a>
        <a href="{{ route('reparaciones.edit', $reparacion) }}" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-white transition-colors hover:bg-amber-700">
            <i class="fas fa-edit mr-2"></i>Editar
        </a>
    </div>
</div>
@endsection

@section('content')
@php
    $cliente = $reparacion->cliente;
    $usuario = $reparacion->usuario;
    $tecnico = $reparacion->tecnico;
    $costoEstimado = (float) $reparacion->costo_estimado;
    $costoFinal = (float) $reparacion->costo_final;
    $adelanto = (float) $reparacion->adelanto;
    $saldoPendiente = $costoFinal > 0
        ? max(0, $costoFinal - $adelanto)
        : max(0, $costoEstimado - $adelanto);

    $fotosAntes = collect([
        $reparacion->foto_antes_1,
        $reparacion->foto_antes_2,
        $reparacion->foto_antes_3,
    ])->filter();

    $fotosDespues = collect([
        $reparacion->foto_despues_1,
        $reparacion->foto_despues_2,
        $reparacion->foto_despues_3,
    ])->filter();
@endphp

<div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-gray-500">Estado actual:</span>
            <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold bg-{{ $reparacion->estado_color }}-100 text-{{ $reparacion->estado_color }}-800">
                {{ $reparacion->estado_nombre }}
            </span>

            @if($reparacion->fecha_estimada_entrega)
            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                Entrega estimada: {{ $reparacion->fecha_estimada_entrega->format('d/m/Y') }}
            </span>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if($reparacion->estado !== 'entregado' && $reparacion->estado !== 'cancelado')
            <form action="{{ route('reparaciones.cambiar-estado', $reparacion) }}" method="POST" class="flex flex-wrap gap-2">
                @csrf
                <select name="estado" class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500">
                    @foreach(App\Models\Reparacion::ESTADOS as $key => $nombre)
                    @if($key !== $reparacion->estado)
                    <option value="{{ $key }}">{{ $nombre }}</option>
                    @endif
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-white transition-colors hover:bg-primary-700">
                    Cambiar
                </button>
            </form>
            @endif

            @if($reparacion->estado === 'listo' && !$reparacion->notificado_listo)
            <a href="{{ route('reparaciones.notificar', $reparacion) }}" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700">
                <i class="fab fa-whatsapp mr-2"></i>Notificar
            </a>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">
            <i class="fas fa-user mr-2 text-primary-600"></i>Informacion del cliente
        </h3>

        @if($cliente)
        <div class="space-y-3">
            <div>
                <p class="text-sm text-gray-500">Cliente</p>
                <p class="text-lg font-semibold text-gray-800">{{ $cliente->nombre_completo }}</p>
            </div>

            @if($cliente->telefono)
            <div>
                <p class="text-sm text-gray-500">Telefono</p>
                <p class="text-gray-700">{{ $cliente->telefono }}</p>
            </div>
            @endif

            @if($cliente->email)
            <div>
                <p class="text-sm text-gray-500">Correo</p>
                <p class="text-gray-700">{{ $cliente->email }}</p>
            </div>
            @endif

            @if($cliente->direccion)
            <div>
                <p class="text-sm text-gray-500">Direccion</p>
                <p class="text-gray-700">{{ $cliente->direccion }}</p>
            </div>
            @endif

            <a href="{{ route('clientes.show', $cliente) }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                Ver perfil del cliente <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        @else
        <p class="text-sm text-gray-500">No hay un cliente asociado a esta orden.</p>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">
            <i class="fas fa-mobile-alt mr-2 text-primary-600"></i>Equipo recibido
        </h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm text-gray-500">Tipo</p>
                <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_tipo ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Marca</p>
                <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_marca ?: 'No registrada' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Modelo</p>
                <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_modelo ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Color</p>
                <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_color ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">IMEI</p>
                <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_imei ?: 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Serial</p>
                <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_serial ?: 'No registrado' }}</p>
            </div>
        </div>

        @if($reparacion->dispositivo_contrasena)
        <div class="mt-4 border-t border-gray-200 pt-4">
            <p class="text-sm text-gray-500">Contrasena entregada</p>
            <p class="font-medium text-gray-800">{{ $reparacion->dispositivo_contrasena }}</p>
        </div>
        @endif
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">
            <i class="fas fa-clipboard-check mr-2 text-primary-600"></i>Seguimiento
        </h3>

        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Fecha de recepcion</p>
                    <p class="font-medium text-gray-800">{{ $reparacion->fecha_recepcion?->format('d/m/Y H:i') ?? 'Sin registrar' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Entrega estimada</p>
                    <p class="font-medium text-gray-800">{{ $reparacion->fecha_estimada_entrega?->format('d/m/Y H:i') ?? 'Pendiente' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Entrega final</p>
                    <p class="font-medium text-gray-800">{{ $reparacion->fecha_entrega?->format('d/m/Y H:i') ?? 'Aun no entregada' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Garantia</p>
                    <p class="font-medium text-gray-800">{{ $reparacion->garantia_dias ?: '0' }} dias</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Recibido por</p>
                    <p class="font-medium text-gray-800">{{ $usuario?->name ?? 'No asignado' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tecnico asignado</p>
                    <p class="font-medium text-gray-800">{{ $tecnico?->name ?? 'Sin tecnico asignado' }}</p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <p class="text-sm text-gray-500">Notificacion al cliente</p>
                <p class="font-medium {{ $reparacion->notificado_listo ? 'text-green-600' : 'text-gray-700' }}">
                    {{ $reparacion->notificado_listo ? 'Cliente notificado' : 'Aun no notificado' }}
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">
            <i class="fas fa-dollar-sign mr-2 text-primary-600"></i>Costos y cobro
        </h3>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Costo estimado</span>
                <span class="font-medium text-gray-800">{{ money($costoEstimado) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Adelanto</span>
                <span class="font-medium text-green-600">{{ money($adelanto) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Costo final</span>
                @if($costoFinal > 0)
                <span class="font-medium text-primary-600">{{ money($costoFinal) }}</span>
                @else
                <span class="font-medium text-amber-600">Pendiente de definir</span>
                @endif
            </div>

            <div class="border-t border-gray-200 pt-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Saldo pendiente</span>
                    @if($costoFinal > 0)
                    <span class="font-bold {{ $saldoPendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ money($saldoPendiente) }}
                    </span>
                    @else
                    <span class="font-bold text-amber-600">Por calcular</span>
                    @endif
                </div>

                @if($costoFinal <= 0 && $costoEstimado > 0)
                <p class="mt-2 text-xs text-gray-500">
                    Referencia actual: con el costo estimado quedarian {{ money(max(0, $costoEstimado - $adelanto)) }} por cobrar.
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">
            <i class="fas fa-exclamation-circle mr-2 text-red-600"></i>Problema reportado
        </h3>

        <p class="whitespace-pre-line text-gray-700">{{ $reparacion->problema_reportado ?: 'Sin descripcion del problema.' }}</p>

        @if($reparacion->condiciones_previas)
        <div class="mt-4 border-t border-gray-200 pt-4">
            <p class="mb-1 text-sm text-gray-500">Condiciones previas</p>
            <p class="text-gray-700 whitespace-pre-line">{{ $reparacion->condiciones_previas }}</p>
        </div>
        @endif

        @if($reparacion->accesorios_incluidos)
        <div class="mt-4 border-t border-gray-200 pt-4">
            <p class="mb-1 text-sm text-gray-500">Accesorios incluidos</p>
            <p class="text-gray-700 whitespace-pre-line">{{ $reparacion->accesorios_incluidos }}</p>
        </div>
        @endif

        @if($reparacion->notas_cliente)
        <div class="mt-4 border-t border-gray-200 pt-4">
            <p class="mb-1 text-sm text-gray-500">Notas del cliente</p>
            <p class="text-gray-700 whitespace-pre-line">{{ $reparacion->notas_cliente }}</p>
        </div>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">
            <i class="fas fa-stethoscope mr-2 text-blue-600"></i>Diagnostico y solucion
        </h3>

        <div class="space-y-4">
            <div>
                <p class="mb-1 text-sm text-gray-500">Diagnostico</p>
                <p class="whitespace-pre-line text-gray-700">{{ $reparacion->diagnostico ?: 'Aun no se ha registrado un diagnostico.' }}</p>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <p class="mb-1 text-sm text-gray-500">Solucion</p>
                <p class="whitespace-pre-line text-gray-700">{{ $reparacion->solucion ?: 'Aun no se ha registrado una solucion.' }}</p>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <p class="mb-1 text-sm text-gray-500">Notas del tecnico</p>
                <p class="whitespace-pre-line text-gray-700">{{ $reparacion->notas_tecnico ?: 'Sin notas tecnicas por ahora.' }}</p>
            </div>
        </div>
    </div>
</div>

@if($fotosAntes->isNotEmpty() || $fotosDespues->isNotEmpty())
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="mb-4 text-lg font-semibold text-gray-800">
        <i class="fas fa-camera mr-2 text-primary-600"></i>Registro fotografico
    </h3>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div>
            <p class="mb-3 text-sm font-medium text-gray-500">Fotos antes</p>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                @forelse($fotosAntes as $foto)
                <a href="{{ asset('storage/' . $foto) }}" target="_blank" class="block overflow-hidden rounded-lg border border-gray-200">
                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto antes" class="h-32 w-full object-cover">
                </a>
                @empty
                <p class="col-span-full text-sm text-gray-500">No hay fotos iniciales registradas.</p>
                @endforelse
            </div>
        </div>

        <div>
            <p class="mb-3 text-sm font-medium text-gray-500">Fotos despues</p>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                @forelse($fotosDespues as $foto)
                <a href="{{ asset('storage/' . $foto) }}" target="_blank" class="block overflow-hidden rounded-lg border border-gray-200">
                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto despues" class="h-32 w-full object-cover">
                </a>
                @empty
                <p class="col-span-full text-sm text-gray-500">Todavia no hay fotos finales registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="mb-4 text-lg font-semibold text-gray-800">
        <i class="fas fa-history mr-2 text-primary-600"></i>Historial de estados
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Fecha</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Estado anterior</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Estado nuevo</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Usuario</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Comentario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($reparacion->historial as $hist)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-sm text-gray-700">{{ $hist->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700">
                            {{ $hist->estado_anterior_nombre }}
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        <span class="inline-flex rounded-full bg-primary-100 px-2 py-1 text-xs text-primary-700">
                            {{ $hist->estado_nuevo_nombre }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-sm text-gray-700">{{ $hist->usuario?->name ?? 'Sistema' }}</td>
                    <td class="px-3 py-2 text-sm text-gray-700">{{ $hist->comentario ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 text-center text-gray-500">No hay cambios de estado registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
