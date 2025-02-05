<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Product;
use App\Models\Representative;
use App\Models\MedicalSpecialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CicloController extends Controller
{
    public function index()
    {
        $ciclos = Ciclo::with('detallesCiclo')->latest()->paginate(10);
        return view('ciclos.index', compact('ciclos'));
    }

    public function create()
    {
        $representantes = Representative::with('doctors')->get();
        $especialidades = MedicalSpecialty::with('productos')->get();
        $productos = Product::all();
        
        return view('ciclos.create', compact('representantes', 'especialidades', 'productos'));
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
            ]);

            $ciclo = Ciclo::create([
                'fecha_inicio' => now(),
                'porcentaje_hospitalario' => $request->porcentaje_hospitalario
            ]);

            foreach ($request->detalles as $detalle) {
                $representante = Representative::findOrFail($detalle['representante_id']);
                $cantidadDoctores = $representante->doctors->sum('doctors_count');
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

                // Actualizar stock del producto
                $producto = Product::findOrFail($detalle['producto_id']);
                $producto->decrement('stock', $cantidadConPorcentaje);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Ciclo creado exitosamente']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear ciclo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el ciclo: ' . $e->getMessage()], 422);
        }
    }

    public function show(Ciclo $ciclo)
    {
        $detallesPorRepresentante = $ciclo->detallesCiclo()
            ->with(['representante.doctors', 'especialidad', 'producto'])
            ->get()
            ->groupBy('representante_id');

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

        return view('ciclos.show', compact('ciclo', 'detallesPorRepresentante', 'totalesPorProducto'));
    }

    public function generatePdf(Ciclo $ciclo)
    {
        $ciclo->load(['detallesCiclo.representante.doctors', 'detallesCiclo.especialidad', 'detallesCiclo.producto']);
        $detallesPorRepresentante = $ciclo->detallesCiclo->groupBy('representante_id');
        
        $pdf = \PDF::loadView('ciclos.pdf', [
            'ciclo' => $ciclo,
            'detallesPorRepresentante' => $detallesPorRepresentante
        ]);
        
        return $pdf->download('ciclo-' . $ciclo->id . '.pdf');
    }

    public function deliver(Ciclo $ciclo)
    {
        if ($ciclo->status !== 'pendiente') {
            return back()->with('error', 'Este ciclo ya ha sido entregado.');
        }

        try {
            DB::beginTransaction();
            
            // Verificar stock disponible
            $faltantes = [];
            foreach ($ciclo->detallesCiclo as $detalle) {
                $producto = $detalle->producto;
                if ($producto->quantity < $detalle->cantidad_con_porcentaje) {
                    $faltantes[] = [
                        'producto' => $producto->name,
                        'requerido' => $detalle->cantidad_con_porcentaje,
                        'disponible' => $producto->quantity
                    ];
                }
            }

            // Si hay faltantes, mostrar error
            if (!empty($faltantes)) {
                DB::rollBack();
                $mensaje = "<div class='space-y-4'>";
                $mensaje .= "<p class='font-semibold text-lg'>Los siguientes productos no tienen suficiente stock:</p>";
                $mensaje .= "<div class='space-y-2'>";
                
                // Agrupar por producto para evitar repeticiones
                $faltantesAgrupados = collect($faltantes)->groupBy('producto')->map(function($grupo) {
                    return [
                        'producto' => $grupo->first()['producto'],
                        'requeridos' => $grupo->pluck('requerido'),
                        'disponible' => $grupo->first()['disponible']
                    ];
                });

                foreach ($faltantesAgrupados as $faltante) {
                    $totalRequerido = $faltante['requeridos']->sum();
                    $mensaje .= "<div class='faltante-item'>";
                    $mensaje .= "<p class='font-medium'>{$faltante['producto']}</p>";
                    $mensaje .= "<div class='ml-4'>";
                    $mensaje .= "<p>Total Requerido: <span class='font-semibold'>{$totalRequerido}</span></p>";
                    $mensaje .= "<p>Disponible: <span class='font-semibold'>{$faltante['disponible']}</span></p>";
                    $mensaje .= "<p class='text-red-700'>Faltante: <span class='font-semibold'>" . ($totalRequerido - $faltante['disponible']) . "</span></p>";
                    $mensaje .= "</div>";
                    $mensaje .= "</div>";
                }
                
                $mensaje .= "</div></div>";
                return back()->with('error', $mensaje);
            }

            // Reducir stock
            foreach ($ciclo->detallesCiclo as $detalle) {
                $producto = $detalle->producto;
                $producto->quantity -= $detalle->cantidad_con_porcentaje;
                $producto->save();
            }

            // Actualizar estado del ciclo
            $ciclo->update([
                'status' => 'entregado',
                'delivered_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Ciclo entregado exitosamente y stock actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la entrega: ' . $e->getMessage());
        }
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
            return redirect()->route('ciclos.show', $ciclo)
                ->with('error', 'No se puede editar un ciclo que ya ha sido entregado.');
        }

        try {
            DB::beginTransaction();

            // Actualizar ciclo
            $ciclo->update([
                'porcentaje_hospitalario' => $request->porcentaje_hospitalario,
            ]);

            // Eliminar detalles antiguos
            $ciclo->detallesCiclo()->delete();

            // Crear nuevos detalles
            foreach ($request->detalles as $detalle) {
                $cantidadTotal = $detalle['cantidad_por_doctor'] * Representative::find($detalle['representante_id'])->doctors->sum('doctors_count');
                $cantidadConPorcentaje = $cantidadTotal + ($cantidadTotal * ($request->porcentaje_hospitalario / 100));

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
            return redirect()->route('ciclos.show', $ciclo)
                ->with('success', 'Ciclo actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el ciclo: ' . $e->getMessage());
        }
    }

    public function destroy(Ciclo $ciclo)
    {
        if ($ciclo->status !== 'pendiente') {
            return back()->with('error', 'No se puede eliminar un ciclo que ya ha sido entregado.');
        }

        try {
            DB::beginTransaction();
            $ciclo->detallesCiclo()->delete();
            $ciclo->delete();
            DB::commit();

            return redirect()->route('ciclos.index')
                ->with('success', 'Ciclo eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el ciclo: ' . $e->getMessage());
        }
    }
}
