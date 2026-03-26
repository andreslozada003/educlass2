<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Entrega #{{ $reparacion->numero_orden }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        .ticket {
            width: 80mm;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 9px;
        }
        .tipo-doc {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 5px;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .section-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-align: center;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .info-label {
            font-weight: bold;
        }
        .info-value {
            text-align: right;
        }
        .full-width {
            width: 100%;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .estado-box {
            border: 2px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 10px 0;
        }
        .costos-table {
            width: 100%;
            margin-top: 5px;
        }
        .costos-table td {
            padding: 2px 0;
        }
        .costos-table .total {
            border-top: 1px solid #000;
            font-weight: bold;
            font-size: 12px;
            padding-top: 5px;
        }
        .garantia-box {
            border: 1px solid #000;
            padding: 8px;
            margin: 10px 0;
            text-align: center;
        }
        .garantia-box strong {
            font-size: 12px;
        }
        .qr-section {
            text-align: center;
            margin: 10px 0;
        }
        .qr-placeholder {
            width: 60px;
            height: 60px;
            border: 1px dashed #000;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
        }
        .firma-section {
            margin-top: 20px;
            text-align: center;
        }
        .firma-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 3px;
            font-size: 9px;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        .asteriscos {
            text-align: center;
            margin: 5px 0;
        }
        .mensaje-gracias {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
        @media print {
            body {
                width: 80mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'CELLFIX PRO') }}</h1>
            <p>{{ config('app.direccion', 'Av. Principal #123') }}</p>
            <p>Tel: {{ config('app.telefono', '555-1234') }}</p>
            <p>{{ config('app.email', 'info@cellfix.com') }}</p>
        </div>

        <!-- Tipo de Documento -->
        <div class="tipo-doc">
            TICKET DE ENTREGA
        </div>

        <!-- Información de la Orden -->
        <div class="section">
            <div class="info-row">
                <span class="info-label">ORDEN:</span>
                <span class="info-value">{{ $reparacion->numero_orden }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">FECHA ENTREGA:</span>
                <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">ATENDIÓ:</span>
                <span class="info-value">{{ auth()->user()->name }}</span>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="section">
            <div class="section-title">CLIENTE</div>
            <div class="full-width">
                <strong>{{ $reparacion->cliente->nombre }}</strong>
            </div>
            <div class="full-width">Tel: {{ $reparacion->cliente->telefono }}</div>
            @if($reparacion->cliente->email)
            <div class="full-width">{{ $reparacion->cliente->email }}</div>
            @endif
        </div>

        <!-- Información del Dispositivo -->
        <div class="section">
            <div class="section-title">DISPOSITIVO</div>
            <div class="full-width">
                <strong>{{ $reparacion->marca }} {{ $reparacion->modelo }}</strong>
            </div>
            <div class="info-row">
                <span>Tipo: {{ $reparacion->tipo_dispositivo }}</span>
            </div>
            @if($reparacion->imei)
            <div class="info-row">
                <span>IMEI: {{ $reparacion->imei }}</span>
            </div>
            @endif
            @if($reparacion->color)
            <div class="info-row">
                <span>Color: {{ $reparacion->color }}</span>
            </div>
            @endif
        </div>

        <!-- Estado -->
        <div class="estado-box">
            {{ $reparacion->estado }}
        </div>

        <!-- Descripción del Servicio -->
        <div class="section">
            <div class="section-title">SERVICIO REALIZADO</div>
            <div class="full-width">
                <strong>{{ $reparacion->tipo_falla }}</strong>
            </div>
            @if($reparacion->solucion_aplicada)
            <div class="full-width" style="margin-top: 5px; font-size: 9px;">
                {{ Str::limit($reparacion->solucion_aplicada, 100) }}
            </div>
            @endif
        </div>

        <!-- Costos -->
        <div class="section">
            <div class="section-title">DETALLE DE PAGO</div>
            <table class="costos-table">
                <tr>
                    <td>Costo Reparación:</td>
                    <td class="text-right">{{ money($reparacion->costo_final) }}</td>
                </tr>
                @if($reparacion->adelanto > 0)
                <tr>
                    <td>Abono/Adelanto:</td>
                    <td class="text-right">-{{ money($reparacion->adelanto) }}</td>
                </tr>
                @endif
                <tr class="total">
                    <td>TOTAL A PAGAR:</td>
                    <td class="text-right">{{ money($reparacion->costo_final - $reparacion->adelanto) }}</td>
                </tr>
            </table>
            <div class="info-row" style="margin-top: 5px;">
                <span class="info-label">MÉTODO DE PAGO:</span>
                <span class="info-value">{{ $reparacion->metodo_pago ?? 'Efectivo' }}</span>
            </div>
        </div>

        <!-- Garantía -->
        @if($reparacion->garantia_dias)
        <div class="garantia-box">
            <strong>GARANTÍA: {{ $reparacion->garantia_dias }} DÍAS</strong>
            <div style="font-size: 9px; margin-top: 3px;">
                Válida hasta: {{ $reparacion->garantia_hasta ? $reparacion->garantia_hasta->format('d/m/Y') : now()->addDays($reparacion->garantia_dias)->format('d/m/Y') }}
            </div>
        </div>
        @endif

        <!-- QR -->
        <div class="qr-section">
            <div class="qr-placeholder">
                QR
            </div>
            <div style="font-size: 8px;">Escanea para verificar</div>
        </div>

        <!-- Mensaje -->
        <div class="mensaje-gracias">
            ¡GRACIAS POR SU PREFERENCIA!
        </div>

        <div class="asteriscos">* * * * * * * * * *</div>

        <!-- Firma -->
        <div class="firma-section">
            <div class="firma-line">
                FIRMA DEL CLIENTE
            </div>
            <div style="font-size: 8px; margin-top: 5px;">
                He recibido mi dispositivo en buenas condiciones
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este documento es comprobante de entrega</p>
            <p>Conserve este ticket para cualquier reclamo</p>
            <p style="margin-top: 5px;">{{ config('app.name') }} - {{ now()->format('Y') }}</p>
        </div>
    </div>
</body>
</html>