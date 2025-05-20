<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\Facturas;
use App\Models\Proveedores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProveedoresDeudaController extends Controller
{
    /**
     * Get the total debt to providers as of current date
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeudaTotal()
    {
        try {
            // Get the latest status of each invoice by using the max id for each invoice number
            $latestInvoices = DB::table('facturas as f')
                ->join(DB::raw('(SELECT nro_documento, MAX(id) as max_id FROM facturas GROUP BY nro_documento) as latest'), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 0)          // Not paid
                ->select('f.proveedor', 'f.total')
                ->get();

            // Group and sum by provider
            $proveedoresDeuda = [];
            $deudaTotal = 0;

            foreach ($latestInvoices as $invoice) {
                $proveedorId = $invoice->proveedor;
                if (!isset($proveedoresDeuda[$proveedorId])) {
                    $proveedoresDeuda[$proveedorId] = 0;
                }
                $proveedoresDeuda[$proveedorId] += $invoice->total;
                $deudaTotal += $invoice->total;
            }

            // Format provider data with names
            $formattedData = [];
            foreach ($proveedoresDeuda as $proveedorId => $total) {
                $proveedor = Proveedores::where('nro_proveedor', $proveedorId)
                    ->where('estadoInterno', '!=', 6) // Not deleted
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($proveedor) {
                    $formattedData[] = [
                        'id' => $proveedorId,
                        'nombre' => $proveedor->nombre,
                        'deuda' => $total,
                        'porcentaje' => $deudaTotal > 0 ? round(($total / $deudaTotal) * 100, 2) : 0
                    ];
                }
            }

            // Sort by debt amount (highest first)
            usort($formattedData, function($a, $b) {
                return $b['deuda'] <=> $a['deuda'];
            });

            return response()->json([
                'success' => true,
                'deuda_total' => $deudaTotal,
                'proveedores' => $formattedData,
                'fecha_consulta' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the current invoice liabilities (validated and not validated)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPasivoFacturas()
    {
        try {
            // Get the latest status of each invoice
            $invoiceTotals = DB::table('facturas as f')
                ->join(DB::raw('(SELECT nro_documento, MAX(id) as max_id FROM facturas GROUP BY nro_documento) as latest'), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->select(
                    DB::raw('SUM(CASE WHEN f.estadoDocumento IN (2, 3) THEN f.total ELSE 0 END) as total_validado'),
                    DB::raw('SUM(CASE WHEN f.estadoDocumento = 1 THEN f.total ELSE 0 END) as total_por_validar'),
                    DB::raw('SUM(CASE WHEN f.pagado = 0 THEN f.total ELSE 0 END) as total_pendiente_pago'),
                    DB::raw('SUM(f.total) as total_general')
                )
                ->first();

            // Get counts by status
            $invoiceCounts = DB::table('facturas as f')
                ->join(DB::raw('(SELECT nro_documento, MAX(id) as max_id FROM facturas GROUP BY nro_documento) as latest'), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->select(
                    DB::raw('COUNT(CASE WHEN f.estadoDocumento IN (2, 3) THEN 1 ELSE NULL END) as count_validado'),
                    DB::raw('COUNT(CASE WHEN f.estadoDocumento = 1 THEN 1 ELSE NULL END) as count_por_validar'),
                    DB::raw('COUNT(CASE WHEN f.pagado = 0 THEN 1 ELSE NULL END) as count_pendiente_pago'),
                    DB::raw('COUNT(*) as count_total')
                )
                ->first();

            return response()->json([
                'success' => true,
                'totales' => [
                    'total_validado' => $invoiceTotals->total_validado ?? 0,
                    'total_por_validar' => $invoiceTotals->total_por_validar ?? 0,
                    'total_pendiente_pago' => $invoiceTotals->total_pendiente_pago ?? 0,
                    'total_general' => $invoiceTotals->total_general ?? 0
                ],
                'conteo' => [
                    'facturas_validadas' => $invoiceCounts->count_validado ?? 0,
                    'facturas_por_validar' => $invoiceCounts->count_por_validar ?? 0,
                    'facturas_pendientes_pago' => $invoiceCounts->count_pendiente_pago ?? 0,
                    'facturas_total' => $invoiceCounts->count_total ?? 0
                ],
                'fecha_consulta' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get historical debt and payment data (weekly and monthly)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistoricoPagos()
    {
        try {
            // Start date is 6 months ago
            $startDate = Carbon::now()->subMonths(6)->startOfMonth();
            $endDate = Carbon::now();

            // Get monthly data
            $monthlyData = $this->getMonthlySummary($startDate, $endDate);

            // Get weekly data (for the last 8 weeks)
            $weeklyStartDate = Carbon::now()->subWeeks(8)->startOfWeek();
            $weeklyData = $this->getWeeklySummary($weeklyStartDate, $endDate);

            // Get payment trend data (new debts vs. paid amounts)
            $paymentTrend = $this->getPaymentTrendData($startDate, $endDate);

            return response()->json([
                'success' => true,
                'periodo' => [
                    'fecha_inicio' => $startDate->format('Y-m-d'),
                    'fecha_fin' => $endDate->format('Y-m-d')
                ],
                'datos_mensuales' => $monthlyData,
                'datos_semanales' => $weeklyData,
                'tendencia_pagos' => $paymentTrend,
                'fecha_consulta' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get details for a specific provider
     * 
     * @param int $proveedorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetalleProveedor($proveedorId)
    {
        try {
            // Get provider info
            $proveedor = Proveedores::where('nro_proveedor', $proveedorId)
                ->where('estadoInterno', '!=', 6)
                ->orderBy('id', 'desc')
                ->first();

            if (!$proveedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found'
                ], 404);
            }

            // Get all unpaid invoices for this provider
            $unpaidInvoices = DB::table('facturas as f')
                ->join(DB::raw('(SELECT nro_documento, MAX(id) as max_id FROM facturas GROUP BY nro_documento) as latest'), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 0)          // Not paid
                ->where('f.proveedor', '=', $proveedorId)
                ->select(
                    'f.id',
                    'f.nro_documento',
                    'f.nro_factura',
                    'f.fecha_factura',
                    'f.fecha_limite',
                    'f.total',
                    'f.estadoDocumento',
                    DB::raw('DATEDIFF(NOW(), f.fecha_limite) as dias_vencidos')
                )
                ->orderBy('f.fecha_limite', 'asc')
                ->get();

            // Payment history (last 10 payments)
            $paymentHistory = DB::table('facturas as f')
                ->join(DB::raw('(SELECT nro_documento, MAX(id) as max_id FROM facturas GROUP BY nro_documento) as latest'), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 1)          // Paid
                ->where('f.proveedor', '=', $proveedorId)
                ->select(
                    'f.id',
                    'f.nro_documento',
                    'f.nro_factura',
                    'f.fecha_factura',
                    'f.fecha_limite',
                    'f.total',
                    'f.updated_at as fecha_pago'
                )
                ->orderBy('f.updated_at', 'desc')
                ->limit(10)
                ->get();

            // Calculate payment statistics
            $totalDebt = $unpaidInvoices->sum('total');
            $avgPaymentTime = $paymentHistory->isEmpty() ? 0 : 
                $paymentHistory->avg(function($item) {
                    $fechaFactura = Carbon::parse($item->fecha_factura);
                    $fechaPago = Carbon::parse($item->fecha_pago);
                    return $fechaPago->diffInDays($fechaFactura);
                });

            // Group invoices by age
            $debtByAge = [
                'current' => 0,
                '1_30' => 0,
                '31_60' => 0,
                '61_90' => 0,
                'over_90' => 0
            ];

            foreach ($unpaidInvoices as $invoice) {
                $diasVencidos = $invoice->dias_vencidos;
                
                if ($diasVencidos <= 0) {
                    $debtByAge['current'] += $invoice->total;
                } elseif ($diasVencidos <= 30) {
                    $debtByAge['1_30'] += $invoice->total;
                } elseif ($diasVencidos <= 60) {
                    $debtByAge['31_60'] += $invoice->total;
                } elseif ($diasVencidos <= 90) {
                    $debtByAge['61_90'] += $invoice->total;
                } else {
                    $debtByAge['over_90'] += $invoice->total;
                }
            }

            return response()->json([
                'success' => true,
                'proveedor' => [
                    'id' => $proveedor->nro_proveedor,
                    'nombre' => $proveedor->nombre,
                    'razon_social' => $proveedor->razonSocial,
                    'cuit' => $proveedor->cuit,
                    'dias_credito' => $proveedor->diasCredito ?? 0,
                    'contacto' => [
                        'email' => $proveedor->email,
                        'telefono' => $proveedor->tel
                    ]
                ],
                'deuda_actual' => $totalDebt,
                'facturas_pendientes' => count($unpaidInvoices),
                'tiempo_promedio_pago' => round($avgPaymentTime, 1),
                'deuda_por_antiguedad' => $debtByAge,
                'facturas_pendientes_detalle' => $unpaidInvoices,
                'historial_pagos' => $paymentHistory,
                'fecha_consulta' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate monthly debt summary
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getMonthlySummary(Carbon $startDate, Carbon $endDate)
    {
        $result = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lt($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            $monthEndString = $monthEnd->format('Y-m-d 23:59:59');

            // Get total unpaid invoices at the end of this month
            $totalDebt = DB::table('facturas as f')
                ->join(DB::raw("(SELECT nro_documento, MAX(id) as max_id FROM facturas WHERE created_at <= '{$monthEndString}' GROUP BY nro_documento) as latest"), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 0)          // Not paid
                ->where('f.created_at', '<=', $monthEndString)
                ->sum('f.total');

            // New invoices this month
            $newDebt = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->whereBetween('f.created_at', [
                    $monthStart->format('Y-m-d 00:00:00'),
                    $monthEnd->format('Y-m-d 23:59:59')
                ])
                ->sum('f.total');

            // Payments made this month
            $paidAmount = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 1)          // Paid
                ->whereBetween('f.updated_at', [
                    $monthStart->format('Y-m-d 00:00:00'),
                    $monthEnd->format('Y-m-d 23:59:59')
                ])
                ->sum('f.total');

            $result[] = [
                'periodo' => $currentDate->format('Y-m'),
                'etiqueta' => $currentDate->format('M Y'),
                'deuda_total' => $totalDebt,
                'nuevas_facturas' => $newDebt,
                'pagos_realizados' => $paidAmount
            ];

            $currentDate->addMonth();
        }

        return $result;
    }

    /**
     * Generate weekly debt summary
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getWeeklySummary(Carbon $startDate, Carbon $endDate)
    {
        $result = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lt($endDate)) {
            $weekStart = $currentDate->copy()->startOfWeek();
            $weekEnd = $currentDate->copy()->endOfWeek();
            $weekEndString = $weekEnd->format('Y-m-d 23:59:59');

            // Get total unpaid invoices at the end of this week
            $totalDebt = DB::table('facturas as f')
                ->join(DB::raw("(SELECT nro_documento, MAX(id) as max_id FROM facturas WHERE created_at <= '{$weekEndString}' GROUP BY nro_documento) as latest"), 
                    function ($join) {
                        $join->on('f.nro_documento', '=', 'latest.nro_documento')
                            ->on('f.id', '=', 'latest.max_id');
                    })
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 0)          // Not paid
                ->where('f.created_at', '<=', $weekEndString)
                ->sum('f.total');

            // New invoices this week
            $newDebt = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->whereBetween('f.created_at', [
                    $weekStart->format('Y-m-d 00:00:00'),
                    $weekEnd->format('Y-m-d 23:59:59')
                ])
                ->sum('f.total');

            // Payments made this week
            $paidAmount = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 1)          // Paid
                ->whereBetween('f.updated_at', [
                    $weekStart->format('Y-m-d 00:00:00'),
                    $weekEnd->format('Y-m-d 23:59:59')
                ])
                ->sum('f.total');

            $result[] = [
                'periodo' => $weekStart->format('Y-m-d') . ' - ' . $weekEnd->format('Y-m-d'),
                'etiqueta' => 'W' . $weekStart->format('W'),
                'deuda_total' => $totalDebt,
                'nuevas_facturas' => $newDebt,
                'pagos_realizados' => $paidAmount
            ];

            $currentDate->addWeek();
        }

        return $result;
    }

    /**
     * Generate payment trend data
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getPaymentTrendData(Carbon $startDate, Carbon $endDate)
    {
        $result = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lt($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();

            // New invoices this month
            $newDebt = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->whereBetween('f.fecha_factura', [
                    $monthStart->format('Y-m-d'),
                    $monthEnd->format('Y-m-d')
                ])
                ->sum('f.total');

            // Payments made this month (based on pay date, not invoice date)
            $paidAmount = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 1)          // Paid
                ->whereBetween('f.updated_at', [
                    $monthStart->format('Y-m-d 00:00:00'),
                    $monthEnd->format('Y-m-d 23:59:59')
                ])
                ->sum('f.total');

            // Count invoices
            $newInvoiceCount = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->whereBetween('f.fecha_factura', [
                    $monthStart->format('Y-m-d'),
                    $monthEnd->format('Y-m-d')
                ])
                ->count();

            $paidInvoiceCount = DB::table('facturas as f')
                ->where('f.estadoDocumento', '!=', 6) // Not deleted
                ->where('f.pagado', '=', 1)          // Paid
                ->whereBetween('f.updated_at', [
                    $monthStart->format('Y-m-d 00:00:00'),
                    $monthEnd->format('Y-m-d 23:59:59')
                ])
                ->count();

            $result[] = [
                'periodo' => $currentDate->format('Y-m'),
                'etiqueta' => $currentDate->format('M Y'),
                'nuevas_facturas_monto' => $newDebt,
                'nuevas_facturas_cantidad' => $newInvoiceCount,
                'pagos_realizados_monto' => $paidAmount,
                'pagos_realizados_cantidad' => $paidInvoiceCount,
                'balance_mensual' => $paidAmount - $newDebt
            ];

            $currentDate->addMonth();
        }

        return $result;
    }
}