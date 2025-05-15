<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Ventas;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ResumenVerController extends Controller
{
    /**
     * Get KPI data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRealizarData(Request $request)
    {
        // Ignore input dates and use current month instead
        $today = Carbon::now();
        $fechaInicio = $today->startOfMonth()->format('Y-m-d');
        $fechaFin = $today->endOfMonth()->format('Y-m-d');

        // Log the query
        // Log::info(Auth::user()->name . " | API consulta KPIs desde $fechaInicio hasta $fechaFin | ");

        // Get sales data for the current month
        $ventas = $this->obtenerVentas($fechaInicio, $fechaFin);

        if (empty($ventas)) {
            return response()->json([
                'success' => false,
                'message' => 'No existen datos validados en el mes actual',
                'data' => null
            ], 404);
        }

        // Calculate KPIs
        $kpis = $this->calcularProyecciones($ventas, $fechaInicio, $fechaFin);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Consulta generada correctamente para el mes actual',
            'data' => $kpis
        ]);
    }

    /**
     * Get validated sales data
     * 
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function obtenerVentas($fechaInicio, $fechaFin)
    {
        // Get the latest valid records for each sale
        $ultimos_por_nro_venta = Ventas::select('nro_venta', DB::raw('MAX(id) as idMax'))
            ->groupBy('nro_venta');

        $ventas = Ventas::joinSub($ultimos_por_nro_venta, 'ultimo_doc', function ($join) {
            $join->on('ventas.nro_venta', '=', 'ultimo_doc.nro_venta');
            $join->on('ventas.id', '=', 'ultimo_doc.idMax');
        })
            ->OrderBy('ventas.nro_venta', 'desc')
            ->where('estado_venta', '!=', '6')  // Not deleted
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->whereIn('estado_venta', [1, 2]) // Loaded and validated - fixed the 'in' syntax
            ->get();

        return $ventas;
    }
    
    /**
     * Calculate sales projections and other KPIs
     * 
     * @param \Illuminate\Database\Eloquent\Collection $ventas
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    private function calcularProyecciones($ventas, $fechaInicio, $fechaFin)
    {
        // Group sales by day
        $ventasPorDia = $this->agruparVentasPorDia($ventas);

        // Current date info
        $today = Carbon::now();
        $currentDayOfMonth = $today->day;
        $daysInMonth = $today->daysInMonth;

        // Calculate monthly sales projection
        $ventasTotalesMes = $ventasPorDia->sum('ingresos');
        $proyeccionVentasMes = ($ventasTotalesMes / $currentDayOfMonth) * $daysInMonth;

        // Calculate annual sales projection
        $currentMonth = $today->month;
        
        // For all months including January, use the same formula
        // Get the YTD (Year To Date) sales
        $ventasTotalesAnual = DB::table('ventas')
            ->where('estado_venta', '!=', '6')
            ->whereYear('fecha_venta', $today->year)
            ->whereIn('estado_venta', [1, 2]) // Loaded and validated
            ->sum('ingresos');
        
        // Calculate projection based on actual data for all months
        $proyeccionVentasAnual = ($ventasTotalesAnual / $currentMonth) * 12;

        // Get target/objective sales from settings
        $objetivoEstablecido = $this->obtenerObjetivoVentas();

        // Calculate achievement percentage
        $porcentajeRealizacion = 0;
        if ($objetivoEstablecido > 0) {
            $porcentajeRealizacion = ($ventasTotalesMes / $objetivoEstablecido) * 100;
        }

        // Return KPIs with their formulas for documentation
        return [
            'monthly' => round($proyeccionVentasMes, 2),
            'annual' => round($proyeccionVentasAnual, 2),
            'achieved' => round($ventasTotalesMes, 2),
            'objective' => $objetivoEstablecido,
            'percentAchieved' => round($porcentajeRealizacion, 2),
            'metadatos' => [
                'ventasTotalesMes' => round($ventasTotalesMes, 2),
                'diasTranscurridos' => $currentDayOfMonth,
                'diasTotalesMes' => $daysInMonth,
                'mesActual' => $currentMonth
            ]
        ];
    }

    /**
     * Group sales data by day
     * 
     * @param \Illuminate\Database\Eloquent\Collection $ventas
     * @return \Illuminate\Support\Collection
     */
    private function agruparVentasPorDia($ventas)
    {
        $ventasPorDia = $ventas->groupBy(function ($item) {
            return Carbon::parse($item->fecha_venta)->format('Y-m-d');
        })->map(function ($group) {
            return [
                'fecha' => $group->first()->fecha_venta,
                'ingresos' => $group->sum('ingresos'),
                'ventasFiscal' => $group->sum('ventas_fiscal'),
                'ventasNoFiscal' => $group->sum('ventas_no_fiscal')
            ];
        });

        return $ventasPorDia;
    }

    /**
     * Get sales objectives/targets from settings
     * 
     * @return float
     */
    private function obtenerObjetivoVentas()
    {
        // This would typically come from a settings table or configuration
        // For now, return a placeholder value
        return 100000; // Example objective
    }
}