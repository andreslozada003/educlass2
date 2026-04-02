@extends('layouts.app')

@section('title', $type === 'ventas' ? 'Detalle de Mora ' . $record->folio : 'Detalle de Mora ' . $record->orden)

@section('page-header')
@php
    $isVenta = $type === 'ventas';
    $cliente = $record->cliente;
    $palette = $palette ?? \App\Support\MoraSupport::palette($record->mora_semaforo);
    $whatsappRoute = $isVenta ? route('mora.ventas.whatsapp', $record) : route('mora.reparaciones.whatsapp', $record);
    $updateRoute = $isVenta ? route('mora.ventas.update', $record) : route('mora.reparaciones.update', $record);
    $abonoRoute = $isVenta ? route('mora.ventas.abonos.store', $record) : route('mora.reparaciones.abonos.store', $record);
    $creditSummary = $creditSummary ?? null;
    $calendarQuery = array_merge(request()->except('month'), ['months' => $months]);
    $previousMonthUrl = $detailRoute . '?' . http_build_query(array_merge($calendarQuery, ['month' => $visibleMonth->copy()->subMonth()->format('Y-m')]));
    $nextMonthUrl = $detailRoute . '?' . http_build_query(array_merge($calendarQuery, ['month' => $visibleMonth->copy()->addMonth()->format('Y-m')]));
@endphp

