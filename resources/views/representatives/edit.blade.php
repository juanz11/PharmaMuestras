<x-app-layout>
    <x-slot name="header">
        {{ __('Editar Representante') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('representatives.update', $representative) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Información básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" value="Nombre" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $representative->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="zone" value="Zona" />
                                <x-text-input id="zone" name="zone" type="text" class="mt-1 block w-full" :value="old('zone', $representative->zone)" required />
                                <x-input-error :messages="$errors->get('zone')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Cantidad de médicos por especialidad -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Cantidad de Médicos por Especialidad</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($specialties as $specialty)
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <label for="doctors[{{ $specialty->id }}]" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ $specialty->name }}
                                        </label>
                                        <input type="number" 
                                               name="doctors[{{ $specialty->id }}]" 
                                               id="doctors[{{ $specialty->id }}]"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                               min="0"
                                               value="{{ old('doctors.' . $specialty->id, $doctorCounts[$specialty->id] ?? 0) }}"
                                               required>
                                        <x-input-error :messages="$errors->get('doctors.' . $specialty->id)" class="mt-2" />
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('representatives.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    style="background-color: #0d6efd !important;"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:opacity-90 active:opacity-80 focus:outline-none focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Actualizar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
