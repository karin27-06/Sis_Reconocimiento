<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeTypeController;
use App\Http\Controllers\Reportes\EmployeeTypePDFController;
use App\Http\Controllers\Reportes\EmployeePDFController;
use App\Http\Controllers\Reportes\PresentationPDFController;
use App\Http\Controllers\Api\PresentationController;
use App\Models\EmployeeType;

Route::middleware('auth')->group(function () {
    Route::apiResource('tipos_empleados', EmployeeType::class);
    Route::apiResource('empleado', EmployeeController::class);
    //Route::apiResource('caja', CajaController::class);

    #EXPORT API
    // Exportación a Excel
    Route::get('/presentaciones/export-excel', [PresentationController::class, 'exportExcel']);
    Route::get('/tipos_empleados/export-excel', [EmployeeTypeController::class, 'exportExcel']);
    Route::get('/empleados/export-excel', [EmployeeController::class, 'exportExcel']);

    // Exportación a PDF
    Route::get('/presentaciones/export-pdf', [PresentationPDFController::class, 'exportPDF']);
    Route::get('/tipos_empleados/export-pdf', [EmployeeTypePDFController::class, 'exportPDF']);
    Route::get('/empleados/export-pdf', [EmployeePDFController::class, 'exportPDF']);

    #IMPORT API
    // Importación de Excel
    Route::post('/presentaciones/import-excel', [PresentationController::class, 'importExcel']);
    Route::post('/tipos_empleados/import-excel', [EmployeeTypeController::class, 'importExcel']);
    Route::post('/empleados/import-excel', [EmployeeController::class, 'importExcel']);

});

