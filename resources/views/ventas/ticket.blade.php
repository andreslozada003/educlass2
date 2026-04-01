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
            font-family: "Courier New", "DejaVu Sans Mono", monospace;
            font-size: 10px;
            line-height: 1.35;
            color: #000;
            background: #fff;
        }

        .ticket {
            width: 80mm;
            max-width: 302px;
            margin: 0 auto;
            padding: 8px 7px 12px;
        }

        .center {
            text-align: center;
        }

        .logo {
            max-width: 72px;
            max-height: 42px;
            margin: 0 auto 6px;
            display: block;
        }

        .company-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .company-line {
            margin-bottom: 2px;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 9px 0;
        }

        .section-title {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .invoice-title {
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2px;
        }

        .muted {
            color: #111;
        }

        .line {
            margin-bottom: 3px;
            word-break: break-word;
        }

        .line strong {
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 3px 2px;
            vertical-align: top;
        }

        thead th {
            font-weight: 700;
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
        }

        tbody td {
            border-bottom: 1px dotted #bbb;
        }

        .no-border td {
            border-bottom: 0;
        }

        .right {
            text-align: right;
        }

        .small {
            font-size: 9px;
        }

        .total-block td {
            padding: 2px 0;
        }

        .grand-total td {
            font-size: 13px;
            font-weight: 700;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .code-box {
            margin: 10px 0 7px;
            text-align: center;
        }

        .folio-display {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.12em;
            margin-top: 3px;
        }

        .signature {
            margin-top: 14px;
            text-align: center;
        }

        .signature-line {
            width: 78%;
            margin: 0 auto 4px;
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 7px;
        }
    </style>
</head>
<body>
@php
    $money = fn ($amount) => money($amount, 2);
    $cliente = $venta->cliente;
    $saldoPendiente = max(0, (float) $venta->total - (float) $venta->monto_pagado);
    $metodoPago = ucfirst(str_replace('_', ' ', $venta->metodo_pago));
    $pagadoCon = (float) ($venta->pagado_con ?: 0);
    $ivaPorcentaje = $venta->subtotal > 0 ? round(((float) $venta->impuestos / (float) $venta->subtotal) * 100, 2) : 0;
@endphp

<div class="ticket">
    <div class="center">
        @if(!empty($empresa['logo_path']) && is_file($empresa['logo_path']))
        <img src="{{ $empresa['logo_path'] }}" alt="Logo" class="logo">
        @endif
        <div class="company-name">{{ $empresa['nombre'] ?: 'Sistema POS' }}</div>
        @if(!empty($empresa['rfc']))
        <div class="company-line">NIT / RFC: {{ $empresa['rfc'] }}</div>
        @endif
        @if(!empty($empresa['direccion']))
        <div class="company-line">{{ $empresa['direccion'] }}</div>
        @endif
        @if(!empty($empresa['telefono']))
        <div class="company-line">{{ $empresa['telefono'] }}</div>
        @endif
        @if(!empty($empresa['web']))
        <div class="company-line">{{ $empresa['web'] }}</div>
        @endif
    </div>

    <div class="separator"></div>

    <div class="invoice-title">Factura / Ticket {{ $venta->folio }}</div>
    <div class="center muted">{{ $venta->fecha_venta->format('m/d/Y H:i') }}</div>

    <div class="separator"></div>

    <div class="line"><strong>Cliente:</strong> {{ $cliente?->nombre_completo ?: 'Cliente general' }}</div>
    @if($cliente?->rfc)
    <div class="line"><strong>NIT / RFC:</strong> {{ $cliente->rfc }}</div>
    @endif
    @if($cliente?->ciudad || $cliente?->estado)
    <div class="line">{{ collect([$cliente?->ciudad, $cliente?->estado])->filter()->implode(' - ') }}</div>
    @endif
    <div class="line"><strong>Vendedor:</strong> {{ $venta->usuario?->name ?: 'Caja principal' }}</div>
    <div class="line"><strong>Empleado:</strong> {{ $venta->usuario?->name ?: 'No asignado' }}</div>

    <div class="separator"></div>

    <table>
        <thead>
            <tr>
                <th style="width: 43%;">Articulo</th>
                <th class="right" style="width: 22%;">Precio</th>
                <th class="right" style="width: 13%;">Cant.</th>
                <th class="right" style="width: 22%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>
                    {{ $detalle->producto?->nombre ?: 'Producto eliminado' }}
                    @if($detalle->producto?->codigo)
                    <div class="small">{{ $detalle->producto->codigo }}</div>
                    @endif
                    @if($detalle->notas)
                    <div class="small">{{ $detalle->notas }}</div>
                    @endif
                </td>
                <td class="right">{{ $money($detalle->precio_unitario) }}</td>
                <td class="right">{{ $detalle->cantidad }}</td>
                <td class="right">{{ $money($detalle->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-block">
        <tr class="no-border">
            <td class="right"><strong>Subtotal:</strong> {{ $money($venta->subtotal) }}</td>
        </tr>
        @if((float) $venta->descuento > 0)
        <tr class="no-border">
            <td class="right"><strong>Descuento:</strong> -{{ $money($venta->descuento) }}</td>
        </tr>
        @endif
        <tr class="no-border grand-total">
            <td class="right">TOTAL: {{ $money($venta->total) }}</td>
        </tr>
    </table>

    <div class="separator"></div>

    <div class="section-title">Detalle del impuesto</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th class="right">Base/Imp</th>
                <th class="right">Impuesto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>IVA {{ $ivaPorcentaje }}%</td>
                <td class="right">{{ $money($venta->subtotal) }}</td>
                <td class="right">{{ $money($venta->impuestos) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="separator"></div>

    <div class="section-title">Forma de pago</div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo de pago</th>
                <th class="right">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                <td>{{ $venta->estado === 'credito' ? 'Linea de credito' : $metodoPago }}</td>
                <td class="right">{{ $money($venta->monto_pagado ?: $venta->total) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="line" style="margin-top: 5px;"><strong>Metodo de pago:</strong> {{ $metodoPago }}</div>
    @if($pagadoCon > 0)
    <div class="line"><strong>Recibido:</strong> {{ $money($pagadoCon) }}</div>
    @endif
    <div class="line"><strong>Cambio:</strong> {{ $money($venta->cambio) }}</div>
    @if($cliente)
    <div class="line"><strong>Balance en la cuenta del cliente:</strong> {{ $money($clienteBalance) }}</div>
    @endif

    @if($venta->estado === 'credito' && $saldoPendiente > 0)
    <div class="separator"></div>

    <div class="section-title">Cuotas</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th class="right">Pendiente</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $venta->fecha_compromiso_pago?->format('d/m/Y') ?: 'No definida' }}</td>
                <td>Pendiente</td>
                <td class="right">{{ $money($saldoPendiente) }}</td>
                <td class="right">{{ $money($venta->total) }}</td>
            </tr>
        </tbody>
    </table>
    @if($venta->numero_cuotas)
    <div class="line" style="margin-top: 5px;"><strong>Plan acordado:</strong> {{ $venta->numero_cuotas }} cuota(s)</div>
    @endif
    @if($venta->plazo_acordado_dias)
    <div class="line"><strong>Plazo:</strong> {{ $venta->plazo_acordado_dias }} dia(s)</div>
    @endif
    @if($venta->mora_observaciones)
    <div class="line">{{ $venta->mora_observaciones }}</div>
    @endif
    @endif

    @if($venta->notas)
    <div class="separator"></div>
    <div class="line"><strong>Observaciones:</strong> {{ $venta->notas }}</div>
    @endif

    <div class="separator"></div>

    <div class="center" style="font-weight: 700; margin-bottom: 2px;">SIMPLIFICADO</div>

    <div class="code-box">
        <div class="folio-display">{{ preg_replace('/[^0-9]/', '', $venta->folio) ?: $venta->id }}</div>
    </div>

    <div class="signature">
        <div class="signature-line">Firma</div>
        <div class="small">Titular acusa recibo de los bienes y/o servicios.</div>
    </div>

    <div class="footer">
        <div>Gracias por su compra</div>
        <div>Conserve este ticket para cambios, garantias o seguimiento.</div>
        <div style="margin-top: 4px; font-weight: 700;">Desarrollado para {{ $empresa['nombre'] ?: 'Sistema POS' }}</div>
    </div>
</div>
</body>
</html>
