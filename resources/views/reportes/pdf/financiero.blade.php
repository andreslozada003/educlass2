<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero</title>
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
    </style>
</head>
<body>
    <h1>Reporte Financiero</h1>

    <div class="meta">
        <div>Periodo: {{ $fechaInicio }} al {{ $fechaFin }}</div>
        <div>Generado: {{ $generado->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ingresos por ventas</td>
                <td class="text-right">{{ money($stats['ingresos_ventas']) }}</td>
            </tr>
            <tr>
                <td>Ingresos por reparaciones</td>
                <td class="text-right">{{ money($stats['ingresos_reparaciones']) }}</td>
            </tr>
            <tr>
                <td>Costos de ventas</td>
                <td class="text-right">{{ money($stats['costos']) }}</td>
            </tr>
            <tr>
                <td>Costos de reparaciones</td>
                <td class="text-right">{{ money($stats['costos_reparaciones']) }}</td>
            </tr>
            <tr>
                <td>Ganancia bruta</td>
                <td class="text-right">{{ money($stats['ganancia_bruta']) }}</td>
            </tr>
            <tr>
                <td>Margen</td>
                <td class="text-right">{{ number_format($stats['margen'], 1) }}%</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
