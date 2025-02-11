<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ciclos de Entrega') }}
            </h2>
            <a href="{{ route('ciclos.create') }}" style="background-color: #0d6efd !important" class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Crear Nuevo Ciclo
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Ciclo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospitalario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ciclos as $ciclo)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">Ciclo {{ $ciclo->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $ciclo->fecha_inicio->format('Y-m-d') }}</td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $ciclo->status === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($ciclo->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $ciclo->porcentaje_hospitalario }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div style="padding-right: 9px;">
                                                <a href="{{ route('ciclos.show', $ciclo) }}" 
                                                   class="btn-action inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                   style="background-color: #0d6efd !important">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver
                                                </a>
                                            </div>
                                            @if($ciclo->status === 'pendiente')
                                                <div style="padding-right: 9px;">
                                                    <a href="{{ route('ciclos.edit', $ciclo) }}" 
                                                       class="btn-action inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                       style="background-color: #0d6efd !important">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Editar
                                                    </a>
                                                </div>
                                                <div>
                                                    <form action="{{ route('ciclos.destroy', $ciclo) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" 
                                                                onclick="confirmarEliminacion(this)"
                                                                class="btn-action inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $ciclos->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmarEliminacion(button) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
