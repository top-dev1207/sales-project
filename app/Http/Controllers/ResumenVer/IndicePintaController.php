<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IndicePintaController extends Controller
{
    /**
     * Obtener datos del índice pinta para gráficos temporales
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIndicePintaTemporal(Request $request)
    {
        // Validar parámetros de la solicitud
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'agrupacion' => 'required|in:dia,semana,mes,anio',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $agrupacion = $request->agrupacion;

        // Registrar la consulta en logs
        // Log::info(Auth::user()->name . " | consulta API INDICE PINTA desde $fechaInicio hasta $fechaFin agrupado por $agrupacion | ");

        // Definir la parte de agrupación para la consulta SQL
        switch ($agrupacion) {
            case 'dia':
                $groupByClause = "DATE(v.fecha_venta)";
                $dateFormat = "DATE(v.fecha_venta) as fecha";
                break;
            case 'semana':
                $groupByClause = "YEARWEEK(v.fecha_venta, 1)";
                $dateFormat = "DATE(DATE_ADD(v.fecha_venta, INTERVAL(-WEEKDAY(v.fecha_venta)) DAY)) as fecha";
                break;
            case 'mes':
                $groupByClause = "YEAR(v.fecha_venta), MONTH(v.fecha_venta)";
                $dateFormat = "DATE_FORMAT(v.fecha_venta, '%Y-%m-01') as fecha";
                break;
            case 'anio':
                $groupByClause = "YEAR(v.fecha_venta)";
                $dateFormat = "DATE_FORMAT(v.fecha_venta, '%Y-01-01') as fecha";
                break;
        }

        // Construir la consulta SQL para obtener los datos
        $query = "
            SELECT 
                $dateFormat,
                AVG(v.indice_pinta) as promedio,
                MIN(v.indice_pinta) as minimo,
                MAX(v.indice_pinta) as maximo,
                SUM(v.indice_pinta) as total,
                COUNT(v.indice_pinta) as cantidad
            FROM (
                SELECT v1.*
                FROM ventas AS v1
                INNER JOIN (
                    SELECT nro_venta, MAX(id) as idMax
                    FROM ventas
                    GROUP BY nro_venta
                ) AS max
                ON v1.id = max.idMax
            ) AS v
            WHERE 
                v.fecha_venta BETWEEN ? AND ?
                AND v.estado_venta != 6
                AND v.indice_pinta IS NOT NULL
            GROUP BY $groupByClause
            ORDER BY fecha ASC
        ";

        try {
            // Ejecutar la consulta
            $datos = DB::select($query, [$fechaInicio, $fechaFin]);

            // Preparar los datos para el gráfico
            $labels = [];
            $valores = [];
            $promedios = [];

            foreach ($datos as $dato) {
                $fecha = Carbon::parse($dato->fecha)->format('d/m/Y');
                
                $labels[] = $fecha;
                $valores[] = $dato->total;
                $promedios[] = $dato->promedio;
            }

            // Devolver la respuesta en formato JSON
            return response()->json([
                'status' => 'success',
                'periodo' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'agrupacion' => $agrupacion
                ],
                'datos' => $datos,
                'grafico' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Índice Pinta (Total)',
                            'data' => $valores,
                            'borderColor' => '#3B82F6',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'fill' => true
                        ],
                        [
                            'label' => 'Índice Pinta (Promedio)',
                            'data' => $promedios,
                            'borderColor' => '#10B981',
                            'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                            'fill' => true
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error al generar gráfico de índice pinta: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar los datos del gráfico',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos comparativos del índice pinta entre dos períodos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComparativoIndicePinta(Request $request)
    {
        // Validar parámetros de la solicitud
        $request->validate([
            'periodo1_inicio' => 'required|date',
            'periodo1_fin' => 'required|date|after_or_equal:periodo1_inicio',
            'periodo2_inicio' => 'required|date',
            'periodo2_fin' => 'required|date|after_or_equal:periodo2_inicio',
            'agrupacion' => 'required|in:dia,semana,mes',
        ]);

        $periodo1Inicio = $request->periodo1_inicio;
        $periodo1Fin = $request->periodo1_fin;
        $periodo2Inicio = $request->periodo2_inicio;
        $periodo2Fin = $request->periodo2_fin;
        $agrupacion = $request->agrupacion;

        // Registrar la consulta en logs
        // Log::info(Auth::user()->name . " | consulta API COMPARATIVO INDICE PINTA periodos: ($periodo1Inicio - $periodo1Fin) vs ($periodo2Inicio - $periodo2Fin) | ");

        // Obtener datos para cada período usando una función auxiliar
        $datosPeriodo1 = $this->obtenerDatosPeriodo($periodo1Inicio, $periodo1Fin, $agrupacion);
        $datosPeriodo2 = $this->obtenerDatosPeriodo($periodo2Inicio, $periodo2Fin, $agrupacion);

        // Calcular métricas comparativas
        $variacionPromedio = 0;
        $variacionTotal = 0;

        if (!empty($datosPeriodo1['total']) && !empty($datosPeriodo2['total'])) {
            $variacionTotal = (($datosPeriodo2['total'] - $datosPeriodo1['total']) / $datosPeriodo1['total']) * 100;
        }
        
        if (!empty($datosPeriodo1['promedio']) && !empty($datosPeriodo2['promedio'])) {
            $variacionPromedio = (($datosPeriodo2['promedio'] - $datosPeriodo1['promedio']) / $datosPeriodo1['promedio']) * 100;
        }

        return response()->json([
            'status' => 'success',
            'periodo1' => [
                'fecha_inicio' => $periodo1Inicio,
                'fecha_fin' => $periodo1Fin,
                'total' => $datosPeriodo1['total'],
                'promedio' => $datosPeriodo1['promedio'],
                'maximo' => $datosPeriodo1['maximo'],
                'minimo' => $datosPeriodo1['minimo']
            ],
            'periodo2' => [
                'fecha_inicio' => $periodo2Inicio,
                'fecha_fin' => $periodo2Fin,
                'total' => $datosPeriodo2['total'],
                'promedio' => $datosPeriodo2['promedio'],
                'maximo' => $datosPeriodo2['maximo'],
                'minimo' => $datosPeriodo2['minimo']
            ],
            'comparacion' => [
                'variacion_total' => round($variacionTotal, 2),
                'variacion_promedio' => round($variacionPromedio, 2)
            ],
            'grafico' => [
                'labels' => ['Total', 'Promedio', 'Mínimo', 'Máximo'],
                'datasets' => [
                    [
                        'label' => "Período 1 ({$periodo1Inicio} - {$periodo1Fin})",
                        'data' => [
                            $datosPeriodo1['total'],
                            $datosPeriodo1['promedio'],
                            $datosPeriodo1['minimo'],
                            $datosPeriodo1['maximo']
                        ],
                        'backgroundColor' => 'rgba(59, 130, 246, 0.7)'
                    ],
                    [
                        'label' => "Período 2 ({$periodo2Inicio} - {$periodo2Fin})",
                        'data' => [
                            $datosPeriodo2['total'],
                            $datosPeriodo2['promedio'],
                            $datosPeriodo2['minimo'],
                            $datosPeriodo2['maximo']
                        ],
                        'backgroundColor' => 'rgba(16, 185, 129, 0.7)'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Función auxiliar para obtener datos de un período específico
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param string $agrupacion
     * @return array
     */
    private function obtenerDatosPeriodo($fechaInicio, $fechaFin, $agrupacion)
    {
        // Construir la consulta para obtener datos resumidos del período
        $query = "
            SELECT 
                AVG(v.indice_pinta) as promedio,
                MIN(v.indice_pinta) as minimo,
                MAX(v.indice_pinta) as maximo,
                SUM(v.indice_pinta) as total,
                COUNT(v.indice_pinta) as cantidad
            FROM (
                SELECT v1.*
                FROM ventas AS v1
                INNER JOIN (
                    SELECT nro_venta, MAX(id) as idMax
                    FROM ventas
                    GROUP BY nro_venta
                ) AS max
                ON v1.id = max.idMax
            ) AS v
            WHERE 
                v.fecha_venta BETWEEN ? AND ?
                AND v.estado_venta != 6
                AND v.indice_pinta IS NOT NULL
        ";

        $resultados = DB::select($query, [$fechaInicio, $fechaFin]);

        if (empty($resultados)) {
            return [
                'promedio' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'total' => 0,
                'cantidad' => 0
            ];
        }

        return [
            'promedio' => (float) $resultados[0]->promedio,
            'minimo' => (float) $resultados[0]->minimo,
            'maximo' => (float) $resultados[0]->maximo,
            'total' => (float) $resultados[0]->total,
            'cantidad' => (int) $resultados[0]->cantidad
        ];
    }
}
