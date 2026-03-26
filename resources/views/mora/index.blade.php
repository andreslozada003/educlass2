@extends('layouts.app')

@section('title', 'Modulo de Mora')

@section('page-header')
@php
    $headerMetrics = collect([
        $canViewSales && $ventasMetrics ? [
            'label' => 'Ventas',
            'value' => $ventasMetrics['total'],
            'meta' => 'casos abiertos',
            'accent' => 'from-cyan-400/25 to-blue-500/10',
            'icon' => 'fa-mobile-screen-button',
        ] : null,
        $canViewRepairs && $reparacionesMetrics ? [
            'label' => 'Reparaciones',
            'value' => $reparacionesMetrics['total'],
            'meta' => 'casos abiertos',
            'accent' => 'from-emerald-400/25 to-teal-500/10',
            'icon' => 'fa-screwdriver-wrench',
        ] : null,
        [
            'label' => 'Semaforo',
            'value' => '7 / 15 / 30',
            'meta' => 'hitos de control',
            'accent' => 'from-amber-300/25 to-orange-500/10',
            'icon' => 'fa-traffic-light',
        ],
        [
            'label' => 'Canal',
            'value' => 'WA',
            'meta' => 'registro de envios',
            'accent' => 'from-lime-300/25 to-green-500/10',
            'icon' => 'fa-comment-dots',
        ],
    ])->filter()->values();
@endphp

