<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Ciclo
            </h2>
            <div class="flex space-x-4 items-center">
                @if($ciclo->status === 'pendiente')
                    <form action="{{ route('ciclos.completar-entrega', $ciclo) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important;">
                            Efectuar Entrega
                        </button>
                    </form>
                @endif
                <div>
                    <a href="{{ route('ciclos.pdf', $ciclo) }}" class="text-white font-bold py-2 px-4 rounded inline-block" style="background-color: #dc3545 !important;">
                        Descargar PDF
                    </a>
                </div>
                <div>
                    <a href="{{ route('ciclos.excel', $ciclo) }}" class="text-white font-bold py-2 px-4 rounded inline-block" style="background-color: #28a745 !important;">
                        Exportar Excel
                    </a>
                </div>
                @if($ciclo->status !== 'pendiente')
                <div>
                    <a href="{{ route('ciclos.invoice', $ciclo) }}" class="text-white font-bold py-2 px-4 rounded inline-block" style="background-color: #198754 !important;">
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
                    </div>
                </div>

                <!-- Detalles por Representante -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Detalles por Representante</h3>
                    <div class="space-y-6">
                        @foreach($detallesPorRepresentante as $representanteId => $detalles)
                            @php
                                $representante = $detalles->first()->representante;
                                $descargo = isset($descargos[$representanteId]) ? $descargos[$representanteId] : null;
                            @endphp
                            <div class="bg-white shadow rounded-lg p-6 mb-6">
                                <div class="grid grid-cols-2 gap-4">
                                   
                                    
                                </div>
                                <div class="flex justify-between items-center mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $representante->name }}</h3>
                                        <p class="text-sm text-gray-600">Zona: {{ $representante->zone ?? 'Sin zona' }}</p>
                                    </div>
                                    @if($ciclo->status === 'pendiente')
                                        <div class="flex items-center space-x-2">
                                            <button type="button" 
                                                onclick="mostrarModalDescargo('{{ $representante->id }}', '{{ $representante->name }}', '{{ $descargo ? $descargo->numero_descargo : '' }}')"
                                                class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important;">
                                                Registrar Descargo
                                            </button>
                                            @if($descargo)
                                                <span class="text-sm text-gray-600">(#{{ $descargo->numero_descargo }})</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-sm">
                                            <span class="font-medium">Número de Descargo:</span>
                                            <span class="ml-2">{{ $descargo ? $descargo->numero_descargo : 'No registrado' }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                @php
                                                    $especialidades = \App\Models\MedicalSpecialty::whereIn('id', $detalles->pluck('especialidad_id')->unique())->get();
                                                @endphp
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
                                            @php
                                                $productos = $detalles->groupBy('producto_id');
                                            @endphp
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
                                                                $totalProducto += $detalle->cantidad_total;
                                                            }
                                                        @endphp
                                                        <td class="px-4 py-2 whitespace-nowrap">
                                                            @if($detalle)
                                                                {{ round($detalle->cantidad_total) }}
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
                                // Obtener todos los productos únicos usados en el ciclo
                                $productos = collect($detallesPorRepresentante)
                                    ->flatten(1)
                                    ->pluck('producto_id')
                                    ->unique()
                                    ->map(function($productoId) {
                                        return \App\Models\Product::find($productoId);
                                    });
                            @endphp
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Representante</th>
                                    @foreach($productos as $producto)
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-l">
                                            {{ $producto ? $producto->name : 'Producto eliminado' }}
                                            @if($producto && $producto->valor)
                                                <div class="text-xxs text-gray-400 normal-case">
                                                    (${{ number_format($producto->valor, 2) }})
                                                </div>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-l">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($detallesPorRepresentante as $representanteId => $detalles)
                                    @php
                                        $valorTotalRepresentante = 0;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $detalles->first()->representante->name }}
                                        </td>
                                        @foreach($productos as $producto)
                                            @php
                                                $totalProducto = $detalles
                                                    ->where('producto_id', $producto->id)
                                                    ->sum('cantidad_total');
                                                $valorProducto = $producto && $producto->valor ? $totalProducto * $producto->valor : 0;
                                                $valorTotalRepresentante += $valorProducto;
                                            @endphp
                                            <td class="px-4 py-2 whitespace-nowrap text-center border-l">
                                                {{ $totalProducto > 0 ? round($totalProducto) : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-2 whitespace-nowrap text-right border-l">
                                            ${{ number_format($valorTotalRepresentante, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Fila de hospitalario -->
                                <tr class="bg-gray-100">
                                    <td class="px-4 py-2 whitespace-nowrap">Hospitalario ({{ $ciclo->porcentaje_hospitalario }}%)</td>
                                    @php
                                        $totalValorHospitalario = 0;
                                    @endphp
                                    @foreach($productos as $producto)
                                        @php
                                            $totalProducto = collect($detallesPorRepresentante)
                                                ->flatten(1)
                                                ->where('producto_id', $producto->id)
                                                ->sum('cantidad_total');
                                            $hospitalario = round($totalProducto * ($ciclo->porcentaje_hospitalario / 100));
                                            $valorHospitalario = $producto && $producto->valor ? $hospitalario * $producto->valor : 0;
                                            $totalValorHospitalario += $valorHospitalario;
                                        @endphp
                                        <td class="px-4 py-2 whitespace-nowrap text-center border-l">
                                            {{ $hospitalario > 0 ? $hospitalario : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-2 whitespace-nowrap text-right border-l">
                                        ${{ number_format($totalValorHospitalario, 2) }}
                                    </td>
                                </tr>
                                <!-- Fila de totales -->
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 whitespace-nowrap">Total</td>
                                    @php
                                        $valorTotalGeneral = 0;
                                    @endphp
                                    @foreach($productos as $producto)
                                        @php
                                            $totalProducto = collect($detallesPorRepresentante)
                                                ->flatten(1)
                                                ->where('producto_id', $producto->id)
                                                ->sum('cantidad_total');
                                            $granTotal = $totalProducto * (1 + ($ciclo->porcentaje_hospitalario / 100));
                                            $valorTotal = $producto && $producto->valor ? $granTotal * $producto->valor : 0;
                                            $valorTotalGeneral += $valorTotal;
                                        @endphp
                                        <td class="px-4 py-2 whitespace-nowrap text-center border-l">
                                            {{ $granTotal > 0 ? round($granTotal) : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-2 whitespace-nowrap text-right border-l font-bold">
                                        ${{ number_format($valorTotalGeneral, 2) }}
                                    </td>
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
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><strong>Valor Unitario</strong></th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><strong>Valor Total</strong></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $resumenPorProducto = collect($detallesPorRepresentante)
                                        ->flatten(1)
                                        ->groupBy('producto_id')
                                        ->map(function ($grupo) {
                                            return $grupo->sum('cantidad_total');
                                        });
                                    $totalValorGeneral = 0;
                                @endphp
                        
                                @foreach($resumenPorProducto as $productoId => $total)
                                    @php
                                        $producto = \App\Models\Product::find($productoId);
                                        $totalConHospitalario = round($total * (1 + $ciclo->porcentaje_hospitalario / 100));
                                        $valorTotal = $producto ? $totalConHospitalario * $producto->valor : 0;
                                        $totalValorGeneral += $valorTotal;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $producto ? $producto->name : 'Producto eliminado' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-center">{{ $totalConHospitalario }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right">
                                            @if($producto && $producto->valor)
                                                ${{ number_format($producto->valor, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right">
                                            @if($producto && $producto->valor)
                                                ${{ number_format($valorTotal, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 whitespace-nowrap">Total General</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-center">{{ round($resumenPorProducto->sum() * (1 + $ciclo->porcentaje_hospitalario / 100)) }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">-</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">${{ number_format($totalValorGeneral, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@if($ciclo->status === 'pendiente')
    <!-- Modal para registrar número de descargo -->
    <div id="modalDescargo" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 50;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Registrar Número de Descargo</h3>
                <form id="formDescargo" method="POST" action="{{ route('ciclos.deliver', $ciclo) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="descargos[0][representante_id]" id="representante_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Representante</label>
                        <p id="nombreRepresentante" class="text-gray-900"></p>
                        <p id="numeroActual" class="text-sm text-gray-600 mt-1"></p>
                    </div>
                    <div class="mb-4">
                        <label for="numero_descargo" class="block text-sm font-medium text-gray-700 mb-2">
                            Número de Descargo
                        </label>
                        <input type="text" 
                            name="descargos[0][numero_descargo]" 
                            id="numero_descargo" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            required>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                            onclick="cerrarModalDescargo()"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancelar
                        </button>
                        <button type="submit" 
                            class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important;">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function mostrarModalDescargo(representanteId, nombreRepresentante, numeroDescargo) {
            document.getElementById('modalDescargo').classList.remove('hidden');
            document.getElementById('representante_id').value = representanteId;
            document.getElementById('nombreRepresentante').textContent = nombreRepresentante;
            
            // Mostrar el número actual si existe
            const numeroActualElement = document.getElementById('numeroActual');
            if (numeroDescargo) {
                numeroActualElement.textContent = 'Número actual: ' + numeroDescargo;
                document.getElementById('numero_descargo').value = numeroDescargo;
            } else {
                numeroActualElement.textContent = 'Sin número de descargo registrado';
                document.getElementById('numero_descargo').value = '';
            }
        }

        function cerrarModalDescargo() {
            document.getElementById('modalDescargo').classList.add('hidden');
            document.getElementById('numero_descargo').value = '';
        }

        // Asegurarse de que el formulario use el método PUT
        document.getElementById('formDescargo').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            
            // Formatear los datos correctamente
            const data = {
                descargos: [{
                    representante_id: formData.get('descargos[0][representante_id]'),
                    numero_descargo: formData.get('descargos[0][numero_descargo]')
                }]
            };
            
            fetch(form.action, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al guardar el número de descargo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el número de descargo');
            });
        });
    </script>
@endif

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
