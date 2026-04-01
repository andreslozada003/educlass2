<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} - AZ libro</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 10px;
        }

        .header {
            margin-bottom: 16px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }

        .title {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .description {
            margin: 6px 0 0;
            color: #475569;
            line-height: 1.5;
        }

        .meta {
            margin-top: 10px;
            color: #334155;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #0f172a;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $title }}</h1>
        <p class="description">{{ $description }}</p>
        <div class="meta">
            <strong>Generado:</strong> {{ $generatedAt->format('Y-m-d H:i:s') }}
            <strong style="margin-left: 12px;">Registros:</strong> {{ number_format($recordCount) }}
            <strong style="margin-left: 12px;">Adjuntos:</strong> {{ number_format($attachmentCount) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headings) }}">No hay registros para exportar en este modulo.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
