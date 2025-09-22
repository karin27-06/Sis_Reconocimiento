<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use TCPDF;
use Carbon\Carbon;

class AlertPDFController extends Controller
{
    public function exportPDF()
    {
        // Obtener los datos de alertas con su relación
        $alerts = Alert::orderBy('id', 'asc')->get();

        // Convertirlos en array con formato
        $alertsArray = $alerts->map(function ($alert) {
            return [
                'id' => $alert->id,
                'movimiento' => $alert->movimiento ? $alert->movimiento->id : 'Sin movimiento',
                'descripcion' => $alert->descripcion,
                'fecha' => $alert->fecha ? Carbon::parse($alert->fecha)->format('d-m-Y') : null,
                'tipo' => $alert->tipo == 1 ? 'Huella' : ($alert->tipo == 2 ? 'Cara' : 'Desconocido'),
                'created_at'=>$alert->created_at,
                'updated_at' => $alert->updated_at,
            ];
        })->toArray();

        // Crear el objeto TCPDF
        $pdf = new TCPDF();

        $pdf->SetCreator('Laravel TCPDF');
        $pdf->SetAuthor('Laravel');
        $pdf->SetTitle('Lista de Alertas');
        $pdf->SetSubject('Reporte de Alertas');

        // Configuración de márgenes
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Eliminar la línea de encabezado
        $pdf->SetHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);

        // Personalizar pie de página
        $pdf->setFooterData(array(0, 0, 0), array(255, 255, 255));

        // Agregar página
        $pdf->AddPage();

        // Encabezado principal
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 20, 'Lista de Alertas', 0, 1, 'C');

        // Encabezados de la tabla
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(242, 242, 242);

        $header = ['ID', 'Movimiento', 'Descripción', 'Fecha', 'Tipo', 'Creación', 'Actualización'];
        $widths = [7, 23, 60, 20, 20, 30, 30]; // Ajustados para 8 columnas

        // Dibujar encabezados
        foreach ($header as $i => $col) {
            $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
        }
        $pdf->Ln();

        // Imprimir los datos
        $pdf->SetFont('helvetica', '', 8);

        foreach ($alertsArray as $alert) {
            // Si se llega al final de la página -> nueva página y repetir encabezados
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(242, 242, 242);
                foreach ($header as $i => $col) {
                    $pdf->MultiCell($widths[$i], 8, $col, 1, 'C', 1, 0);
                }
                $pdf->Ln();
            }

            // Datos
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell($widths[0], 8, $alert['id'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[1], 8, $alert['movimiento'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[2], 8, $alert['descripcion'], 1, 'L', 0, 0); // Descripción alineada a la izquierda
            $pdf->MultiCell($widths[3], 8, $alert['fecha'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[4], 8, $alert['tipo'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[5], 8, $alert['created_at'], 1, 'C', 0, 0);
            $pdf->MultiCell($widths[6], 8, $alert['updated_at'], 1, 'C', 0, 0);
            $pdf->Ln();
        }

        // Limpiar buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdfOutput = $pdf->Output('Alertas.pdf', 'S');

        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Alertas.pdf"');
    }
}
