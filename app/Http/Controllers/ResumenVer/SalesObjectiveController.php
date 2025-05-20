<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use App\Models\ObjetivosVentas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SalesObjectiveController extends Controller
{
    /**
     * Get the current progress towards monthly and annual sales objectives
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgress()
    {
        // Get current date information
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $daysInMonth = $now->daysInMonth;
        $dayOfMonth = $now->day;
        
        // Get monthly sales and objective
        $monthlySales = $this->getMonthSales($currentYear, $currentMonth);
        $monthlyObjective = $this->getMonthlySalesObjective($currentYear, $currentMonth);
        
        // Calculate monthly projection
        $monthlyProjection = 0;
        if ($dayOfMonth > 0) {
            $monthlyProjection = ($monthlySales / $dayOfMonth) * $daysInMonth;
        }
        
        // Get yearly sales and objective
        $yearSales = $this->getYearSales($currentYear);
        $yearObjective = $this->getYearlySalesObjective($currentYear);
        
        // Calculate yearly projection
        $yearlyProjection = 0;
        if ($currentMonth > 0) {
            $yearlyProjection = ($yearSales / $currentMonth) * 12;
        }
        
        // If it's the first month, use monthly projection * 12 as specified
        if ($currentMonth === 1) {
            $yearlyProjection = $monthlyProjection * 12;
        }
        
        return response()->json([
            'current_date' => $now->format('Y-m-d'),
            'monthly_data' => [
                'year' => $currentYear,
                'month' => $currentMonth,
                'month_name' => $now->format('F'),
                'days_in_month' => $daysInMonth,
                'days_elapsed' => $dayOfMonth,
                'sales_to_date' => round($monthlySales, 2),
                'monthly_objective' => round($monthlyObjective, 2),
                'monthly_projection' => round($monthlyProjection, 2),
                'progress_percentage' => $monthlyObjective > 0 ? round(($monthlySales / $monthlyObjective) * 100, 2) : 0,
                'projection_percentage' => $monthlyObjective > 0 ? round(($monthlyProjection / $monthlyObjective) * 100, 2) : 0,
                'days_remaining' => $daysInMonth - $dayOfMonth
            ],
            'yearly_data' => [
                'year' => $currentYear,
                'months_elapsed' => $currentMonth,
                'sales_to_date' => round($yearSales, 2),
                'yearly_objective' => round($yearObjective, 2),
                'yearly_projection' => round($yearlyProjection, 2),
                'progress_percentage' => $yearObjective > 0 ? round(($yearSales / $yearObjective) * 100, 2) : 0,
                'projection_percentage' => $yearObjective > 0 ? round(($yearlyProjection / $yearObjective) * 100, 2) : 0,
                'months_remaining' => 12 - $currentMonth
            ],
            'formulas' => [
                'monthly_projection' => "({$monthlySales} / {$dayOfMonth}) * {$daysInMonth}",
                'yearly_projection' => $currentMonth === 1 ? 
                    "Monthly projection({$monthlyProjection}) * 12" : 
                    "({$yearSales} / {$currentMonth}) * 12"
            ]
        ]);
    }
    
    /**
     * Set a sales objective for a specific month or year
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setObjective(Request $request)
    {
        // Validate request data
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:0|max:12', // 0 means annual objective
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);
        
        // Extract request data
        $year = $request->input('year');
        $month = $request->input('month');
        $amount = $request->input('amount');
        $description = $request->input('description');
        
        try {
            // Check if an objective already exists for this period
            $objective = ObjetivosVentas::updateOrCreate(
                ['year' => $year, 'month' => $month],
                ['monto' => $amount, 'descripcion' => $description]
            );
            
            // Log the action
            $period = $month == 0 ? 'annual' : Carbon::createFromDate($year, $month, 1)->format('F Y');
            // Log::info("Sales objective for {$period} set to {$amount} by " . auth()->user()->name);
            
            return response()->json([
                'success' => true,
                'message' => "Sales objective for {$period} successfully updated",
                'data' => $objective
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error setting sales objective: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Error setting sales objective: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all sales objectives for a specific year
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearlyObjectives(Request $request)
    {
        // Get year from request or use current year
        $year = $request->input('year', Carbon::now()->year);
        
        // Get all objectives for the year
        $objectives = ObjetivosVentas::where('year', $year)
            ->orderBy('month')
            ->get();
            
        // Format the objectives for display
        $formattedObjectives = $objectives->map(function($objective) {
            $monthName = $objective->month == 0 ? 'Annual' : Carbon::createFromDate($objective->year, $objective->month, 1)->format('F');
            
            return [
                'id' => $objective->id,
                'year' => $objective->year,
                'month' => $objective->month,
                'month_name' => $monthName,
                'amount' => $objective->monto,
                'description' => $objective->descripcion,
                'created_at' => $objective->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $objective->updated_at->format('Y-m-d H:i:s')
            ];
        });
        
        // Get the yearly total from monthly objectives (excluding annual objective)
        $monthlyTotal = $objectives->where('month', '>', 0)->sum('monto');
        $annualObjective = $objectives->where('month', 0)->first();
        
        return response()->json([
            'year' => (int)$year,
            'objectives' => $formattedObjectives,
            'summary' => [
                'monthly_objectives_total' => round($monthlyTotal, 2),
                'annual_objective' => $annualObjective ? round($annualObjective->monto, 2) : 0,
                'has_complete_monthly_objectives' => $objectives->where('month', '>', 0)->count() == 12,
            ]
        ]);
    }
    
    /**
     * Helper method to get the monthly sales data
     * 
     * @param int $year
     * @param int $month
     * @return float
     */
    private function getMonthSales($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay()->format('Y-m-d');
        
        $sales = Ventas::select(DB::raw("SUM(ingresos) as total"))
            ->whereRaw("fecha_venta BETWEEN ? AND ?", [$startDate, $endDate])
            ->whereIn('estado_venta', [1, 2]) // Only consider loaded and validated sales
            ->whereNotIn('estado_venta', [6]) // Exclude deleted sales
            ->first();
            
        return $sales ? $sales->total : 0;
    }
    
    /**
     * Helper method to get the yearly sales data
     * 
     * @param int $year
     * @return float
     */
    private function getYearSales($year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay()->format('Y-m-d');
        
        $sales = Ventas::select(DB::raw("SUM(ingresos) as total"))
            ->whereRaw("fecha_venta BETWEEN ? AND ?", [$startDate, $endDate])
            ->whereIn('estado_venta', [1, 2])
            ->whereNotIn('estado_venta', [6])
            ->first();
            
        return $sales ? $sales->total : 0;
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
        $objective = ObjetivosVentas::where('year', $year)
            ->where('month', $month)
            ->first();
            
        if ($objective) {
            return $objective->monto;
        }
        
        // If no monthly objective exists, divide annual objective by 12
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
        // First check if there's a specific annual objective
        $annualObjective = ObjetivosVentas::where('year', $year)
            ->where('month', 0)
            ->first();
            
        if ($annualObjective) {
            return $annualObjective->monto;
        }
        
        // Otherwise, sum all monthly objectives
        $totalMonthlyObjectives = ObjetivosVentas::where('year', $year)
            ->where('month', '>', 0)
            ->sum('monto');
            
        return $totalMonthlyObjectives > 0 ? $totalMonthlyObjectives : 0;
    }
}