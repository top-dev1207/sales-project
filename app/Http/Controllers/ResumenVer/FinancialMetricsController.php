<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Ventas;
use App\Models\Facturas;
use App\Models\DatosResultados;
use App\Models\Transacciones;

class FinancialMetricsController extends Controller
{
    /**
     * Get yearly financial metrics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearlyMetrics(Request $request)
    {
        // Validate request
        $request->validate([
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        // Get year from request or use current year
        $year = $request->input('year', Carbon::now()->year);
        
        // Define months for response
        $months = [
            'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 
            'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
        ];
        
        // Get sales totals by month
        $salesByMonth = $this->getMonthlySales($year);
        
        // Get expenses totals by month
        $expensesByMonth = $this->getMonthlyExpenses($year);
        
        // Calculate gross profit
        $grossProfitByMonth = $this->calculateGrossProfit($salesByMonth, $expensesByMonth);
        
        // Get tax data by month
        $taxDataByMonth = $this->getMonthlyTaxData($year);
        
        // Calculate net profit
        $netProfitByMonth = $this->calculateNetProfit($grossProfitByMonth, $taxDataByMonth);
        
        // Calculate efficiency percentages
        $percentagesByMonth = $this->calculatePercentages($salesByMonth, $expensesByMonth, $netProfitByMonth);
        
        // Get additional financial data
        $additionalData = $this->getAdditionalFinancialData($year);
        
        // Calculate final cash
        $finalCash = $this->calculateFinalCash($netProfitByMonth, $additionalData);
        
        // Format totals
        $totals = $this->calculateTotals(
            $salesByMonth, 
            $expensesByMonth, 
            $grossProfitByMonth,
            $taxDataByMonth,
            $netProfitByMonth,
            $percentagesByMonth,
            $additionalData,
            $finalCash
        );
        
        // Prepare response data
        $data = [
            'year' => $year,
            'months' => $months,
            'metrics' => [
                'ventas_totales' => $salesByMonth,
                'total_gastos' => $expensesByMonth,
                'ganancia_bruta' => $grossProfitByMonth,
                'iibb' => $taxDataByMonth['iibb'],
                'iva' => $taxDataByMonth['iva'],
                'impuesto_ganancia' => $taxDataByMonth['impuesto_ganancia'],
                'ganancia_neta' => $netProfitByMonth,
                'ganancia_porcentaje' => $percentagesByMonth['ganancia'],
                'costo_alimento_porcentaje' => $percentagesByMonth['costo_alimento'],
                'costo_bebida_porcentaje' => $percentagesByMonth['costo_bebida'],
                'costo_mixto_porcentaje' => $percentagesByMonth['costo_mixto'],
                'ingresos_propietarios' => $additionalData['ingresos_propietarios'],
                'inversiones' => $additionalData['inversiones'],
                'pago_deuda_atrasada' => $additionalData['pago_deuda_atrasada'],
                'retiro_dividendos' => $additionalData['retiro_dividendos'],
                'gastos_cta_cte' => $additionalData['gastos_cta_cte'],
                'caja_final' => $finalCash
            ],
            'totals' => $totals
        ];
        
        return response()->json($data);
    }
    
    /**
     * Get monthly sales data for the specified year
     * 
     * @param int $year
     * @return array
     */
    private function getMonthlySales($year)
    {
        $salesByMonth = array_fill(0, 12, 0);
        
        // Get sales data from the database (using the same query pattern as in ResultadosController)
        $sales = DB::select(DB::raw("
            SELECT 
                MONTH(v.fecha_venta) - 1 as month_index,
                SUM(v.ingresos) as total
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
            ON (v.dshowId = s.id AND v.nro_venta = s.idVenta)
            WHERE YEAR(v.fecha_venta) = ?
            AND v.estado_venta != 6
            GROUP BY MONTH(v.fecha_venta)
        "), [$year]);
        
        // Populate sales array
        foreach ($sales as $sale) {
            $salesByMonth[$sale->month_index] = (float) $sale->total;
        }
        
        return $salesByMonth;
    }
    
    /**
     * Get monthly expenses data for the specified year
     * 
     * @param int $year
     * @return array
     */
    private function getMonthlyExpenses($year)
    {
        $expensesByMonth = array_fill(0, 12, 0);
        
        // Get expense data from facturas
        $expenses = DB::select(DB::raw("
            SELECT 
                MONTH(f.fecha_limite) - 1 as month_index,
                SUM(f.total) as total
            FROM (
                SELECT f1.*
                FROM facturas AS f1
                INNER JOIN (
                    SELECT nro_documento, MAX(id) as idMax
                    FROM facturas
                    GROUP BY nro_documento
                ) AS max
                ON f1.id = max.idMax
            ) AS f
            WHERE YEAR(f.fecha_limite) = ?
            AND f.estadoDocumento != 6
            AND f.rubro != 55
            GROUP BY MONTH(f.fecha_limite)
        "), [$year]);
        
        // Populate expenses array from facturas
        foreach ($expenses as $expense) {
            $expensesByMonth[$expense->month_index] = (float) $expense->total;
        }
        
        // Add salaries from recibo_sueldos
        $salaries = DB::select(DB::raw("
            SELECT 
                MONTH(periodo) - 1 as month_index,
                SUM(total) as total
            FROM recibo_sueldos
            WHERE YEAR(periodo) = ?
            AND estadoRecibo != 6
            GROUP BY MONTH(periodo)
        "), [$year]);
        
        // Add salaries to expenses array
        foreach ($salaries as $salary) {
            $expensesByMonth[$salary->month_index] += (float) $salary->total;
        }
        
        return $expensesByMonth;
    }
    
    /**
     * Calculate gross profit from sales and expenses
     * 
     * @param array $sales
     * @param array $expenses
     * @return array
     */
    private function calculateGrossProfit($sales, $expenses)
    {
        $grossProfit = [];
        
        for ($i = 0; $i < 12; $i++) {
            $grossProfit[$i] = $sales[$i] - $expenses[$i];
        }
        
        return $grossProfit;
    }
    
    /**
     * Get monthly tax data for the specified year
     * 
     * @param int $year
     * @return array
     */
    private function getMonthlyTaxData($year)
    {
        $taxData = [
            'iibb' => array_fill(0, 12, 0),
            'iva' => array_fill(0, 12, 0),
            'impuesto_ganancia' => array_fill(0, 12, 0)
        ];
        
        // Get tax data from datos_resultados table
        $taxes = DB::select(DB::raw("
            SELECT 
                MONTH(periodo) - 1 as month_index,
                iibb, iva, impuestoGanancias
            FROM datos_resultados
            WHERE YEAR(periodo) = ?
            AND estado = 1
        "), [$year]);
        
        // Populate tax arrays
        foreach ($taxes as $tax) {
            $taxData['iibb'][$tax->month_index] = (float) $tax->iibb;
            $taxData['iva'][$tax->month_index] = (float) $tax->iva;
            $taxData['impuesto_ganancia'][$tax->month_index] = (float) $tax->impuestoGanancias;
        }
        
        return $taxData;
    }
    
    /**
     * Calculate net profit from gross profit and taxes
     * 
     * @param array $grossProfit
     * @param array $taxData
     * @return array
     */
    private function calculateNetProfit($grossProfit, $taxData)
    {
        $netProfit = [];
        
        for ($i = 0; $i < 12; $i++) {
            $netProfit[$i] = $grossProfit[$i] - $taxData['iibb'][$i] - $taxData['iva'][$i] - $taxData['impuesto_ganancia'][$i];
        }
        
        return $netProfit;
    }
    
    /**
     * Calculate efficiency percentages 
     * 
     * @param array $sales
     * @param array $expenses
     * @param array $netProfit
     * @return array
     */
    private function calculatePercentages($sales, $expenses, $netProfit)
    {
        $percentages = [
            'ganancia' => array_fill(0, 12, 0),
            'costo_alimento' => array_fill(0, 12, 0),
            'costo_bebida' => array_fill(0, 12, 0),
            'costo_mixto' => array_fill(0, 12, 0)
        ];
        
        // Get food and beverage sales data
        $foodAndBeverageSales = $this->getFoodAndBeverageSales($sales[0]);
        
        // Get food and beverage costs
        $foodAndBeverageCosts = $this->getFoodAndBeverageCosts($expenses[0]);
        
        for ($i = 0; $i < 12; $i++) {
            // Calculate profit percentage
            if ($sales[$i] > 0) {
                $percentages['ganancia'][$i] = round(($netProfit[$i] / $sales[$i]) * 100, 2);
            }
            
            // Calculate food cost percentage
            if (isset($foodAndBeverageSales[$i]['food']) && $foodAndBeverageSales[$i]['food'] > 0) {
                $percentages['costo_alimento'][$i] = round(($foodAndBeverageCosts[$i]['food'] / $foodAndBeverageSales[$i]['food']) * 100, 2);
            }
            
            // Calculate beverage cost percentage
            if (isset($foodAndBeverageSales[$i]['beverage']) && $foodAndBeverageSales[$i]['beverage'] > 0) {
                $percentages['costo_bebida'][$i] = round(($foodAndBeverageCosts[$i]['beverage'] / $foodAndBeverageSales[$i]['beverage']) * 100, 2);
            }
            
            // Calculate mixed cost percentage
            if ($sales[$i] > 0) {
                $totalFoodBeverageCost = $foodAndBeverageCosts[$i]['food'] + $foodAndBeverageCosts[$i]['beverage'];
                $percentages['costo_mixto'][$i] = round(($totalFoodBeverageCost / $sales[$i]) * 100, 2);
            }
        }
        
        return $percentages;
    }
    
    /**
     * Get food and beverage sales data
     * 
     * @param int $year
     * @return array
     */
    private function getFoodAndBeverageSales($year)
    {
        $salesData = [];
        
        // Get food and beverage sales data from database
        $sales = DB::select(DB::raw("
            SELECT 
                MONTH(v.fecha_venta) - 1 as month_index,
                SUM(v.venta_alimentos) as food_sales,
                SUM(v.venta_bebidas) as beverage_sales
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
            WHERE YEAR(v.fecha_venta) = ?
            AND v.estado_venta != 6
            GROUP BY MONTH(v.fecha_venta)
        "), [$year]);
        
        // Initialize array with zeros for all months
        for ($i = 0; $i < 12; $i++) {
            $salesData[$i] = [
                'food' => 0,
                'beverage' => 0
            ];
        }
        
        // Populate sales data array
        foreach ($sales as $sale) {
            $salesData[$sale->month_index]['food'] = (float) $sale->food_sales;
            $salesData[$sale->month_index]['beverage'] = (float) $sale->beverage_sales;
        }
        
        return $salesData;
    }
    
    /**
     * Get food and beverage costs
     * 
     * @param int $year
     * @return array
     */
    private function getFoodAndBeverageCosts($year)
    {
        $costsData = [];
        
        // Get food (rubro=1) and beverage (rubro=2) costs from database
        $costs = DB::select(DB::raw("
            SELECT 
                MONTH(f.fecha_limite) - 1 as month_index,
                f.rubro,
                SUM(f.total) as total
            FROM (
                SELECT f1.*
                FROM facturas AS f1
                INNER JOIN (
                    SELECT nro_documento, MAX(id) as idMax
                    FROM facturas
                    GROUP BY nro_documento
                ) AS max
                ON f1.id = max.idMax
            ) AS f
            WHERE YEAR(f.fecha_limite) = ?
            AND f.estadoDocumento != 6
            AND (f.rubro = 1 OR f.rubro = 2)
            GROUP BY MONTH(f.fecha_limite), f.rubro
        "), [$year]);
        
        // Initialize array with zeros for all months
        for ($i = 0; $i < 12; $i++) {
            $costsData[$i] = [
                'food' => 0,
                'beverage' => 0
            ];
        }
        
        // Populate costs data array
        foreach ($costs as $cost) {
            if ($cost->rubro == 1) { // Alimentos
                $costsData[$cost->month_index]['food'] = (float) $cost->total;
            } else if ($cost->rubro == 2) { // Bebidas
                $costsData[$cost->month_index]['beverage'] = (float) $cost->total;
            }
        }
        
        return $costsData;
    }
    
    /**
     * Get additional financial data
     * 
     * @param int $year
     * @return array
     */
    private function getAdditionalFinancialData($year)
    {
        $data = [
            'ingresos_propietarios' => array_fill(0, 12, 0),
            'inversiones' => array_fill(0, 12, 0),
            'pago_deuda_atrasada' => array_fill(0, 12, 0),
            'retiro_dividendos' => array_fill(0, 12, 0),
            'gastos_cta_cte' => array_fill(0, 12, 0)
        ];
        
        // Get data from datos_resultados
        $additionalData = DB::select(DB::raw("
            SELECT 
                MONTH(periodo) - 1 as month_index,
                ingresoPropietarios, inversiones, pagoDeudaAtrasada, gastosCtaCte
            FROM datos_resultados
            WHERE YEAR(periodo) = ?
            AND estado = 1
        "), [$year]);
        
        // Populate data arrays
        foreach ($additionalData as $item) {
            $data['ingresos_propietarios'][$item->month_index] = (float) $item->ingresoPropietarios;
            $data['inversiones'][$item->month_index] = (float) $item->inversiones;
            $data['pago_deuda_atrasada'][$item->month_index] = (float) $item->pagoDeudaAtrasada;
            $data['gastos_cta_cte'][$item->month_index] = (float) $item->gastosCtaCte;
        }
        
        // Get dividend withdrawals from transactions
        $dividends = DB::select(DB::raw("
            SELECT 
                MONTH(fecha) - 1 as month_index,
                SUM(importeOrigen) as total
            FROM transacciones
            WHERE YEAR(fecha) = ?
            AND destino = 200
            AND movimiento != 4
            AND estado != 5
            GROUP BY MONTH(fecha)
        "), [$year]);
        
        // Populate dividends array
        foreach ($dividends as $dividend) {
            $data['retiro_dividendos'][$dividend->month_index] = (float) $dividend->total;
        }
        
        // Add investment data from facturas (rubro = 55)
        $investments = DB::select(DB::raw("
            SELECT 
                MONTH(f.fecha_limite) - 1 as month_index,
                SUM(f.total) as total
            FROM (
                SELECT f1.*
                FROM facturas AS f1
                INNER JOIN (
                    SELECT nro_documento, MAX(id) as idMax
                    FROM facturas
                    GROUP BY nro_documento
                ) AS max
                ON f1.id = max.idMax
            ) AS f
            WHERE YEAR(f.fecha_limite) = ?
            AND f.estadoDocumento != 6
            AND f.rubro = 55
            GROUP BY MONTH(f.fecha_limite)
        "), [$year]);
        
        // Add to investments array
        foreach ($investments as $investment) {
            $data['inversiones'][$investment->month_index] += (float) $investment->total;
        }
        
        return $data;
    }
    
    /**
     * Calculate final cash
     * 
     * @param array $netProfit
     * @param array $additionalData
     * @return array
     */
    private function calculateFinalCash($netProfit, $additionalData)
    {
        $finalCash = [];
        
        for ($i = 0; $i < 12; $i++) {
            $finalCash[$i] = $netProfit[$i] + 
                             $additionalData['ingresos_propietarios'][$i] - 
                             $additionalData['inversiones'][$i] - 
                             $additionalData['pago_deuda_atrasada'][$i] - 
                             $additionalData['retiro_dividendos'][$i] - 
                             $additionalData['gastos_cta_cte'][$i];
        }
        
        return $finalCash;
    }
    
    /**
     * Calculate totals for all metrics
     * 
     * @param array $salesByMonth
     * @param array $expensesByMonth
     * @param array $grossProfitByMonth
     * @param array $taxDataByMonth
     * @param array $netProfitByMonth
     * @param array $percentagesByMonth
     * @param array $additionalData
     * @param array $finalCash
     * @return array
     */
    private function calculateTotals(
        $salesByMonth, 
        $expensesByMonth, 
        $grossProfitByMonth,
        $taxDataByMonth,
        $netProfitByMonth,
        $percentagesByMonth,
        $additionalData,
        $finalCash
    ) {
        $totals = [
            'ventas_totales' => array_sum($salesByMonth),
            'total_gastos' => array_sum($expensesByMonth),
            'ganancia_bruta' => array_sum($grossProfitByMonth),
            'iibb' => array_sum($taxDataByMonth['iibb']),
            'iva' => array_sum($taxDataByMonth['iva']),
            'impuesto_ganancia' => array_sum($taxDataByMonth['impuesto_ganancia']),
            'ganancia_neta' => array_sum($netProfitByMonth),
            'ingresos_propietarios' => array_sum($additionalData['ingresos_propietarios']),
            'inversiones' => array_sum($additionalData['inversiones']),
            'pago_deuda_atrasada' => array_sum($additionalData['pago_deuda_atrasada']),
            'retiro_dividendos' => array_sum($additionalData['retiro_dividendos']),
            'gastos_cta_cte' => array_sum($additionalData['gastos_cta_cte']),
            'caja_final' => array_sum($finalCash)
        ];
        
        // Calculate percentage totals
        if ($totals['ventas_totales'] > 0) {
            $totals['ganancia_porcentaje'] = round(($totals['ganancia_neta'] / $totals['ventas_totales']) * 100, 2);
            
            // Get food and beverage sales totals
            $foodSalesTotal = 0;
            $beverageSalesTotal = 0;
            $foodBeverageSales = $this->getFoodAndBeverageSales($salesByMonth[0]);
            foreach ($foodBeverageSales as $month) {
                $foodSalesTotal += $month['food'];
                $beverageSalesTotal += $month['beverage'];
            }
            
            // Get food and beverage costs totals
            $foodCostsTotal = 0;
            $beverageCostsTotal = 0;
            $foodBeverageCosts = $this->getFoodAndBeverageCosts($expensesByMonth[0]);
            foreach ($foodBeverageCosts as $month) {
                $foodCostsTotal += $month['food'];
                $beverageCostsTotal += $month['beverage'];
            }
            
            // Calculate cost percentages
            if ($foodSalesTotal > 0) {
                $totals['costo_alimento_porcentaje'] = round(($foodCostsTotal / $foodSalesTotal) * 100, 2);
            } else {
                $totals['costo_alimento_porcentaje'] = 0;
            }
            
            if ($beverageSalesTotal > 0) {
                $totals['costo_bebida_porcentaje'] = round(($beverageCostsTotal / $beverageSalesTotal) * 100, 2);
            } else {
                $totals['costo_bebida_porcentaje'] = 0;
            }
            
            $totals['costo_mixto_porcentaje'] = round((($foodCostsTotal + $beverageCostsTotal) / $totals['ventas_totales']) * 100, 2);
        } else {
            $totals['ganancia_porcentaje'] = 0;
            $totals['costo_alimento_porcentaje'] = 0;
            $totals['costo_bebida_porcentaje'] = 0;
            $totals['costo_mixto_porcentaje'] = 0;
        }
        
        return $totals;
    }
}
