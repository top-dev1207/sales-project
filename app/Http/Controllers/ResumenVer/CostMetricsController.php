<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use App\Models\Facturas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CostMetricsController extends Controller
{
    /**
     * Get cost metrics with specified temporality
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCostMetrics(Request $request)
    {
        try {
            // Validate request parameters
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'temporality' => 'required|in:weekly,monthly',
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $temporality = $request->temporality;

            // Get sales and cost data based on temporality
            if ($temporality == 'weekly') {
                $results = $this->getWeeklyCostMetrics($startDate, $endDate);
            } else {
                $results = $this->getMonthlyCostMetrics($startDate, $endDate);
            }

            return response()->json([
                'status' => 'success',
                'temporality' => $temporality,
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d')
                ],
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error calculating cost metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly cost metrics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getWeeklyCostMetrics(Carbon $startDate, Carbon $endDate)
    {
        $results = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $weekStart = $currentDate->copy()->startOfWeek();
            $weekEnd = $currentDate->copy()->endOfWeek();

            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            // Calculate metrics for this week
            $weeklyData = $this->calculateMetricsForPeriod($weekStart, $weekEnd);
            
            $results[] = [
                'period' => 'Week ' . $weekStart->weekOfYear . ' (' . $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d') . ')',
                'start_date' => $weekStart->format('Y-m-d'),
                'end_date' => $weekEnd->format('Y-m-d'),
                'food_cost' => $weeklyData['food_cost'],
                'beverage_cost' => $weeklyData['beverage_cost'],
                'mix_cost' => $weeklyData['mix_cost']
            ];

            $currentDate->addWeek();
        }

        return $results;
    }

    /**
     * Get monthly cost metrics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getMonthlyCostMetrics(Carbon $startDate, Carbon $endDate)
    {
        $results = [];
        $currentDate = $startDate->copy()->startOfMonth();

        while ($currentDate->lte($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();

            if ($monthEnd->gt($endDate)) {
                $monthEnd = $endDate->copy();
            }

            // Calculate metrics for this month
            $monthlyData = $this->calculateMetricsForPeriod($monthStart, $monthEnd);
            
            $results[] = [
                'period' => $monthStart->format('F Y'),
                'start_date' => $monthStart->format('Y-m-d'),
                'end_date' => $monthEnd->format('Y-m-d'),
                'food_cost' => $monthlyData['food_cost'],
                'beverage_cost' => $monthlyData['beverage_cost'],
                'mix_cost' => $monthlyData['mix_cost']
            ];

            $currentDate->addMonth();
        }

        return $results;
    }

    /**
     * Calculate cost metrics for a specific period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function calculateMetricsForPeriod(Carbon $startDate, Carbon $endDate)
    {
        // Get sales data for the period
        $sales = $this->getSalesData($startDate, $endDate);
        
        // Get cost data for the period
        $costs = $this->getCostsData($startDate, $endDate);
        
        // Calculate food cost percentage
        $foodCost = 0;
        if ($sales['food_sales'] > 0) {
            $foodCost = ($costs['food_costs'] / $sales['food_sales']) * 100;
        }
        
        // Calculate beverage cost percentage
        $beverageCost = 0;
        if ($sales['beverage_sales'] > 0) {
            $beverageCost = ($costs['beverage_costs'] / $sales['beverage_sales']) * 100;
        }
        
        // Calculate mix cost percentage
        $mixCost = 0;
        if ($sales['total_sales'] > 0) {
            $mixCost = (($costs['food_costs'] + $costs['beverage_costs']) / $sales['total_sales']) * 100;
        }
        
        return [
            'food_cost' => round($foodCost, 2),
            'beverage_cost' => round($beverageCost, 2),
            'mix_cost' => round($mixCost, 2),
            'raw_data' => [
                'sales' => $sales,
                'costs' => $costs
            ]
        ];
    }

    /**
     * Get sales data for the specified period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getSalesData(Carbon $startDate, Carbon $endDate)
    {
        $latestSalesPerDocument = Ventas::select('nro_venta', DB::raw('MAX(id) as idMax'))
            ->groupBy('nro_venta');
            
        $salesData = Ventas::joinSub($latestSalesPerDocument, 'latest_sales', function ($join) {
                $join->on('ventas.nro_venta', '=', 'latest_sales.nro_venta')
                     ->on('ventas.id', '=', 'latest_sales.idMax');
            })
            ->whereBetween('fecha_venta', [$startDate, $endDate])
            ->where('estado_venta', '!=', 6) // Not deleted
            ->select(
                DB::raw('SUM(venta_alimentos) as food_sales'),
                DB::raw('SUM(venta_bebidas) as beverage_sales'),
                DB::raw('SUM(ingresos) as total_sales')
            )
            ->first();
            
        return [
            'food_sales' => $salesData->food_sales ?? 0,
            'beverage_sales' => $salesData->beverage_sales ?? 0,
            'total_sales' => $salesData->total_sales ?? 0
        ];
    }

    /**
     * Get cost data for the specified period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getCostsData(Carbon $startDate, Carbon $endDate)
    {
        $latestCostsPerDocument = Facturas::select('nro_documento', DB::raw('MAX(id) as idMax'))
            ->groupBy('nro_documento');
            
        $costsData = Facturas::joinSub($latestCostsPerDocument, 'latest_costs', function ($join) {
                $join->on('facturas.nro_documento', '=', 'latest_costs.nro_documento')
                     ->on('facturas.id', '=', 'latest_costs.idMax');
            })
            ->whereBetween('fecha_limite', [$startDate, $endDate])
            ->where('estadoDocumento', '!=', 6) // Not deleted
            ->select(
                DB::raw('SUM(CASE WHEN rubro = 1 THEN total ELSE 0 END) as food_costs'),
                DB::raw('SUM(CASE WHEN rubro = 2 THEN total ELSE 0 END) as beverage_costs'),
                DB::raw('SUM(total) as total_costs')
            )
            ->first();
            
        return [
            'food_costs' => $costsData->food_costs ?? 0,
            'beverage_costs' => $costsData->beverage_costs ?? 0,
            'total_costs' => $costsData->total_costs ?? 0
        ];
    }

    /**
     * Get detailed cost metrics by day
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailedCostMetrics(Request $request)
    {
        try {
            // Validate request parameters
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'metric_type' => 'required|in:food,beverage,mix'
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $metricType = $request->metric_type;

            // Get daily data for selected metric
            $dailyData = $this->getDailyCostMetrics($startDate, $endDate, $metricType);

            return response()->json([
                'status' => 'success',
                'metric_type' => $metricType,
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d')
                ],
                'data' => $dailyData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error calculating detailed cost metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily cost metrics for the specified period and metric type
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $metricType
     * @return array
     */
    private function getDailyCostMetrics(Carbon $startDate, Carbon $endDate, string $metricType)
    {
        $results = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dailyData = $this->calculateMetricsForPeriod($currentDate, $currentDate->copy()->endOfDay());
            
            $metricValue = 0;
            switch ($metricType) {
                case 'food':
                    $metricValue = $dailyData['food_cost'];
                    break;
                case 'beverage':
                    $metricValue = $dailyData['beverage_cost'];
                    break;
                case 'mix':
                    $metricValue = $dailyData['mix_cost'];
                    break;
            }
            
            $results[] = [
                'date' => $currentDate->format('Y-m-d'),
                'value' => $metricValue,
                'day_name' => $currentDate->format('l'),
                'raw_data' => $dailyData['raw_data']
            ];

            $currentDate->addDay();
        }

        return $results;
    }
}