<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\MedicalSpecialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->paginate(12);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialties = MedicalSpecialty::all();
        return view('products.create', compact('specialties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validateProduct($request);

        $imagePath = $request->file('image')->store('products', 'public');

        Product::create([
            'name' => $validatedData['name'],
            'quantity' => $validatedData['quantity'],
            'valor' => $validatedData['valor'],
            'medical_specialty_id' => $validatedData['medical_specialty_id'],
            'image_path' => $imagePath
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $specialties = MedicalSpecialty::all();
        return view('products.edit', compact('product', 'specialties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $this->validateProduct($request);

        $data = $validatedData;

        if ($request->hasFile('image')) {
            // Eliminar la imagen anterior
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    protected function validateProduct(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'valor' => 'required|numeric|min:0',
            'medical_specialty_id' => 'required|exists:medical_specialties,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    }
}
