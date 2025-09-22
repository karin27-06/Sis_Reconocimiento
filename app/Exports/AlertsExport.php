<?php

namespace App\Exports;

use App\Models\Alert;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AlertsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Alert::orderBy('id', 'asc')->get();
    }

    public function map($alert): array
    {
        return [
            $alert->id,
            $alert->movimiento ? $alert->movimiento->id : 'Sin movimiento',
            $alert->descripcion,
            $alert->fecha ? Carbon::parse($alert->fecha)->format('d-m-Y') : null,
            $alert->tipo == 1 ? 'Huella' : ($alert->tipo == 2 ? 'Cara' : 'Desconocido'),
            Carbon::parse($alert->created_at)->format('d-m-Y H:i:s A'),
            Carbon::parse($alert->updated_at)->format('d-m-Y H:i:s A'),
        ];
    }

    public function headings(): array
    {
        return [
            ['LISTA DE ALERTAS', '', '', '', '', '', ''], // Título
            [], // Fila en blanco
            ['ID', 'Movimiento', 'Descripción', 'Fecha', 'Tipo', 'Creación', 'Actualización'], // Encabezados
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el título
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CFE2F3'],
            ],
        ]);

        // Estilo para los encabezados
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EAD3'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Estilo para las filas de datos
        $sheet->getStyle('A4:G' . $sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Ajuste de columnas
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
