<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    public function collection()
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'Número',
            'Fecha',
            'Cliente',
            'Vendedor',
            'Estado',
            'Método de Pago',
            'Subtotal',
            'Impuestos',
            'Descuento',
            'Total',
            'Ganancia',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->sale_number,
            $sale->created_at->format('d/m/Y H:i'),
            $sale->customer ? $sale->customer->full_name : 'Cliente General',
            $sale->user->name,
            $sale->status_label,
            $sale->payment_method_label,
            number_format($sale->subtotal, 2),
            number_format($sale->tax_amount, 2),
            number_format($sale->discount_amount, 2),
            number_format($sale->total, 2),
            number_format($sale->profit, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
