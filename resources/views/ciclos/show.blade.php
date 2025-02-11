<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Ciclo
            </h2>
            <div class="flex space-x-4">
                @if($ciclo->status === 'pendiente')
                <form action="{{ route('ciclos.deliver', $ciclo) }}" method="POST" class="inline" id="deliverForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <input type="text" 
                               name="numero_descargo"
                               class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2" 
                               placeholder="Ingrese # de descargo"
                               required>
                        <button type="submit" class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important;">
                            Efectuar Entrega
                        </button>
                    </div>
                </form>
                @endif
                <div>
                    <a href="{{ route('ciclos.pdf', $ciclo) }}" class="text-white font-bold py-2 px-4 rounded" style="background-color: #dc3545 !important;">
                        Descargar PDF
                    </a>
                </div>
                @if($ciclo->status !== 'pendiente')
                <div style="padding-left: 10px;">
                    <a href="{{ route('ciclos.invoice', $ciclo) }}" class="text-white font-bold py-2 px-4 rounded" style="background-color: #198754 !important;">
                        Notas por Representante
                    </a>
                </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg shadow-md" role="alert">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h3 class="text-lg font-bold text-red-800">Error en la Entrega</h3>
                    </div>
                    <div class="text-red-700">
                        {!! session('error') !!}
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Información General -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Información General</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Fecha</p>
                            <p class="font-medium">{{ $ciclo->fecha_inicio->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
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
                        @if($ciclo->status !== 'pendiente')
                        <div>
                            <p class="text-sm text-gray-600">Número de Descargo</p>
                            <p class="font-medium">{{ $ciclo->numero_descargo ?? 'No especificado' }}</p>
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
                                
                                @php
                                    $productos = $detalles->groupBy('producto_id');
                                    $especialidades = \App\Models\MedicalSpecialty::whereIn('id', $detalles->pluck('especialidad_id')->unique())->get();
                                @endphp

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                @foreach($especialidades as $especialidad)
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ $especialidad->name }}
                                                        <div class="text-xxs text-gray-400 normal-case">
                                                            ({{ $detalles->first()->representante->doctors->where('medical_specialty_id', $especialidad->id)->sum('doctors_count') }} )
                                                        </div>
                                                    </th>
                                                @endforeach
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospitalario</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><strong>TOTAL</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($productos as $productoId => $productoDetalles)
                                                @php
                                                    $producto = \App\Models\Product::find($productoId);
                                                    $totalProducto = 0;
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-2 whitespace-nowrap">{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                                                    @foreach($especialidades as $especialidad)
                                                        @php
                                                            $detalle = $productoDetalles->where('especialidad_id', $especialidad->id)->first();
                                                            if ($detalle) {
                                                                $totalProducto += $detalle->cantidad_con_porcentaje;
                                                            }
                                                        @endphp
                                                        <td class="px-4 py-2 whitespace-nowrap">
                                                            @if($detalle)
                                                                {{ round($detalle->cantidad_con_porcentaje) }}
                                                                <div class="text-xs text-gray-500">
                                                                    ({{ $detalle->cantidad_por_doctor }} und)
                                                                </div>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="px-4 py-2 whitespace-nowrap">
                                                        {{ round($totalProducto * ($ciclo->porcentaje_hospitalario / 100)) }}
                                                    </td>
                                                    <td class="px-4 py-2 whitespace-nowrap"><strong>{{ round($totalProducto + $totalProducto * ($ciclo->porcentaje_hospitalario / 100)) }}</strong></td>
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
                    <h3 class="text-lg font-semibold mb-4">Resumen Total</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            @php
                                $especialidades = \App\Models\MedicalSpecialty::whereIn('id', collect($detallesPorRepresentante)->flatten(1)->pluck('especialidad_id')->unique())->get();
                                $productosPorEspecialidad = collect($detallesPorRepresentante)
                                    ->flatten(1)
                                    ->groupBy('especialidad_id')
                                    ->map(function($grupo) {
                                        return $grupo->pluck('producto_id')->unique();
                                    });
                            @endphp
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Representante</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
                                    @foreach($especialidades as $especialidad)
                                        @php
                                            $numProductos = $productosPorEspecialidad->get($especialidad->id, collect())->count();
                                        @endphp
                                        <th colspan="{{ $numProductos }}" class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l">
                                            {{ $especialidad->name }}
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th class="px-4 py-2"></th>
                                    <th class="px-4 py-2"></th>
                                    @foreach($especialidades as $especialidad)
                                        @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                                            @php
                                                $producto = \App\Models\Product::find($productoId);
                                            @endphp
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-l">
                                                {{ $producto ? $producto->name : 'Producto eliminado' }}
                                            </th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($detallesPorRepresentante as $representanteId => $detalles)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $detalles->first()->representante->name }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $detalles->first()->representante->zone ?? 'Sin zona' }}
                                        </td>
                                        @foreach($especialidades as $especialidad)
                                            @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                                                @php
                                                    $detalle = $detalles->first(function($d) use ($especialidad, $productoId) {
                                                        return $d->especialidad_id == $especialidad->id && $d->producto_id == $productoId;
                                                    });
                                                @endphp
                                                <td class="px-4 py-2 whitespace-nowrap text-center border-l">
                                                    {{ $detalle ? round($detalle->cantidad_con_porcentaje) : '-' }}
                                                </td>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                @endforeach
                                <!-- Fila de totales -->
                                <tr class="bg-gray-100">
                                    <td class="px-4 py-2 whitespace-nowrap">Hospitalario ({{ $ciclo->porcentaje_hospitalario }}%)</td>
                                    <td class="px-4 py-2 whitespace-nowrap"></td>
                                    @foreach($especialidades as $especialidad)
                                        @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                                            <td class="px-4 py-2 whitespace-nowrap text-center border-l">
                                                @php
                                                    $totalRegular = 0;
                                                    foreach($detallesPorRepresentante as $detalles) {
                                                        foreach($detalles as $detalle) {
                                                            if($detalle->especialidad_id == $especialidad->id && $detalle->producto_id == $productoId) {
                                                                $totalRegular += $detalle->cantidad_total;
                                                            }
                                                        }
                                                    }
                                                    $totalHospitalario = $totalRegular * ($ciclo->porcentaje_hospitalario / 100);
                                                @endphp
                                                {{ $totalHospitalario > 0 ? round($totalHospitalario) : '-' }}
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>

                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 whitespace-nowrap">Total</td>
                                    <td class="px-4 py-2 whitespace-nowrap"></td>
                                    @foreach($especialidades as $especialidad)
                                        @foreach($productosPorEspecialidad->get($especialidad->id, collect()) as $productoId)
                                            @php
                                                $total = collect($detallesPorRepresentante)
                                                    ->flatten(1)
                                                    ->where('especialidad_id', $especialidad->id)
                                                    ->where('producto_id', $productoId)
                                                    ->sum('cantidad_con_porcentaje');
                                            @endphp
                                            <td class="px-4 py-2 whitespace-nowrap text-center border-l">
                                                {{ $total ? round($total) : '-' }}
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                    <!-- Resumen Total por Productos -->
                    <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Productos Entregados</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Entregados</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $resumenPorProducto = collect($detallesPorRepresentante)
                                        ->flatten(1)
                                        ->groupBy('producto_id')
                                        ->map(function ($grupo) {
                                            return $grupo->sum('cantidad_con_porcentaje');
                                        });
                                @endphp
                        
                                @foreach($resumenPorProducto as $productoId => $total)
                                    @php
                                        $producto = \App\Models\Product::find($productoId);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ round($total) }}</td>
                                    </tr>
                                @endforeach

                                <tr class="bg-gray-100">
                                    <td class="px-4 py-2 whitespace-nowrap">Hospitalario ({{ $ciclo->porcentaje_hospitalario }}%)</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ round($resumenPorProducto->sum() * ($ciclo->porcentaje_hospitalario / 100)) }}</td>
                                </tr>
                        
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 whitespace-nowrap">Total General</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ round($resumenPorProducto->sum() * (1 + $ciclo->porcentaje_hospitalario / 100)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliverForm = document.querySelector('#deliverForm');
    const numeroDescargoInput = document.querySelector('input[name="numero_descargo"]');
    
    if (deliverForm && numeroDescargoInput) {
        deliverForm.addEventListener('submit', function(e) {
            const valor = numeroDescargoInput.value.trim();
            if (!valor) {
                e.preventDefault();
                alert('Debe ingresar el número de descargo antes de efectuar la entrega');
                numeroDescargoInput.focus();
                numeroDescargoInput.classList.add('border-red-500');
            }
        });

        numeroDescargoInput.addEventListener('input', function() {
            this.classList.remove('border-red-500');
        });
    }
});
</script>
@endpush
