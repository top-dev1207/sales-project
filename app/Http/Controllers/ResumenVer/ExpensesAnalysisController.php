<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Facturas;
use App\Models\Rubro;
use Carbon\Carbon;

class ExpensesAnalysisController extends Controller
{
    /**
     * Get expenses grouped by percentage incidence for a given period
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExpensesByIncidence(Request $request)
    {
        // Validate request
        $request->validate([
            'period_type' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $periodType = $request->input('period_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $limit = $request->input('limit', 10);

        try {
            // Log the API request
            Log::info(Auth::check() ? Auth::user()->name : 'Guest' . " | API request for expenses by incidence: $periodType from $startDate to $endDate");

            // Get the expenses data based on period type
            $result = $this->getExpensesByPeriodType($periodType, $startDate, $endDate, $limit);

            return response()->json([
                'status' => 'success',
                'period' => [
                    'type' => $periodType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getExpensesByIncidence: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process expenses data based on period type
     *
     * @param string $periodType
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return array
     */
    private function getExpensesByPeriodType($periodType, $startDate, $endDate, $limit)
    {
        // SQL date grouping format based on period type
        $groupFormat = $this->getGroupFormat($periodType);
        
        // Get the expenses grouped by rubros and period
        $expensesQuery = $this->getExpensesBaseQuery($startDate, $endDate, $groupFormat);
        
        // Get total amounts by period for percentage calculation
        $totalsQuery = $this->getTotalsQuery($startDate, $endDate, $groupFormat);
        
        // Execute queries
        $expenses = $expensesQuery->get();
        $totals = $totalsQuery->get()->keyBy('period');
        
        // Process and calculate percentages
        $result = $this->processExpensesData($expenses, $totals, $limit);
        
        return $result;
    }

    /**
     * Get the date format for SQL grouping based on period type
     *
     * @param string $periodType
     * @return string
     */
    private function getGroupFormat($periodType)
    {
        switch ($periodType) {
            case 'daily':
                return "DATE_FORMAT(fecha_limite, '%Y-%m-%d')";
            case 'weekly':
                return "CONCAT(YEAR(fecha_limite), '-', WEEK(fecha_limite))";
            case 'monthly':
            default:
                return "DATE_FORMAT(fecha_limite, '%Y-%m')";
        }
    }

    /**
     * Get base query for expenses grouped by rubros and period
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $groupFormat
     * @return \Illuminate\Database\Query\Builder
     */
    private function getExpensesBaseQuery($startDate, $endDate, $groupFormat)
    {
        // This function is based on the existing code pattern in ResultadosController
        return DB::table('facturas as f')
            ->join(DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as sub'), 
                function($join) {
                    $join->on('f.nro_documento', '=', 'sub.nro_documento')
                        ->on('f.id', '=', 'sub.idMax');
                })
            ->join('rubros as r', 'f.rubro', '=', 'r.valor')
            ->select(
                DB::raw($groupFormat . ' as period'),
                'r.nombre as rubro_nombre',
                'f.rubro as rubro_id',
                'r.orden as orden',
                DB::raw('SUM(f.total) as importe')
            )
            ->whereBetween('f.fecha_limite', [$startDate, $endDate])
            ->where('f.estadoDocumento', '!=', 6) // Not deleted
            ->where('f.rubro', '!=', 55) // Exclude investments
            ->groupBy(DB::raw($groupFormat), 'f.rubro', 'r.nombre', 'r.orden')
            ->orderBy(DB::raw($groupFormat));
    }

    /**
     * Get query for total expenses by period
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $groupFormat
     * @return \Illuminate\Database\Query\Builder
     */
    private function getTotalsQuery($startDate, $endDate, $groupFormat)
    {
        return DB::table('facturas as f')
            ->join(DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as sub'), 
                function($join) {
                    $join->on('f.nro_documento', '=', 'sub.nro_documento')
                        ->on('f.id', '=', 'sub.idMax');
                })
            ->select(
                DB::raw($groupFormat . ' as period'),
                DB::raw('SUM(f.total) as total')
            )
            ->whereBetween('f.fecha_limite', [$startDate, $endDate])
            ->where('f.estadoDocumento', '!=', 6) // Not deleted
            ->where('f.rubro', '!=', 55) // Exclude investments
            ->groupBy(DB::raw($groupFormat));
    }

