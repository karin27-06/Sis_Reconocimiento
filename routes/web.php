<?php

use App\Http\Controllers\Reportes\EmployeeTypePDFController;
use App\Http\Controllers\Reportes\EmployeePDFController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ConsultasDni;
use App\Http\Controllers\Api\ConsultasId;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UsuariosController;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\MovementController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Reportes\SchedulePDFController;
use App\Http\Controllers\Reportes\SpacePDFController;
use App\Http\Controllers\Web\EmployeeTypeWebController;
use App\Http\Controllers\Web\EmployeeWebController;
use App\Http\Controllers\Web\MovementWebController;
use App\Http\Controllers\Web\ScheduleWebController;
use App\Http\Controllers\Web\SpaceWebController;
use App\Http\Controllers\Web\UsuarioWebController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return Inertia::render('Dashboard', [
            'mustReset' => $user->restablecimiento == 0,
        ]);
    })->name('dashboard');

    #VISTAS DEL FRONTEND
    Route::get('/horarios', [ScheduleWebController::class, 'index'])->name('index.view');
    Route::get('/espacios', [SpaceWebController::class, 'index'])->name('index.view');
    Route::get('/empleados', [EmployeeWebController::class, 'index'])->name('index.view');
    Route::get('/movimientos', [MovementWebController::class, 'index'])->name('index.view');
    Route::get('/tipo_empleados', [EmployeeTypeWebController::class, 'index'])->name('index.view');
    Route::get('/usuario', [UsuarioWebController::class, 'index'])->name('index.view');
    Route::get('/roles', [UsuarioWebController::class, 'roles'])->name('roles.view');
    Route::get('/datos/dashboard', [DashboardController::class, 'getdatos']);

    #CONSULTA  => BACKEND
    Route::get('/consulta/{dni}', [ConsultasDni::class, 'consultar'])->name('consultar.dni');
    Route::get('/user-id', [ConsultasId::class, 'getUserId'])->middleware('auth:api');

    // ESPACIO -> BACKEND
    Route::prefix('espacio')->group(function () {
        Route::get('/', [SpaceController::class, 'index'])->name('espacio.index');
        Route::post('/', [SpaceController::class, 'store'])->name('espacios.store');
        Route::get('/{space}', [SpaceController::class, 'show'])->name('espacios.show');
        Route::put('/{space}', [SpaceController::class, 'update'])->name('espacios.update');
        Route::delete('/{space}', [SpaceController::class, 'destroy'])->name('espacios.destroy');
    });

    #HORARIO => BACKEND
    Route::prefix('horario')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('horario.index');
        Route::post('/', [ScheduleController::class, 'store'])->name('horarios.store');
        Route::get('{schedule}', [ScheduleController::class, 'show'])->name('horarios.show');
        Route::put('{schedule}', [ScheduleController::class, 'update'])->name('horarios.update');
        Route::delete('{schedule}', [ScheduleController::class, 'destroy'])->name('horarios.destroy');
    });

    #EMPLEADO => BACKEND
    Route::prefix('empleado')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('empleado.index');
        Route::post('/', [EmployeeController::class, 'store'])->name('empleados.store');
        Route::get('{employee}', [EmployeeController::class, 'show'])->name('empleados.show');
        Route::put('{employee}', [EmployeeController::class, 'update'])->name('empleados.update');
        Route::delete('{employee}', [EmployeeController::class, 'destroy'])->name('empleados.destroy');
    });

    #MOVIMIENTO => BACKEND
    Route::prefix('movimiento')->group(function () {
        Route::get('/', [MovementController::class, 'index'])->name('movimiento.index');
        Route::post('/', [MovementController::class, 'store'])->name('movimientos.store');
        Route::get('{movement}', [MovementController::class, 'show'])->name('movimientos.show');
        Route::put('{movement}', [MovementController::class, 'update'])->name('movimientos.update');
        Route::delete('{movement}', [MovementController::class, 'destroy'])->name('movimientos.destroy');
    });

    #TIPOS DE EMPLEADOS -> BACKEND
    Route::prefix('tipo_empleado')->group(function () {
        Route::get('/', [EmployeeTypeController::class, 'index'])->name('Tipos_Empleados.index');
        Route::post('/', [EmployeeTypeController::class, 'store'])->name('Tipos_Empleados.store');
        Route::get('/{employeeType}', [EmployeeTypeController::class, 'show'])->name('Tipos_Empleados.show');
        Route::put('/{employeeType}', [EmployeeTypeController::class, 'update'])->name('Tipos_Empleados.update');
        Route::delete('/{employeeType}', [EmployeeTypeController::class, 'destroy'])->name('Tipos_Empleados.destroy');
    });

    #USUARIOS -> BACKEND
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/{user}', [UsuariosController::class, 'show'])->name('usuarios.show');
        Route::put('/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
    });

    #ROLES => BACKEND
    Route::prefix('rol')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('rol.index');
        Route::get('/Permisos', [RolesController::class, 'indexPermisos'])->name('rol.indexPermisos');
        Route::post('/', [RolesController::class, 'store'])->name('rol.store');
        Route::get('/{id}', [RolesController::class, 'show'])->name('rol.show');
        Route::put('/{id}', [RolesController::class, 'update'])->name('rol.update');
        Route::delete('/{id}', [RolesController::class, 'destroy'])->name('rol.destroy');
    });
    Route::prefix('panel/reports')->group(function () {

        #EXPORTACION Y IMPORTACION ESPACIOS
        Route::get('/export-excel-spaces', [SpaceController::class, 'exportExcel'])->name('export-excel-spaces');
        Route::get('/export-pdf-spaces', [SpacePDFController::class, 'exportPDF'])->name('export-pdf-spaces');
        // Ruta para importar desde Excel
        Route::post('/import-excel-spaces', [SpaceController::class, 'importExcel'])->name('import-excel-spaces');

        #EXPORTACION Y IMPORTACION HORARIOS
        Route::get('/export-excel-schedules', [ScheduleController::class, 'exportExcel'])->name('export-excel-schedules');
        Route::get('/export-pdf-schedules', [SchedulePDFController::class, 'exportPDF'])->name('export-pdf-schedules');
        // Ruta para importar desde Excel
        Route::post('/import-excel-schedules', [ScheduleController::class, 'importExcel'])->name('import-excel-schedules');

        #EXPORTACION Y IMPORTACION TIPOS DE EMPLEADOS
        Route::get('/export-excel-employeeTypes', [EmployeeTypeController::class, 'exportExcel'])->name('export-excel-employeeTypes');
        Route::get('/export-pdf-employeeTypes', [EmployeeTypePDFController::class, 'exportPDF'])->name('export-pdf-employeeTypes');
        // Ruta para importar desde Excel
        Route::post('/import-excel-employeeTypes', [EmployeeTypeController::class, 'importExcel'])->name('import-excel-employeeTypes');

        #EXPORTACION Y IMPORTACION MOVIMIENTOS
        Route::get('/export-excel-movements', [MovementController::class, 'exportExcel'])->name('export-excel-movements');
        //Route::get('/export-pdf-movements', [MovementPDFController::class, 'exportPDF'])->name('export-pdf-movements');
        // Ruta para importar desde Excel
        Route::post('/import-excel-movements', [MovementController::class, 'importExcel'])->name('import-excel-movements');

        #EXPORTACION Y IMPORTACION EMPLEADOS
        Route::get('/export-excel-employees', [EmployeeController::class, 'exportExcel'])->name('export-excel-employees');
        Route::get('/export-pdf-employees', [EmployeePDFController::class, 'exportPDF'])->name('export-pdf-employees');
        // Ruta para importar desde Excel
        Route::post('/import-excel-employees', [EmployeeController::class, 'importExcel'])->name('import-excel-employees');
    
    });
});

// Archivos de configuración adicionales
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
