<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\DetalleCiclo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function getCycleReport(Request $request)
    {
        Log::info('Received request for cycle report', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $cycles = Ciclo::with(['detalles.producto'])
                ->where(function($query) use ($request) {
                    $query->whereBetween('fecha_inicio', [$request->start_date, $request->end_date])
                        ->orWhereBetween('fecha_fin', [$request->start_date, $request->end_date]);
                })
                ->get();

            Log::info('Found cycles:', ['count' => $cycles->count()]);

            $result = [];
            foreach ($cycles as $ciclo) {
                Log::info('Processing cycle:', [
                    'id' => $ciclo->id,
                    'nombre' => $ciclo->nombre,
                    'detalles_count' => $ciclo->detalles->count()
                ]);

                $totalEntregado = 0;
                $costoTotal = 0;
                $productosAgrupados = [];

                foreach ($ciclo->detalles as $detalle) {
                    Log::info('Processing detalle:', [
                        'id' => $detalle->id,
                        'producto_id' => $detalle->producto_id,
                        'cantidad_por_doctor' => $detalle->cantidad_por_doctor,
                        'cantidad_total' => $detalle->cantidad_total,
                        'cantidad_con_porcentaje' => $detalle->cantidad_con_porcentaje,
                    ]);

                    $cantidadEntregada = $detalle->cantidad_con_porcentaje ?? 0;
                    
                    if ($detalle->producto && $cantidadEntregada > 0) {
                        $productoId = $detalle->producto->id;
                        $costo = $detalle->producto->valor ?? 0;

                        if (!isset($productosAgrupados[$productoId])) {
                            $productosAgrupados[$productoId] = [
                                'name' => $detalle->producto->name,
                                'cantidad_entregada' => 0,
                                'costo_unitario' => $costo,
                                'costo_total' => 0
                            ];
                        }

                        $productosAgrupados[$productoId]['cantidad_entregada'] += $cantidadEntregada;
                        $productosAgrupados[$productoId]['costo_total'] = 
                            $productosAgrupados[$productoId]['cantidad_entregada'] * $costo;

                        $totalEntregado += $cantidadEntregada;
                        $costoTotal += $cantidadEntregada * $costo;
                    }
                }

                // Convertir el array asociativo a un array indexado
                $productos = array_values($productosAgrupados);

                // Ordenar productos por nombre
                usort($productos, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });

                $result[] = [
                    'id' => $ciclo->id,
                    'nombre' => $ciclo->nombre,
                    'fecha_inicio' => $ciclo->fecha_inicio ? $ciclo->fecha_inicio->format('Y-m-d') : null,
                    'fecha_fin' => $ciclo->fecha_fin ? $ciclo->fecha_fin->format('Y-m-d') : null,
                    'total_entregado' => $totalEntregado,
                    'costo_total' => $costoTotal,
                    'estado' => $ciclo->estado,
                    'productos' => $productos
                ];

                Log::info('Cycle result:', [
                    'nombre' => $ciclo->nombre,
                    'total_entregado' => $totalEntregado,
                    'costo_total' => $costoTotal,
                    'productos_count' => count($productos)
                ]);
            }

            Log::info('Returning result', ['count' => count($result)]);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in getCycleReport:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
