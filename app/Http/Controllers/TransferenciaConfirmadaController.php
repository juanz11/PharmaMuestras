<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use Illuminate\Http\Request;

class TransferenciaConfirmadaController extends Controller
{
    public function index(Request $request)
    {
        $query = TransferenciaConfirmada::with(['transferencia.visitador']);

        if ($request->has(['fecha_inicio', 'fecha_fin'])) {
            $query->whereHas('transferencia', function($q) use ($request) {
                $q->whereBetween('fecha_transferencia', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            });
        }

        if ($request->has('visitador_id')) {
            $query->whereHas('transferencia', function($q) use ($request) {
                $q->where('visitador_id', $request->visitador_id);
            });
        }

        $transferencias = $query->get();

        $visitadores = Transferencia::select('visitador_id')
            ->with('visitador')
            ->distinct()
            ->get()
            ->pluck('visitador');

        return view('reports.transferencias', compact('transferencias', 'visitadores'));
    }
}
