<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" 
                          action="{{ route('products.store') }}" 
                          enctype="multipart/form-data" 
                          class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nombre del Producto')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="quantity" :value="__('Cantidad')" />
                            <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity')" required min="0" />
                            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="valor" :value="__('Valor')" />
                            <x-text-input id="valor" 
                                      name="valor" 
                                      type="number" 
                                      step="0.01" 
                                      class="mt-1 block w-full" 
                                      :value="old('valor')" 
                                      required />
                            <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <label for="medical_specialties" class="block text-sm font-medium text-gray-700">Especialidades Médicas</label>
                            <select name="medical_specialties[]" id="medical_specialties" multiple 
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @foreach($medicalSpecialties as $specialty)
                                    <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                @endforeach
                            </select>
                            @error('medical_specialties')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Mantén presionado Ctrl (Windows) o Command (Mac) para seleccionar múltiples especialidades</p>
                        </div>

                        <div>
                            <x-input-label for="image" :value="__('Imagen del Producto (Opcional)')" />
                            <input id="image" name="image" type="file" class="mt-1 block w-full" accept="image/*" />
                            <x-input-error class="mt-2" :messages="$errors->get('image')" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Crear Producto') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
