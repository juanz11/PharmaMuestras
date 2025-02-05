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
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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

                        <div>
                            <x-input-label for="category" :value="__('Categoría')" />
                            <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>

                        <div class="mb-4">
                            <label for="medical_specialty_id" class="block text-sm font-medium text-gray-700">Especialidad Médica</label>
                            @if($specialties->count() > 0)
                            <select id="medical_specialty_id" 
                                    name="medical_specialty_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Seleccionar Especialidad</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ old('medical_specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @else
                            <div class="mt-1 text-sm text-red-600">
                                No hay especialidades médicas disponibles. Por favor, <a href="{{ route('medical-specialties.index') }}" class="text-blue-600 hover:text-blue-800">cree una especialidad</a> primero.
                            </div>
                            @endif
                            @error('medical_specialty_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="image" :value="__('Imagen del Producto')" />
                            <input id="image" name="image" type="file" class="mt-1 block w-full" required accept="image/*" />
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
