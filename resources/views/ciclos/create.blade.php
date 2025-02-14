@php
    use App\Helpers\NumberToRoman;
@endphp

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
                        <h3 class="text-lg font-semibold mb-4">Configuración del Ciclo</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Ciclo:</label>
                            <select id="nombre_ciclo" name="nombre_ciclo" class="w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Seleccionar número...</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="Ciclo {{ $i }}">Ciclo {{ NumberToRoman::convert($i) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Objetivo visita diaria (1-7):
                                    <span class="text-xs text-gray-500 ml-1" title="Número de médicos a visitar por día">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                                </label>
                                <input type="number" 
                                    id="objetivo" 
                                    name="objetivo" 
                                    min="1" 
                                    max="7" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    required 
                                    value="7"
                                    title="Ingrese un número entre 1 y 7">
                                <div id="objetivo-error" class="text-red-500 text-xs mt-1 hidden">El objetivo debe estar entre 1 y 7</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Días Hábiles (max 31):
                                    <span class="text-xs text-gray-500 ml-1" title="Días laborables en el ciclo">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                                </label>
                                <input type="number" 
                                    id="dias_habiles" 
                                    name="dias_habiles" 
                                    min="1" 
                                    max="31" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    required 
                                    value="20"
                                    title="Ingrese un número entre 1 y 31">
                                <div id="dias-error" class="text-red-500 text-xs mt-1 hidden">Los días hábiles deben estar entre 1 y 31</div>
                            </div>
                        </div>

                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Meta Total:
                                    <span class="text-xs text-gray-500 ml-1" title="Meta base: 20 días * 7 objetivos = 140">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                                </label>
                                <span id="meta_total" class="text-sm font-semibold">140 (100%)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700">
                                    Meta Actual:
                                    <span class="text-xs text-gray-500 ml-1" title="Calculado como: días hábiles * objetivo">
                                        <i class="fas fa-info-circle"></i>
                                    </span>
                                </label>
                                <span id="meta_actual" class="text-sm font-semibold">140 (100%)</span>
                            </div>
                            <div class="mt-2 relative pt-1">
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                    <div id="meta-progress" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Porcentaje Hospitalario:</label>
                            <input type="number" id="porcentaje_hospitalario" name="porcentaje_hospitalario" min="0" max="100" value="0"
                                class="w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cargar configuración desde ciclo anterior:</label>
                            <div class="flex space-x-2">
                                <select id="cicloPrevio" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Seleccionar ciclo anterior...</option>
                                    @foreach($ciclosAnteriores as $ciclo)
                                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }} - {{ $ciclo->created_at->format('d/m/Y') }}</option>
                                    @endforeach
                                </select>
                                <button type="button" 
                                    id="cargarConfiguracion"
                                    class="px-3 py-1 text-sm text-white rounded"
                                    style="background-color: #0d6efd !important;">
                                    Cargar Configuración
                                </button>
                            </div>
                        </div>

                        <div id="especialidades-config">
                            @foreach($especialidades as $especialidad)
                                <div class="border p-4 rounded mb-4">
                                    <h4 class="font-semibold mb-2">{{ $especialidad->name }}</h4>
                                    <div class="space-y-4">
                                        <!-- Productos Recomendados -->
                                        <div class="mb-4">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Productos Recomendados para esta Especialidad</h5>
                                            @if($especialidad->products->isNotEmpty())
                                                <div class="bg-gray-50 p-3 rounded mb-4">
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
                                            <div class="productos-dinamicos" data-especialidad-id="{{ $especialidad->id }}">
                                                <!-- Los productos se agregarán dinámicamente aquí -->
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
            const btnCargarCiclo = document.getElementById('cargarConfiguracion');
            const selectCicloPrevio = document.getElementById('cicloPrevio');

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
                    // Validar que se haya seleccionado un nombre de ciclo
                    const nombreCiclo = document.getElementById('nombre_ciclo').value;
                    if (!nombreCiclo) {
                        alert('Por favor, seleccione un nombre de ciclo');
                        return;
                    }

                    // Recopilar datos del formulario
                    const formData = {
                        nombre: nombreCiclo,
                        porcentaje_hospitalario: document.getElementById('porcentaje_hospitalario').value,
                        objetivo: document.getElementById('objetivo').value,
                        dias_habiles: document.getElementById('dias_habiles').value,
                        representantes: [],
                        detalles: []
                    };

                    // Obtener representantes seleccionados
                    const representantesSeleccionados = [];
                    document.querySelectorAll('input[name="representantes[]"]:checked').forEach(rep => {
                        representantesSeleccionados.push(rep.value);
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
                                representantesSeleccionados.forEach(representanteId => {
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

                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    
                    const response = await fetch('{{ route('ciclos.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.href = '{{ route('ciclos.index') }}';
                    } else {
                        throw new Error(data.message || 'Error al crear el ciclo');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Error al guardar el ciclo');
                }
            });

            // Función para agregar el evento de eliminación a un botón
            function agregarEventoEliminar(boton) {
                boton.addEventListener('click', function() {
                    const productoDiv = this.closest('.flex.items-center');
                    if (productoDiv) {
                        productoDiv.remove();
                    }
                });
            }

            // Agregar eventos de eliminación a los botones existentes
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.eliminar-producto').forEach(boton => {
                    agregarEventoEliminar(boton);
                });
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
                    <input type="number" 
                           class="cantidad-input w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                           placeholder="Cantidad" 
                           min="1" 
                           value="1">
                    <button type="button" class="eliminar-producto px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;

                const cantidadInput = div.querySelector('.cantidad-input');
                
                // Manejar cambios en la cantidad
                cantidadInput.addEventListener('input', function() {
                    const valor = parseInt(this.value) || 1;
                    this.setAttribute('data-cantidad-original', valor);
                });

                // Agregar evento de eliminación al nuevo botón
                const botonEliminar = div.querySelector('.eliminar-producto');
                agregarEventoEliminar(botonEliminar);

                return div;
            }

            // Agregar producto dinámicamente
            document.querySelectorAll('.agregar-producto').forEach(button => {
                button.addEventListener('click', function() {
                    const especialidadId = this.dataset.especialidadId;
                    const contenedor = document.querySelector(`.productos-dinamicos[data-especialidad-id="${especialidadId}"]`);
                    const nuevoProducto = crearProductoTemplate(especialidadId);
                    contenedor.appendChild(nuevoProducto);
                });
            });

            // Manejar productos recomendados
            document.querySelectorAll('.producto-recomendado').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const especialidadId = this.dataset.especialidadId;
                    const productoId = this.dataset.productoId;
                    const contenedor = this.closest('.border').querySelector(`.productos-dinamicos[data-especialidad-id="${especialidadId}"]`);

                    if (this.checked) {
                        const nuevoProducto = crearProductoTemplate(especialidadId);
                        contenedor.appendChild(nuevoProducto);
                        
                        // Seleccionar automáticamente el producto
                        const select = nuevoProducto.querySelector('.producto-select');
                        const cantidad = nuevoProducto.querySelector('.cantidad-input');
                        
                        select.value = productoId;
                        
                        // Establecer cantidad original
                        if (!cantidad.hasAttribute('data-cantidad-original')) {
                            cantidad.setAttribute('data-cantidad-original', cantidad.value);
                        }
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

            // Función para calcular el porcentaje y actualizar cantidades
            function actualizarCantidades() {
                const objetivo = parseInt(document.getElementById('objetivo').value) || 0;
                const diasHabiles = parseInt(document.getElementById('dias_habiles').value) || 0;
                const diasMaximos = 20; // Máximo de días efectivos para el cálculo
                const metaTotal = 140; // 20 días * 7 objetivos
                const diasCalculados = Math.min(diasHabiles, diasMaximos); // Limitar a 20 días máximo
                const metaActual = objetivo * diasCalculados;
                
                // Validación de inputs
                const objetivoError = document.getElementById('objetivo-error');
                const diasError = document.getElementById('dias-error');
                
                objetivoError.classList.toggle('hidden', objetivo >= 1 && objetivo <= 7);
                diasError.classList.toggle('hidden', diasHabiles >= 1 && diasHabiles <= 31);
                
                // Actualizar metas y barra de progreso
                const porcentaje = (metaActual / metaTotal) * 100;
                const porcentajeFormateado = Math.min(Math.round(porcentaje * 10) / 10, 100);
                
                document.getElementById('meta_total').textContent = `${metaTotal} (100%)`;
                document.getElementById('meta_actual').textContent = `${metaActual} (${porcentajeFormateado}%)`;
                
                const progressBar = document.getElementById('meta-progress');
                progressBar.style.width = `${Math.min(porcentajeFormateado, 100)}%`;
                progressBar.style.backgroundColor = porcentajeFormateado >= 100 ? '#10B981' : '#3B82F6';
                
                // Actualizar cantidades de productos
                document.querySelectorAll('.cantidad-input').forEach(input => {
                    const cantidadOriginal = parseInt(input.getAttribute('data-cantidad-original')) || parseInt(input.value) || 1;
                    if (!input.hasAttribute('data-cantidad-original')) {
                        input.setAttribute('data-cantidad-original', cantidadOriginal);
                    }
                    const nuevaCantidad = Math.max(1, Math.round(cantidadOriginal * (porcentaje / 100)));
                    input.value = nuevaCantidad;
                    
                    // Resaltar cambios en las cantidades
                    const esReduccion = nuevaCantidad < cantidadOriginal;
                    input.classList.remove('bg-yellow-50', 'bg-red-50');
                    input.classList.add(esReduccion ? 'bg-red-50' : 'bg-yellow-50');
                    setTimeout(() => input.classList.remove('bg-yellow-50', 'bg-red-50'), 500);
                });
            }

            // Agregar event listeners para los campos de objetivo y días hábiles
            document.getElementById('objetivo').addEventListener('input', actualizarCantidades);
            document.getElementById('dias_habiles').addEventListener('input', actualizarCantidades);

            // Función para cargar configuración de ciclo anterior
            btnCargarCiclo.addEventListener('click', async function() {
                const cicloId = selectCicloPrevio.value;
                if (!cicloId) {
                    alert('Por favor, seleccione un ciclo anterior');
                    return;
                }

                try {
                    const response = await fetch(`/ciclos/${cicloId}/configuracion-anterior`);
                    const data = await response.json();

                    if (data.success) {
                        // Limpiar configuración actual
                        document.querySelectorAll('.productos-dinamicos').forEach(div => {
                            div.innerHTML = '';
                        });

                        // Cargar productos por especialidad
                        data.detalles.forEach(detalle => {
                            const contenedor = document.querySelector(`.productos-dinamicos[data-especialidad-id="${detalle.especialidad_id}"]`);
                            if (contenedor) {
                                const nuevoProducto = crearProductoTemplate(detalle.especialidad_id);
                                contenedor.appendChild(nuevoProducto);
                                
                                // Seleccionar automáticamente el producto
                                const select = nuevoProducto.querySelector('.producto-select');
                                const cantidad = nuevoProducto.querySelector('.cantidad-input');
                                
                                select.value = detalle.producto_id;
                                cantidad.value = detalle.cantidad_por_doctor;
                            }
                        });

                        // Actualizar porcentaje hospitalario
                        document.getElementById('porcentaje_hospitalario').value = data.porcentaje_hospitalario;

                        // Actualizar nombre del ciclo
                        const cicloSeleccionado = selectCicloPrevio.options[selectCicloPrevio.selectedIndex];
                        const numeroCiclo = cicloSeleccionado.textContent.match(/Ciclo (\d+)/);
                        if (numeroCiclo && numeroCiclo[1]) {
                            document.getElementById('nombre_ciclo').value = `Ciclo ${numeroCiclo[1]}`;
                        }

                        // Actualizar objetivo y días hábiles
                        document.getElementById('objetivo').value = data.objetivo;
                        document.getElementById('dias_habiles').value = data.dias_habiles;

                        // Actualizar cantidades según objetivo y días
                        actualizarCantidades();

                        // Mostrar mensaje de éxito
                        alert('Configuración cargada exitosamente');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar la configuración del ciclo');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
