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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Especialidades Médicas</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @foreach($medicalSpecialties as $specialty)
                                    <div class="relative specialty-item">
                                        <input type="checkbox" 
                                               name="medical_specialties[]" 
                                               id="specialty_{{ $specialty->id }}"
                                               value="{{ $specialty->id }}"
                                               class="specialty-checkbox absolute w-0 h-0 opacity-0" 
                                               {{ in_array($specialty->id, old('medical_specialties', [])) ? 'checked' : '' }}>
                                        <label for="specialty_{{ $specialty->id }}" 
                                               class="specialty-label relative block p-3 rounded-lg border-2 cursor-pointer transition-all duration-200 hover:bg-gray-50">
                                            <!-- Check icon -->
                                            <div class="check-icon absolute top-2 right-2 w-4 h-4 hidden">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </div>
                                            <p class="font-medium">{{ $specialty->name }}</p>
                                            @if($specialty->description)
                                                <p class="text-xs opacity-75">{{ $specialty->description }}</p>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('medical_specialties')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">Haz clic en las especialidades para seleccionarlas o deseleccionarlas</p>
                        </div>

                        <style>
                            .specialty-item input:checked + label {
                                background-color: #0d6efd;
                                border-color: #0d6efd;
                                color: white;
                            }
                            .specialty-item input:checked + label .check-icon {
                                display: block;
                            }
                        </style>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const checkboxes = document.querySelectorAll('.specialty-checkbox');
                                checkboxes.forEach(checkbox => {
                                    updateCheckboxState(checkbox);
                                    checkbox.addEventListener('change', function() {
                                        updateCheckboxState(this);
                                    });
                                });
                            });

                            function updateCheckboxState(checkbox) {
                                const label = checkbox.nextElementSibling;
                                if (checkbox.checked) {
                                    label.style.backgroundColor = '#0d6efd';
                                    label.style.borderColor = '#0d6efd';
                                    label.style.color = 'white';
                                    label.querySelector('.check-icon').style.display = 'block';
                                } else {
                                    label.style.backgroundColor = '';
                                    label.style.borderColor = '';
                                    label.style.color = '';
                                    label.querySelector('.check-icon').style.display = 'none';
                                }
                            }
                        </script>

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
