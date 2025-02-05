<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Ciclo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form id="ciclo-form">
                    @csrf
                    @method('PUT')

                    <!-- Paso 1: Selección de Representantes -->
                    <div class="mb-8" id="paso1">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Paso 1: Seleccionar Representantes</h3>
                            <button type="button" id="seleccionarTodos" class="text-blue-600 hover:text-blue-900">
                                Seleccionar Todos
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($representantes as $representante)
                            <div class="border rounded p-4">
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           name="representantes[]" 
                                           value="{{ $representante->id }}"
                                           class="form-checkbox h-5 w-5 text-blue-600"
                                           {{ $ciclo->detallesCiclo->contains('representante_id', $representante->id) ? 'checked' : '' }}>
                                    <span>
                                        <span class="text-gray-900 font-medium">{{ $representante->name }}</span>
                                        <span class="text-gray-500 text-sm block">
                                            Doctores: {{ $representante->doctors->sum('doctors_count') }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Paso 2: Configuración de Productos -->
                    <div class="mb-8 hidden" id="paso2">
                        <h3 class="text-lg font-semibold mb-4">Paso 2: Configurar Productos</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Porcentaje Hospitalario (%)</label>
                            <input type="number" 
                                   id="porcentaje_hospitalario" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                   value="{{ $ciclo->porcentaje_hospitalario }}"
                                   min="0"
                                   max="100">
                        </div>

                        <div id="especialidades-config">
                            @foreach($especialidades as $especialidad)
                            <div class="border rounded-lg p-4 mb-4">
                                <h4 class="font-semibold mb-2">{{ $especialidad->name }}</h4>
                                <div class="productos-dinamicos" data-especialidad-id="{{ $especialidad->id }}">
                                    @foreach($ciclo->detallesCiclo->where('especialidad_id', $especialidad->id) as $detalle)
                                    <div class="flex items-center space-x-4 mb-2">
                                        <select class="producto-select form-select rounded-md border-gray-300 flex-1">
                                            <option value="">Seleccionar Producto</option>
                                            @foreach($especialidad->products as $producto)
                                            <option value="{{ $producto->id }}" {{ $detalle->producto_id == $producto->id ? 'selected' : '' }}>
                                                {{ $producto->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <input type="number" 
                                               class="cantidad-input form-input rounded-md border-gray-300 w-32"
                                               placeholder="Cantidad"
                                               value="{{ $detalle->cantidad_por_doctor }}"
                                               min="1">
                                        <button type="button" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="this.parentElement.remove()">
                                            Eliminar
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button"
                                        class="mt-2 text-sm text-blue-600 hover:text-blue-900"
                                        onclick="agregarProducto(this.previousElementSibling)">
                                    + Agregar Producto
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botones de Navegación -->
                    <div class="flex justify-between">
                        <button type="button" 
                                id="anterior" 
                                class="hidden bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Anterior
                        </button>
                        <button type="button" 
                                id="siguiente" 
                                class="text-white px-4 py-2 rounded" 
                                style="background-color: #0d6efd !important;">
                            Siguiente
                        </button>
                        <button type="submit" 
                                id="confirmar" 
                                class="hidden text-white px-4 py-2 rounded"
                                style="background-color: #0d6efd !important;">
                            Confirmar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('ciclo-form');
            const paso1 = document.getElementById('paso1');
            const paso2 = document.getElementById('paso2');
            const btnAnterior = document.getElementById('anterior');
            const btnSiguiente = document.getElementById('siguiente');
            const btnConfirmar = document.getElementById('confirmar');
            const btnSeleccionarTodos = document.getElementById('seleccionarTodos');

            // Evento para Seleccionar Todos
            btnSeleccionarTodos.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('input[name="representantes[]"]');
                const todosSeleccionados = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !todosSeleccionados);
            });

            btnSiguiente.addEventListener('click', function() {
                const representantesSeleccionados = document.querySelectorAll('input[name="representantes[]"]:checked');
                if (representantesSeleccionados.length === 0) {
                    alert('Por favor, seleccione al menos un representante.');
                    return;
                }

                paso1.style.display = 'none';
                paso2.style.display = 'block';
                btnAnterior.style.display = 'block';
                btnSiguiente.style.display = 'none';
                btnConfirmar.style.display = 'block';
            });

            btnAnterior.addEventListener('click', function() {
                paso1.style.display = 'block';
                paso2.style.display = 'none';
                btnAnterior.style.display = 'none';
                btnSiguiente.style.display = 'block';
                btnConfirmar.style.display = 'none';
            });

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    // Recopilar datos del formulario
                    const formData = {
                        representantes: [],
                        porcentaje_hospitalario: document.getElementById('porcentaje_hospitalario').value,
                        detalles: []
                    };

                    // Obtener representantes seleccionados
                    document.querySelectorAll('input[name="representantes[]"]:checked').forEach(rep => {
                        formData.representantes.push(rep.value);
                    });

                    if (formData.representantes.length === 0) {
                        alert('Por favor, seleccione al menos un representante.');
                        return;
                    }

                    // Obtener productos por especialidad
                    document.querySelectorAll('#especialidades-config .border').forEach(especialidadDiv => {
                        const especialidadId = especialidadDiv.querySelector('.productos-dinamicos').dataset.especialidadId;
                        
                        especialidadDiv.querySelectorAll('.productos-dinamicos .flex').forEach(productoDiv => {
                            const productoSelect = productoDiv.querySelector('.producto-select');
                            const cantidadInput = productoDiv.querySelector('.cantidad-input');
                            
                            if (productoSelect.value && cantidadInput.value) {
                                // Crear un detalle para cada representante seleccionado
                                formData.representantes.forEach(representanteId => {
                                    formData.detalles.push({
                                        representante_id: representanteId,
                                        especialidad_id: especialidadId,
                                        producto_id: productoSelect.value,
                                        cantidad_por_doctor: parseInt(cantidadInput.value)
                                    });
                                });
                            }
                        });
                    });

                    // Validar que haya al menos un producto configurado
                    if (formData.detalles.length === 0) {
                        alert('Por favor, configure al menos un producto para continuar.');
                        return;
                    }

                    const response = await fetch('{{ route('ciclos.update', $ciclo) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error al actualizar el ciclo');
                    }

                    if (data.success) {
                        window.location.href = '{{ route('ciclos.show', $ciclo) }}';
                    } else {
                        throw new Error(data.message || 'Error al actualizar el ciclo');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Hubo un error al actualizar el ciclo. Por favor, intente nuevamente.');
                }
            });

            // Template para nuevo producto
            window.agregarProducto = function(productosDiv) {
                const especialidadId = productosDiv.dataset.especialidadId;
                const productos = {!! $especialidades->find(1)->products->pluck('name', 'id') !!};
                
                const nuevoProducto = document.createElement('div');
                nuevoProducto.className = 'flex items-center space-x-4 mb-2';
                nuevoProducto.innerHTML = `
                    <select class="producto-select form-select rounded-md border-gray-300 flex-1">
                        <option value="">Seleccionar Producto</option>
                        ${Object.entries(productos).map(([id, name]) => `
                            <option value="${id}">${name}</option>
                        `).join('')}
                    </select>
                    <input type="number" 
                           class="cantidad-input form-input rounded-md border-gray-300 w-32"
                           placeholder="Cantidad"
                           min="1">
                    <button type="button" 
                            class="text-red-600 hover:text-red-900"
                            onclick="this.parentElement.remove()">
                        Eliminar
                    </button>
                `;
                
                productosDiv.appendChild(nuevoProducto);
            };
        });
    </script>
    @endpush
</x-app-layout>
