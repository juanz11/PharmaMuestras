<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Ciclo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form id="ciclo-form">
                    @csrf
                    <div class="mb-8" id="paso1">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Paso 1: Seleccionar Representantes</h3>
                            <button type="button" id="seleccionarTodos" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Seleccionar Todos
                            </button>
                        </div>

                        <div class="grid grid-cols-3 gap-4" id="representantes-lista">
                            @if($representantes->isEmpty())
                                <div class="col-span-3 text-center py-4 text-gray-500">
                                    No hay representantes disponibles.
                                </div>
                            @else
                                @foreach($representantes as $representante)
                                <div class="border p-4 rounded">
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="representantes[]" value="{{ $representante->id }}" class="form-checkbox h-5 w-5 text-blue-600">
                                        <span>{{ $representante->name }}</span>
                                        <span class="text-sm text-gray-500">({{ $representante->doctors->sum('doctors_count') }} doctores)</span>
                                    </label>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="mb-8" id="paso2" style="display: none;">
                        <h3 class="text-lg font-semibold mb-4">Paso 2: Configurar Productos por Especialidad</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Porcentaje Hospitalario (%)</label>
                            <input type="number" id="porcentaje_hospitalario" name="porcentaje_hospitalario" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="0" min="0" max="100">
                        </div>
                        
                        <div id="especialidades-config">
                            @foreach($especialidades as $especialidad)
                            <div class="border p-4 rounded mb-4">
                                <h4 class="font-semibold mb-2">{{ $especialidad->name }}</h4>
                                <div class="space-y-4">
                                    <!-- Productos Recomendados -->
                                    @if($especialidad->products->isNotEmpty())
                                    <div class="bg-gray-50 p-3 rounded">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Productos Recomendados para esta Especialidad</label>
                                        <div class="space-y-2">
                                            @foreach($especialidad->products as $producto)
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox" 
                                                    class="producto-recomendado form-checkbox h-4 w-4 text-blue-600"
                                                    data-especialidad-id="{{ $especialidad->id }}"
                                                    data-producto-id="{{ $producto->id }}"
                                                    data-producto-nombre="{{ $producto->name }}">
                                                <span>{{ $producto->name }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Agregar Productos -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Agregar Productos</label>
                                        <div class="productos-dinamicos" data-especialidad-id="{{ $especialidad->id }}">
                                            <!-- Aquí se agregarán dinámicamente los productos -->
                                        </div>
                                        <button type="button" 
                                            class="agregar-producto mt-2 px-3 py-1 text-sm text-white rounded"
                                            style="background-color: #0d6efd !important;"
                                            data-especialidad-id="{{ $especialidad->id }}">
                                            + Agregar Producto
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-8" id="paso3" style="display: none;">
                        <h3 class="text-lg font-semibold mb-4">Paso 3: Resumen de Entrega</h3>
                        <div id="resumen-entrega" class="space-y-4">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button type="button" id="anterior" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded" style="display: none;">
                            Anterior
                        </button>
                        <button type="button" id="siguiente" class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important;">
                            Siguiente
                        </button>
                        <button type="submit" id="confirmar" class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important; display: none;">
                            Confirmar Ciclo
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

                    // Obtener el token CSRF directamente del formulario
                    const token = document.querySelector('input[name="_token"]').value;
                    
                    const response = await fetch('{{ route('ciclos.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Error al crear el ciclo');
                    }

                    if (data.success) {
                        window.location.href = '{{ route('ciclos.index') }}';
                    } else {
                        throw new Error(data.message || 'Error al crear el ciclo');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Hubo un error al crear el ciclo. Por favor, intente nuevamente.');
                }
            });

            // Template para nuevo producto
            function crearProductoTemplate(especialidadId) {
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2 mt-2';
                div.innerHTML = `
                    <select class="producto-select flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Seleccionar Producto</option>
                        @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="cantidad-input w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        placeholder="Cantidad" min="1" value="1">
                    <button type="button" class="eliminar-producto px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                return div;
            }

            // Agregar producto dinámicamente
            document.querySelectorAll('.agregar-producto').forEach(button => {
                button.addEventListener('click', function() {
                    const especialidadId = this.dataset.especialidadId;
                    const contenedor = document.querySelector(`.productos-dinamicos[data-especialidad-id="${especialidadId}"]`);
                    const nuevoProducto = crearProductoTemplate(especialidadId);
                    contenedor.appendChild(nuevoProducto);

                    // Evento para eliminar producto
                    nuevoProducto.querySelector('.eliminar-producto').addEventListener('click', function() {
                        nuevoProducto.remove();
                    });
                });
            });

            // Manejar productos recomendados
            document.querySelectorAll('.producto-recomendado').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const especialidadId = this.dataset.especialidadId;
                    const productoId = this.dataset.productoId;
                    const productoNombre = this.dataset.productoNombre;
                    const contenedor = document.querySelector(`.productos-dinamicos[data-especialidad-id="${especialidadId}"]`);

                    if (this.checked) {
                        const nuevoProducto = crearProductoTemplate(especialidadId);
                        contenedor.appendChild(nuevoProducto);
                        
                        // Seleccionar automáticamente el producto
                        const select = nuevoProducto.querySelector('.producto-select');
                        select.value = productoId;
                        select.disabled = true;
                    } else {
                        // Buscar y eliminar el producto si existe
                        const productos = contenedor.querySelectorAll('.producto-select');
                        productos.forEach(select => {
                            if (select.value === productoId) {
                                select.closest('.flex').remove();
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
