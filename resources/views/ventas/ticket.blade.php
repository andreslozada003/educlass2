<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $venta->folio }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #111827;
            background: #ffffff;
        }

        .ticket {
            width: 80mm;
            max-width: 302px;
            margin: 0 auto;
            padding: 7px;
        }

        .panel {
            border: 1px solid #111827;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 8px;
        }

        .header {
            text-align: center;
            padding-bottom: 8px;
        }

        .header-rule {
            width: 100%;
            height: 4px;
            border-radius: 999px;
            background: #111827;
            margin-bottom: 10px;
        }

        .brand {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .subtitle {
            margin-top: 3px;
            font-size: 9px;
            color: #4b5563;
        }

        .document-chip {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 8px;
            border: 1px solid #111827;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 0 6px 0;
        }

        .label {
            display: block;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .value {
            font-size: 10px;
            font-weight: 600;
            color: #111827;
        }

        .muted {
            color: #6b7280;
            font-weight: 500;
        }

        .full-row {
            margin-top: 6px;
        }

        .separator {
            border-top: 1px dashed #9ca3af;
            margin: 8px 0;
        }

        .section-title {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #374151;
            margin-bottom: 6px;
        }

        .meta-card {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 8px;
            margin-top: 8px;
        }

        .meta-card + .meta-card {
            margin-top: 6px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            text-align: left;
            vertical-align: top;
            padding: 4px 0;
        }

        .items-table thead th {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }

        .items-table tbody td {
            border-bottom: 1px dashed #e5e7eb;
        }

        .col-qty {
            width: 14%;
        }

        .col-item {
            width: 56%;
            padding-right: 6px;
        }

        .col-amount {
            width: 30%;
            text-align: right;
        }

        .item-name {
            font-size: 10px;
            font-weight: 600;
        }

        .item-line {
            font-size: 8px;
            color: #374151;
            margin-top: 2px;
        }

        .item-meta {
            font-size: 8px;
            color: #6b7280;
            margin-top: 1px;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .summary-row span {
            display: table-cell;
        }

        .summary-row .summary-label {
            color: #4b5563;
        }

        .summary-row .summary-value {
            text-align: right;
            font-weight: 600;
        }

        .total-box {
            border: 1px solid #111827;
            border-radius: 10px;
            padding: 8px;
            margin-top: 8px;
        }

        .total-box .summary-row {
            margin-bottom: 0;
        }

        .total-box .summary-label,
        .total-box .summary-value {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }

        .status-box {
            border: 1px solid #111827;
            border-radius: 10px;
            padding: 8px;
            margin-top: 8px;
        }

        .status-title {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #111827;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .note-box {
            border: 1px dashed #9ca3af;
            border-radius: 10px;
            padding: 8px;
            margin-top: 8px;
        }

        .footer {
            text-align: center;
            padding-top: 4px;
        }

        .footer strong {
            display: block;
            font-size: 10px;
            margin-bottom: 3px;
        }

        .footer p {
            font-size: 8px;
            color: #4b5563;
        }
    </style>
</head>
<body>
@php
    $ticketMoney = fn ($amount) => '$' . number_format((float) $amount, 0, ',', '.');
    $cliente = $venta->cliente;
    $saldoPendiente = max(0, (float) $venta->total - (float) $venta->monto_pagado);
    $totalItems = $venta->detalles->sum('cantidad');
    $estadoPago = $venta->estado === 'credito' && $saldoPendiente > 0 ? 'Credito' : 'Pagada';
    $metodoPago = ucfirst(str_replace('_', ' ', $venta->metodo_pago));
    $pagadoCon = (float) ($venta->pagado_con ?: 0);
@endphp

<div class="ticket">
    <div class="panel header">
        <div class="header-rule"></div>
        <div class="brand">{{ $empresa['nombre'] ?: 'CellFix Pro' }}</div>
        @if(!empty($empresa['direccion']))
        <div class="subtitle">{{ $empresa['direccion'] }}</div>
        @endif
        <div class="subtitle">
            @if(!empty($empresa['telefono'])) Tel: {{ $empresa['telefono'] }} @endif
            @if(!empty($empresa['rfc'])) {{ !empty($empresa['telefono']) ? ' | ' : '' }}RFC: {{ $empresa['rfc'] }} @endif
        </div>
        @if(!empty($empresa['email']))
        <div class="subtitle">{{ $empresa['email'] }}</div>
        @endif
        <div class="document-chip">Ticket de venta</div>
    </div>

    <div class="panel">
        <table class="meta-grid">
            <tr>
                <td>
                    <span class="label">Folio</span>
                    <span class="value">{{ $venta->folio }}</span>
                </td>
                <td>
                    <span class="label">Fecha</span>
                    <span class="value">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Vendedor</span>
                    <span class="value">{{ $venta->usuario?->name ?: 'No asignado' }}</span>
                </td>
                <td>
                    <span class="label">Items</span>
                    <span class="value">{{ $totalItems }}</span>
                </td>
            </tr>
        </table>

        <div class="separator"></div>

        <div class="section-title">Cliente</div>
        <div class="meta-card">
            <div class="value">{{ $cliente?->nombre_completo ?: 'Cliente general' }}</div>
            @if($cliente?->telefono)
            <div class="item-meta">Telefono: {{ $cliente->telefono }}</div>
            @endif
            @if($cliente?->email)
            <div class="item-meta">Email: {{ $cliente->email }}</div>
            @endif
            @if($cliente?->rfc)
            <div class="item-meta">Documento / RFC: {{ $cliente->rfc }}</div>
            @endif
            @if($cliente?->direccion)
            <div class="item-meta">{{ $cliente->direccion }}</div>
            @endif
            @if($cliente?->ciudad)
            <div class="item-meta">{{ $cliente->ciudad }}</div>
            @endif
        </div>

        <div class="meta-card">
            <div class="summary-row">
                <span class="summary-label">Metodo</span>
                <span class="summary-value">{{ $metodoPago }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Estado</span>
                <span class="summary-value">{{ $estadoPago }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Articulos</span>
                <span class="summary-value">{{ $totalItems }}</span>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="section-title">Detalle</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-item">Descripcion</th>
                    <th class="col-amount">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr>
                    <td class="col-qty">
                        <div class="item-name">{{ $detalle->cantidad }}</div>
                        <div class="item-meta">x {{ $ticketMoney($detalle->precio_unitario) }}</div>
                    </td>
                    <td class="col-item">
                        <div class="item-name">{{ $detalle->producto?->nombre ?: 'Producto eliminado' }}</div>
                        <div class="item-line">{{ $detalle->cantidad }} x {{ $ticketMoney($detalle->precio_unitario) }}</div>
                        @if($detalle->notas)
                        <div class="item-meta">{{ $detalle->notas }}</div>
                        @endif
                    </td>
                    <td class="col-amount">
                        <div class="item-name">{{ $ticketMoney($detalle->subtotal) }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="panel">
        <div class="section-title">Resumen de pago</div>
        <div class="summary-row">
            <span class="summary-label">Subtotal</span>
            <span class="summary-value">{{ $ticketMoney($venta->subtotal) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">IVA (16%)</span>
            <span class="summary-value">{{ $ticketMoney($venta->impuestos) }}</span>
        </div>
        @if((float) $venta->descuento > 0)
        <div class="summary-row">
            <span class="summary-label">Descuento</span>
            <span class="summary-value">-{{ $ticketMoney($venta->descuento) }}</span>
        </div>
        @endif

        <div class="total-box">
            <div class="summary-row">
                <span class="summary-label">Total</span>
                <span class="summary-value">{{ $ticketMoney($venta->total) }}</span>
            </div>
        </div>

        <div class="status-box">
            <div class="status-title">Estado de pago</div>
            <div class="status-pill">{{ $estadoPago }}</div>

            <div class="summary-row" style="margin-top: 8px;">
                <span class="summary-label">Metodo</span>
                <span class="summary-value">{{ $metodoPago }}</span>
            </div>
            @if($pagadoCon > 0)
            <div class="summary-row">
                <span class="summary-label">Recibido</span>
                <span class="summary-value">{{ $ticketMoney($pagadoCon) }}</span>
            </div>
            @endif
            <div class="summary-row">
                <span class="summary-label">{{ $venta->estado === 'credito' ? 'Abono inicial' : 'Pagado' }}</span>
                <span class="summary-value">{{ $ticketMoney($venta->monto_pagado ?: $venta->total) }}</span>
            </div>
            @if((float) $venta->cambio > 0)
            <div class="summary-row">
                <span class="summary-label">Cambio</span>
                <span class="summary-value">{{ $ticketMoney($venta->cambio) }}</span>
            </div>
            @endif
            @if($venta->estado === 'credito' && $saldoPendiente > 0)
            <div class="summary-row">
                <span class="summary-label">Saldo pendiente</span>
                <span class="summary-value">{{ $ticketMoney($saldoPendiente) }}</span>
            </div>
            @endif
            @if($venta->fecha_compromiso_pago)
            <div class="summary-row">
                <span class="summary-label">Compromiso</span>
                <span class="summary-value">{{ $venta->fecha_compromiso_pago->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>

        @if($venta->estado === 'credito' && ($venta->fecha_inicio_mora || $venta->numero_cuotas || $venta->plazo_acordado_dias || $venta->mora_observaciones))
        <div class="note-box">
            <div class="section-title">Seguimiento de credito</div>
            @if($venta->fecha_inicio_mora)
            <div class="item-meta">Inicio mora: {{ $venta->fecha_inicio_mora->format('d/m/Y') }}</div>
            @endif
            @if($venta->numero_cuotas)
            <div class="item-meta">Cuotas: {{ $venta->numero_cuotas }}</div>
            @endif
            @if($venta->plazo_acordado_dias)
            <div class="item-meta">Plazo: {{ $venta->plazo_acordado_dias }} dias</div>
            @endif
            @if($venta->mora_observaciones)
            <div class="item-meta" style="margin-top: 4px;">{{ $venta->mora_observaciones }}</div>
            @endif
        </div>
        @endif

        @if($venta->notas)
        <div class="note-box">
            <div class="section-title">Observaciones</div>
            <div class="item-meta">{{ $venta->notas }}</div>
        </div>
        @endif
    </div>

    <div class="panel footer">
        <strong>Gracias por tu compra</strong>
        <p>Conserva este ticket para garantias, soporte o seguimiento.</p>
        <p>Comprobante interno de venta.</p>
        <p>{{ $venta->fecha_venta->format('d/m/Y H:i:s') }}</p>
    </div>
</div>
</body>
</html>
