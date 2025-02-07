<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Nombre del Producto')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="quantity" :value="__('Cantidad')" />
                            <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity', $product->quantity)" required min="0" />
                            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="valor" :value="__('Valor')" />
                            <x-text-input id="valor" 
                                      name="valor" 
                                      type="number" 
                                      step="0.01" 
                                      class="mt-1 block w-full" 
                                      :value="old('valor', $product->valor)" 
                                      required />
                            <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="medical_specialty_id" :value="__('Especialidad Médica')" />
                            <select id="medical_specialty_id" 
                                   name="medical_specialty_id" 
                                   class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                   required>
                                <option value="">Seleccione una especialidad</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ (old('medical_specialty_id', $product->medical_specialty_id) == $specialty->id) ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('medical_specialty_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="image" :value="__('Imagen del Producto (Opcional)')" />
                            @if($product->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover">
                                    <p class="text-sm text-gray-600 mt-1">Imagen actual</p>
                                </div>
                            @endif
                            <input id="image" name="image" type="file" class="mt-1 block w-full" accept="image/*" />
                            <p class="text-sm text-gray-600 mt-1">Deja este campo vacío si no deseas cambiar la imagen</p>
                            <x-input-error class="mt-2" :messages="$errors->get('image')" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Actualizar Producto') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
