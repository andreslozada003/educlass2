<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RepairsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $repairs;

    public function __construct($repairs)
    {
        $this->repairs = $repairs;
    }

    public function collection()
    {
        return $this->repairs;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Fecha',
            'Cliente',
            'Dispositivo',
            'Marca',
            'Modelo',
            'IMEI',
            'Estado',
            'Prioridad',
            'Técnico',
            'Costo Total',
            'Fecha Entrega',
        ];
    }

    public function map($repair): array
    {
        return [
            $repair->repair_code,
            $repair->created_at->format('d/m/Y'),
            $repair->customer->full_name,
            $repair->device_type_label,
            $repair->brand,
            $repair->model,
            $repair->imei,
            $repair->status_label,
            $repair->priority_label,
            $repair->technician ? $repair->technician->name : 'No asignado',
            number_format($repair->total_cost, 2),
            $repair->delivered_at ? $repair->delivered_at->format('d/m/Y') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
