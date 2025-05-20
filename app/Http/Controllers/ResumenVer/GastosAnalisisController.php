<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Facturas;
use App\Models\Ventas;
use App\Models\Rubro;
use Carbon\Carbon;

class GastosAnalisisController extends Controller
{
    /**
     * Obtiene el porcentaje de gastos por rubro sobre las ventas totales
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gastosSobreVentas(Request $request)
    {
        // Obtener fechas de consulta o usar valores predeterminados
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
        
        // Obtener ventas totales del período
        $ventasTotales = $this->obtenerVentasTotales($fechaInicio, $fechaFin);
        
        if ($ventasTotales <= 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'No hay datos de ventas para el período seleccionado.',
                'data' => []
            ]);
        }
        
        // Obtener gastos por rubro
        $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
        
        // Calcular porcentaje sobre ventas
        $data = [];
        foreach ($gastosPorRubro as $gasto) {
            $porcentajeVentas = ($gasto->importe / $ventasTotales) * 100;
            
            $data[] = [
                'rubro_id' => $gasto->rubro,
                'rubro_nombre' => $gasto->nombre,
                'importe' => round($gasto->importe, 2),
                'porcentaje_ventas' => round($porcentajeVentas, 2)
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'ventas_totales' => round($ventasTotales, 2),
            'data' => $data
        ]);
    }
    
    /**
     * Obtiene el porcentaje de gastos por rubro sobre el total de gastos
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gastosSobreTotalGastos(Request $request)
    {
        // Obtener fechas de consulta o usar valores predeterminados
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
        
        // Obtener gastos por rubro
        $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
        
        // Calcular gastos totales
        $gastosTotales = 0;
        foreach ($gastosPorRubro as $gasto) {
            $gastosTotales += $gasto->importe;
        }
        
        if ($gastosTotales <= 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'No hay datos de gastos para el período seleccionado.',
                'data' => []
            ]);
        }
        
        // Calcular porcentaje sobre total gastos
        $data = [];
        foreach ($gastosPorRubro as $gasto) {
            $porcentajeGastosTotales = ($gasto->importe / $gastosTotales) * 100;
            
            $data[] = [
                'rubro_id' => $gasto->rubro,
                'rubro_nombre' => $gasto->nombre,
                'importe' => round($gasto->importe, 2),
                'porcentaje_gastos_totales' => round($porcentajeGastosTotales, 2)
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'gastos_totales' => round($gastosTotales, 2),
            'data' => $data
        ]);
    }
    
    /**
     * Obtiene los rubros de gastos más relevantes en el período
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gastosMasRelevantes(Request $request)
    {
        // Obtener fechas de consulta o usar valores predeterminados
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
        $limit = $request->input('limit', 5);
        
        // Validar límite
        $limit = min(max(1, intval($limit)), 20);
        
        // Obtener gastos por rubro
        $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
        
        // Calcular gastos totales
        $gastosTotales = 0;
        foreach ($gastosPorRubro as $gasto) {
            $gastosTotales += $gasto->importe;
        }
        
        if ($gastosTotales <= 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'No hay datos de gastos para el período seleccionado.',
                'data' => []
            ]);
        }
        
        // Calcular porcentaje sobre total gastos y ordenar por importe
        $data = [];
        foreach ($gastosPorRubro as $gasto) {
            $porcentajeGastosTotales = ($gasto->importe / $gastosTotales) * 100;
            
            $data[] = [
                'rubro_id' => $gasto->rubro,
                'rubro_nombre' => $gasto->nombre,
                'importe' => round($gasto->importe, 2),
                'porcentaje_gastos_totales' => round($porcentajeGastosTotales, 2)
            ];
        }
        
        // Ordenar por importe (mayor a menor) y limitar cantidad
        usort($data, function($a, $b) {
            return $b['importe'] - $a['importe'];
        });
        
        $data = array_slice($data, 0, $limit);
        
        return response()->json([
            'status' => 'success',
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'gastos_totales' => round($gastosTotales, 2),
            'data' => $data
        ]);
    }
    
    /**
     * Endpoint para el dashboard de gastos que contiene todos los análisis
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardGastos(Request $request)
    {
        // Obtener fechas de consulta o usar valores predeterminados
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
        
        // Obtener ventas totales del período
        $ventasTotales = $this->obtenerVentasTotales($fechaInicio, $fechaFin);
        
        // Obtener gastos por rubro
        $gastosPorRubro = $this->obtenerGastosPorRubro($fechaInicio, $fechaFin);
        
        // Calcular gastos totales
        $gastosTotales = 0;
        foreach ($gastosPorRubro as $gasto) {
            $gastosTotales += $gasto->importe;
        }
        
        if ($gastosTotales <= 0 || $ventasTotales <= 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'No hay suficientes datos para el período seleccionado.',
                'data' => []
            ]);
        }
        
        // Preparar datos para gastos sobre ventas
        $gastosSobreVentas = [];
        $gastosSobreTotalGastos = [];
        $gastosRelevantes = [];
        
        foreach ($gastosPorRubro as $gasto) {
            $porcentajeVentas = ($gasto->importe / $ventasTotales) * 100;
            $porcentajeGastosTotales = ($gasto->importe / $gastosTotales) * 100;
            
            $item = [
                'rubro_id' => $gasto->rubro,
                'rubro_nombre' => $gasto->nombre,
                'importe' => round($gasto->importe, 2),
            ];
            
            $gastosSobreVentas[] = array_merge($item, [
                'porcentaje_ventas' => round($porcentajeVentas, 2)
            ]);
            
            $gastosSobreTotalGastos[] = array_merge($item, [
                'porcentaje_gastos_totales' => round($porcentajeGastosTotales, 2)
            ]);
            
            $gastosRelevantes[] = array_merge($item, [
                'porcentaje_gastos_totales' => round($porcentajeGastosTotales, 2)
            ]);
        }
        
        // Ordenar gastos relevantes por importe (mayor a menor) y limitar a 5
        usort($gastosRelevantes, function($a, $b) {
            return $b['importe'] - $a['importe'];
        });
        
        $gastosRelevantes = array_slice($gastosRelevantes, 0, 5);
        
        return response()->json([
            'status' => 'success',
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'ventas_totales' => round($ventasTotales, 2),
            'gastos_totales' => round($gastosTotales, 2),
            'data' => [
                'gastos_sobre_ventas' => $gastosSobreVentas,
                'gastos_sobre_total' => $gastosSobreTotalGastos,
                'gastos_relevantes' => $gastosRelevantes
            ]
        ]);
    }
    
    /**
     * Obtiene las ventas totales en el período consultado
     * 
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return float
     */
    private function obtenerVentasTotales($fechaInicio, $fechaFin)
    {
        $sql = "
            SELECT SUM(v.ingresos) as ventasTotales
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
            LEFT JOIN dshows AS s
            ON (v.dshowId = s.id AND v.nro_venta = s.idVenta) AND s.estado = 1
            WHERE v.fecha_venta BETWEEN ? AND ?
            AND v.estado_venta != 6
        ";
        
        $result = DB::select($sql, [$fechaInicio, $fechaFin]);
        
        return $result[0]->ventasTotales ?? 0;
    }
    
    /**
     * Obtiene los gastos por rubro en el período consultado
     * 
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    private function obtenerGastosPorRubro($fechaInicio, $fechaFin)
    {
        $sql = "
            SELECT r.nombre, f.rubro, SUM(f.total) as importe
            FROM (
                SELECT a.*
                FROM facturas AS a
                JOIN (
                    SELECT nro_documento, MAX(id) as idMax
                    FROM facturas
                    GROUP BY nro_documento
                ) AS sub
                WHERE a.id = sub.idMax
                AND a.nro_documento = sub.nro_documento
                AND a.estadoDocumento != 6
                AND a.fecha_limite BETWEEN ? AND ?
                AND a.rubro != 55 -- excluir inversiones
            ) AS f
            JOIN rubros AS r ON f.rubro = r.valor
            GROUP BY f.rubro, r.nombre
            ORDER BY r.orden ASC
        ";
        
        return DB::select($sql, [$fechaInicio, $fechaFin]);
    }
}