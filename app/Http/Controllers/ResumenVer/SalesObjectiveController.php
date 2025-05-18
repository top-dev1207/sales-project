<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use App\Models\ObjetivoVentas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class SalesObjectiveController extends Controller
{
    /**
     * Obtener el progreso actual de objetivos de ventas por mes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgress(Request $request)
    {
        try {
            // Obtener mes y año de la solicitud o usar el mes actual
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            // Obtener el objetivo del mes de la base de datos
            $objetivo = ObjetivoVentas::where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$objetivo) {
                return response()->json([
                    'error' => 'No se encontró objetivo para el mes especificado'
                ], 404);
            }

            $totalObjective = $objetivo->monto;

            // Calcular las ventas actuales del mes
            $currentAchieved = $this->getVentasActuales($month, $year);

            // Obtener días restantes en el mes
            $now = Carbon::now();
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;

            // Si estamos en el mes actual, calcular días restantes
            if ($now->month == $month && $now->year == $year) {
                $remainingDays = Carbon::create($year, $month, $daysInMonth)->diffInDays($now);
            } else {
                // Si es un mes futuro, todos los días son restantes
                // Si es un mes pasado, no hay días restantes
                $remainingDays = $now->month == $month && $now->year == $year ?
                    Carbon::create($year, $month, $daysInMonth)->diffInDays($now) :
                    ($now->month < $month || $now->year < $year ? $daysInMonth : 0);
            }

            // Calcular proyección diaria
            $dailyProjection = $remainingDays > 0 ?
                ($totalObjective - $currentAchieved) / $remainingDays :
                0;

            // Log::info(Auth::user()->name . " | consulta objetivos de ventas para $month/$year | ");

            return response()->json([
                'totalObjective' => $totalObjective,
                'currentAchieved' => $currentAchieved,
                'remainingDays' => $remainingDays,
                'dailyProjection' => round($dailyProjection, 0)
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ObjetivosVentasController@getProgress: ' . $e->getMessage());
            return response()->json(['error' => 'Error al calcular objetivos de ventas'], 500);
        }
    }

    /**
     * Establecer un nuevo objetivo de ventas para un mes específico
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setObjective(Request $request)
    {
        try {
            $validated = $request->validate([
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2000',
                'monto' => 'required|numeric|min:1',
            ]);

            $objetivo = ObjetivoVentas::updateOrCreate(
                ['month' => $validated['month'], 'year' => $validated['year']],
                ['monto' => $validated['monto']]
            );
            // Log::info(Auth::user()->name . " | estableció objetivo de ventas para {$validated['month']}/{$validated['year']} en {$validated['monto']} | ");

            return response()->json([
                'message' => 'Objetivo de ventas establecido correctamente',
                'data' => $objetivo
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ObjetivosVentasController@setObjective: ' . $e->getMessage());
            return response()->json(['error' => 'Error al establecer objetivo de ventas'], 500);
        }
    }

    /**
     * Obtener ventas realizadas en un mes específico
     * 
     * @param int $month
     * @param int $year
     * @return float
     */
    private function getVentasActuales($month, $year)
    {
        // Obtener ventas usando la misma lógica que en ResultadosController
        $ventasTotales = Ventas::join(
            DB::raw('(SELECT nro_venta, MAX(id) as idMax FROM ventas GROUP BY nro_venta) as ultimo_doc'),
            function ($join) {
                $join->on('ventas.nro_venta', '=', 'ultimo_doc.nro_venta');
                $join->on('ventas.id', '=', 'ultimo_doc.idMax');
            }
        )
            ->whereMonth('fecha_venta', $month)
            ->whereYear('fecha_venta', $year)
            ->where('estado_venta', '!=', '6') // No borrados
            ->sum('ingresos');

        return $ventasTotales;
    }

    /**
     * Obtener todos los objetivos y progreso para el año actual
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearlyObjectives(Request $request)
    {
        try {
            $year = $request->input('year', Carbon::now()->year);

            $objetivos = ObjetivoVentas::where('year', $year)
                ->orderBy('month')
                ->get();

            $result = [];

            foreach ($objetivos as $objetivo) {
                $currentAchieved = $this->getVentasActuales($objetivo->month, $year);
                $daysInMonth = Carbon::create($year, $objetivo->month)->daysInMonth;

                // Calcular días restantes (similar a getProgress)
                $now = Carbon::now();
                if ($now->month == $objetivo->month && $now->year == $year) {
                    $remainingDays = Carbon::create($year, $objetivo->month, $daysInMonth)->diffInDays($now);
                } else {
                    $remainingDays = $now->month == $objetivo->month && $now->year == $year ?
                        Carbon::create($year, $objetivo->month, $daysInMonth)->diffInDays($now) :
                        ($now->month < $objetivo->month || $now->year < $year ? $daysInMonth : 0);
                }

                $dailyProjection = $remainingDays > 0 ?
                    ($objetivo->monto - $currentAchieved) / $remainingDays :
                    0;

                $result[] = [
                    'month' => $objetivo->month,
                    'monthName' => Carbon::create($year, $objetivo->month, 1)->format('F'),
                    'totalObjective' => $objetivo->monto,
                    'currentAchieved' => $currentAchieved,
                    'remainingDays' => $remainingDays,
                    'dailyProjection' => round($dailyProjection, 0),
                    'percentageAchieved' => $objetivo->monto > 0 ?
                        round(($currentAchieved / $objetivo->monto) * 100, 2) : 0
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error en ObjetivosVentasController@getYearlyObjectives: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener objetivos anuales'], 500);
        }
    }
}
