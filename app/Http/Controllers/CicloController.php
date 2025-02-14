<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Product;
use App\Models\Representative;
use App\Models\CicloProduct;
use App\Models\DetalleCiclo;
use App\Models\MedicalSpecialty;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\CicloExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;

class CicloController extends Controller
{
    public function index()
    {
        $ciclos = Ciclo::with('detallesCiclo')->latest()->paginate(10);
        return view('ciclos.index', compact('ciclos'));
    }

    public function create()
    {
        $representantes = Representative::with(['doctors.medicalSpecialty.products'])->get();
        $especialidades = MedicalSpecialty::with('products')->get();
        $ciclosAnteriores = Ciclo::orderBy('created_at', 'desc')
            ->with(['detallesCiclo.producto.medicalSpecialties'])
            ->get();
        $productos = Product::all();
        
        // Obtener años únicos de los ciclos
        $años = Ciclo::selectRaw('YEAR(fecha_inicio) as año')
                    ->distinct()
                    ->orderBy('año', 'desc')
                    ->pluck('año');

        return view('ciclos.create', compact('representantes', 'especialidades', 'productos', 'ciclosAnteriores', 'años'));
    }

    public function getConfiguracion(Ciclo $ciclo)
    {
        $ciclo->load(['detallesCiclo.producto.medicalSpecialties']);
        
        return response()->json([
            'porcentaje_hospitalario' => $ciclo->porcentaje_hospitalario,
            'detalles' => $ciclo->detallesCiclo->map(function($detalle) {
                return [
                    'producto_id' => $detalle->producto_id,
                    'cantidad_por_doctor' => $detalle->cantidad_por_doctor,
                    'producto' => [
                        'medical_specialties' => $detalle->producto->medicalSpecialties
                    ]
                ];
            })
        ]);
    }

    public function getConfiguracionAnterior(Ciclo $ciclo)
    {
        $detalles = $ciclo->detallesCiclo()
            ->with(['producto', 'especialidad'])
            ->get()
            ->map(function ($detalle) {
                return [
                    'especialidad_id' => $detalle->especialidad_id,
                    'producto_id' => $detalle->producto_id,
                    'cantidad_por_doctor' => $detalle->cantidad_por_doctor
                ];
            });

        return response()->json([
            'success' => true,
            'porcentaje_hospitalario' => $ciclo->porcentaje_hospitalario,
            'detalles' => $detalles
        ]);
    }

    public function getConfiguracionPorNombre($nombre)
    {
        try {
            $ciclo = Ciclo::where('nombre', $nombre)
                         ->with(['detallesCiclo' => function($query) {
                             $query->select('id', 'ciclo_id', 'especialidad_id', 'producto_id', 'cantidad_por_doctor')
                                  ->distinct();
                         }])
                         ->first();

            if (!$ciclo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ciclo no encontrado'
                ], 404);
            }

            $detalles = $ciclo->detallesCiclo->map(function($detalle) {
                return [
                    'especialidad_id' => $detalle->especialidad_id,
                    'producto_id' => $detalle->producto_id,
                    'cantidad_por_doctor' => $detalle->cantidad_por_doctor
                ];
            })->unique(function ($item) {
                return $item['especialidad_id'] . '-' . $item['producto_id'];
            })->values();

            return response()->json([
                'success' => true,
                'detalles' => $detalles,
                'porcentaje_hospitalario' => $ciclo->porcentaje_hospitalario,
                'objetivo' => $ciclo->objetivo,
                'dias_habiles' => $ciclo->dias_habiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración del ciclo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validar los datos recibidos
            $request->validate([
                'representantes' => 'required|array|min:1',
                'porcentaje_hospitalario' => 'required|numeric|min:0|max:100',
                'detalles' => 'required|array|min:1',
                'nombre' => 'required|string'
            ]);

            $ciclo = Ciclo::create([
                'fecha_inicio' => now(),
                'fecha_fin' => null,
                'porcentaje_hospitalario' => $request->porcentaje_hospitalario,
                'nombre' => $request->nombre
            ]);

            foreach ($request->detalles as $detalle) {
                $representante = Representative::findOrFail($detalle['representante_id']);
                
                // Obtener la cantidad de doctores específica para esta especialidad
                $cantidadDoctores = $representante->doctors()
                    ->where('medical_specialty_id', $detalle['especialidad_id'])
                    ->value('doctors_count') ?? 0;

                $cantidadTotal = $detalle['cantidad_por_doctor'] * $cantidadDoctores;
                $cantidadConPorcentaje = ceil($cantidadTotal * (1 + ($request->porcentaje_hospitalario / 100)));

                $ciclo->detallesCiclo()->create([
                    'representante_id' => $detalle['representante_id'],
                    'especialidad_id' => $detalle['especialidad_id'],
                    'producto_id' => $detalle['producto_id'],
                    'cantidad_por_doctor' => $detalle['cantidad_por_doctor'],
                    'cantidad_total' => $cantidadTotal,
                    'cantidad_con_porcentaje' => $cantidadConPorcentaje
                ]);

                // Solo actualizar el stock si hay doctores para esta especialidad
                if ($cantidadTotal > 0) {
                    $producto = Product::findOrFail($detalle['producto_id']);
                    $producto->decrement('stock', $cantidadConPorcentaje);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ciclo creado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear ciclo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el ciclo: ' . $e->getMessage()
            ], 422);
        }
    }