    /**
     * Process expenses data and calculate percentages
     *
     * @param \Illuminate\Support\Collection $expenses
     * @param \Illuminate\Support\Collection $totals
     * @param int $limit
     * @return array
     */
    private function processExpensesData($expenses, $totals, $limit)
    {
        $result = [];
        $periods = $expenses->pluck('period')->unique()->values();

        foreach ($periods as $period) {
            $periodExpenses = $expenses->where('period', $period);
            $totalAmount = $totals[$period]->total ?? 0;
            
            if ($totalAmount == 0) {
                continue;
            }

            $periodData = [];
            foreach ($periodExpenses as $expense) {
                $percentage = ($expense->importe / $totalAmount) * 100;
                
                $periodData[] = [
                    'rubro_id' => $expense->rubro_id,
                    'rubro_nombre' => $expense->rubro_nombre,
                    'importe' => round($expense->importe, 2),
                    'porcentaje' => round($percentage, 2),
                    'orden' => $expense->orden,
                ];
            }

            // Sort by percentage descending
            usort($periodData, function($a, $b) {
                return $b['porcentaje'] <=> $a['porcentaje'];
            });

            // Limit results if needed
            if (count($periodData) > $limit) {
                $othersAmount = 0;
                $othersPercentage = 0;
                
                $limitedData = array_slice($periodData, 0, $limit - 1);
                
                for ($i = $limit - 1; $i < count($periodData); $i++) {
                    $othersAmount += $periodData[$i]['importe'];
                    $othersPercentage += $periodData[$i]['porcentaje'];
                }
                
                if ($othersAmount > 0) {
                    $limitedData[] = [
                        'rubro_id' => 0,
                        'rubro_nombre' => 'Otros',
                        'importe' => round($othersAmount, 2),
                        'porcentaje' => round($othersPercentage, 2),
                        'orden' => 999,
                    ];
                }
                
                $periodData = $limitedData;
            }

            $result[] = [
                'period' => $this->formatPeriodDisplay($period),
                'total' => round($totalAmount, 2),
                'expenses' => $periodData
            ];
        }

        return $result;
    }

    /**
     * Format period for display
     *
     * @param string $period
     * @return string|array
     */
    private function formatPeriodDisplay($period)
    {
        // If it's a weekly period (YYYY-WW format)
        if (preg_match('/^\d{4}-\d{1,2}$/', $period) && strpos($period, '-') !== false) {
            list($year, $week) = explode('-', $period);
            
            if (strlen($week) <= 2) { // It's likely a week number
                $firstDayOfWeek = Carbon::now()->setISODate($year, $week, 1)->format('Y-m-d');
                $lastDayOfWeek = Carbon::now()->setISODate($year, $week, 7)->format('Y-m-d');
                
                return [
                    'type' => 'weekly',
                    'year' => (int)$year,
                    'week' => (int)$week,
                    'start_date' => $firstDayOfWeek,
                    'end_date' => $lastDayOfWeek,
                    'display' => "Semana $week, $year"
                ];
            }
        }
        
        // If it's a monthly period (YYYY-MM format)
        if (preg_match('/^\d{4}-\d{2}$/', $period)) {
            list($year, $month) = explode('-', $period);
            $monthName = Carbon::createFromDate($year, $month, 1)->locale('es')->monthName;
            
            return [
                'type' => 'monthly',
                'year' => (int)$year,
                'month' => (int)$month,
                'display' => "$monthName $year"
            ];
        }
        
        // If it's a daily period (YYYY-MM-DD format)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            $date = Carbon::parse($period);
            
            return [
                'type' => 'daily',
                'year' => (int)$date->format('Y'),
                'month' => (int)$date->format('m'),
                'day' => (int)$date->format('d'),
                'display' => $date->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')
            ];
        }
        
