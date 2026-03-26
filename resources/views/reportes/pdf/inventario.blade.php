<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
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

        .summary {
            margin-bottom: 18px;
        }

        .summary div {
            margin-bottom: 4px;
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
    <h1>Reporte de Inventario</h1>

    <div class="meta">
        <div>Periodo: {{ $fechaInicio }} al {{ $fechaFin }}</div>
        <div>Generado: {{ $generado->format('d/m/Y H:i') }}</div>
        <div>Total de productos: {{ $productos->count() }}</div>
        <div>Valor total del inventario: {{ money($productos->sum(fn ($producto) => $producto->valor_inventario)) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Producto</th>
                <th>Categoria</th>
                <th class="text-center">Stock</th>
                <th class="text-center">Minimo</th>
                <th class="text-right">Costo</th>
                <th class="text-right">Venta</th>
                <th class="text-right">Valor total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $producto)
                <tr>
                    <td>{{ $producto->codigo }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->categoria?->nombre ?? 'Sin categoria' }}</td>
                    <td class="text-center">{{ $producto->stock }}</td>
                    <td class="text-center">{{ $producto->stock_minimo }}</td>
                    <td class="text-right">{{ money($producto->precio_compra) }}</td>
                    <td class="text-right">{{ money($producto->precio_venta) }}</td>
                    <td class="text-right">{{ money($producto->valor_inventario) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay productos para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
