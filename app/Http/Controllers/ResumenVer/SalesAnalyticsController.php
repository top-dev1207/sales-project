<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use App\Models\FormaPago;
use App\Models\ObjetivosVentas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SalesAnalyticsController extends Controller
{
    /**
     * Get sales data aggregated by day
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailySales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Log::info(Auth::user()->name . " | consulta API DAILY SALES desde $startDate hasta $endDate | ");

        $results = $this->getSalesByPeriod($startDate, $endDate, 'day');
        
        return response()->json([
            'status' => 'success',
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'granularity' => 'daily'
            ],
            'data' => $results
        ]);
    }

    /**
     * Get sales data aggregated by week, showing date range for each week
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function weeklySales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subWeeks(12)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Log::info(Auth::user()->name . " | consulta API WEEKLY SALES desde $startDate hasta $endDate | ");

        $results = $this->getSalesByPeriod($startDate, $endDate, 'week');
        
        // Enriquecer los resultados con los rangos de fecha para cada semana
        foreach ($results as &$weekData) {
            // La semana viene en formato 'YYYY-WXX'
            preg_match('/(\d{4})-W(\d{1,2})/', $weekData->period, $matches);
            if (count($matches) === 3) {
                $year = $matches[1];
                $weekNumber = $matches[2];
                
                // Calcular el primer día de la semana
                $firstDayOfWeek = Carbon::create($year)->setISODate($year, $weekNumber);
                // Calcular el último día de la semana
                $lastDayOfWeek = Carbon::create($year)->setISODate($year, $weekNumber, 7);
                
                $weekData->date_range = [
                    'from' => $firstDayOfWeek->format('Y-m-d'),
                    'to' => $lastDayOfWeek->format('Y-m-d'),
                    'formatted' => $firstDayOfWeek->format('d/m/Y') . ' - ' . $lastDayOfWeek->format('d/m/Y')
                ];
            }
        }
        
        return response()->json([
            'status' => 'success',
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'granularity' => 'weekly'
            ],
            'data' => $results
        ]);
    }

    /**
     * Get sales data aggregated by month
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlySales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(12)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Log::info(Auth::user()->name . " | consulta API MONTHLY SALES desde $startDate hasta $endDate | ");

        $results = $this->getSalesByPeriod($startDate, $endDate, 'month');
        
        // Enriquecer los resultados con los rangos de fecha para cada mes
        foreach ($results as &$monthData) {
            // El mes viene en formato 'YYYY-MM'
            $dateParts = explode('-', $monthData->period);
            if (count($dateParts) === 2) {
                $year = $dateParts[0];
                $month = $dateParts[1];
                
                $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
                $lastDayOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
                
                $monthData->date_range = [
                    'from' => $firstDayOfMonth->format('Y-m-d'),
                    'to' => $lastDayOfMonth->format('Y-m-d'),
                    'formatted' => $firstDayOfMonth->format('F Y') // Nombre del mes y año
                ];
            }
        }
        
        return response()->json([
            'status' => 'success',
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'granularity' => 'monthly'
            ],
            'data' => $results
        ]);
    }

    /**
     * Get sales data with comparison to previous period
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesComparison(Request $request)
    {
        $period = $request->input('period', 'month');
        $count = $request->input('count', 12);
        
        $endDate = Carbon::now();
        $startDate = clone $endDate;
        
        switch ($period) {
            case 'day':
                $startDate->subDays($count);
                $previousStartDate = (clone $startDate)->subDays($count);
                $previousEndDate = (clone $endDate)->subDays($count);
                break;
            case 'week':
                $startDate->subWeeks($count);
                $previousStartDate = (clone $startDate)->subWeeks($count);
                $previousEndDate = (clone $endDate)->subWeeks($count);
                break;
            default: // month
                $startDate->subMonths($count);
                $previousStartDate = (clone $startDate)->subMonths($count);
                $previousEndDate = (clone $endDate)->subMonths($count);
                break;
        }
        
        $currentPeriodData = $this->getSalesByPeriod($startDate->format('Y-m-d'), $endDate->format('Y-m-d'), $period);
        $previousPeriodData = $this->getSalesByPeriod($previousStartDate->format('Y-m-d'), $previousEndDate->format('Y-m-d'), $period);
        
        // Agregar rangos de fecha para periodos semanales y mensuales
        if ($period === 'week' || $period === 'month') {
            $this->addDateRangesToPeriodData($currentPeriodData, $period);
            $this->addDateRangesToPeriodData($previousPeriodData, $period);
        }
        
        // Calcular totales y crecimiento
        $currentTotal = collect($currentPeriodData)->sum('total');
        $previousTotal = collect($previousPeriodData)->sum('total');
        $growth = $previousTotal > 0 ? (($currentTotal - $previousTotal) / $previousTotal) * 100 : 0;
        
        return response()->json([
            'status' => 'success',
            'current_period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'granularity' => $period,
                'total' => $currentTotal
            ],
            'previous_period' => [
                'start_date' => $previousStartDate->format('Y-m-d'),
                'end_date' => $previousEndDate->format('Y-m-d'),
                'granularity' => $period,
                'total' => $previousTotal
            ],
            'growth_percentage' => round($growth, 2),
            'current_data' => $currentPeriodData,
            'previous_data' => $previousPeriodData,
        ]);
    }
    
    /**
     * Get sales data by product categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesByCategory(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(1)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $foodSales = $this->getSalesByCategoryPeriod($startDate, $endDate, 'venta_alimentos');
        $beverageSales = $this->getSalesByCategoryPeriod($startDate, $endDate, 'venta_bebidas');
        
        return response()->json([
            'status' => 'success',
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'categories' => [
                'food' => [
                    'total' => collect($foodSales)->sum('total'),
                    'data' => $foodSales
                ],
                'beverages' => [
                    'total' => collect($beverageSales)->sum('total'),
                    'data' => $beverageSales
                ]
            ]
        ]);
    }

    /**
     * Get sales targets vs. actual sales
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesTargets(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', null);
        
        $query = DB::table('objetivos_ventas')
            ->where('year', $year);
            
        if ($month) {
            $query->where('month', $month);
        }
        
        $targets = $query->get();
        
        $result = [];
        
        foreach ($targets as $target) {
            $startDate = Carbon::create($target->year, $target->month, 1)->startOfMonth();
            $endDate = Carbon::create($target->year, $target->month, 1)->endOfMonth();
            
            $actual = $this->getTotalSales($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
            
            $result[] = [
                'year' => $target->year,
                'month' => $target->month,
                'month_name' => $startDate->format('F'),
                'target' => $target->monto,
                'actual' => $actual,
                'difference' => $actual - $target->monto,
                'achievement_percentage' => $target->monto > 0 ? round(($actual / $target->monto) * 100, 2) : 0,
                'description' => $target->descripcion,
                'date_range' => [
                    'from' => $startDate->format('Y-m-d'),
                    'to' => $endDate->format('Y-m-d'),
                    'formatted' => $startDate->format('F Y')
                ]
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'year' => $year,
            'month' => $month,
            'data' => $result
        ]);
    }

    /**
     * Get real-time dashboard summary data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        // Today's sales
        $today = Carbon::now()->format('Y-m-d');
        $todaySales = $this->getTotalSales($today, $today);
        
        // Yesterday sales for comparison
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');
        $yesterdaySales = $this->getTotalSales($yesterday, $yesterday);
        
        // This month's sales
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $monthlySales = $this->getTotalSales($startOfMonth, $endOfMonth);
        
        // Get monthly target from ObjetivoVentas model
        $target = ObjetivosVentas::where('year', Carbon::now()->year)
            ->where('month', Carbon::now()->month)
            ->first();
            
        $monthlyTarget = $target ? $target->monto : 0;
        
        // Monthly progress
        $monthProgress = $monthlyTarget > 0 ? round(($monthlySales / $monthlyTarget) * 100, 2) : 0;
        
        // Get recent sales data for chart
        $recentSales = $this->getSalesByPeriod(
            Carbon::now()->subDays(14)->format('Y-m-d'),
            Carbon::now()->format('Y-m-d'),
            'day'
        );
        
        // Payment method distribution
        $paymentMethods = $this->getPaymentMethodDistribution($startOfMonth, $endOfMonth);
        
        return response()->json([
            'status' => 'success',
            'current_date' => Carbon::now()->format('Y-m-d'),
            'summary' => [
                'today_sales' => $todaySales,
                'yesterday_sales' => $yesterdaySales,
                'daily_change' => $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 2) : 0,
                'monthly_sales' => $monthlySales,
                'monthly_target' => $monthlyTarget,
                'month_progress' => $monthProgress,
                'days_left_in_month' => Carbon::now()->endOfMonth()->diffInDays(Carbon::now()),
                'month_range' => [
                    'from' => $startOfMonth,
                    'to' => $endOfMonth,
                    'formatted' => Carbon::now()->format('F Y')
                ]
            ],
            'recent_sales' => $recentSales,
            'payment_methods' => $paymentMethods
        ]);
    }

    /**
     * Add date ranges to weekly or monthly period data
     *
     * @param array $periodData
     * @param string $period
     * @return void
     */
    private function addDateRangesToPeriodData(&$periodData, $period)
    {
        foreach ($periodData as &$data) {
            if ($period === 'week') {
                preg_match('/(\d{4})-W(\d{1,2})/', $data->period, $matches);
                if (count($matches) === 3) {
                    $year = $matches[1];
                    $weekNumber = $matches[2];
                    
                    $firstDay = Carbon::create($year)->setISODate($year, $weekNumber);
                    $lastDay = Carbon::create($year)->setISODate($year, $weekNumber, 7);
                    
                    $data->date_range = [
                        'from' => $firstDay->format('Y-m-d'),
                        'to' => $lastDay->format('Y-m-d'),
                        'formatted' => $firstDay->format('d/m/Y') . ' - ' . $lastDay->format('d/m/Y')
                    ];
                }
            } elseif ($period === 'month') {
                $dateParts = explode('-', $data->period);
                if (count($dateParts) === 2) {
                    $year = $dateParts[0];
                    $month = $dateParts[1];
                    
                    $firstDay = Carbon::create($year, $month, 1)->startOfMonth();
                    $lastDay = Carbon::create($year, $month, 1)->endOfMonth();
                    
                    $data->date_range = [
                        'from' => $firstDay->format('Y-m-d'),
                        'to' => $lastDay->format('Y-m-d'),
                        'formatted' => $firstDay->format('F Y')
                    ];
                }
            }
        }
    }

    /**
     * Get sales data by period
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $period day|week|month
     * @return array
     */
    private function getSalesByPeriod($startDate, $endDate, $period = 'day')
    {
        $dateFormat = '%Y-%m-%d';
        $dateColumn = 'DATE(fecha_venta)';
        
        if ($period === 'week') {
            $dateFormat = '%x-W%v'; // ISO week format: 2023-W01
            $dateColumn = "CONCAT(YEAR(fecha_venta), '-W', LPAD(WEEK(fecha_venta, 3), 2, '0'))";
        } elseif ($period === 'month') {
            $dateFormat = '%Y-%m';
            $dateColumn = "DATE_FORMAT(fecha_venta, '%Y-%m')";
        }
        
        $sql = "
            SELECT 
                $dateColumn as period,
                SUM(ingresos) as total,
                SUM(ventas_fiscal) as ventas_fiscal,
                SUM(ventas_no_fiscal) as ventas_no_fiscal,
                SUM(venta_alimentos) as venta_alimentos,
                SUM(venta_bebidas) as venta_bebidas
            FROM (
                SELECT v1.*
                FROM ventas as v1
                INNER JOIN (
                    SELECT nro_venta, MAX(id) as idMax
                    FROM ventas
                    GROUP BY nro_venta
                ) as max
                ON v1.id = max.idMax
            ) as v
            WHERE v.fecha_venta BETWEEN ? AND ?
            AND v.estado_venta != 6
            GROUP BY $dateColumn
            ORDER BY period ASC
        ";
        
        $results = DB::select($sql, [$startDate, $endDate]);
        
        return $results;
    }
    
    /**
     * Get sales by category for a period
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $categoryColumn
     * @return array
     */
    private function getSalesByCategoryPeriod($startDate, $endDate, $categoryColumn)
    {
        $sql = "
            SELECT 
                DATE(fecha_venta) as period,
                SUM($categoryColumn) as total
            FROM (
                SELECT v1.*
                FROM ventas as v1
                INNER JOIN (
                    SELECT nro_venta, MAX(id) as idMax
                    FROM ventas
                    GROUP BY nro_venta
                ) as max
                ON v1.id = max.idMax
            ) as v
            WHERE v.fecha_venta BETWEEN ? AND ?
            AND v.estado_venta != 6
            GROUP BY DATE(fecha_venta)
            ORDER BY period ASC
        ";
        
        $results = DB::select($sql, [$startDate, $endDate]);
        
        // Añadir formato de fecha legible
        foreach ($results as &$data) {
            $date = Carbon::parse($data->period);
            $data->formatted_date = $date->format('d/m/Y');
        }
        
        return $results;
    }

    /**
     * Get total sales for a period
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    private function getTotalSales($startDate, $endDate)
    {
        $sql = "
            SELECT 
                SUM(ingresos) as total
            FROM (
                SELECT v1.*
                FROM ventas as v1
                INNER JOIN (
                    SELECT nro_venta, MAX(id) as idMax
                    FROM ventas
                    GROUP BY nro_venta
                ) as max
                ON v1.id = max.idMax
            ) as v
            WHERE v.fecha_venta BETWEEN ? AND ?
            AND v.estado_venta != 6
        ";
        
        $result = DB::selectOne($sql, [$startDate, $endDate]);
        
        return $result ? $result->total : 0;
    }
    
    /**
     * Get payment method distribution
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getPaymentMethodDistribution($startDate, $endDate)
    {
        $paymentMethods = [];
        
        // Get all payment methods from FormaPago model
        $methods = FormaPago::where('estado', 1)->get();
        
        foreach ($methods as $method) {
            $column = "fp{$method->id}";
            
            $sql = "
                SELECT 
                    SUM($column) as total
                FROM (
                    SELECT v1.*
                    FROM ventas as v1
                    INNER JOIN (
                        SELECT nro_venta, MAX(id) as idMax
                        FROM ventas
                        GROUP BY nro_venta
                    ) as max
                    ON v1.id = max.idMax
                ) as v
                WHERE v.fecha_venta BETWEEN ? AND ?
                AND v.estado_venta != 6
            ";
            
            $result = DB::selectOne($sql, [$startDate, $endDate]);
            $total = $result ? $result->total : 0;
            
            $paymentMethods[] = [
                'id' => $method->id,
                'name' => $method->tipo,
                'total' => $total
            ];
        }
        
        // Sort by total in descending order
        usort($paymentMethods, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        return $paymentMethods;
    }
}