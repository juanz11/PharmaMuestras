<x-app-layout>
    <x-slot name="header">
        {{ __('Especialidades Médicas') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Formulario para crear nueva especialidad -->
                    <form method="POST" action="{{ route('medical-specialties.store') }}" class="mb-6">
                        @csrf
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <x-text-input
                                    type="text"
                                    name="name"
                                    class="w-full"
                                    placeholder="Nombre de la especialidad"
                                    required
                                />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-primary-button>
                                    {{ __('Agregar') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <!-- Lista de especialidades -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Especialidades Registradas</h3>
                        
                        @if($specialties->isEmpty())
                            <p class="text-gray-500">No hay especialidades registradas.</p>
                        @else
                            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                <ul class="divide-y divide-gray-200">
                                    @foreach($specialties as $specialty)
                                        <li class="px-6 py-4 flex items-center justify-between">
                                            <span class="text-gray-900">{{ $specialty->name }}</span>
                                            <form method="POST" action="{{ route('medical-specialties.destroy', $specialty) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('¿Estás seguro que deseas eliminar esta especialidad?')">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
