<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ResumenVer\ResumenVerController;
use App\Http\Controllers\ResumenVer\SalesObjectiveController;
use App\Http\Controllers\ResumenVer\ProveedoresDeudaController;
use App\Http\Controllers\ResumenVer\GastosAnalisisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return response()->json([
//             'success' => true
//             ]);
// });
Route::middleware('auth:api')->group(function () {
    // Route::get('realizar', [ResumenVerController::class, "getRealizarData"] );
});
Route::get('/getRealizarData', [ResumenVerController::class, "getRealizarData"]);
// Rutas para objetivos de ventas
Route::get('/objetivos/progress', [SalesObjectiveController::class, "getProgress"]);
Route::post('/objetivos/set', [SalesObjectiveController::class, 'setObjective']);
Route::get('/objetivos/yearly', [SalesObjectiveController::class, 'getYearlyObjectives']);

// Rutas para el seguimiento de deuda a proveedores
Route::prefix('proveedores')->group(function () {
    // Deuda total de proveedores a la fecha
    Route::get('/deuda-total', [ProveedoresDeudaController::class, 'getDeudaTotal']);

    // Pasivo de facturas actual
    Route::get('/pasivo-facturas', [ProveedoresDeudaController::class, 'getPasivoFacturas']);

    // Históricos de deuda y pagos (semanales y mensuales)
    Route::get('/historico-pagos', [ProveedoresDeudaController::class, 'getHistoricoPagos']);

    // Detalle de un proveedor específico
    Route::get('/detalle/{proveedorId}', [ProveedoresDeudaController::class, 'getDetalleProveedor']);
});

// Rutas para análisis de gastos
Route::prefix('gastos-analisis')->group(function () {
    Route::get('/sobre-ventas', [GastosAnalisisController::class, 'gastosSobreVentas']);
    Route::get('/sobre-total', [GastosAnalisisController::class, 'gastosSobreTotalGastos']);
    Route::get('/mas-relevantes', [GastosAnalisisController::class, 'gastosMasRelevantes']);
    Route::get('/dashboard', [GastosAnalisisController::class, 'dashboardGastos']);
});
