<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ResumenVer\SalesObjectiveController;
use App\Http\Controllers\ResumenVer\ProveedoresDeudaController;
use App\Http\Controllers\ResumenVer\GastosAnalisisController;
use App\Http\Controllers\ResumenVer\CostMetricsController;
use App\Http\Controllers\ResumenVer\SalesProjectionController;
use App\Http\Controllers\ResumenVer\VentasAnalisisController;
use App\Http\Controllers\ResumenVer\UserLoginTimeController;
use App\Http\Controllers\ResumenVer\ExpensesAnalysisController;
use App\Http\Controllers\ResumenVer\SalesAnalyticsController;
use App\Http\Controllers\ResumenVer\FormaPagoController;
use App\Http\Controllers\ResumenVer\FinancialMetricsController;
use App\Http\Controllers\ResumenVer\IndicePintaController;
use App\Http\Controllers\ResumenVer\UserLoginAnalyticsController;
//      * Almacena una nueva forma de pago

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true
    ]);
});

Route::prefix('gastos-analisis')->group(function () {
    Route::get('/sobre-ventas', [GastosAnalisisController::class, 'gastosSobreVentas']);
    Route::get('/sobre-total', [GastosAnalisisController::class, 'gastosSobreTotalGastos']);
    Route::get('/mas-relevantes', [GastosAnalisisController::class, 'gastosMasRelevantes']);
    Route::get('/dashboard', [GastosAnalisisController::class, 'dashboardGastos']);
});
// Rutas para métricas de costos
Route::prefix('cost-metrics')->group(function () {
    Route::get('/', [CostMetricsController::class, 'getCostMetrics']);
    Route::get('/detailed', [CostMetricsController::class, 'getDetailedCostMetrics']);
});
Route::prefix('sales-projection')->group(function () {
    Route::get('/monthly', [SalesProjectionController::class, 'getMonthlyProjection']);
    Route::get('/annual', [SalesProjectionController::class, 'getAnnualProjection']);
    Route::get('/dashboard', [SalesProjectionController::class, 'getDashboard']);
});

// Sales Objective Routes
Route::prefix('sales-objective')->group(function () {
    Route::get('/progress', [SalesObjectiveController::class, 'getProgress']);
    Route::post('/set', [SalesObjectiveController::class, 'setObjective']);
    Route::get('/yearly', [SalesObjectiveController::class, 'getYearlyObjectives']);
});
Route::prefix('proveedores')->group(function () {
    // Total debt of providers as of current date
    Route::get('/deuda-total', [ProveedoresDeudaController::class, 'getDeudaTotal']);

    // Current invoice liabilities
    Route::get('/pasivo-facturas', [ProveedoresDeudaController::class, 'getPasivoFacturas']);

    // Historical debt and payment data (weekly and monthly)
    Route::get('/historico-pagos', [ProveedoresDeudaController::class, 'getHistoricoPagos']);

    // Details for a specific provider
    Route::get('/detalle/{proveedorId}', [ProveedoresDeudaController::class, 'getDetalleProveedor']);
});
Route::prefix('ventas-analisis')->group(function () {
    Route::get('/por-local-periodo-clima', [VentasAnalisisController::class, 'getVentasPorLocalPeriodoClima']);
    Route::get('/climas', [VentasAnalisisController::class, 'getClimas']);
    Route::get('/correlacion-clima-ventas', [VentasAnalisisController::class, 'getCorrelacionClimaVentas']);
});

Route::prefix('user-login-analytics')->group(function () {
    Route::get('/getAllUsers', [UserLoginAnalyticsController::class, 'getAllUsers']);
    // Daily login time analytics
    Route::get('/daily', [UserLoginAnalyticsController::class, 'getDailyLoginTime']);

    // Weekly login time analytics
    Route::get('/weekly', [UserLoginAnalyticsController::class, 'getWeeklyLoginTime']);

    // Monthly login time analytics
    Route::get('/monthly', [UserLoginAnalyticsController::class, 'getMonthlyLoginTime']);

    // Comprehensive analytics dashboard
    Route::get('/dashboard', [UserLoginAnalyticsController::class, 'getAnalyticsDashboard']);

    // Compare multiple users
    Route::get('/compare', [UserLoginAnalyticsController::class, 'compareUsers']);
});

Route::prefix('expenses-analysis')->group(function () {
    Route::get('/by-incidence', [ExpensesAnalysisController::class, 'getExpensesByIncidence']);
    Route::get('/compare-periods', [ExpensesAnalysisController::class, 'compareExpensesPeriods']);
});

Route::prefix('sales-analytics')->group(function () {
    Route::get('/day', [SalesAnalyticsController::class, 'dailySales']);
    Route::get('/week', [SalesAnalyticsController::class, 'weeklySales']);
    Route::get('/month', [SalesAnalyticsController::class, 'monthlySales']);
    Route::get('/comparison', [SalesAnalyticsController::class, 'salesComparison']);
    Route::get('/by-category', [SalesAnalyticsController::class, 'salesByCategory']);
    Route::get('/targets', [SalesAnalyticsController::class, 'salesTargets']);
    Route::get('/dashboard', [SalesAnalyticsController::class, 'dashboard']);
});

Route::prefix('formas-pago')->group(function () {
    Route::get('/', [FormaPagoController::class, 'index']);
    Route::get('/configuraciones', [FormaPagoController::class, 'getConfiguraciones']);
    Route::get('/{id}', [FormaPagoController::class, 'show']);
    Route::post('/', [FormaPagoController::class, 'store']);
    Route::put('/{id}', [FormaPagoController::class, 'update']);
    Route::delete('/{id}', [FormaPagoController::class, 'destroy']);
});
Route::get('/financial-metrics/yearly', [FinancialMetricsController::class, 'getYearlyMetrics']);

Route::prefix('indice-pinta')->group(function () {
    // Gráfico temporal del índice pinta
    Route::get('/temporal', [IndicePintaController::class, 'getIndicePintaTemporal']);

    // Comparativo entre períodos 
    Route::get('/comparativo', [IndicePintaController::class, 'getComparativoIndicePinta']);
});

