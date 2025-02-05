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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Hospitalario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ciclos as $ciclo)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $ciclo->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $ciclo->fecha_inicio }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $ciclo->status === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($ciclo->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $ciclo->porcentaje_hospitalario }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('ciclos.show', $ciclo) }}" class="text-indigo-600 hover:text-indigo-900">Ver Detalle</a>
                                        @if($ciclo->status === 'pendiente')
                                            <a href="{{ route('ciclos.edit', $ciclo) }}" class="ml-2 text-blue-600 hover:text-blue-900">Editar</a>
                                            <form action="{{ route('ciclos.destroy', $ciclo) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-2 text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        @endif
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
</x-app-layout>