    public function show(Ciclo $ciclo)
    {
        $detallesPorRepresentante = $ciclo->detallesCiclo()
            ->with(['representante.doctors', 'especialidad', 'producto'])
            ->get()
            ->groupBy('representante_id');

        // Obtener los números de descargo por representante
        $descargos = $ciclo->descargos()
            ->with('representante')
            ->get()
            ->keyBy('representante_id');

        // Calcular totales por producto
        $totalesPorProducto = $ciclo->detallesCiclo()
            ->with(['producto', 'especialidad'])
            ->get()
            ->groupBy('producto_id')
            ->map(function($detalles) {
                return [
                    'producto' => $detalles->first()->producto,
                    'especialidad' => $detalles->first()->especialidad,
                    'total_base' => $detalles->sum('cantidad_total'),
                    'total_con_porcentaje' => $detalles->sum('cantidad_con_porcentaje')
                ];
            });

        return view('ciclos.show', compact('ciclo', 'detallesPorRepresentante', 'totalesPorProducto', 'descargos'));
    }

    public function generatePdf(Ciclo $ciclo)
    {
        $ciclo->load(['detallesCiclo.representante.doctors', 'detallesCiclo.especialidad', 'detallesCiclo.producto']);
        $detallesPorRepresentante = $ciclo->detallesCiclo->groupBy('representante_id');
        
        $pdf = Pdf::loadView('ciclos.pdf', [
            'ciclo' => $ciclo,
            'detallesPorRepresentante' => $detallesPorRepresentante
        ]);
        
        return $pdf->download('ciclo-' . $ciclo->id . '.pdf');
    }

    public function generateInvoice(Ciclo $ciclo)
    {
        $ciclo->load([
            'detalles.producto.medicalSpecialties',
            'detalles.representante',
            'detalles.especialidad',
            'descargos'
        ]);
        
        $pdf = Pdf::loadView('ciclos.invoice', compact('ciclo'));
        return $pdf->stream('nota_entrega.pdf');
    }

