<?php

namespace App\Http\Controllers\ResumenVer;

use App\Http\Controllers\Controller;
use App\Models\FormaPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FormaPagoController extends Controller
{
    /**
     * Obtiene formas de pago con posibilidad de filtrar por local y periodo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Parámetros de filtrado opcionales
            $local = $request->input('local');
            $periodo = $request->input('periodo');
            
            // Consulta base
            $query = FormaPago::select('forma_pagos.*')
                ->where('estado', 1); // Solo formas de pago activas
                
            // Filtrar por local si se especifica
            if ($local) {
                $query->where('local_id', $local);
            }
            
            // Filtrar por periodo si se especifica
            if ($periodo) {
                $query->whereDate('fecha_inicio', '<=', $periodo)
                      ->whereDate('fecha_fin', '>=', $periodo);
            }
            
            $formasPago = $query->orderBy('id', 'asc')->get();
            
            // Log::info(Auth::user()->name . " | Consulta API formas de pago");
            
            return response()->json([
                'status' => 'success',
                'data' => $formasPago
            ]);
        } catch (\Exception $e) {
            Log::error("Error en API formas de pago: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener formas de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene configuraciones disponibles para selección múltiple o simple
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfiguraciones()
    {
        try {
            $configuraciones = [
                'seleccion_multiple' => true,
                'opciones_maximas' => 2,
                'es_dinamico' => true
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $configuraciones
            ]);
        } catch (\Exception $e) {
            Log::error("Error en API configuraciones: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener configuraciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene formas de pago por ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $formaPago = FormaPago::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $formaPago
            ]);
        } catch (\Exception $e) {
            Log::error("Error en API forma de pago ID {$id}: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Forma de pago no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Guarda una nueva forma de pago
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|string|max:255',
                'estado' => 'required|integer',
                'fiscal' => 'required|integer',
                'opciones' => 'required|integer',
                'local_id' => 'nullable|integer',
                'periodo_inicio' => 'nullable|date',
                'periodo_fin' => 'nullable|date'
            ]);
            
            $formaPago = new FormaPago();
            $formaPago->tipo = $validated['tipo'];
            $formaPago->estado = $validated['estado'];
            $formaPago->fiscal = $validated['fiscal'];
            $formaPago->opciones = $validated['opciones'];
            
            if (isset($validated['local_id'])) {
                $formaPago->local_id = $validated['local_id'];
            }
            
            if (isset($validated['periodo_inicio'])) {
                $formaPago->fecha_inicio = $validated['periodo_inicio'];
            }
            
            if (isset($validated['periodo_fin'])) {
                $formaPago->fecha_fin = $validated['periodo_fin'];
            }
            
            $formaPago->save();
            
            // Log::info(Auth::user()->name . " | Creada forma de pago ID: " . $formaPago->id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Forma de pago creada correctamente',
                'data' => $formaPago
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error al crear forma de pago: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear forma de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualiza una forma de pago existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'sometimes|required|string|max:255',
                'estado' => 'sometimes|required|integer',
                'fiscal' => 'sometimes|required|integer',
                'opciones' => 'sometimes|required|integer',
                'local_id' => 'nullable|integer',
                'periodo_inicio' => 'nullable|date',
                'periodo_fin' => 'nullable|date'
            ]);
            
            $formaPago = FormaPago::findOrFail($id);
            
            if (isset($validated['tipo'])) {
                $formaPago->tipo = $validated['tipo'];
            }
            
            if (isset($validated['estado'])) {
                $formaPago->estado = $validated['estado'];
            }
            
            if (isset($validated['fiscal'])) {
                $formaPago->fiscal = $validated['fiscal'];
            }
            
            if (isset($validated['opciones'])) {
                $formaPago->opciones = $validated['opciones'];
            }
            
            if (isset($validated['local_id'])) {
                $formaPago->local_id = $validated['local_id'];
            }
            
            if (isset($validated['periodo_inicio'])) {
                $formaPago->fecha_inicio = $validated['periodo_inicio'];
            }
            
            if (isset($validated['periodo_fin'])) {
                $formaPago->fecha_fin = $validated['periodo_fin'];
            }
            
            $formaPago->save();
            
            // Log::info(Auth::user()->name . " | Actualizada forma de pago ID: " . $formaPago->id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Forma de pago actualizada correctamente',
                'data' => $formaPago
            ]);
        } catch (\Exception $e) {
            Log::error("Error al actualizar forma de pago ID {$id}: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar forma de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Elimina una forma de pago
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $formaPago = FormaPago::findOrFail($id);
            // Soft delete marcando como inactivo
            $formaPago->estado = 0;
            $formaPago->save();
            
            // Log::info(Auth::user()->name . " | Desactivada forma de pago ID: " . $formaPago->id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Forma de pago desactivada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al desactivar forma de pago ID {$id}: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al desactivar forma de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
