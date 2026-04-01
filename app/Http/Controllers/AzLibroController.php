<?php

namespace App\Http\Controllers;

use App\Exports\GenericTableExport;
use App\Support\AzLibroSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use PharData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class AzLibroController extends Controller
{
    public function __construct(
        protected AzLibroSupport $support
    ) {
        $this->middleware('auth');
        $this->middleware('can:ver reportes');
    }

    public function index()
    {
        return view('az-libro.index', [
            'datasets' => array_values($this->support->summaries()),
        ]);
    }

    public function export(Request $request, string $dataset, string $format)
    {
        abort_unless(in_array($format, ['excel', 'csv', 'pdf', 'zip'], true), 404);

        $payload = $this->support->dataset($dataset);
        $timestamp = now()->format('Ymd-His');
        $baseName = 'az-libro-' . Str::slug($payload['name']) . '-' . $timestamp;

        return match ($format) {
            'excel' => Excel::download(
                new GenericTableExport($payload['headings'], $payload['rows']),
                $baseName . '.xlsx',
                ExcelWriter::XLSX
            ),
            'csv' => Excel::download(
                new GenericTableExport($payload['headings'], $payload['rows']),
                $baseName . '.csv',
                ExcelWriter::CSV
            ),
            'pdf' => $this->downloadPdf($payload, $baseName . '.pdf'),
            'zip' => $this->downloadDatasetZip($payload, $baseName . '.zip'),
        };
    }

    public function backup()
    {
        $datasets = $this->support->allDatasets();
        $fileName = 'az-libro-respaldo-completo-' . now()->format('Ymd-His') . '.zip';
        $zipPath = $this->temporaryPath($fileName);
        $archive = $this->createArchive($zipPath);

        $this->archiveAddFromString($archive, 'README.txt', $this->backupReadme($datasets));

        foreach ($datasets as $dataset) {
            $csvPath = 'data/' . Str::slug($dataset['name']) . '.csv';
            $this->archiveAddFromString($archive, $csvPath, $this->buildCsvString($dataset['headings'], $dataset['rows']));
        }

        $allAttachments = collect($datasets)
            ->flatMap(fn (array $dataset) => $dataset['attachments'])
            ->unique(fn (array $attachment) => ($attachment['absolute_path'] ?? '') . '|' . $attachment['zip_path'])
            ->values();

        foreach ($allAttachments as $attachment) {
            if (!($attachment['exists'] ?? false) || empty($attachment['absolute_path'])) {
                continue;
            }

            $this->archiveAddFile($archive, $attachment['absolute_path'], 'adjuntos/' . $attachment['zip_path']);
        }

        $this->archiveClose($archive);

        return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
    }

    protected function downloadPdf(array $payload, string $fileName)
    {
        if ($payload['key'] === 'archivos-adjuntos') {
            $pdf = Pdf::loadView('az-libro.pdf-attachments', [
                'title' => $payload['name'],
                'description' => $payload['description'],
                'attachments' => $this->attachmentPreviewRows($payload['attachments']),
                'recordCount' => $payload['record_count'],
                'attachmentCount' => $payload['attachment_count'],
                'generatedAt' => now(),
            ]);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download($fileName);
        }

        $pdf = Pdf::loadView('az-libro.pdf', [
            'title' => $payload['name'],
            'description' => $payload['description'],
            'headings' => $payload['headings'],
            'rows' => $payload['rows'],
            'recordCount' => $payload['record_count'],
            'attachmentCount' => $payload['attachment_count'],
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    protected function downloadDatasetZip(array $payload, string $fileName)
    {
        $zipPath = $this->temporaryPath($fileName);
        $archive = $this->createArchive($zipPath);

        $this->archiveAddFromString(
            $archive,
            'data/' . Str::slug($payload['name']) . '.csv',
            $this->buildCsvString($payload['headings'], $payload['rows'])
        );
        $this->archiveAddFromString($archive, 'resumen.txt', $this->datasetReadme($payload));

        foreach ($payload['attachments'] as $attachment) {
            if (!($attachment['exists'] ?? false) || empty($attachment['absolute_path'])) {
                continue;
            }

            $this->archiveAddFile($archive, $attachment['absolute_path'], 'adjuntos/' . $attachment['zip_path']);
        }

        $this->archiveClose($archive);

        return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
    }

    protected function buildCsvString(array $headings, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headings);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv;
    }

    protected function temporaryPath(string $fileName): string
    {
        $directory = storage_path('app/az-libro-temp');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory . DIRECTORY_SEPARATOR . $fileName;
    }

    protected function datasetReadme(array $payload): string
    {
        return implode(PHP_EOL, [
            'AZ libro',
            'Modulo: ' . $payload['name'],
            'Generado: ' . now()->format('Y-m-d H:i:s'),
            'Registros: ' . $payload['record_count'],
            'Adjuntos incluidos: ' . $payload['attachment_count'],
            '',
            $payload['description'],
        ]);
    }

    protected function backupReadme(array $datasets): string
    {
        $lines = [
            'AZ libro - Respaldo completo',
            'Generado: ' . now()->format('Y-m-d H:i:s'),
            '',
            'Datasets incluidos:',
        ];

        foreach ($datasets as $dataset) {
            $lines[] = sprintf(
                '- %s: %d registros, %d adjuntos',
                $dataset['name'],
                $dataset['record_count'],
                $dataset['attachment_count']
            );
        }

        return implode(PHP_EOL, $lines);
    }

    protected function createArchive(string $path): array
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                abort(500, 'No se pudo generar el archivo ZIP.');
            }

            return ['driver' => 'zip', 'handle' => $zip];
        }

        if (file_exists($path)) {
            unlink($path);
        }

        return ['driver' => 'phar', 'handle' => new PharData($path)];
    }

    protected function archiveAddFromString(array $archive, string $path, string $contents): void
    {
        $archive['handle']->addFromString($path, $contents);
    }

    protected function archiveAddFile(array $archive, string $absolutePath, string $pathInArchive): void
    {
        $archive['handle']->addFile($absolutePath, $pathInArchive);
    }

    protected function archiveClose(array $archive): void
    {
        if ($archive['driver'] === 'zip') {
            $archive['handle']->close();

            return;
        }

        unset($archive['handle']);
    }

    protected function attachmentPreviewRows(array $attachments): array
    {
        return collect($attachments)
            ->map(function (array $attachment) {
                $attachment['preview_data_uri'] = null;

                if (
                    ($attachment['previewable_image'] ?? false)
                    && ($attachment['exists'] ?? false)
                    && !empty($attachment['absolute_path'])
                    && is_file($attachment['absolute_path'])
                    && filesize($attachment['absolute_path']) <= 3 * 1024 * 1024
                ) {
                    $attachment['preview_data_uri'] = $this->imageToDataUri(
                        $attachment['absolute_path'],
                        $attachment['extension'] ?? 'jpg'
                    );
                }

                return $attachment;
            })
            ->all();
    }

    protected function imageToDataUri(string $absolutePath, string $extension): ?string
    {
        $binary = @file_get_contents($absolutePath);

        if ($binary === false) {
            return null;
        }

        $mime = match (strtolower($extension)) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }
}
