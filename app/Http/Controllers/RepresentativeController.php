<?php

namespace App\Http\Controllers;

use App\Models\Representative;
use App\Models\MedicalSpecialty;
use App\Models\RepresentativeDoctors;
use Illuminate\Http\Request;

class RepresentativeController extends Controller
{
    public function index()
    {
        $representatives = Representative::with('doctors.medicalSpecialty')->get();
        return view('representatives.index', compact('representatives'));
    }

    public function create()
    {
        $specialties = MedicalSpecialty::orderBy('name')->get();
        return view('representatives.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'doctors' => 'required|array',
            'doctors.*' => 'integer|min:0'
        ]);

        $representative = Representative::create([
            'name' => $request->name,
            'zone' => $request->zone,
        ]);

        // Obtener todas las especialidades
        $specialties = MedicalSpecialty::all();
        
        // Crear un registro para cada especialidad, incluso si no se envió en el request
        foreach ($specialties as $specialty) {
            RepresentativeDoctors::create([
                'representative_id' => $representative->id,
                'medical_specialty_id' => $specialty->id,
                'doctors_count' => $request->doctors[$specialty->id] ?? 0
            ]);
        }

        return redirect()->route('representatives.index')
            ->with('success', 'Representante creado exitosamente.');
    }

    public function edit(Representative $representative)
    {
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $doctorCounts = $representative->doctors->pluck('doctors_count', 'medical_specialty_id')->toArray();
        return view('representatives.edit', compact('representative', 'specialties', 'doctorCounts'));
    }

    public function update(Request $request, Representative $representative)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'doctors' => 'required|array',
            'doctors.*' => 'integer|min:0'
        ]);

        $representative->update([
            'name' => $request->name,
            'zone' => $request->zone,
        ]);

        // Actualizar el número de doctores para cada especialidad
        foreach ($request->doctors as $specialtyId => $count) {
            $representative->doctors()->updateOrCreate(
                ['medical_specialty_id' => $specialtyId],
                ['doctors_count' => $count]
            );
        }

        return redirect()->route('representatives.index')
            ->with('success', 'Representante actualizado exitosamente.');
    }

    public function destroy(Representative $representative)
    {
        $representative->delete();
        return redirect()->route('representatives.index')
            ->with('success', 'Representante eliminado exitosamente.');
    }
}
