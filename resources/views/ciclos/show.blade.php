<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Ciclo
            </h2>
            @if($ciclo->status === 'pendiente')
            <form action="{{ route('ciclos.deliver', $ciclo) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <button type="submit" class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important;">
                    Efectuar Entrega
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Información General -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Información General</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Fecha de Inicio</p>
                            <p class="font-medium">{{ $ciclo->fecha_inicio->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Estado</p>
                            <p class="font-medium">
                                @if($ciclo->status === 'pendiente')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Pendiente</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Entregado</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Porcentaje Hospitalario</p>
                            <p class="font-medium">{{ $ciclo->porcentaje_hospitalario }}%</p>
                        </div>
                        @if($ciclo->delivered_at)
                        <div>
                            <p class="text-sm text-gray-600">Fecha de Entrega</p>
                            <p class="font-medium">{{ $ciclo->delivered_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Detalles por Representante -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Detalles por Representante</h3>
                    <div class="space-y-6">
                        @foreach($detallesPorRepresentante as $representanteId => $detalles)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-semibold text-lg mb-2">{{ $detalles->first()->representante->name }}</h4>
                                <p class="text-sm text-gray-600 mb-4">
                                    Total de doctores: {{ $detalles->first()->representante->doctors->sum('doctors_count') }}
                                </p>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad por Doctor</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad Total</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Con % Hospitalario</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($detalles as $detalle)
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $detalle->especialidad->name }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $detalle->producto->name }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $detalle->cantidad_por_doctor }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $detalle->cantidad_total }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $detalle->cantidad_con_porcentaje }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Resumen Total por Especialidad -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Resumen Total por Especialidad</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Productos Entregados</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $totalesPorEspecialidad = collect($detallesPorRepresentante)
                                        ->flatten(1)
                                        ->groupBy('especialidad.name')
                                        ->map(function ($grupo) {
                                            return $grupo->sum('cantidad_con_porcentaje');
                                        });
                                @endphp
                                
                                @foreach($totalesPorEspecialidad as $especialidad => $total)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $especialidad }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $total }}</td>
                                </tr>
                                @endforeach
                                
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 whitespace-nowrap">Total General</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $totalesPorEspecialidad->sum() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
