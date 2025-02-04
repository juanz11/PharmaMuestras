<?php

namespace App\Http\Controllers;

use App\Models\MedicalSpecialty;
use Illuminate\Http\Request;

class MedicalSpecialtyController extends Controller
{
    public function index()
    {
        $specialties = MedicalSpecialty::orderBy('name')->get();
        return view('medical-specialties.index', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:medical_specialties'
        ]);

        MedicalSpecialty::create([
            'name' => $request->name
        ]);

        return redirect()->route('medical-specialties.index')
            ->with('success', 'Especialidad médica creada exitosamente.');
    }

    public function destroy(MedicalSpecialty $medicalSpecialty)
    {
        $medicalSpecialty->delete();
        return redirect()->route('medical-specialties.index')
            ->with('success', 'Especialidad médica eliminada exitosamente.');
    }
}
