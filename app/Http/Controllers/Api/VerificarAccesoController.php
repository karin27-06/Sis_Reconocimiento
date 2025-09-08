<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

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
        $idHuella = $request->input('idHuella');

        // Respuesta base
        $respuesta = [
            'idEspacio' => $idEspacio,
            'idTipo' => $idTipo,
            'codigoError' => $codigoError,
            'fechaRecepcion' => $fechaRecepcion,
        ];

        try {
            // Probar conexión a la BD
            DB::select("SELECT 1 as test");
        } catch (Exception $e) {
            return response()->json([
                'error' => '❌ Error de conexión a la base de datos',
                'detalle' => $e->getMessage(),
                'fechaRecepcion' => $fechaRecepcion,
            ], 500);
        }

        try {
            if ($idTipo === 1) {
                // Procesar reconocimiento facial
                $respuesta['mensaje'] = '📸 Recibida imagen para reconocimiento facial';
                $respuesta['fotoBase64_recibida'] = $request->has('fotoBase64');

            } elseif ($idTipo === 2) {
                // Procesar huella
                $respuesta['mensaje'] = '👆 Recibida huella para verificación';
                $respuesta['idHuella'] = $idHuella;

                if (empty($idHuella)) {
                    $respuesta['codigoError'] = 107;
                    $respuesta['reconocido'] = 0;
                    $respuesta['acceso'] = 0;
                } else {
                    // Buscar empleado por huella
                    $empleado = DB::table('employees')
                        ->where('idHuella', (int) $idHuella)
                        ->first();

                    if ($empleado) {
                        $respuesta['reconocido'] = 1;
                        $respuesta['nombre'] = $empleado->nombre ?? '';
                        $respuesta['apellido'] = $empleado->apellido ?? '';

                        // Buscar horario válido para este empleado en este espacio
                        $fechaActual = Carbon::now();

                        $horario = DB::table('horarios')
                            ->where('idEmpleado', $empleado->id)
                            ->where('idEspacio', $idEspacio)
                            ->whereDate('fechaInicio', '<=', $fechaActual->toDateString())
                            ->whereDate('fechaFin', '>=', $fechaActual->toDateString())
                            ->first();

                        if ($horario) {
                            $respuesta['acceso'] = 1;
                            $respuesta['mensajeHorario'] = '✅ Acceso dentro del horario permitido';
                        } else {
                            $respuesta['acceso'] = 0;
                            $respuesta['mensajeHorario'] = '⛔ Acceso denegado: fuera del horario permitido';
                        }
                    } else {
                        $respuesta['reconocido'] = 0;
                        $respuesta['acceso'] = 0;
                        $respuesta['mensaje'] = '❌ Huella no reconocida';
                    }
                }

                $respuesta['fechaReconocimiento'] = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                return response()->json([
                    'error' => '❌ idTipo inválido, debe ser 1 (foto) o 2 (huella)',
                    'fechaRecepcion' => $fechaRecepcion,
                ], 400);
            }

        } catch (Exception $e) {
            return response()->json([
                'error' => '❌ Error interno al procesar la solicitud',
                'detalle' => $e->getMessage(),
                'fechaRecepcion' => $fechaRecepcion,
            ], 500);
        }

        return response()->json($respuesta);
    }
}