        return $period;
    }

    /**
     * Compare expenses between periods
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareExpensesPeriods(Request $request)
    {
        // Validate request
        $request->validate([
            'period_type' => 'required|in:daily,weekly,monthly',
            'period1_start' => 'required|date_format:Y-m-d',
            'period1_end' => 'required|date_format:Y-m-d',
            'period2_start' => 'required|date_format:Y-m-d',
            'period2_end' => 'required|date_format:Y-m-d',
            'group_by' => 'nullable|in:rubro,category',
        ]);

        $periodType = $request->input('period_type');
        $period1Start = $request->input('period1_start');
        $period1End = $request->input('period1_end');
        $period2Start = $request->input('period2_start');
        $period2End = $request->input('period2_end');
        $groupBy = $request->input('group_by', 'rubro');

        try {
            // Get data for period 1
            $period1Data = $this->getExpensesSummaryForPeriod($periodType, $period1Start, $period1End, $groupBy);
            
            // Get data for period 2
            $period2Data = $this->getExpensesSummaryForPeriod($periodType, $period2Start, $period2End, $groupBy);
            
            // Compare the periods
            $comparison = $this->comparePeriodsData($period1Data, $period2Data);
            
            return response()->json([
                'status' => 'success',
                'period1' => [
                    'start_date' => $period1Start,
                    'end_date' => $period1End,
                    'total' => $period1Data['total'],
                ],
                'period2' => [
                    'start_date' => $period2Start,
                    'end_date' => $period2End,
                    'total' => $period2Data['total'],
                ],
                'comparison' => $comparison
            ]);

        } catch (\Exception $e) {
            Log::error("Error in compareExpensesPeriods: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expenses summary for a specific period
     *
     * @param string $periodType
     * @param string $startDate
     * @param string $endDate
     * @param string $groupBy
     * @return array
     */
    private function getExpensesSummaryForPeriod($periodType, $startDate, $endDate, $groupBy)
    {
        $groupColumn = $groupBy === 'category' ? 'r.tipo' : 'f.rubro';
        $nameColumn = $groupBy === 'category' ? 'r.tipo' : 'r.nombre';
        
        $expenses = DB::table('facturas as f')
            ->join(DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as sub'), 
                function($join) {
                    $join->on('f.nro_documento', '=', 'sub.nro_documento')
                        ->on('f.id', '=', 'sub.idMax');
                })
            ->join('rubros as r', 'f.rubro', '=', 'r.valor')
            ->select(
                $groupColumn . ' as group_id',
                $nameColumn . ' as group_name',
                DB::raw('SUM(f.total) as importe')
            )
            ->whereBetween('f.fecha_limite', [$startDate, $endDate])
            ->where('f.estadoDocumento', '!=', 6) // Not deleted
            ->where('f.rubro', '!=', 55) // Exclude investments
            ->groupBy($groupColumn, $nameColumn)
            ->get();
        
        $total = $expenses->sum('importe');
        
        $data = [];
        foreach ($expenses as $expense) {
            $percentage = $total > 0 ? ($expense->importe / $total) * 100 : 0;
            
            $data[] = [
                'group_id' => $expense->group_id,
                'group_name' => $expense->group_name,
                'importe' => round($expense->importe, 2),
                'porcentaje' => round($percentage, 2)
            ];
        }
        
        return [
            'total' => round($total, 2),
            'items' => $data
        ];
    }

    /**
     * Compare data between two periods
     *
     * @param array $period1Data
     * @param array $period2Data
     * @return array
     */
    private function comparePeriodsData($period1Data, $period2Data)
    {
        $result = [];
        $period1Items = collect($period1Data['items'])->keyBy('group_id');
        $period2Items = collect($period2Data['items'])->keyBy('group_id');
        
        // Get all unique groups
        $allGroups = $period1Items->keys()->merge($period2Items->keys())->unique();
        
        foreach ($allGroups as $groupId) {
            $period1Item = $period1Items->get($groupId);
            $period2Item = $period2Items->get($groupId);
            
            $period1Amount = $period1Item['importe'] ?? 0;
            $period2Amount = $period2Item['importe'] ?? 0;
            
            $diff = $period2Amount - $period1Amount;
            $diffPercentage = $period1Amount > 0 ? ($diff / $period1Amount) * 100 : null;
            
            $result[] = [
                'group_id' => $groupId,
                'group_name' => $period2Item['group_name'] ?? $period1Item['group_name'],
                'period1_amount' => round($period1Amount, 2),
                'period2_amount' => round($period2Amount, 2),
                'difference' => round($diff, 2),
                'difference_percentage' => $diffPercentage !== null ? round($diffPercentage, 2) : null,
            ];
        }
        
        // Sort by absolute difference descending
        usort($result, function($a, $b) {
            return abs($b['difference']) <=> abs($a['difference']);
        });
        
        return $result;
    }
}