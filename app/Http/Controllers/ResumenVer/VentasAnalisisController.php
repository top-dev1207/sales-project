<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use App\Models\Climas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentasAnalisisController extends Controller
{
    /**
     * Obtiene datos de ventas por local, periodo y clima
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVentasPorLocalPeriodoClima(Request $request)
    {
        // Validar parámetros de entrada
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'local' => 'sometimes|integer', // IDs de los locales (caja=1 para Temple 1, caja=2 para Temple 2)
            'tipo_periodo' => 'required|in:diario,semanal,mensual',
            'clima' => 'sometimes|integer', // ID del clima
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $tipoPeriodo = $request->tipo_periodo;
        $local = $request->local;
        $clima = $request->clima;

        // Obtener solo los últimos registros de cada venta (no los históricos)
        $ventasQuery = Ventas::join(
            DB::raw('(SELECT nro_venta, MAX(id) as idMax FROM ventas GROUP BY nro_venta) as max'),
            function ($join) {
                $join->on('ventas.nro_venta', '=', 'max.nro_venta')
                    ->on('ventas.id', '=', 'max.idMax');
            }
        )
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado_venta', '!=', 6); // No incluir ventas borradas

        // Filtrar por local si se especifica
        if ($local) {
            $ventasQuery->where('caja', $local);
        }

        // Filtrar por clima si se especifica
        if ($clima) {
            $ventasQuery->where('clima', $clima);
        }

        // Agrupar por periodo según lo solicitado
        switch ($tipoPeriodo) {
            case 'diario':
                $ventasQuery->select(
                    DB::raw('DATE(fecha_venta) as periodo'),
                    'caja',
                    'clima',
                    DB::raw('SUM(ingresos) as total_ventas'),
                    DB::raw('SUM(venta_alimentos) as ventas_alimentos'),
                    DB::raw('SUM(venta_bebidas) as ventas_bebidas'),
                    DB::raw('COUNT(*) as cantidad_ventas')
                )
                    ->groupBy(DB::raw('DATE(fecha_venta)'), 'caja', 'clima');
                break;

            case 'semanal':
                $ventasQuery->select(
                    DB::raw('YEARWEEK(fecha_venta) as periodo'),
                    DB::raw('MIN(DATE(fecha_venta)) as fecha_inicio_semana'),
                    DB::raw('MAX(DATE(fecha_venta)) as fecha_fin_semana'),
                    'caja',
                    'clima',
                    DB::raw('SUM(ingresos) as total_ventas'),
                    DB::raw('SUM(venta_alimentos) as ventas_alimentos'),
                    DB::raw('SUM(venta_bebidas) as ventas_bebidas'),
                    DB::raw('COUNT(*) as cantidad_ventas')
                )
                    ->groupBy(DB::raw('YEARWEEK(fecha_venta)'), 'caja', 'clima');
                break;

            case 'mensual':
                $ventasQuery->select(
                    DB::raw('CONCAT(YEAR(fecha_venta), "-", LPAD(MONTH(fecha_venta), 2, "0")) as periodo'),
                    DB::raw('YEAR(fecha_venta) as anio'),
                    DB::raw('MONTH(fecha_venta) as mes'),
                    'caja',
                    'clima',
                    DB::raw('SUM(ingresos) as total_ventas'),
                    DB::raw('SUM(venta_alimentos) as ventas_alimentos'),
                    DB::raw('SUM(venta_bebidas) as ventas_bebidas'),
                    DB::raw('COUNT(*) as cantidad_ventas')
                )
                    ->groupBy(DB::raw('YEAR(fecha_venta)'), DB::raw('MONTH(fecha_venta)'), 'caja', 'clima');
                break;
        }

        $ventas = $ventasQuery->orderBy('periodo')->get();

        // Obtener los nombres de los climas para mejor lectura
        $climas = Climas::pluck('tipo', 'valor')->toArray();

        // Formatear los resultados para el gráfico
        $resultados = $ventas->map(function ($venta) use ($climas, $tipoPeriodo) {
            $nombreClima = isset($climas[$venta->clima]) ? $climas[$venta->clima] : 'Desconocido';
            $nombreLocal = $venta->caja == 1 ? 'Temple 1' : ($venta->caja == 2 ? 'Temple 2' : 'Otro');

            $item = [
                'local' => $nombreLocal,
                'clima' => $nombreClima,
                'total_ventas' => $venta->total_ventas,
                'ventas_alimentos' => $venta->ventas_alimentos,
                'ventas_bebidas' => $venta->ventas_bebidas,
                'cantidad_ventas' => $venta->cantidad_ventas,
            ];

            // Formatear el periodo según el tipo
            if ($tipoPeriodo === 'diario') {
                $item['periodo'] = $venta->periodo;
            } elseif ($tipoPeriodo === 'semanal') {
                $item['periodo'] = 'Semana ' . $venta->periodo;
                $item['fecha_inicio'] = $venta->fecha_inicio_semana;
                $item['fecha_fin'] = $venta->fecha_fin_semana;
            } elseif ($tipoPeriodo === 'mensual') {
                $fecha = Carbon::createFromDate($venta->anio, $venta->mes, 1);
                $item['periodo'] = $fecha->format('Y-m');
                $item['mes_nombre'] = $fecha->locale('es')->monthName;
                $item['anio'] = $venta->anio;
            }

            return $item;
        });

        // Preparar los datos para diferentes tipos de gráficos
        $labels = $resultados->pluck('periodo')->unique()->values();

        $datasets = [];

        // Si no se filtra por local, agrupamos por local
        if (!$local) {
            $porLocal = $resultados->groupBy('local');
            foreach ($porLocal as $nombreLocal => $datos) {
                $datasets[] = [
                    'label' => $nombreLocal,
                    'data' => $this->obtenerDatosPorPeriodo($datos, $labels, 'total_ventas'),
                    // Aquí se pueden agregar más propiedades del gráfico como colores
                ];
            }
        }
        // Si no se filtra por clima, agrupamos por clima
        else if (!$clima) {
            $porClima = $resultados->groupBy('clima');
            foreach ($porClima as $nombreClima => $datos) {
                $datasets[] = [
                    'label' => $nombreClima,
                    'data' => $this->obtenerDatosPorPeriodo($datos, $labels, 'total_ventas'),
                ];
            }
        }
        // Si se filtra por local y clima, mostramos ventas alimentos vs bebidas
        else {
            $datasets[] = [
                'label' => 'Alimentos',
                'data' => $this->obtenerDatosPorPeriodo($resultados, $labels, 'ventas_alimentos'),
            ];
            $datasets[] = [
                'label' => 'Bebidas',
                'data' => $this->obtenerDatosPorPeriodo($resultados, $labels, 'ventas_bebidas'),
            ];
        }

        return response()->json([
            'status' => 'success',
            'parametros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tipo_periodo' => $tipoPeriodo,
                'local' => $local ? ($local == 1 ? 'Temple 1' : 'Temple 2') : 'Todos',
                'clima' => $clima ? ($climas[$clima] ?? 'Desconocido') : 'Todos',
            ],
            'datos_grafico' => [
                'labels' => $labels,
                'datasets' => $datasets
            ],
            'datos_completos' => $resultados
        ]);
    }

    /**
     * Obtiene datos por periodo para un valor específico
     */
    private function obtenerDatosPorPeriodo($datos, $periodos, $campoDatos)
    {
        $result = [];
        foreach ($periodos as $periodo) {
            $valorPeriodo = $datos->where('periodo', $periodo)->sum($campoDatos);
            $result[] = $valorPeriodo;
        }
        return $result;
    }

    /**
     * Obtiene lista de climas disponibles
     */
    public function getClimas()
    {
        $climas = Climas::select('id', 'valor', 'tipo')->get();
        return response()->json([
            'status' => 'success',
            'data' => $climas
        ]);
    }

    /**
     * Obtiene datos de clima vs ventas para análisis de correlación
     */
    public function getCorrelacionClimaVentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        // Análisis por clima
        $ventasPorClima = Ventas::join(
            DB::raw('(SELECT nro_venta, MAX(id) as idMax FROM ventas GROUP BY nro_venta) as max'),
            function ($join) {
                $join->on('ventas.nro_venta', '=', 'max.nro_venta')
                    ->on('ventas.id', '=', 'max.idMax');
            }
        )
            ->join('climas', 'ventas.clima', '=', 'climas.valor')
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado_venta', '!=', 6)
            ->select(
                'climas.tipo as clima',
                DB::raw('COUNT(DISTINCT ventas.fecha_venta) as dias'),
                DB::raw('SUM(ventas.ingresos) as total_ventas'),
                DB::raw('AVG(ventas.ingresos) as promedio_ventas_por_dia'),
                DB::raw('SUM(ventas.venta_alimentos) as ventas_alimentos'),
                DB::raw('SUM(ventas.venta_bebidas) as ventas_bebidas')
            )
            ->groupBy('climas.tipo')
            ->orderBy('total_ventas', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
            'data' => $ventasPorClima
        ]);
    }
}