<div class="overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-emerald-50 p-6 shadow-sm">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-3xl">
            <a href="{{ $backRoute }}" class="inline-flex items-center text-sm font-medium text-slate-500 transition hover:text-primary-600">
                <i class="fas fa-arrow-left mr-2"></i>Volver al tablero de mora
            </a>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $palette['badge'] }}">
                    <span class="h-2.5 w-2.5 rounded-full {{ $palette['dot'] }}"></span>{{ $record->mora_etapa }}
                </span>
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                    {{ $isVenta ? 'Venta' : 'Reparacion' }} {{ $isVenta ? $record->folio : $record->orden }}
                </span>
            </div>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">
                {{ $cliente?->nombre_completo ?: 'Cliente no asociado' }}
            </h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                {{ $isVenta ? 'Seguimiento de cobranza para una venta a credito.' : 'Seguimiento de cobro y retiro para una reparacion pendiente.' }}
            </p>
        </div>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Saldo</p>
                <p class="mt-2 text-2xl font-semibold text-rose-600">{{ money($record->saldo_pendiente_mora) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Dias</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $record->dias_en_mora }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $isVenta ? 'Vence actual' : 'Inicio mora' }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">
                    @if($isVenta && $creditSummary)
                    {{ $creditSummary['current_due_date']?->format('d/m/Y') ?: 'Sin fecha' }}
                    @else
                    {{ $record->fecha_inicio_mora?->format('d/m/Y') ?: 'Sin fecha' }}
                    @endif
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Ultimo aviso</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $record->ultima_notificacion_mora_at?->format('d/m/Y H:i') ?: 'Sin registros' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Cliente</h2>
                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Nombre</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $cliente?->nombre_completo ?: 'Sin cliente' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Documento</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $cliente?->rfc ?: 'Sin documento' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">WhatsApp</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $cliente?->telefono ?: 'Sin telefono' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Direccion</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $cliente?->direccion ?: 'No registrada' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Correo</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $cliente?->email ?: 'No registrado' }}</p>
                        </div>
                    </div>
                    @if($cliente)
                    <a href="{{ route('clientes.show', $cliente) }}" class="mt-5 inline-flex items-center text-sm font-semibold text-primary-600 transition hover:text-primary-700">
                        Ver ficha del cliente <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $isVenta ? 'Operacion de venta' : 'Operacion de reparacion' }}</h2>
                    <div class="mt-5 space-y-4">
                        @if($isVenta)
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Equipo vendido</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->resumen_equipo_mora }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Fecha de venta</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->fecha_venta?->format('d/m/Y H:i') ?: 'Sin registro' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Base, cuotas y vencimiento</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                Base: {{ $creditSummary['base_date']?->format('d/m/Y') ?: ($record->fecha_inicio_mora?->format('d/m/Y') ?: 'Sin fecha base') }}
                                <br>
                                Vence actual:
                                @if($creditSummary && $creditSummary['current_due_date'])
                                {{ $creditSummary['current_installment_number'] ? 'Cuota ' . $creditSummary['current_installment_number'] . ' de ' . max(1, $creditSummary['installments']) . ' / ' : '' }}{{ $creditSummary['current_due_date']->format('d/m/Y') }}
                                @else
                                Sin vencimiento calculado
                                @endif
                            </p>
                            <p class="mt-1 font-medium text-slate-900">
                                {{ $record->numero_cuotas ? $record->numero_cuotas . ' cuotas' : 'Sin cuotas registradas' }}
                                @if($creditSummary && $creditSummary['installment_amount'] > 0)
                                / {{ money($creditSummary['installment_amount']) }} por cuota
                                @endif
                                @if($record->plazo_acordado_dias)
                                / {{ $record->plazo_acordado_dias }} dias
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Vendedor responsable</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->usuario?->name ?: 'Sin asignar' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Observaciones</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->notas ?: 'Sin observaciones de venta' }}</p>
                        </div>
                        @else
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Equipo recibido</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->dispositivo_info }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">IMEI / serial</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->dispositivo_imei ?: ($record->dispositivo_serial ?: 'No registrado') }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Fecha de ingreso</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->fecha_recepcion?->format('d/m/Y H:i') ?: 'Sin registro' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Diagnostico</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->diagnostico ?: 'Pendiente de diagnostico' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Servicio reportado</p>
                            <p class="mt-1 font-medium text-slate-900">{{ $record->problema_reportado ?: 'Sin detalle del servicio' }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Calendario semaforizado</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            @if($isVenta && $creditSummary)
                            El color se calcula desde el vencimiento vigente de la cuota actual.
                            @else
                            Puedes visualizar uno, dos o tres meses completos segun la mora configurada.
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach([1, 2, 3] as $option)
                        <a href="{{ request()->fullUrlWithQuery(['months' => $option]) }}" class="inline-flex items-center rounded-full px-3 py-2 text-sm font-semibold transition {{ $months === $option ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $option }} {{ $option === 1 ? 'mes' : 'meses' }}
                        </a>
                        @endforeach
                    </div>
                </div>

                @if($isVenta && $creditSummary)
                <div class="mt-6 grid gap-3 rounded-3xl border border-slate-200 bg-slate-50/80 p-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Fecha base</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $creditSummary['base_date']?->format('d/m/Y') ?: 'Sin fecha base' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Cuota vigente</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $creditSummary['current_installment_number'] ? 'Cuota ' . $creditSummary['current_installment_number'] . ' de ' . max(1, $creditSummary['installments']) : 'Sin cuota activa' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Vence</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $creditSummary['current_due_date']?->format('d/m/Y') ?: 'Sin vencimiento' }}</p>
                    </div>
                </div>
                @endif

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ $previousMonthUrl }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        <i class="fas fa-chevron-left mr-2"></i>Anterior
                    </a>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($milestones as $milestone)
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $milestone['reached'] ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                            {{ $milestone['label'] }}
                            @if($milestone['date'])
                            <span class="{{ $milestone['reached'] ? 'text-slate-200' : 'text-slate-400' }}">{{ $milestone['date']->format('d/m') }}</span>
                            @endif
                        </span>
                        @endforeach
                    </div>
                    <a href="{{ $nextMonthUrl }}" class="inline-flex items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Siguiente<i class="fas fa-chevron-right ml-2"></i>
                    </a>
                </div>

                <div class="mt-6 grid gap-4 {{ count($calendar) > 1 ? 'xl:grid-cols-2' : '' }}">
                    @foreach($calendar as $month)
                    <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $month['title'] }}</h3>
                            <span class="text-xs text-slate-400">{{ $isVenta ? 'Cuotas y mora' : 'Semaforo automatico' }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-7 gap-2 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            <span>Lun</span>
                            <span>Mar</span>
                            <span>Mie</span>
                            <span>Jue</span>
                            <span>Vie</span>
                            <span>Sab</span>
                            <span>Dom</span>
                        </div>
                        <div class="mt-3 grid grid-cols-7 gap-2">
                            @foreach($month['days'] as $day)
                                @if($day['blank'])
                                <div class="min-h-[110px] rounded-2xl border border-dashed border-transparent"></div>
                                @else
                                @php($entries = collect($day['entries'] ?? []))
                                @php($installmentEntries = $entries->where('entry_type', 'installment'))
                                @php($metaEntries = $entries->reject(fn ($entry) => ($entry['entry_type'] ?? null) === 'installment'))
                                <div class="min-h-[110px] rounded-2xl border p-2 text-left {{ $day['palette']['calendar'] }} {{ $day['is_today'] ? 'ring-2 ring-slate-900/20' : '' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="text-sm font-semibold">{{ $day['date']->day }}</span>
                                        <div class="flex flex-wrap justify-end gap-1">
                                            @foreach($metaEntries as $entry)
                                            <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-700">{{ $entry['label'] }}</span>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if($installmentEntries->isNotEmpty())
                                    <div class="mt-2 space-y-2">
                                        @foreach($installmentEntries as $entry)
                                        <div class="rounded-xl border px-2 py-2 text-[11px] leading-4 {{ $entry['card_classes'] ?? 'border-slate-200 bg-white/80 text-slate-700' }}">
                                            <p class="font-semibold">{{ $entry['client_name'] }}</p>
                                            <p class="mt-1">Cuota {{ $entry['installment_number'] }} / {{ $entry['amount_label'] }}</p>
                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $entry['badge_classes'] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $entry['status_label'] }}</span>
                                                @if(($entry['days_in_mora'] ?? 0) > 0)
                                                <span class="text-[10px] font-semibold">+{{ $entry['days_in_mora'] }} dias</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($isVenta && $creditSummary && $creditSummary['cuotas']->isNotEmpty())
                <div class="mt-6 rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Plan de cuotas</h3>
                            <p class="mt-1 text-sm text-slate-500">Cada pago se aplica primero a la cuota pendiente mas antigua y la mora se quita sola cuando la cuota queda cubierta.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $creditSummary['installments'] }} cuotas</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    <th class="px-5 py-4">Cuota</th>
                                    <th class="px-5 py-4">Vencimiento</th>
                                    <th class="px-5 py-4">Valor</th>
                                    <th class="px-5 py-4">Pagado</th>
                                    <th class="px-5 py-4">Saldo</th>
                                    <th class="px-5 py-4">Estado</th>
                                    <th class="px-5 py-4">Dias mora</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($creditSummary['cuotas'] as $cuota)
                                <tr class="{{ $cuota->esta_en_mora ? 'bg-rose-50/60' : ($cuota->esta_pagada ? 'bg-emerald-50/40' : '') }}">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900">Cuota {{ $cuota->numero_cuota }}</p>
                                        @if($creditSummary['current_quota'] && $creditSummary['current_quota']->numero_cuota === $cuota->numero_cuota)
                                        <p class="mt-1 text-xs font-semibold {{ $cuota->esta_en_mora ? 'text-rose-600' : 'text-primary-600' }}">Cuota actual</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-700">{{ $cuota->fecha_vencimiento?->format('d/m/Y') ?: '-' }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-900">{{ money($cuota->valor_cuota) }}</td>
                                    <td class="px-5 py-4 text-sm text-emerald-700">
                                        {{ money($cuota->monto_pagado) }}
                                        @if($cuota->fecha_pago)
                                        <p class="mt-1 text-xs text-slate-500">Pagada {{ $cuota->fecha_pago->format('d/m/Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold {{ $cuota->esta_en_mora ? 'text-rose-600' : 'text-slate-900' }}">{{ money($cuota->saldo_pendiente) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $cuota->estado_badge_classes }}">{{ $cuota->estado_etiqueta }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold {{ $cuota->esta_en_mora ? 'text-rose-600' : 'text-slate-500' }}">{{ $cuota->dias_mora_actual }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Historial de abonos</h2>
                            <p class="mt-1 text-sm text-slate-500">Cada movimiento queda asociado al usuario y fecha.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $record->moraAbonos->count() }} registros</span>
                    </div>

                    <div class="mt-5 max-h-[420px] space-y-3 overflow-y-auto pr-1">
                        @forelse($record->moraAbonos->sortByDesc('fecha_pago') as $abono)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold {{ $abono->monto < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ money($abono->monto) }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ ucfirst($abono->tipo) }} · {{ $abono->metodo_pago ?: 'Sin metodo' }}</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    <p>{{ $abono->fecha_pago?->format('d/m/Y H:i') ?: '-' }}</p>
                                    <p class="mt-1">{{ $abono->usuario?->name ?: 'Sistema' }}</p>
                                </div>
                            </div>
                            @if($abono->notas)
                            <p class="mt-3 text-sm text-slate-600">{{ $abono->notas }}</p>
                            @endif
                        </div>
                        @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                            Todavia no hay abonos registrados en este caso.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Historial de WhatsApp</h2>
                            <p class="mt-1 text-sm text-slate-500">Se registra plantilla, nivel y usuario que disparo la gestion.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $record->moraNotificaciones->count() }} envios</span>
                    </div>

                    <div class="mt-5 max-h-[420px] space-y-3 overflow-y-auto pr-1">
                        @forelse($record->moraNotificaciones->sortByDesc('fecha_envio') as $notificacion)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ ucfirst($notificacion->nivel ?: 'manual') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $notificacion->telefono ?: 'Sin telefono registrado' }}</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    <p>{{ $notificacion->fecha_envio?->format('d/m/Y H:i') ?: '-' }}</p>
                                    <p class="mt-1">{{ $notificacion->usuario?->name ?: 'Sistema' }}</p>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">{{ $notificacion->mensaje }}</p>
                        </div>
                        @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                            No se han enviado notificaciones de mora para este caso.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Saldo y gestion</h2>
                        <p class="mt-1 text-sm text-slate-500">Estado ejecutivo del caso.</p>
                    </div>
                    @if($cliente?->telefono)
                    <a href="{{ $whatsappRoute }}" target="_blank" class="inline-flex items-center rounded-2xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
                        <i class="fab fa-whatsapp mr-2"></i>Enviar WhatsApp
                    </a>
                    @else
                    <span class="inline-flex items-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-500">
                        Sin WhatsApp
                    </span>
                    @endif
                </div>

                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl {{ $palette['surface'] }} border p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-600">Semaforo actual</span>
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $palette['badge'] }}">
                                <span class="h-2.5 w-2.5 rounded-full {{ $palette['dot'] }}"></span>{{ $palette['label'] }}
                            </span>
                        </div>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ money($record->saldo_pendiente_mora) }}</p>
                        <p class="mt-2 text-sm text-slate-600">Saldo pendiente actual</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">{{ $isVenta ? 'Valor total' : 'Valor de referencia' }}</span>
                            <span class="font-semibold text-slate-900">{{ money($isVenta ? $record->total : $record->valor_operacion_mora) }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-slate-500">Abonado</span>
                            <span class="font-semibold text-emerald-600">{{ money($isVenta ? $record->monto_pagado : $record->adelanto) }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-slate-500">Responsable</span>
                            <span class="font-semibold text-slate-900">{{ $record->usuario?->name ?: 'Sin asignar' }}</span>
                        </div>
                        @if(!$isVenta && $record->tecnico)
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-slate-500">Tecnico</span>
                            <span class="font-semibold text-slate-900">{{ $record->tecnico->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Configurar seguimiento</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $isVenta ? 'Actualiza la fecha base del credito, el compromiso y las condiciones de cobro.' : 'Actualiza la fecha de inicio, notas y condiciones de cobro.' }}
                </p>

                <form action="{{ $updateRoute }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="fecha_inicio_mora" class="mb-2 block text-sm font-medium text-slate-600">{{ $isVenta ? 'Fecha base del credito' : 'Fecha de inicio de mora' }}</label>
                        <input type="date" id="fecha_inicio_mora" name="fecha_inicio_mora" value="{{ old('fecha_inicio_mora', optional($record->fecha_inicio_mora)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                        @if($isVenta)
                        <p class="mt-2 text-xs text-slate-500">Si la dejas vacia, el sistema toma la fecha de la venta para calcular las cuotas y los dias de mora.</p>
                        @endif
                    </div>

                    @if($isVenta)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="fecha_compromiso_pago" class="mb-2 block text-sm font-medium text-slate-600">Compromiso de pago</label>
                            <input type="date" id="fecha_compromiso_pago" name="fecha_compromiso_pago" value="{{ old('fecha_compromiso_pago', optional($record->fecha_compromiso_pago)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                        </div>
                        <div>
                            <label for="numero_cuotas" class="mb-2 block text-sm font-medium text-slate-600">Numero de cuotas</label>
                            <input type="number" id="numero_cuotas" name="numero_cuotas" min="1" max="48" value="{{ old('numero_cuotas', $record->numero_cuotas) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                        </div>
                    </div>

                    <div>
                        <label for="plazo_acordado_dias" class="mb-2 block text-sm font-medium text-slate-600">Plazo acordado en dias</label>
                        <input type="number" id="plazo_acordado_dias" name="plazo_acordado_dias" min="1" max="365" value="{{ old('plazo_acordado_dias', $record->plazo_acordado_dias) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    </div>
                    @endif

                    <div>
                        <label for="mora_observaciones" class="mb-2 block text-sm font-medium text-slate-600">Observaciones</label>
                        <textarea id="mora_observaciones" name="mora_observaciones" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">{{ old('mora_observaciones', $record->mora_observaciones) }}</textarea>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Guardar seguimiento
                    </button>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Registrar abono</h2>
                <p class="mt-1 text-sm text-slate-500">El sistema suma el movimiento y recalcula el saldo automaticamente.</p>

                <form action="{{ $abonoRoute }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="monto" class="mb-2 block text-sm font-medium text-slate-600">Monto</label>
                        <input type="number" id="monto" name="monto" step="0.01" min="0.01" max="{{ $record->saldo_pendiente_mora }}" value="{{ old('monto') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="metodo_pago" class="mb-2 block text-sm font-medium text-slate-600">Metodo de pago</label>
                            <input type="text" id="metodo_pago" name="metodo_pago" value="{{ old('metodo_pago') }}" placeholder="Efectivo, transferencia..." class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                        </div>
                        <div>
                            <label for="fecha_pago" class="mb-2 block text-sm font-medium text-slate-600">Fecha de pago</label>
                            <input type="date" id="fecha_pago" name="fecha_pago" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">
                        </div>
                    </div>

                    <div>
                        <label for="notas" class="mb-2 block text-sm font-medium text-slate-600">Notas</label>
                        <textarea id="notas" name="notas" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100">{{ old('notas') }}</textarea>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Registrar abono
                    </button>
                </form>
            </div>

            @if(!$isVenta)
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Historial tecnico</h2>
                <div class="mt-5 space-y-3">
                    @forelse($record->historial->sortByDesc('created_at') as $historial)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-slate-900">{{ $historial->estado_nuevo_nombre }}</span>
                            <span class="text-xs text-slate-400">{{ $historial->created_at?->format('d/m/Y H:i') ?: '-' }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $historial->comentario ?: 'Sin comentario adicional.' }}</p>
                    </div>
                    @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        No hay movimientos tecnicos registrados.
                    </div>
                    @endforelse
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
