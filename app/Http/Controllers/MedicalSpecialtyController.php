<?php

namespace App\Http\Controllers;

use App\Models\MedicalSpecialty;
use Illuminate\Http\Request;

class MedicalSpecialtyController extends Controller
{
    public function index()
    {
        $specialties = MedicalSpecialty::whereNull('deleted_at')->orderBy('name')->get();
        return view('medical-specialties.index', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:medical_specialties,name,NULL,id,deleted_at,NULL'
        ]);

        MedicalSpecialty::create([
            'name' => $request->name
        ]);

        return redirect()->route('medical-specialties.index')
            ->with('success', 'Especialidad médica creada exitosamente.');
    }

    public function destroy(MedicalSpecialty $medicalSpecialty)
    {
        try {
            $medicalSpecialty->delete();
            return redirect()->route('medical-specialties.index')
                ->with('success', 'Especialidad médica eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('medical-specialties.index')
                ->with('error', 'No se puede eliminar esta especialidad porque está siendo utilizada.');
        }
    }
}