<div class="overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-6 text-white shadow-xl shadow-slate-900/10">
    <div class="grid gap-6 2xl:grid-cols-[minmax(0,1.35fr)_minmax(420px,0.9fr)] 2xl:items-end">
        <div class="max-w-3xl">
            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-200">
                Cobranza y seguimiento
            </span>
            <h1 class="mt-4 max-w-2xl text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">Modulo de mora para ventas y reparaciones</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-[15px]">
                Separamos cartera de ventas y reparaciones, con semaforo automatico, detalle completo por cliente y trazabilidad de abonos y WhatsApp.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-slate-200">
                    <i class="fas fa-layer-group mr-2 text-slate-300"></i>Dos flujos independientes
                </span>
                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-slate-200">
                    <i class="fas fa-calendar-days mr-2 text-slate-300"></i>Calendario por meses
                </span>
                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-slate-200">
                    <i class="fab fa-whatsapp mr-2 text-slate-300"></i>Seguimiento con evidencia
                </span>
            </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-2">
            @foreach($headerMetrics as $metric)
            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br {{ $metric['accent'] }}"></div>
                <div class="relative flex h-full min-h-[132px] flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-300">{{ $metric['label'] }}</p>
                            <p class="mt-3 text-2xl font-semibold leading-none text-white sm:text-3xl">{{ $metric['value'] }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-slate-100">
                            <i class="fas {{ $metric['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-300">{{ $metric['meta'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('content')
@php
    $baseQuery = request()->except(['tab', 'ventas_page', 'reparaciones_page']);
@endphp

<div class="space-y-6">
    <div class="grid gap-4 lg:grid-cols-2">
        @if($canViewSales && $ventasMetrics)
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Ventas en mora</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $ventasMetrics['total'] }} operaciones activas</h2>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Saldo {{ money($ventasMetrics['saldo']) }}</span>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-rose-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-rose-500">Criticos</p>
                    <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $ventasMetrics['criticos'] }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-500">Seguimiento</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ max(0, $ventasMetrics['total'] - $ventasMetrics['criticos'] - $ventasMetrics['porConfigurar']) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Sin fecha</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-700">{{ $ventasMetrics['porConfigurar'] }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($canViewRepairs && $reparacionesMetrics)
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Reparaciones en mora</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $reparacionesMetrics['total'] }} ordenes activas</h2>
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Saldo {{ money($reparacionesMetrics['saldo']) }}</span>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-rose-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-rose-500">Criticos</p>
                    <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $reparacionesMetrics['criticos'] }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-500">Seguimiento</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-700">{{ max(0, $reparacionesMetrics['total'] - $reparacionesMetrics['criticos'] - $reparacionesMetrics['porConfigurar']) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Sin fecha</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-700">{{ $reparacionesMetrics['porConfigurar'] }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Filtros</p>
                <h3 class="text-lg font-semibold text-slate-900">Busca por cliente, asesor, rango o color de mora</h3>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @foreach($semaforos as $meta)
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $meta['badge'] }}">
                    <span class="h-2.5 w-2.5 rounded-full {{ $meta['dot'] }}"></span>{{ $meta['label'] }}
                </span>
                @endforeach
            </div>
        </div>

        <form method="GET" action="{{ route('mora.index') }}" class="mt-6 grid gap-4 xl:grid-cols-6">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="xl:col-span-2">
                <label for="search" class="mb-2 block text-sm font-medium text-slate-600">Cliente o referencia</label>
                <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre, telefono, folio, orden..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
            </div>

            <div>
                <label for="color" class="mb-2 block text-sm font-medium text-slate-600">Semaforo</label>
                <select id="color" name="color" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    <option value="">Todos</option>
                    <option value="sin_fecha" @selected(($filters['color'] ?? '') === 'sin_fecha')>Sin fecha</option>
                    <option value="verde" @selected(($filters['color'] ?? '') === 'verde')>Verde</option>
                    <option value="amarillo" @selected(($filters['color'] ?? '') === 'amarillo')>Amarillo</option>
                    <option value="rojo" @selected(($filters['color'] ?? '') === 'rojo')>Rojo</option>
                </select>
            </div>

            <div>
                <label for="asesor_id" class="mb-2 block text-sm font-medium text-slate-600">Asesor</label>
                <select id="asesor_id" name="asesor_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    <option value="">Todos</option>
                    @foreach($asesores as $asesor)
                    <option value="{{ $asesor->id }}" @selected((string) ($filters['asesor_id'] ?? '') === (string) $asesor->id)>{{ $asesor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha_desde" class="mb-2 block text-sm font-medium text-slate-600">Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
            </div>

            <div>
                <label for="fecha_hasta" class="mb-2 block text-sm font-medium text-slate-600">Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
            </div>

            <div class="flex items-end gap-3 xl:col-span-6">
                <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <i class="fas fa-filter mr-2"></i>Aplicar filtros
                </button>
                <a href="{{ route('mora.index', ['tab' => $tab]) }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                    <i class="fas fa-rotate-left mr-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 pt-5">
            <div class="flex flex-wrap gap-3">
                @if($canViewSales)
                <a href="{{ route('mora.index', array_merge($baseQuery, ['tab' => 'ventas'])) }}" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition {{ $tab === 'ventas' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-mobile-alt mr-2"></i>Ventas
                </a>
                @endif
                @if($canViewRepairs)
                <a href="{{ route('mora.index', array_merge($baseQuery, ['tab' => 'reparaciones'])) }}" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition {{ $tab === 'reparaciones' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-tools mr-2"></i>Reparaciones
                </a>
                @endif
            </div>
        </div>

        @if($tab === 'ventas' && $ventas)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <th class="px-5 py-4">Venta</th>
                        <th class="px-5 py-4">Cliente</th>
                        <th class="px-5 py-4">Equipo</th>
                        <th class="px-5 py-4">Saldo</th>
                        <th class="px-5 py-4">Inicio mora</th>
                        <th class="px-5 py-4">Dias</th>
                        <th class="px-5 py-4">Semaforo</th>
                        <th class="px-5 py-4">Ultimo aviso</th>
                        <th class="px-5 py-4 text-right">Accion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ventas as $venta)
                    @php($palette = \App\Support\MoraSupport::palette($venta->mora_semaforo))
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-5 py-4 align-top">
                            <p class="font-semibold text-slate-900">{{ $venta->folio }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $venta->fecha_venta?->format('d/m/Y') }}</p>
                            <p class="mt-2 text-xs text-slate-400">Asesor: {{ $venta->usuario?->name ?? 'Sin asignar' }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-medium text-slate-900">{{ $venta->cliente?->nombre_completo ?? 'Cliente no asociado' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $venta->cliente?->telefono ?: 'Sin telefono' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $venta->cliente?->rfc ?: 'Sin documento' }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="text-sm font-medium text-slate-800">{{ $venta->resumen_equipo_mora }}</p>
                            <p class="mt-1 text-xs text-slate-500">Total {{ money($venta->total) }} / Abonado {{ money($venta->monto_pagado) }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="text-lg font-semibold text-rose-600">{{ money($venta->saldo_pendiente_mora) }}</p>
                            @if($venta->fecha_compromiso_pago)
                            <p class="mt-1 text-xs text-slate-500">Compromiso {{ $venta->fecha_compromiso_pago->format('d/m/Y') }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top">
                            @if($venta->fecha_inicio_mora)
                            <p class="font-medium text-slate-800">{{ $venta->fecha_inicio_mora->format('d/m/Y') }}</p>
                            @else
                            <p class="font-medium text-slate-500">Sin fecha</p>
                            <p class="mt-1 text-xs text-slate-400">Configurar para activar el semaforo</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-semibold text-slate-900">{{ $venta->dias_en_mora }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $venta->mora_etapa }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $palette['badge'] }}">
                                <span class="h-2.5 w-2.5 rounded-full {{ $palette['dot'] }}"></span>{{ $palette['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top">
                            @if($venta->ultima_notificacion_mora_at)
                            <p class="font-medium text-slate-800">{{ $venta->ultima_notificacion_mora_at->format('d/m/Y H:i') }}</p>
                            @else
                            <p class="text-slate-500">Sin registros</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top text-right">
                            <a href="{{ route('mora.ventas.show', $venta) }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-slate-500">No hay ventas en mora con los filtros seleccionados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4">
            {{ $ventas->links() }}
        </div>
        @endif

        @if($tab === 'reparaciones' && $reparaciones)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <th class="px-5 py-4">Orden</th>
                        <th class="px-5 py-4">Cliente</th>
                        <th class="px-5 py-4">Equipo / servicio</th>
                        <th class="px-5 py-4">Saldo</th>
                        <th class="px-5 py-4">Inicio mora</th>
                        <th class="px-5 py-4">Dias</th>
                        <th class="px-5 py-4">Semaforo</th>
                        <th class="px-5 py-4">Ultimo aviso</th>
                        <th class="px-5 py-4 text-right">Accion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reparaciones as $reparacion)
                    @php($palette = \App\Support\MoraSupport::palette($reparacion->mora_semaforo))
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-5 py-4 align-top">
                            <p class="font-semibold text-slate-900">{{ $reparacion->orden }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $reparacion->fecha_recepcion?->format('d/m/Y') }}</p>
                            <p class="mt-2 text-xs text-slate-400">Asesor: {{ $reparacion->usuario?->name ?? 'Sin asignar' }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-medium text-slate-900">{{ $reparacion->cliente?->nombre_completo ?? 'Cliente no asociado' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $reparacion->cliente?->telefono ?: 'Sin telefono' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $reparacion->cliente?->rfc ?: 'Sin documento' }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="text-sm font-medium text-slate-800">{{ $reparacion->dispositivo_info }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($reparacion->problema_reportado, 72) }}</p>
                            <p class="mt-1 text-xs text-slate-400">Estimado {{ money($reparacion->valor_operacion_mora) }} / Abonado {{ money($reparacion->adelanto) }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="text-lg font-semibold text-rose-600">{{ money($reparacion->saldo_pendiente_mora) }}</p>
                            <p class="mt-1 text-xs text-slate-500">Estado {{ $reparacion->estado_nombre }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            @if($reparacion->fecha_inicio_mora)
                            <p class="font-medium text-slate-800">{{ $reparacion->fecha_inicio_mora->format('d/m/Y') }}</p>
                            @else
                            <p class="font-medium text-slate-500">Sin fecha</p>
                            <p class="mt-1 text-xs text-slate-400">Configurar para activar el semaforo</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-semibold text-slate-900">{{ $reparacion->dias_en_mora }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $reparacion->mora_etapa }}</p>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $palette['badge'] }}">
                                <span class="h-2.5 w-2.5 rounded-full {{ $palette['dot'] }}"></span>{{ $palette['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top">
                            @if($reparacion->ultima_notificacion_mora_at)
                            <p class="font-medium text-slate-800">{{ $reparacion->ultima_notificacion_mora_at->format('d/m/Y H:i') }}</p>
                            @else
                            <p class="text-slate-500">Sin registros</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top text-right">
                            <a href="{{ route('mora.reparaciones.show', $reparacion) }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-sm text-slate-500">No hay reparaciones en mora con los filtros seleccionados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4">
            {{ $reparaciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
