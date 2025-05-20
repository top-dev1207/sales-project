<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use App\Models\ObjetivosVentas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesProjectionController extends Controller
{
    /**
     * Get monthly sales projection data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyProjection(Request $request)
    {
        // Get optional request params
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        // Create date objects for start and end of month
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
        $today = Carbon::now();
        
        // Calculate total days in month and days elapsed
        $totalDaysInMonth = $endOfMonth->day;
        $daysElapsed = $today->isSameMonth($startOfMonth) ? $today->day : $totalDaysInMonth;
        
        // Get total sales for the month so far
        $totalSales = $this->getMonthSales($year, $month);
        
        // Calculate projections
        $projectedMonthSales = 0;
        if ($daysElapsed > 0) {
            $projectedMonthSales = ($totalSales / $daysElapsed) * $totalDaysInMonth;
        }
        
        // Get sales objective for the month if exists
        $objective = $this->getMonthlySalesObjective($year, $month);
        $objectiveProgress = ($objective > 0) ? ($totalSales / $objective) * 100 : 0;
        
        return response()->json([
            'year' => $year,
            'month' => $month,
            'month_name' => Carbon::createFromDate($year, $month, 1)->format('F'),
            'total_days' => $totalDaysInMonth,
            'days_elapsed' => $daysElapsed,
            'sales_to_date' => round($totalSales, 2),
            'projected_sales' => round($projectedMonthSales, 2),
            'projection_formula' => "({$totalSales} / {$daysElapsed}) * {$totalDaysInMonth}",
            'objective' => round($objective, 2),
            'objective_progress' => round($objectiveProgress, 2),
            'remaining_days' => $totalDaysInMonth - $daysElapsed,
        ]);
    }
    
    /**
     * Get annual sales projection data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnualProjection(Request $request)
    {
        // Get optional request param
        $year = $request->input('year', Carbon::now()->year);
        
        // Get current month and calculate months elapsed
        $currentMonth = Carbon::now()->month;
        $monthsElapsed = $year < Carbon::now()->year ? 12 : $currentMonth;
        
        // Get total sales for the year to date
        $yearSales = $this->getYearSales($year);
        
        // Calculate annual projection
        $projectedAnnualSales = 0;
        if ($monthsElapsed > 0) {
            $projectedAnnualSales = ($yearSales / $monthsElapsed) * 12;
        }
        
        // Get monthly breakdown
        $monthlySales = $this->getMonthlySalesBreakdown($year);
        
        // Calculate annual objective and progress
        $annualObjective = $this->getYearlySalesObjective($year);
        $objectiveProgress = ($annualObjective > 0) ? ($yearSales / $annualObjective) * 100 : 0;
        
        // If it's the first month, use monthly projection * 12 as suggested in requirements
        if ($monthsElapsed === 1) {
            // Get the first month's projection
            $firstMonthProjection = $this->getMonthlyProjection(new Request(['year' => $year, 'month' => 1]));
            $firstMonthData = json_decode($firstMonthProjection->getContent(), true);
            $projectedAnnualSales = $firstMonthData['projected_sales'] * 12;
        }
        
        return response()->json([
            'year' => $year,
            'months_elapsed' => $monthsElapsed,
            'sales_to_date' => round($yearSales, 2),
            'projected_annual_sales' => round($projectedAnnualSales, 2),
            'projection_formula' => $monthsElapsed === 1 ? 
                "First month projection * 12" : 
                "({$yearSales} / {$monthsElapsed}) * 12",
            'annual_objective' => round($annualObjective, 2),
            'objective_progress' => round($objectiveProgress, 2),
            'monthly_breakdown' => $monthlySales,
            'remaining_months' => 12 - $monthsElapsed,
        ]);
    }
    
    /**
     * Get dashboard data combining monthly and annual projections
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request)
    {
        $monthlyProjection = $this->getMonthlyProjection($request);
        $annualProjection = $this->getAnnualProjection($request);
        
        $monthlyData = json_decode($monthlyProjection->getContent(), true);
        $annualData = json_decode($annualProjection->getContent(), true);
        
        // Combine data for a complete dashboard
        return response()->json([
            'current_date' => Carbon::now()->format('Y-m-d'),
            'monthly_projection' => $monthlyData,
            'annual_projection' => $annualData,
            'summary' => [
                'monthly_progress' => $monthlyData['objective_progress'],
                'annual_progress' => $annualData['objective_progress'],
                'monthly_projection_formula' => "({$monthlyData['sales_to_date']} / {$monthlyData['days_elapsed']}) * {$monthlyData['total_days']}",
                'annual_projection_formula' => "({$annualData['sales_to_date']} / {$annualData['months_elapsed']}) * 12",
            ]
        ]);
    }
    
    /**
     * Helper method to get total sales for a specific month
     * 
     * @param int $year
     * @param int $month
     * @return float
     */
    private function getMonthSales($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay()->format('Y-m-d');
        
        // Use the same structure as the ResultadosController from the project
        // to get the sales data
        $sales = Ventas::select(DB::raw("SUM(ingresos) as total"))
            ->whereRaw("fecha_venta BETWEEN ? AND ?", [$startDate, $endDate])
            ->whereIn('estado_venta', [1, 2]) // Only consider loaded and validated sales
            ->whereNotIn('estado_venta', [6]) // Exclude deleted sales
            ->first();
            
        return $sales ? $sales->total : 0;
    }
    
    /**
     * Helper method to get total sales for a year
     * 
     * @param int $year
     * @return float
     */
    private function getYearSales($year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay()->format('Y-m-d');
        
        // Use the same structure as the ResultadosController from the project
        $sales = Ventas::select(DB::raw("SUM(ingresos) as total"))
            ->whereRaw("fecha_venta BETWEEN ? AND ?", [$startDate, $endDate])
            ->whereIn('estado_venta', [1, 2]) // Only consider loaded and validated sales
            ->whereNotIn('estado_venta', [6]) // Exclude deleted sales
            ->first();
            
        return $sales ? $sales->total : 0;
    }
    
    /**
     * Helper method to get monthly sales breakdown for a year
     * 
     * @param int $year
     * @return array
     */
    private function getMonthlySalesBreakdown($year)
    {
        $result = [];
        $currentMonth = Carbon::now()->month;
        $maxMonth = $year < Carbon::now()->year ? 12 : $currentMonth;
        
        for ($month = 1; $month <= $maxMonth; $month++) {
            $monthSales = $this->getMonthSales($year, $month);
            $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
            
            // Get objective and calculate progress
            $objective = $this->getMonthlySalesObjective($year, $month);
            $objectiveProgress = ($objective > 0) ? ($monthSales / $objective) * 100 : 0;
            
            $result[] = [
                'month' => $month,
                'month_name' => $monthName,
                'sales' => round($monthSales, 2),
                'objective' => round($objective, 2),
                'progress' => round($objectiveProgress, 2),
            ];
        }
        
        return $result;
    }
    
    /**
     * Helper method to get the monthly sales objective
     * 
     * @param int $year
     * @param int $month
     * @return float
     */
    private function getMonthlySalesObjective($year, $month)
    {
        // Check if there is a monthly objective for this month
        $objective = ObjetivosVentas::where('year', $year)
            ->where('month', $month)
            ->first();
            
        if ($objective) {
            return $objective->monto;
        }
        
        // If no monthly objective, divide annual objective by 12
        $annualObjective = $this->getYearlySalesObjective($year);
        return $annualObjective / 12;
    }
    
    /**
     * Helper method to get the yearly sales objective
     * 
     * @param int $year
     * @return float
     */
    private function getYearlySalesObjective($year)
    {
        // Sum all monthly objectives for the year
        // $yearlyObjective = ObjetivosVentas::where('year', $year)
        //     ->sum('monto');
            
        // If no monthly objectives are set, look for an annual objective
        // if ($yearlyObjective == 0) {
            $annualObjective = ObjetivosVentas::where('year', $year)
                ->where('month', 0) // Use month=0 for annual objectives
                ->first();
                
            // if ($annualObjective) {
                return $annualObjective->monto;
            // }
        // }
        
        // return $yearlyObjective;
    }
}