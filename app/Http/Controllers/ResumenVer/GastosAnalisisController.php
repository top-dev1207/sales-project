<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Facturas;
use App\Models\Rubro;
use App\Models\Ventas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GastosAnalisisController extends Controller
{
    /**
     * Obtener porcentaje de gastos sobre ventas totales
     * Incidencia de Gastos Rubro / Ventas totales (en el periodo consultado)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gastosSobreVentas(Request $request)
    {
        try {
            // Validar parámetros
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            // Definir fechas de inicio y fin
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : Carbon::now();
            $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : $fechaFin->copy()->subMonths(1);

            // Obtener total de ventas en el período
            $ventasTotales = $this->obtenerVentasTotales($fechaInicio, $fechaFin);
            
            // Si no hay ventas en el período, evitar división por cero
            if ($ventasTotales <= 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No hay ventas registradas en el período seleccionado',
                    'data' => []
                ]);
            }

            // Obtener gastos por rubro
            $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
            
            // Calcular porcentajes sobre ventas totales
            $resultados = [];
            foreach ($gastosPorRubro as $gasto) {
                $porcentajeVentas = ($gasto->importe / $ventasTotales) * 100;
                
                $resultados[] = [
                    'rubro_id' => $gasto->rubro,
                    'rubro_nombre' => $gasto->nombre_rubro,
                    'importe' => round($gasto->importe, 2),
                    'porcentaje_ventas' => round($porcentajeVentas, 2)
                ];
            }
            
            // Ordenar por porcentaje (mayor a menor)
            usort($resultados, function ($a, $b) {
                return $b['porcentaje_ventas'] <=> $a['porcentaje_ventas'];
            });

            return response()->json([
                'status' => 'success',
                'periodo' => [
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d')
                ],
                'ventas_totales' => round($ventasTotales, 2),
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error('Error en GastosAnalisisController@gastosSobreVentas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al calcular gastos sobre ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener porcentaje de gastos sobre total de gastos
     * Incidencia de Gastos Rubro / Gastos totales
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gastosSobreTotalGastos(Request $request)
    {
        try {
            // Validar parámetros
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            // Definir fechas de inicio y fin
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : Carbon::now();
            $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : $fechaFin->copy()->subMonths(1);

            // Obtener gastos por rubro
            $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
            
            // Calcular el total de gastos
            $gastosTotales = array_sum(array_column($gastosPorRubro, 'importe'));
            
            // Si no hay gastos en el período
            if ($gastosTotales <= 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No hay gastos registrados en el período seleccionado',
                    'data' => []
                ]);
            }

            // Calcular porcentajes sobre gastos totales
            $resultados = [];
            foreach ($gastosPorRubro as $gasto) {
                $porcentajeGastos = ($gasto->importe / $gastosTotales) * 100;
                
                $resultados[] = [
                    'rubro_id' => $gasto->rubro,
                    'rubro_nombre' => $gasto->nombre_rubro,
                    'importe' => round($gasto->importe, 2),
                    'porcentaje_gastos_totales' => round($porcentajeGastos, 2)
                ];
            }
            
            // Ordenar por porcentaje (mayor a menor)
            usort($resultados, function ($a, $b) {
                return $b['porcentaje_gastos_totales'] <=> $a['porcentaje_gastos_totales'];
            });

            return response()->json([
                'status' => 'success',
                'periodo' => [
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d')
                ],
                'gastos_totales' => round($gastosTotales, 2),
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error('Error en GastosAnalisisController@gastosSobreTotalGastos: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al calcular gastos sobre total de gastos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los gastos más relevantes (top 5)
     * Top 5 de rubros con más gastos realizados (en el periodo consultado)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gastosMasRelevantes(Request $request)
    {
        try {
            // Validar parámetros
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:20'
            ]);

            // Definir fechas de inicio y fin
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : Carbon::now();
            $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : $fechaFin->copy()->subMonths(1);
            
            // Cantidad de rubros a mostrar (por defecto 5)
            $limit = $request->limit ?? 5;

            // Obtener gastos por rubro ordenados de mayor a menor
            $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
            
            // Ordenar por importe (mayor a menor)
            usort($gastosPorRubro, function ($a, $b) {
                return $b->importe <=> $a->importe;
            });
            
            // Tomar solo los primeros N elementos
            $gastosPorRubro = array_slice($gastosPorRubro, 0, $limit);
            
            // Calcular total de gastos para los porcentajes
            $gastosTotales = $this->obtenerGastosTotales($fechaInicio, $fechaFin);
            
            // Formatear resultados
            $resultados = [];
            foreach ($gastosPorRubro as $gasto) {
                $porcentajeGastos = ($gasto->importe / $gastosTotales) * 100;
                
                $resultados[] = [
                    'rubro_id' => $gasto->rubro,
                    'rubro_nombre' => $gasto->nombre_rubro,
                    'importe' => round($gasto->importe, 2),
                    'porcentaje_gastos_totales' => round($porcentajeGastos, 2)
                ];
            }

            return response()->json([
                'status' => 'success',
                'periodo' => [
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d')
                ],
                'gastos_totales' => round($gastosTotales, 2),
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error('Error en GastosAnalisisController@gastosMasRelevantes: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener gastos más relevantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para el dashboard de gastos
     * Incluye todos los indicadores en una sola consulta
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardGastos(Request $request)
    {
        try {
            // Validar parámetros
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            // Definir fechas de inicio y fin
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : Carbon::now();
            $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : $fechaFin->copy()->subMonths(1);

            // Obtener todos los datos necesarios
            $ventasTotales = $this->obtenerVentasTotales($fechaInicio, $fechaFin);
            $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
            $gastosTotales = $this->obtenerGastosTotales($fechaInicio, $fechaFin);
            
            // Si no hay datos en el período
            if ($ventasTotales <= 0 || $gastosTotales <= 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No hay suficientes datos registrados en el período seleccionado',
                    'data' => [
                        'ventas_totales' => round($ventasTotales, 2),
                        'gastos_totales' => round($gastosTotales, 2),
                        'gastos_sobre_ventas' => [],
                        'gastos_sobre_total' => [],
                        'gastos_relevantes' => []
                    ]
                ]);
            }

            // Preparar datos para gastos sobre ventas
            $gastosSobreVentas = [];
            foreach ($gastosPorRubro as $gasto) {
                $porcentajeVentas = ($gasto->importe / $ventasTotales) * 100;
                
                $gastosSobreVentas[] = [
                    'rubro_id' => $gasto->rubro,
                    'rubro_nombre' => $gasto->nombre_rubro,
                    'importe' => round($gasto->importe, 2),
                    'porcentaje_ventas' => round($porcentajeVentas, 2)
                ];
            }
            
            // Ordenar por porcentaje sobre ventas (mayor a menor)
            usort($gastosSobreVentas, function ($a, $b) {
                return $b['porcentaje_ventas'] <=> $a['porcentaje_ventas'];
            });

            // Preparar datos para gastos sobre total de gastos
            $gastosSobreTotal = [];
            foreach ($gastosPorRubro as $gasto) {
                $porcentajeGastos = ($gasto->importe / $gastosTotales) * 100;
                
                $gastosSobreTotal[] = [
                    'rubro_id' => $gasto->rubro,
                    'rubro_nombre' => $gasto->nombre_rubro,
                    'importe' => round($gasto->importe, 2),
                    'porcentaje_gastos_totales' => round($porcentajeGastos, 2)
                ];
            }
            
            // Ordenar por porcentaje sobre total gastos (mayor a menor)
            usort($gastosSobreTotal, function ($a, $b) {
                return $b['porcentaje_gastos_totales'] <=> $a['porcentaje_gastos_totales'];
            });

            // Preparar datos para gastos más relevantes (top 5)
            $gastosRelevantes = array_slice($gastosSobreTotal, 0, 5);

            return response()->json([
                'status' => 'success',
                'periodo' => [
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d')
                ],
                'ventas_totales' => round($ventasTotales, 2),
                'gastos_totales' => round($gastosTotales, 2),
                'data' => [
                    'gastos_sobre_ventas' => $gastosSobreVentas,
                    'gastos_sobre_total' => $gastosSobreTotal,
                    'gastos_relevantes' => $gastosRelevantes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en GastosAnalisisController@dashboardGastos: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar dashboard de gastos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el total de ventas en un período
     * 
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return float
     */
    private function obtenerVentasTotales(Carbon $fechaInicio, Carbon $fechaFin)
    {
        return Ventas::join(
            DB::raw('(SELECT nro_venta, MAX(id) as idMax FROM ventas GROUP BY nro_venta) as ultimo_doc'),
            function ($join) {
                $join->on('ventas.nro_venta', '=', 'ultimo_doc.nro_venta');
                $join->on('ventas.id', '=', 'ultimo_doc.idMax');
            }
        )
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado_venta', '!=', 6) // No borrados
            ->sum('ingresos');
    }

    /**
     * Obtener gastos agrupados por rubro en un período
     * 
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return array
     */
    private function obtenerGastosPorRubro(Carbon $fechaInicio, Carbon $fechaFin)
    {
        return DB::table('facturas')
            ->join(
                DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as ultimo_doc'),
                function ($join) {
                    $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                    $join->on('facturas.id', '=', 'ultimo_doc.idMax');
                }
            )
            ->join('rubros', 'facturas.rubro', '=', 'rubros.valor')
            ->select(
                'facturas.rubro',
                'rubros.nombre as nombre_rubro',
                DB::raw('SUM(facturas.total) as importe')
            )
            ->whereBetween('facturas.fecha_limite', [$fechaInicio, $fechaFin])
            ->where('facturas.estadoDocumento', '!=', 6) // No borrados
            ->where('facturas.rubro', '!=', 55) // Excluir inversiones (según se ve en otros controladores)
            ->groupBy('facturas.rubro', 'rubros.nombre')
            ->get();
    }

    /**
     * Obtener el total de gastos en un período
     * 
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return float
     */
    private function obtenerGastosTotales(Carbon $fechaInicio, Carbon $fechaFin)
    {
        return Facturas::join(
            DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as ultimo_doc'),
            function ($join) {
                $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                $join->on('facturas.id', '=', 'ultimo_doc.idMax');
            }
        )
            ->whereBetween('fecha_limite', [$fechaInicio, $fechaFin])
            ->where('estadoDocumento', '!=', 6) // No borrados
            ->where('rubro', '!=', 55) // Excluir inversiones (según se ve en otros controladores)
            ->sum('total');
    }
} 