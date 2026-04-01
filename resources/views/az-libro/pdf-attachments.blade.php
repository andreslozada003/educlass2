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
            margin-bottom: 18px;
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

        .grid {
            width: 100%;
        }

        .card {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .preview {
            width: 100%;
            height: 180px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            text-align: center;
            margin-bottom: 10px;
        }

        .preview img {
            max-width: 100%;
            max-height: 176px;
        }

        .placeholder {
            padding-top: 70px;
            color: #475569;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            vertical-align: top;
        }

        .label {
            width: 26%;
            font-weight: bold;
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

    @forelse($attachments as $attachment)
    <div class="card">
        <div class="preview">
            @if(!empty($attachment['preview_data_uri']))
                <img src="{{ $attachment['preview_data_uri'] }}" alt="Vista previa">
            @elseif(($attachment['file_kind'] ?? '') === 'PDF')
                <div class="placeholder">Documento PDF</div>
            @elseif(($attachment['file_kind'] ?? '') === 'XML')
                <div class="placeholder">Archivo XML</div>
            @else
                <div class="placeholder">Sin vista previa</div>
            @endif
        </div>

        <table>
            <tr>
                <td class="label">Modulo</td>
                <td>{{ $attachment['module'] }}</td>
                <td class="label">Registro</td>
                <td>{{ $attachment['record'] }}</td>
            </tr>
            <tr>
                <td class="label">Tipo</td>
                <td>{{ $attachment['type'] }}</td>
                <td class="label">Clase archivo</td>
                <td>{{ $attachment['file_kind'] }}</td>
            </tr>
            <tr>
                <td class="label">Archivo</td>
                <td>{{ $attachment['filename'] }}</td>
                <td class="label">Extension</td>
                <td>{{ $attachment['extension'] }}</td>
            </tr>
            <tr>
                <td class="label">Tamano</td>
                <td>{{ $attachment['size_human'] }}</td>
                <td class="label">Disponible</td>
                <td>{{ $attachment['exists'] ? 'Si' : 'No' }}</td>
            </tr>
            <tr>
                <td class="label">Ruta</td>
                <td colspan="3">{{ $attachment['path'] }}</td>
            </tr>
        </table>
    </div>
    @empty
    <p>No hay adjuntos para exportar.</p>
    @endforelse
</body>
</html>
