<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\GraficosController;


Auth::routes();
require __DIR__ . '/auth.php';

//Route::middleware('web')->middleware('auth')

Route::middleware('web')
    ->group(function () {
        Route::get('/', 'HomeController@index')->name('inicio');
        //Route::get  ('/',                  'ReclamosController@inicio')                 ->name('inicio');
    

        // Route::get('/authUser', function () {
        //     $userId = Auth::id();
        //     return $userId
        // });
        Route::get('/authUserName', function () {
            $userName = Auth::user()->name;
            return $userName;
        });

        Route::get('/{any}', function () {
            return view('crm.plantillas.resumenVer');
        })->where('any', '.*');

        Route::prefix('resultados')
            ->middleware('permission:ver_estadísticas')
            ->group(function () {
                Route::get('/consultar', 'ResultadosController@formulario')->name('resultados.formulario');
                Route::get('/anual/{anio}', 'DatosAnualController@calcularAnual')->name('resultados.anual')->middleware('permission:ver_info_sensible');
                Route::post('/anual', 'DatosAnualController@calcularAnualAnio')->name('resultados.anual.anio')->middleware('permission:ver_info_sensible');
                Route::view('/resultados', 'crm.resultados.listar2')->name('resultados.resultados');
                Route::post('/resultados', 'ResultadosController@calcular')->name('resultados.calcular');       //Panel de busqueda de clicod
                // Route::view('/graphic', "crm.graficos.consultar")->name('graficos.consultar');
                // Route::get('/grafic', '')->name('resultados.resultados');
                Route::get('/detalle/{tipo}/{rango}/{unidad}/{anio}', 'DatosAnualController@detalleItems')->name('resultados.detalle');
            });

        // Route::prefix('graficos')->group(function () {
        // Route::get('/resumen', [GraficosController::class, 'resumen'])->name('graficos.resumen');
        // Route::match(['get', 'post'], '/tablero', [GraficosController::class, 'tablero'])->name('graficos.tablero');
        // Route::get('/consultar', [GraficosController::class, 'formulario'])->name('graficos.formulario');
        // });
    

        Route::prefix('graficos')
            ->middleware('permission:ver_gráficos')
            ->group(function () {
                Route::get('/', 'GraficosController@tablero')->name('graficos.resumen');
                //Route::post ('/graficos',     'GraficosController@tablero')               ->name('graficos.tablero');
                Route::post('/', 'GraficosController@tablero')->name('graficos.resumen1');
                Route::get('/graphics', 'GraficosController@formulario')->name('graficos.formulario');
            });


        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('role:developer');


    });