    public function deliver(Request $request, Ciclo $ciclo)
    {
        if ($ciclo->status !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden entregar ciclos en estado pendiente.'
            ], 422);
        }

        try {
            $request->validate([
                'descargos' => 'required|array',
                'descargos.*.representante_id' => 'required|exists:representatives,id',
                'descargos.*.numero_descargo' => 'required|string|max:255'
            ]);

            DB::beginTransaction();
            
            // Guardar o actualizar los números de descargo por representante
            foreach ($request->descargos as $descargo) {
                $ciclo->descargos()->updateOrCreate(
                    [
                        'ciclo_id' => $ciclo->id,
                        'representante_id' => $descargo['representante_id']
                    ],
                    [
                        'numero_descargo' => $descargo['numero_descargo']
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Número de descargo registrado exitosamente.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al registrar descargo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el número de descargo: ' . $e->getMessage()
            ], 422);
        }
    }

    public function completarEntrega(Request $request, Ciclo $ciclo)
    {
        if ($ciclo->status !== 'pendiente') {
            return back()->with('error', 'Solo se pueden entregar ciclos en estado pendiente.');
        }

        // Verificar que todos los representantes tengan número de descargo
        $representantesConDescargo = $ciclo->descargos()->pluck('representante_id')->unique();
        $totalRepresentantes = $ciclo->detallesCiclo()
            ->select('representante_id')
            ->distinct()
            ->pluck('representante_id');

        if ($representantesConDescargo->count() !== $totalRepresentantes->count()) {
            return back()->with('error', 'Todos los representantes deben tener un número de descargo antes de completar la entrega.');
        }

        try {
            DB::beginTransaction();

            // Agrupar los detalles por producto para calcular totales
            $totalesPorProducto = $ciclo->detallesCiclo()
                ->select('producto_id')
                ->selectRaw('SUM(cantidad_con_porcentaje) as total_cantidad')
                ->groupBy('producto_id')
                ->get();

            // Descontar del inventario
            foreach ($totalesPorProducto as $total) {
                $producto = Product::findOrFail($total->producto_id);
                
                Log::info("Actualizando inventario del producto {$producto->name}:");
                Log::info("Cantidad actual: {$producto->quantity}");
                Log::info("Cantidad a descontar: {$total->total_cantidad}");

                if ($producto->quantity < $total->total_cantidad) {
                    DB::rollback();
                    return back()->with('error', "No hay suficiente inventario del producto {$producto->name}. Disponible: {$producto->quantity}, Requerido: {$total->total_cantidad}");
                }

                $producto->quantity -= $total->total_cantidad;
                $producto->save();

                Log::info("Nueva cantidad: {$producto->quantity}");
            }

            $ciclo->update([
                'status' => 'entregado',
                'delivered_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Ciclo marcado como entregado exitosamente y el inventario ha sido actualizado.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al completar entrega: ' . $e->getMessage());
            return back()->with('error', 'Error al completar la entrega: ' . $e->getMessage());
        }
    }

    public function updateDescargo(Request $request, Ciclo $ciclo)
    {
        return response()->json([
            'success' => false,
            'message' => 'Esta funcionalidad ha sido reemplazada por el registro de descargos por representante.'
        ], 422);
    }

    public function getCiclosPorAño($año)
    {
        $ciclos = Ciclo::select('nombre', 'fecha_inicio')
                      ->whereYear('fecha_inicio', $año)
                      ->get()
                      ->unique('nombre')
                      ->values()
                      ->sortBy(function($ciclo) {
                          // Extraer el número del nombre del ciclo (e.j., "Ciclo 7" -> 7)
                          preg_match('/Ciclo (\d+)/', $ciclo->nombre, $matches);
                          return $matches[1] ?? 0;
                      })
                      ->values(); // Reindexar después del ordenamiento
        
        return response()->json($ciclos);
    }

    public function generarReporte(Ciclo $ciclo)
    {
        $ciclo->load('detallesCiclo.representante', 'detallesCiclo.especialidad', 'detallesCiclo.producto');
        return view('ciclos.reporte', compact('ciclo'));
    }

    public function edit(Ciclo $ciclo)
    {
        if ($ciclo->status !== 'pendiente') {
            return redirect()->route('ciclos.show', $ciclo)
                ->with('error', 'No se puede editar un ciclo que ya ha sido entregado.');
        }

        $ciclo->load('detallesCiclo.representante', 'detallesCiclo.especialidad', 'detallesCiclo.producto');
        $representantes = Representative::with('doctors')->get();
        $especialidades = MedicalSpecialty::with('products')->get();

        return view('ciclos.edit', compact('ciclo', 'representantes', 'especialidades'));
    }

    public function update(Request $request, Ciclo $ciclo)
    {
        if ($ciclo->status !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar un ciclo que ya ha sido entregado.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Actualizar ciclo
            $ciclo->update([
                'porcentaje_hospitalario' => $request->porcentaje_hospitalario,
                'nombre' => $request->nombre
            ]);

            // Eliminar todos los detalles existentes
            $ciclo->detallesCiclo()->delete();

            // Crear los nuevos detalles
            foreach ($request->detalles as $detalle) {
                $representante = Representative::findOrFail($detalle['representante_id']);
                
                // Obtener la cantidad de doctores específica para esta especialidad
                $cantidadDoctores = $representante->doctors()
                    ->where('medical_specialty_id', $detalle['especialidad_id'])
                    ->value('doctors_count') ?? 0;

                $cantidadTotal = $detalle['cantidad_por_doctor'] * $cantidadDoctores;
                $cantidadConPorcentaje = ceil($cantidadTotal * (1 + ($request->porcentaje_hospitalario / 100)));

                $ciclo->detallesCiclo()->create([
                    'representante_id' => $detalle['representante_id'],
                    'especialidad_id' => $detalle['especialidad_id'],
                    'producto_id' => $detalle['producto_id'],
                    'cantidad_por_doctor' => $detalle['cantidad_por_doctor'],
                    'cantidad_total' => $cantidadTotal,
                    'cantidad_con_porcentaje' => $cantidadConPorcentaje
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Ciclo actualizado exitosamente',
                'redirect_url' => route('ciclos.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar ciclo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el ciclo: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Ciclo $ciclo)
    {
        try {
            DB::beginTransaction();

            // Eliminar los descargos asociados
            $ciclo->descargos()->delete();
            
            // Eliminar los detalles asociados
            $ciclo->detallesCiclo()->delete();
            
            // Finalmente eliminar el ciclo
            $ciclo->delete();

            DB::commit();

            return redirect()->route('ciclos.index')
                ->with('success', 'Ciclo eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar ciclo: ' . $e->getMessage());
            return redirect()->route('ciclos.index')
                ->with('error', 'Error al eliminar el ciclo: ' . $e->getMessage());
        }
    }

    public function exportToExcel(Ciclo $ciclo)
    {
        return Excel::download(new CicloExport($ciclo), 'ciclo-' . $ciclo->id . '.xlsx');
    }
}
