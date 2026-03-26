<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Reparaciones</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        .meta {
            margin-bottom: 18px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Reporte de Reparaciones</h1>

    <div class="meta">
        <div>Periodo: {{ $fechaInicio }} al {{ $fechaFin }}</div>
        <div>Generado: {{ $generado->format('d/m/Y H:i') }}</div>
        <div>Total de reparaciones: {{ $reparaciones->count() }}</div>
        <div>Ingresos entregados: {{ money($reparaciones->where('estado', 'entregado')->sum('costo_final')) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Orden</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Dispositivo</th>
                <th>Estado</th>
                <th>Tecnico</th>
                <th class="text-right">Costo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reparaciones as $reparacion)
                <tr>
                    <td>{{ $reparacion->orden }}</td>
                    <td>{{ $reparacion->fecha_recepcion?->format('d/m/Y') }}</td>
                    <td>{{ $reparacion->cliente?->nombre_completo ?? 'Sin cliente' }}</td>
                    <td>{{ trim(($reparacion->dispositivo_marca ?? '') . ' ' . ($reparacion->dispositivo_modelo ?? '')) }}</td>
                    <td>{{ $reparacion->estado_nombre }}</td>
                    <td>{{ $reparacion->tecnico?->name ?? 'Sin asignar' }}</td>
                    <td class="text-right">{{ money($reparacion->costo_final) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No hay reparaciones para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
