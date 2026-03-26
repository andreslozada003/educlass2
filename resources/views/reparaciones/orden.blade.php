<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Reparación #{{ $reparacion->numero_orden }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
        }
        .orden-info {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .orden-info h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 5px 10px;
            width: 50%;
        }
        .info-cell strong {
            color: #555;
        }
        .section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .section h3 {
            font-size: 14px;
            background: #333;
            color: #fff;
            padding: 8px 12px;
            margin: -15px -15px 15px -15px;
            border-radius: 5px 5px 0 0;
        }
        .section-content {
            margin-top: 10px;
        }
        .dispositivo-info {
            display: table;
            width: 100%;
        }
        .dispositivo-row {
            display: table-row;
        }
        .dispositivo-cell {
            display: table-cell;
            padding: 5px 10px;
            width: 33.33%;
        }
        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        .estado-recibido { background: #fbbf24; color: #000; }
        .estado-diagnostico { background: #3b82f6; color: #fff; }
        .estado-reparacion { background: #f97316; color: #fff; }
        .estado-listo { background: #8b5cf6; color: #fff; }
        .estado-entregado { background: #10b981; color: #fff; }
        .estado-cancelado { background: #ef4444; color: #fff; }
        .accesorios-list {
            list-style: none;
            padding: 0;
        }
        .accesorios-list li {
            display: inline-block;
            margin: 3px;
            padding: 3px 10px;
            background: #e5e7eb;
            border-radius: 12px;
            font-size: 11px;
        }
        .condiciones {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .condiciones h4 {
            color: #92400e;
            margin-bottom: 10px;
        }
        .condiciones ol {
            margin-left: 20px;
            color: #78350f;
        }
        .condiciones li {
            margin-bottom: 5px;
        }
        .firma-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .firma-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        .firma-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .qr-code {
            text-align: center;
            margin: 15px 0;
        }
        .qr-placeholder {
            width: 100px;
            height: 100px;
            border: 2px dashed #ccc;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #999;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'CellFix Pro') }}</h1>
            <p>{{ config('app.direccion', 'Tu dirección aquí') }}</p>
            <p>Tel: {{ config('app.telefono', 'Tu teléfono') }} | Email: {{ config('app.email', 'tu@email.com') }}</p>
        </div>

        <!-- Orden Info -->
        <div class="orden-info">
            <h2>ORDEN DE REPARACIÓN #{{ $reparacion->numero_orden }}</h2>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <strong>Fecha de Recepción:</strong> {{ $reparacion->fecha_recepcion->format('d/m/Y H:i') }}
                    </div>
                    <div class="info-cell">
                        <strong>Estado:</strong> 
                        <span class="estado-badge estado-{{ strtolower(str_replace(' ', '-', $reparacion->estado)) }}">
                            {{ $reparacion->estado }}
                        </span>
                    </div>
                </div>
                @if($reparacion->fecha_entrega)
                <div class="info-row">
                    <div class="info-cell">
                        <strong>Fecha de Entrega:</strong> {{ $reparacion->fecha_entrega->format('d/m/Y H:i') }}
                    </div>
                    <div class="info-cell">
                        <strong>Técnico Asignado:</strong> {{ $reparacion->tecnico->name ?? 'No asignado' }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="section">
            <h3>INFORMACIÓN DEL CLIENTE</h3>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell">
                            <strong>Nombre:</strong> {{ $reparacion->cliente->nombre }}
                        </div>
                        <div class="info-cell">
                            <strong>Teléfono:</strong> {{ $reparacion->cliente->telefono }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <strong>Email:</strong> {{ $reparacion->cliente->email ?? 'N/A' }}
                        </div>
                        <div class="info-cell">
                            <strong>Documento:</strong> {{ $reparacion->cliente->documento ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Dispositivo -->
        <div class="section">
            <h3>INFORMACIÓN DEL DISPOSITIVO</h3>
            <div class="section-content">
                <div class="dispositivo-info">
                    <div class="dispositivo-row">
                        <div class="dispositivo-cell">
                            <strong>Tipo:</strong> {{ $reparacion->tipo_dispositivo }}
                        </div>
                        <div class="dispositivo-cell">
                            <strong>Marca:</strong> {{ $reparacion->marca }}
                        </div>
                        <div class="dispositivo-cell">
                            <strong>Modelo:</strong> {{ $reparacion->modelo }}
                        </div>
                    </div>
                    <div class="dispositivo-row">
                        <div class="dispositivo-cell">
                            <strong>Color:</strong> {{ $reparacion->color ?? 'N/A' }}
                        </div>
                        <div class="dispositivo-cell">
                            <strong>IMEI/SN:</strong> {{ $reparacion->imei ?? 'N/A' }}
                        </div>
                        <div class="dispositivo-cell">
                            <strong>Contraseña:</strong> {{ $reparacion->contrasena ?? 'N/A' }}
                        </div>
                    </div>
                </div>
                @if($reparacion->accesorios_dejados)
                <div style="margin-top: 15px;">
                    <strong>Accesorios Dejados:</strong>
                    <ul class="accesorios-list">
                        @foreach(json_decode($reparacion->accesorios_dejados, true) ?? [] as $accesorio)
                        <li>{{ $accesorio }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Descripción del Problema -->
        <div class="section">
            <h3>DESCRIPCIÓN DEL PROBLEMA</h3>
            <div class="section-content">
                <p><strong>Tipo de Falla:</strong> {{ $reparacion->tipo_falla }}</p>
                <p style="margin-top: 10px;"><strong>Descripción:</strong></p>
                <p style="background: #f9fafb; padding: 10px; border-radius: 5px; margin-top: 5px;">
                    {{ $reparacion->descripcion_problema }}
                </p>
                @if($reparacion->condiciones_fisicas)
                <p style="margin-top: 10px;"><strong>Condiciones Físicas:</strong></p>
                <p style="background: #f9fafb; padding: 10px; border-radius: 5px; margin-top: 5px;">
                    {{ $reparacion->condiciones_fisicas }}
                </p>
                @endif
            </div>
        </div>

        <!-- Diagnóstico y Reparación -->
        @if($reparacion->diagnostico)
        <div class="section">
            <h3>DIAGNÓSTICO Y REPARACIÓN</h3>
            <div class="section-content">
                <p><strong>Diagnóstico:</strong></p>
                <p style="background: #eff6ff; padding: 10px; border-radius: 5px; margin: 5px 0 15px 0;">
                    {{ $reparacion->diagnostico }}
                </p>
                @if($reparacion->solucion_aplicada)
                <p><strong>Solución Aplicada:</strong></p>
                <p style="background: #f0fdf4; padding: 10px; border-radius: 5px; margin-top: 5px;">
                    {{ $reparacion->solucion_aplicada }}
                </p>
                @endif
                @if($reparacion->repuestos_usados)
                <p style="margin-top: 10px;"><strong>Repuestos Utilizados:</strong></p>
                <p style="background: #fef3c7; padding: 10px; border-radius: 5px; margin-top: 5px;">
                    {{ $reparacion->repuestos_usados }}
                </p>
                @endif
            </div>
        </div>
        @endif

        <!-- Costos -->
        <div class="section">
            <h3>COSTOS</h3>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell">
                            <strong>Costo Estimado:</strong> {{ money($reparacion->costo_estimado) }}
                        </div>
                        <div class="info-cell">
                            <strong>Costo Final:</strong> {{ money($reparacion->costo_final) }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <strong>Abono/Adelanto:</strong> {{ money($reparacion->adelanto) }}
                        </div>
                        <div class="info-cell">
                            <strong>Saldo Pendiente:</strong> {{ money($reparacion->costo_final - $reparacion->adelanto) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Garantía -->
        @if($reparacion->garantia_dias)
        <div class="section">
            <h3>GARANTÍA</h3>
            <div class="section-content">
                <p>Esta reparación cuenta con una garantía de <strong>{{ $reparacion->garantia_dias }} días</strong> a partir de la fecha de entrega.</p>
                @if($reparacion->garantia_hasta)
                <p style="margin-top: 5px;">Válida hasta: <strong>{{ $reparacion->garantia_hasta->format('d/m/Y') }}</strong></p>
                @endif
            </div>
        </div>
        @endif

        <!-- Condiciones -->
        <div class="condiciones">
            <h4>TÉRMINOS Y CONDICIONES</h4>
            <ol>
                <li>El cliente acepta que el dispositivo será revisado para determinar el diagnóstico final.</li>
                <li>Los costos estimados pueden variar según el diagnóstico real.</li>
                <li>No nos hacemos responsables por datos perdidos durante la reparación. Se recomienda hacer backup.</li>
                <li>Los equipos no reclamados en 30 días después de notificación de reparación completa serán dados de baja.</li>
                <li>La garantía cubre únicamente la reparación realizada y no daños por mal uso o accidentes.</li>
                <li>El cliente debe presentar esta orden para retirar el dispositivo.</li>
            </ol>
        </div>

        <!-- QR Code -->
        <div class="qr-code">
            <div class="qr-placeholder">
                QR: {{ $reparacion->numero_orden }}
            </div>
            <p style="font-size: 10px; color: #666;">Escanea para ver el estado de tu reparación</p>
        </div>

        <!-- Firmas -->
        <div class="firma-section">
            <div class="firma-box">
                <div class="firma-line">
                    Firma del Cliente<br>
                    <small>{{ $reparacion->cliente->nombre }}</small>
                </div>
            </div>
            <div class="firma-box">
                <div class="firma-line">
                    Firma del Técnico/Recepcionista<br>
                    <small>{{ $reparacion->tecnico->name ?? auth()->user()->name }}</small>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | {{ config('app.name') }}</p>
            <p>Para consultas sobre tu reparación, contactanos con tu número de orden: <strong>{{ $reparacion->numero_orden }}</strong></p>
        </div>
    </div>
</body>
</html>