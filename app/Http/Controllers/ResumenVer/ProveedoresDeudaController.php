<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Facturas;
use App\Models\Proveedores;
use Carbon\Carbon;

class ProveedoresDeudaController extends Controller
{
    /**
     * Obtener deuda total de proveedores a la fecha
     * Saldo pendiente de pago de facturas ($), agrupa y proveedor, order por proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeudaTotal()
    {
        try {
            // Obtener últimas facturas por proveedor con saldo pendiente
            $deudaPorProveedor = Facturas::join('proveedores', 'facturas.proveedor', '=', 'proveedores.nro_proveedor')
                ->join(
                    DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as ultimo_doc'),
                    function ($join) {
                        $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                        $join->on('facturas.id', '=', 'ultimo_doc.idMax');
                    }
                )
                ->select(
                    'facturas.proveedor',
                    'proveedores.nombre',
                    DB::raw('SUM(facturas.saldo) as deuda_total'),
                    DB::raw('COUNT(facturas.id) as cantidad_facturas')
                )
                ->where('facturas.saldo', '>', 0)
                ->where('facturas.estadoDocumento', '!=', 6) // No borrados
                ->groupBy('facturas.proveedor', 'proveedores.nombre')
                ->orderBy('proveedores.nombre')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $deudaPorProveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener deuda total: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener pasivo de facturas actual
     * Total de $ de Facturas cargadas (validadas y no)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPasivoFacturas()
    {
        try {
            // Obtener el total de facturas cargadas (validadas y no validadas)
            $pasivoFacturas = DB::table('facturas')
                ->join(
                    DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as ultimo_doc'),
                    function ($join) {
                        $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                        $join->on('facturas.id', '=', 'ultimo_doc.idMax');
                    }
                )
                ->select(
                    DB::raw('COALESCE(SUM(facturas.total), 0) as total_pasivo'),
                    DB::raw('COALESCE(SUM(CASE WHEN facturas.pagado = 1 THEN facturas.total ELSE 0 END), 0) as total_pagado'),
                    DB::raw('COALESCE(SUM(CASE WHEN facturas.pagado = 0 THEN facturas.total ELSE 0 END), 0) as total_pendiente'),
                    DB::raw('COUNT(facturas.id) as cantidad_facturas')
                )
                ->where('facturas.estadoDocumento', '!=', 6) // No borrados
                ->first();

            // Ensure we always have proper data format even if query returns no records
            $result = [
                'total_pasivo' => $pasivoFacturas->total_pasivo ?? 0,
                'total_pagado' => $pasivoFacturas->total_pagado ?? 0,
                'total_pendiente' => $pasivoFacturas->total_pendiente ?? 0,
                'cantidad_facturas' => $pasivoFacturas->cantidad_facturas ?? 0
            ];

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener pasivo de facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener históricos semanales y mensuales de deuda
     * Históricos Semanales y Mensuales de deuda y pagos, a lo largo del tiempo, para ver cómo se le va pagando a cada proveedor
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistoricoPagos(Request $request)
    {
        try {
            // Validar datos de entrada
            $request->validate([
                'proveedor_id' => 'nullable|integer',
                'periodo' => 'required|in:semanal,mensual',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            // Definir fechas de inicio y fin
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : Carbon::now();
            $fechaInicio = $request->fecha_inicio
                ? Carbon::parse($request->fecha_inicio)
                : $request->periodo === 'semanal'
                ? $fechaFin->copy()->subWeeks(12)
                : $fechaFin->copy()->subMonths(12);

            // Construir query base
            $query = Facturas::join('proveedores', 'facturas.proveedor', '=', 'proveedores.nro_proveedor')
                ->whereBetween('facturas.fecha_limite', [$fechaInicio, $fechaFin])
                ->where('facturas.estadoDocumento', '!=', 6); // No borrados

            // Filtrar por proveedor si se especifica
            if ($request->proveedor_id) {
                $query->where('facturas.proveedor', $request->proveedor_id);
            }

            // Agrupar por periodo (semanal o mensual)
            if ($request->periodo === 'semanal') {
                $query->select(
                    DB::raw('YEARWEEK(facturas.fecha_limite, 1) as periodo'),
                    DB::raw('MIN(facturas.fecha_limite) as fecha_inicio_periodo'),
                    DB::raw('MAX(facturas.fecha_limite) as fecha_fin_periodo'),
                    'facturas.proveedor',
                    'proveedores.nombre',
                    DB::raw('SUM(facturas.total) as monto_total'),
                    DB::raw('SUM(CASE WHEN facturas.pagado = 1 THEN facturas.total ELSE 0 END) as monto_pagado'),
                    DB::raw('SUM(CASE WHEN facturas.pagado = 0 THEN facturas.total ELSE 0 END) as monto_pendiente')
                )
                    ->groupBy('periodo', 'facturas.proveedor', 'proveedores.nombre')
                    ->orderBy('periodo')
                    ->orderBy('proveedores.nombre');
            } else {
                $query->select(
                    DB::raw('DATE_FORMAT(facturas.fecha_limite, "%Y-%m") as periodo'),
                    DB::raw('MIN(facturas.fecha_limite) as fecha_inicio_periodo'),
                    DB::raw('MAX(facturas.fecha_limite) as fecha_fin_periodo'),
                    'facturas.proveedor',
                    'proveedores.nombre',
                    DB::raw('SUM(facturas.total) as monto_total'),
                    DB::raw('SUM(CASE WHEN facturas.pagado = 1 THEN facturas.total ELSE 0 END) as monto_pagado'),
                    DB::raw('SUM(CASE WHEN facturas.pagado = 0 THEN facturas.total ELSE 0 END) as monto_pendiente')
                )
                    ->groupBy('periodo', 'facturas.proveedor', 'proveedores.nombre')
                    ->orderBy('periodo')
                    ->orderBy('proveedores.nombre');
            }

            $historico = $query->get();

            // Procesar resultado para formato adecuado de gráficos
            $proveedores = [];
            $periodos = [];
            $dataset = [];

            foreach ($historico as $item) {
                if (!in_array($item->periodo, $periodos)) {
                    $periodos[] = $item->periodo;
                }

                if (!array_key_exists($item->proveedor, $proveedores)) {
                    $proveedores[$item->proveedor] = $item->nombre;
                }

                if (!isset($dataset[$item->proveedor])) {
                    $dataset[$item->proveedor] = [
                        'nombre' => $item->nombre,
                        'total' => [],
                        'pagado' => [],
                        'pendiente' => []
                    ];
                }

                $dataset[$item->proveedor]['total'][$item->periodo] = $item->monto_total;
                $dataset[$item->proveedor]['pagado'][$item->periodo] = $item->monto_pagado;
                $dataset[$item->proveedor]['pendiente'][$item->periodo] = $item->monto_pendiente;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'periodos' => $periodos,
                    'proveedores' => $proveedores,
                    'dataset' => $dataset,
                    'raw' => $historico
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener histórico de pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de deuda de un proveedor específico
     *
     * @param int $proveedorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetalleProveedor($proveedorId)
    {
        try {
            // Validar que el proveedor existe
            $proveedor = Proveedores::where('nro_proveedor', $proveedorId)->first();

            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            // Obtener facturas pendientes del proveedor
            $facturasPendientes = Facturas::join(
                DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as ultimo_doc'),
                function ($join) {
                    $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                    $join->on('facturas.id', '=', 'ultimo_doc.idMax');
                }
            )
                ->select(
                    'facturas.id',
                    'facturas.nro_documento',
                    'facturas.nro_factura',
                    'facturas.fecha_factura',
                    'facturas.fecha_limite',
                    'facturas.total',
                    'facturas.saldo',
                    'facturas.pagado',
                    'facturas.estadoDocumento'
                )
                ->where('facturas.proveedor', $proveedorId)
                ->where('facturas.saldo', '>', 0)
                ->where('facturas.estadoDocumento', '!=', 6) // No borrados
                ->orderBy('facturas.fecha_limite')
                ->get();

            // Obtener histórico de pagos
            $historicoPagos = Facturas::join(
                DB::raw('(SELECT nro_documento, MAX(id) as idMax FROM facturas GROUP BY nro_documento) as ultimo_doc'),
                function ($join) {
                    $join->on('facturas.nro_documento', '=', 'ultimo_doc.nro_documento');
                    $join->on('facturas.id', '=', 'ultimo_doc.idMax');
                }
            )
                ->select(
                    DB::raw('YEAR(facturas.fecha_limite) as anio'),
                    DB::raw('MONTH(facturas.fecha_limite) as mes'),
                    DB::raw('SUM(facturas.total) as monto_total'),
                    DB::raw('SUM(facturas.total - facturas.saldo) as monto_pagado')
                )
                ->where('facturas.proveedor', $proveedorId)
                ->where('facturas.estadoDocumento', '!=', 6) // No borrados
                ->groupBy('anio', 'mes')
                ->orderBy('anio')
                ->orderBy('mes')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'proveedor' => $proveedor,
                    'facturas_pendientes' => $facturasPendientes,
                    'historico_pagos' => $historicoPagos
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener detalle del proveedor: ' . $e->getMessage()
            ], 500);
        }
    }
}