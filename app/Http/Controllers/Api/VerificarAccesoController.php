<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerificarAccesoController extends Controller
{
    public function verificar(Request $request)
    {
        // Establecer zona horaria
        date_default_timezone_set('America/Lima');
        $fechaRecepcion = Carbon::now()->format('Y-m-d H:i:s');

        // Obtener parámetros del request
        $idEspacio = (int) $request->input('idEspacio', 0);
        $idTipo = (int) $request->input('idTipo', 0);
        $codigoError = (int) $request->input('codigo_error', 0);
        $idHuella = $request->input('idHuella'); // si es tipo 2

        // Respuesta base
        $respuesta = [
            'idEspacio' => $idEspacio,
            'idTipo' => $idTipo,
            'codigoError' => $codigoError,
            'fechaRecepcion' => $fechaRecepcion,
        ];

        // Lógica condicional según idTipo
        if ($idTipo === 1) {
            // Foto
            $respuesta['mensaje'] = '📸 Recibida imagen para reconocimiento facial';
            $respuesta['fotoBase64_recibida'] = $request->has('fotoBase64');
        } elseif ($idTipo === 2) {
            // Huella
            $respuesta['mensaje'] = '👆 Recibida huella para verificación';
            $respuesta['idHuella'] = $idHuella;
        } else {
            return response()->json([
                'error' => '❌ idTipo inválido, debe ser 1 (foto) o 2 (huella)',
                'fechaRecepcion' => $fechaRecepcion,
            ], 400);
        }

        return response()->json($respuesta);
    }
}
