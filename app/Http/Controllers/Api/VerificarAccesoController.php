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
        // Usar hora de Perú
        date_default_timezone_set('America/Lima');

        // Definir códigos de error y su descripción
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

        // Variables iniciales
        $idEspacio = (int) $request->input('idEspacio', 0);
        $fotoAcceso = $request->input('fotoBase64', '');
        $idTipo = (int) $request->input('idTipo', 1);
        $fechaEnvioESP32 = $request->input('fechaEnvioESP32');
        $codigoError = (int) $request->input('codigo_error', 0);
        $fechaRecepcion = Carbon::now()->format('Y-m-d H:i:s');

        // Normalizar fecha de envío del ESP32
        $fechaEnvioESP32 = !empty($fechaEnvioESP32)
            ? Carbon::parse($fechaEnvioESP32)->format('Y-m-d H:i:s')
            : $fechaRecepcion;

        // Validar idTipo
        if (!in_array($idTipo, [1, 2])) {
            return response()->json(['error' => '❌ idTipo no válido (debe ser 1 o 2)'], 400);
        }

        // Variables por defecto
        $reconocido = 0;
        $access = 0;
        $fechaReconocimiento = null;

        if ($idTipo === 1) { // Foto
            if ($codigoError === 0) {
                if (!empty($fotoAcceso)) {
                    $carpetaAccesos = 'uploads/fotos/accesos';
                    if (!Storage::disk('public')->exists($carpetaAccesos)) {
                        Storage::disk('public')->makeDirectory($carpetaAccesos);
                    }

                    $nombreArchivo = uniqid("acceso_") . ".png";
                    $rutaFotoAcceso = "$carpetaAccesos/$nombreArchivo";

                    $fotoDecodificada = base64_decode(
                        preg_replace('#^data:image/\w+;base64,#i', '', $fotoAcceso)
                    );

                    if (!$fotoDecodificada) {
                        $codigoError = 102;
                    } else {
                        Storage::disk('public')->put($rutaFotoAcceso, $fotoDecodificada);
                    }

                    // Comparar con fotos de empleados
                    $empleados = DB::table('empleado')
                        ->select('id', 'nombre', 'apellido', 'foto')
                        ->whereNotNull('foto')
                        ->where('foto', '!=', '')
                        ->get();

                    $empleadoReconocido = null;
                    foreach ($empleados as $row) {
                        $rutaFotoEmpleado = storage_path("app/public/uploads/fotos/empleados/{$row->foto}");
                        if (!file_exists($rutaFotoEmpleado)) continue;

                        $data = json_encode([
                            "foto1" => 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($rutaFotoAcceso)),
                            "foto2" => 'data:image/png;base64,' . base64_encode(file_get_contents($rutaFotoEmpleado))
                        ]);

                        $ch = curl_init("http://185.140.33.51:5000/comparar");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        $respuesta = curl_exec($ch);
                        curl_close($ch);

                        $resultado = json_decode($respuesta, true);
                        if (!empty($resultado['resultado']) && $resultado['resultado'] === true) {
                            $empleadoReconocido = $row;
                            break;
                        }
                    }

                    $fechaReconocimiento = Carbon::now()->format('Y-m-d H:i:s');

                    if ($empleadoReconocido) {
                        $idEmpleado = (int) $empleadoReconocido->id;
                        $reconocido = 1;

                        $enHorario = DB::table('horarios')
                            ->where('idEspacio', $idEspacio)
                            ->where('idEmpleado', $idEmpleado)
                            ->whereRaw('? BETWEEN fechaInicio AND fechaFin', [$fechaReconocimiento])
                            ->exists();

                        $access = $enHorario ? 1 : 0;
                    }
                }
            }
        } elseif ($idTipo === 2) { // Huella
            $idHuellaEnviada = $request->input('idHuella');
            if ($idHuellaEnviada) {
                $empleado = DB::table('empleado')
                    ->where('idHuella', $idHuellaEnviada)
                    ->first();

                $fechaReconocimiento = Carbon::now()->format('Y-m-d H:i:s');

                if ($empleado) {
                    $reconocido = 1;
                    $enHorario = DB::table('horarios')
                        ->where('idEspacio', $idEspacio)
                        ->where('idEmpleado', $empleado->id)
                        ->whereRaw('? BETWEEN fechaInicio AND fechaFin', [$fechaReconocimiento])
                        ->exists();

                    $access = $enHorario ? 1 : 0;
                }
            } else {
                $codigoError = 107; // No se envió idHuella
            }
        }

        // Guardar en movimientos
        DB::table('movimientos')->insert([
            'idEspacio' => $idEspacio,
            'idTipo' => $idTipo,
            'reconocido' => $reconocido,
            'access' => $access,
            'error' => $codigoError,
            'fechaEnvioESP32' => $fechaEnvioESP32,
            'fechaRecepcion' => $fechaRecepcion,
            'fechaReconocimiento' => $fechaReconocimiento
        ]);

        return response()->json([
            'reconocido' => $reconocido,
            'access' => $access,
            'codigoError' => $codigoError,
            'mensajeError' => $codigosErrores[$codigoError] ?? 'Error desconocido',
            'fechaRecepcion' => $fechaRecepcion,
            'fechaReconocimiento' => $fechaReconocimiento
        ]);
    }
}
