<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VerificarAccesoController extends Controller
{
    public function verificar(Request $request)
    {
        // Zona horaria Perú
        date_default_timezone_set('America/Lima');
        $fechaRecepcion = Carbon::now()->format('Y-m-d H:i:s');

        // Diccionario de códigos de error
        $codigosErrores = [
            0   => 'Sin error',
            101 => 'Error al tomar la foto en ESP32',
            102 => 'Error al decodificar la foto',
            107 => 'No se envió idHuella',
            205 => 'Acceso denegado según servidor',
            404 => 'Error de servidor al enviar foto',
            405 => 'Falló POST al enviar datos',
            406 => 'Conexión HTTPS fallida',
        ];

        // Obtener datos del request
        $idEspacio       = (int) $request->input('idEspacio', 0);
        $idTipo          = (int) $request->input('idTipo', 1);
        $fotoBase64      = $request->input('fotoBase64');
        $fechaEnvioESP32 = $request->input('fechaEnvioESP32');
        $codigoError     = (int) $request->input('codigo_error', 0);
        $idHuella        = $request->input('idHuella');

        // Normalizar fecha de envío
        $fechaEnvioESP32 = $fechaEnvioESP32 
            ? Carbon::parse($fechaEnvioESP32)->format('Y-m-d H:i:s') 
            : $fechaRecepcion;

        // Validar idTipo
        if (!in_array($idTipo, [1, 2])) {
            return response()->json([
                'error' => '❌ idTipo inválido, debe ser 1 (foto) o 2 (huella)',
                'fechaRecepcion' => $fechaRecepcion,
            ], 400);
        }

        $reconocido = 0;
        $access = 0;
        $fechaReconocimiento = null;
        $empleadoReconocido = null;
        $rutaFotoAcceso = null;

        if ($idTipo === 1) {
            // FOTO
            if ($codigoError === 0 && $fotoBase64) {
                // Guardar foto en storage
                $nombreArchivo = uniqid('acceso_') . '.png';
                $carpeta = 'uploads/fotos/accesos';
                $rutaFotoAcceso = $carpeta . '/' . $nombreArchivo;

                $fotoDecodificada = base64_decode(
                    preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64)
                );

                if ($fotoDecodificada) {
                    Storage::disk('public')->put($rutaFotoAcceso, $fotoDecodificada);
                } else {
                    $codigoError = 102;
                }

                // Comparar con fotos de empleados
                $empleados = DB::table('empleado')
                    ->whereNotNull('foto')
                    ->where('foto', '!=', '')
                    ->get();

                foreach ($empleados as $empleado) {
                    $rutaEmpleado = storage_path('app/public/uploads/fotos/empleados/' . $empleado->foto);
                    if (!file_exists($rutaEmpleado)) continue;

                    $payload = json_encode([
                        "foto1" => 'data:image/png;base64,' . base64_encode($fotoDecodificada),
                        "foto2" => 'data:image/png;base64,' . base64_encode(file_get_contents($rutaEmpleado))
                    ]);

                    $ch = curl_init("http://185.140.33.51:5000/comparar");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    $respuesta = curl_exec($ch);
                    curl_close($ch);

                    $resultado = json_decode($respuesta, true);
                    if (!empty($resultado['resultado']) && $resultado['resultado'] === true) {
                        $empleadoReconocido = $empleado;
                        break;
                    }
                }

                $fechaReconocimiento = Carbon::now()->format('Y-m-d H:i:s');

                if ($empleadoReconocido) {
                    $reconocido = 1;
                    $access = DB::table('horarios')
                        ->where('idEspacio', $idEspacio)
                        ->where('idEmpleado', $empleadoReconocido->id)
                        ->whereRaw('? BETWEEN fechaInicio AND fechaFin', [$fechaReconocimiento])
                        ->exists() ? 1 : 0;
                }
            }
        }

        if ($idTipo === 2) {
            // HUELLA
            if ($idHuella) {
                $empleado = DB::table('empleado')
                    ->where('idHuella', intval($idHuella))
                    ->first();

                $fechaReconocimiento = Carbon::now()->format('Y-m-d H:i:s');

                if ($empleado) {
                    $reconocido = 1;
                    $access = DB::table('horarios')
                        ->where('idEspacio', $idEspacio)
                        ->where('idEmpleado', $empleado->id)
                        ->whereRaw('? BETWEEN fechaInicio AND fechaFin', [$fechaReconocimiento])
                        ->exists() ? 1 : 0;

                    $empleadoReconocido = $empleado;
                } else {
                    $codigoError = 107;
                }
            } else {
                $codigoError = 107;
            }
        }

        // Guardar en tabla movimientos
        DB::table('movimientos')->insert([
            'idEspacio' => $idEspacio,
            'idTipo' => $idTipo,
            'reconocido' => $reconocido,
            'access' => $access,
            'error' => $codigoError,
            'fechaEnvioESP32' => $fechaEnvioESP32,
            'fechaRecepcion' => $fechaRecepcion,
            'fechaReconocimiento' => $fechaReconocimiento,
        ]);

        return response()->json([
            'idEspacio' => $idEspacio,
            'idTipo' => $idTipo,
            'codigoError' => $codigoError,
            'mensajeError' => $codigosErrores[$codigoError] ?? 'Error desconocido',
            'fechaEnvioESP32' => $fechaEnvioESP32,
            'fechaRecepcion' => $fechaRecepcion,
            'fechaReconocimiento' => $fechaReconocimiento,
            'reconocido' => $reconocido,
            'access' => $access,
            'empleado' => $empleadoReconocido,
            'fotoGuardada' => $rutaFotoAcceso,
        ]);
    }
}
