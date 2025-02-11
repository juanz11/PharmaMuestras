<?php

namespace App\Http\Controllers;

use App\Models\DetalleCiclo;
use App\Models\Ciclo;
use Illuminate\Http\Request;

class DetalleCicloController extends Controller
{
    public function updateNumeroDescargo(Request $request)
    {
        $request->validate([
            'numero_descargo' => 'required|string|max:255',
            'ciclo_id' => 'required|exists:ciclos,id',
            'representante_id' => 'required|exists:representantes,id'
        ]);

        $ciclo = Ciclo::findOrFail($request->ciclo_id);

        // Verificar que el ciclo esté en estado pendiente
        if ($ciclo->status !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede actualizar el número de descargo en ciclos pendientes'
            ], 422);
        }

        try {
            // Actualizar todos los detalles del representante en este ciclo
            $updated = DetalleCiclo::where('ciclo_id', $ciclo->id)
                ->where('representante_id', $request->representante_id)
                ->update(['numero_descargo' => $request->numero_descargo]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Número de descargo actualizado exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron registros para actualizar'
                ], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Error al actualizar número de descargo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el número de descargo'
            ], 500);
        }
    }
}
