<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Product;
use App\Models\DetalleCiclo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        // Obtener todos los productos para las columnas
        $productos = Product::orderBy('name')->get();
        return view('reports.index', compact('productos'));
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
            // Obtener todos los productos ordenados
            $productos = Product::orderBy('name')->get();
            
            // Obtener los ciclos con sus detalles
            $cycles = Ciclo::with(['detalles.producto'])
                ->where(function($query) use ($request) {
                    $query->whereBetween('fecha_inicio', [$request->start_date, $request->end_date])
                        ->orWhereBetween('fecha_fin', [$request->start_date, $request->end_date]);
                })
                ->orderBy('fecha_inicio')
                ->get();

            $result = [
                'productos' => $productos->map(function($producto) {
                    return [
                        'id' => $producto->id,
                        'name' => $producto->name,
                        'valor' => $producto->valor
                    ];
                }),
                'ciclos' => []
            ];

            $totalPorProducto = array_fill_keys($productos->pluck('id')->toArray(), 0);
            $costoTotalGeneral = 0;

            foreach ($cycles as $ciclo) {
                $cantidadesPorProducto = array_fill_keys($productos->pluck('id')->toArray(), 0);
                $costoTotal = 0;

                foreach ($ciclo->detalles as $detalle) {
                    if ($detalle->producto && $detalle->cantidad_con_porcentaje > 0) {
                        $cantidadesPorProducto[$detalle->producto_id] += $detalle->cantidad_con_porcentaje;
                        $totalPorProducto[$detalle->producto_id] += $detalle->cantidad_con_porcentaje;
                        $costoTotal += $detalle->cantidad_con_porcentaje * ($detalle->producto->valor ?? 0);
                    }
                }

                $costoTotalGeneral += $costoTotal;

                $result['ciclos'][] = [
                    'id' => $ciclo->id,
                    'nombre' => $ciclo->nombre,
                    'fecha_inicio' => $ciclo->fecha_inicio ? $ciclo->fecha_inicio->format('Y-m-d') : null,
                    'fecha_fin' => $ciclo->fecha_fin ? $ciclo->fecha_fin->format('Y-m-d') : null,
                    'cantidades' => $cantidadesPorProducto,
                    'costo_total' => $costoTotal,
                    'estado' => $ciclo->estado
                ];
            }

            $result['totales'] = [
                'cantidades' => $totalPorProducto,
                'costo_total' => $costoTotalGeneral
            ];

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
