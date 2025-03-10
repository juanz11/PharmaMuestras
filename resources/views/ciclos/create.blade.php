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
                <form action="{{ route('ciclos.store') }}" method="POST" id="cicloForm">
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
                                        <input type="checkbox" name="representantes[]" value="{{ $representante->id }}" class="form-checkbox h-5 w-5 text-blue-600" data-doctors='{{ json_encode($representante->doctors) }}'>
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
                            <label class="block text-sm font-medium text-gray-700">Cargar configuración desde ciclo anterior</label>
                            <div class="flex space-x-2">
                                <select id="año-selector" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Año</option>
                                    @foreach($años as $año)
                                        <option value="{{ $año }}">{{ $año }}</option>
                                    @endforeach
                                </select>
                                <select id="ciclo-selector" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Seleccionar ciclo</option>
                                </select>
                                <button type="button" 
                                    id="cargarConfiguracion"
                                    class="px-3 py-1 text-sm text-white rounded"
                                    style="background-color: #0d6efd !important;">
                                    Cargar Configuración
                                </button>
                            </div>
                        </div>

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
                                <span id="meta_actual" class="text-sm font-semibold">0 (0%)</span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                <div id="meta-progress" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500" style="width: 0%"></div>
                            </div>
                            <div id="factor_texto" class="hidden text-sm text-gray-700 mt-2">
                                Se aplicará un factor de eficiencia del
                                <span id="factor_valor" class="font-bold"></span>
                                a la cantidad de productos
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Porcentaje Hospitalario:</label>
                            <input type="number" id="porcentaje_hospitalario" name="porcentaje_hospitalario" min="0" max="100" value="0"
                                class="w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div id="especialidades-config">
                            @foreach($especialidades as $especialidad)
                                <div class="border p-4 rounded mb-4 especialidad-div" data-especialidad-id="{{ $especialidad->id }}">
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
                        <button type="button" id="guardar" class="text-white font-bold py-2 px-4 rounded" style="background-color: #0d6efd !important; display: none;">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Clase para convertir números a romanos
        const NumberToRoman = {
            convert: function(num) {
                const valores = [
                    { valor: 1000, romano: 'M' },
                    { valor: 900, romano: 'CM' },
                    { valor: 500, romano: 'D' },
                    { valor: 400, romano: 'CD' },
                    { valor: 100, romano: 'C' },
                    { valor: 90, romano: 'XC' },
                    { valor: 50, romano: 'L' },
                    { valor: 40, romano: 'XL' },
                    { valor: 10, romano: 'X' },
                    { valor: 9, romano: 'IX' },
                    { valor: 5, romano: 'V' },
                    { valor: 4, romano: 'IV' },
                    { valor: 1, romano: 'I' }
                ];
                
                let resultado = '';
                for (let i = 0; i < valores.length; i++) {
                    while (num >= valores[i].valor) {
                        resultado += valores[i].romano;
                        num -= valores[i].valor;
                    }
                }
                return resultado;
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('cicloForm');
            const paso1 = document.getElementById('paso1');
            const paso2 = document.getElementById('paso2');
            const btnAnterior = document.getElementById('anterior');
            const btnSiguiente = document.getElementById('siguiente');
            const btnGuardar = document.getElementById('guardar');
            const btnSeleccionarTodos = document.getElementById('seleccionarTodos');
            const btnCargarCiclo = document.getElementById('cargarConfiguracion');
            const selectCicloPrevio = document.getElementById('ciclo-selector');
            const añoSelector = document.getElementById('año-selector');

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
                btnGuardar.style.display = 'block';
            });

            btnAnterior.addEventListener('click', function() {
                paso1.style.display = 'block';
                paso2.style.display = 'none';
                btnAnterior.style.display = 'none';
                btnSiguiente.style.display = 'block';
                btnGuardar.style.display = 'none';
            });

            btnGuardar.addEventListener('click', async function(e) {
                e.preventDefault();
                
                const productos = document.querySelectorAll('.producto-div');
                let mensajesError = [];

                productos.forEach(function(productoDiv) {
                    const select = productoDiv.querySelector('.producto-select');
                    const input = productoDiv.querySelector('.cantidad-input');
                    const productoId = parseInt(select.value);
                    const cantidad = parseInt(input.value) || 0;

                    @foreach($productos as $producto)
                    if ({{ $producto->id }} === productoId) {
                        const disponible = {{ $producto->quantity }};
                        console.log('Validando producto:', {
                            id: productoId,
                            nombre: '{{ $producto->name }}',
                            disponible: disponible,
                            solicitado: cantidad
                        });
                        if (cantidad > disponible) {
                            mensajesError.push(`No hay suficiente inventario del producto {{ $producto->name }}. Disponible: ${disponible}, Requerido: ${cantidad}`);
                        }
                    }
                    @endforeach
                });

                if (mensajesError.length > 0) {
                    alert(mensajesError.join('\n'));
                    return false;
                }
                
                // Si no hay errores, enviamos el formulario
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
                div.className = 'producto-div flex items-center space-x-2 mb-2';
                div.innerHTML = `
                    <select class="producto-select flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Seleccionar Producto</option>
                        @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" data-quantity="{{ $producto->quantity }}">
                            {{ $producto->name }}
                        </option>
                        @endforeach
                    </select>
                    <input type="number" 
                           class="cantidad-input rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                           placeholder="Cantidad" 
                           min="1" 
                           value="1">
                    <button type="button" class="eliminar-producto px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <span class="stock-warning text-red-500 text-sm ml-2" style="display: none;"></span>
                `;

                const cantidadInput = div.querySelector('.cantidad-input');
                const productoSelect = div.querySelector('.producto-select');
                const warning = div.querySelector('.stock-warning');

                // Función para verificar cantidad
                function verificarCantidad() {
                    const cantidad = parseInt(cantidadInput.value) || 0;
                    const option = productoSelect.selectedOptions[0];
                    if (option && option.value) {
                        const productoId = option.value;
                        const disponible = parseInt(option.dataset.quantity) || 0;
                        
                        // Obtener el div de la especialidad que contiene este producto
                        const especialidadDiv = div.closest('.especialidad-div');
                        if (!especialidadDiv) return;
                        
                        const especialidadId = especialidadDiv.dataset.especialidadId;
                        
                        // Obtener porcentaje hospitalario
                        const porcentajeHospitalario = parseInt(document.getElementById('porcentaje_hospitalario').value) || 0;
                        
                        // Contar doctores para esta especialidad
                        let totalDoctores = 0;
                        const representantesSeleccionados = document.querySelectorAll('input[name="representantes[]"]:checked');
                        
                        representantesSeleccionados.forEach(rep => {
                            try {
                                const doctores = JSON.parse(rep.dataset.doctors);
                                const doctoresEspecialidad = doctores.filter(d => parseInt(d.medical_specialty_id) === parseInt(especialidadId));
                                totalDoctores += doctoresEspecialidad.length;
                            } catch (error) {
                                console.error('Error al procesar doctores:', error);
                            }
                        });

                        // Calcular cantidad base total para esta especialidad
                        const cantidadBase = cantidad * totalDoctores;
                        
                        // Sumar todas las cantidades del mismo producto en otras especialidades
                        let cantidadTotalProducto = cantidadBase;
                        document.querySelectorAll('.especialidad-div').forEach(otherEspDiv => {
                            if (otherEspDiv !== especialidadDiv) {
                                otherEspDiv.querySelectorAll('.producto-select').forEach(otherSelect => {
                                    if (otherSelect.value === productoId) {
                                        const otherCantidad = parseInt(otherSelect.closest('.flex').querySelector('.cantidad-input').value) || 0;
                                        const otherEspId = otherEspDiv.dataset.especialidadId;
                                        let otherDoctores = 0;
                                        
                                        representantesSeleccionados.forEach(rep => {
                                            try {
                                                const doctores = JSON.parse(rep.dataset.doctors);
                                                const doctoresOtraEsp = doctores.filter(d => parseInt(d.medical_specialty_id) === parseInt(otherEspId));
                                                otherDoctores += doctoresOtraEsp.length;
                                            } catch (error) {
                                                console.error('Error al procesar doctores de otra especialidad:', error);
                                            }
                                        });
                                        
                                        cantidadTotalProducto += (otherCantidad * otherDoctores);
                                    }
                                });
                            }
                        });
                        
                        // Calcular cantidad adicional por porcentaje hospitalario sobre el total
                        const cantidadAdicional = Math.round(cantidadTotalProducto * (porcentajeHospitalario / 100));
                        
                        // Cantidad total incluyendo el porcentaje hospitalario
                        const cantidadTotal = cantidadTotalProducto + cantidadAdicional;
                        
                        if (cantidadTotal > disponible) {
                            const mensaje = `Advertencia: Cantidad total necesaria (${cantidadTotalProducto} total entre todas las especialidades) + ${cantidadAdicional} (${porcentajeHospitalario}% hospitalario) = ${cantidadTotal}, excede el inventario disponible (${disponible})`;
                            warning.textContent = mensaje;
                            warning.style.display = 'inline';
                            cantidadInput.classList.add('border-red-500');
                        } else if (totalDoctores === 0) {
                            warning.textContent = 'Seleccione al menos un representante con doctores en esta especialidad';
                            warning.style.display = 'inline';
                            cantidadInput.classList.add('border-red-500');
                        } else {
                            warning.style.display = 'none';
                            cantidadInput.classList.remove('border-red-500');
                        }
                    }
                }

                // Verificar cuando cambie la cantidad
                cantidadInput.addEventListener('input', verificarCantidad);
                // Verificar cuando cambie el producto
                productoSelect.addEventListener('change', verificarCantidad);
                // Verificar cuando cambie el porcentaje hospitalario
                document.getElementById('porcentaje_hospitalario').addEventListener('input', () => {
                    const productosEnEspecialidad = document.querySelectorAll('.especialidad-div[data-especialidad-id] .producto-div');
                    productosEnEspecialidad.forEach(productoDiv => {
                        const cantidadInput = productoDiv.querySelector('.cantidad-input');
                        if (cantidadInput) {
                            cantidadInput.dispatchEvent(new Event('input'));
                        }
                    });
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
            function actualizarMetaYFactor() {
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

                // Mostrar el factor que se aplicará solo si es menor al 100%
                const factorTexto = document.getElementById('factor_texto');
                const factorValor = document.getElementById('factor_valor');
                if (porcentajeFormateado < 100) {
                    factorTexto.classList.remove('hidden');
                    factorValor.textContent = porcentajeFormateado + '%';
                } else {
                    factorTexto.classList.add('hidden');
                }
            }

            // Agregar event listeners para los campos de objetivo y días hábiles
            document.getElementById('objetivo').addEventListener('input', actualizarMetaYFactor);
            document.getElementById('dias_habiles').addEventListener('input', actualizarMetaYFactor);

            // Evento para cargar ciclos cuando se selecciona un año
            document.getElementById('año-selector').addEventListener('change', function() {
                const año = this.value;
                const cicloSelector = document.getElementById('ciclo-selector');
                
                // Limpiar selector de ciclos
                cicloSelector.innerHTML = '<option value="">Seleccionar ciclo</option>';
                
                if (año) {
                    fetch(`/ciclos/por-año/${año}`)
                        .then(response => response.json())
                        .then(ciclos => {
                            ciclos.forEach((ciclo, index) => {
                                const option = document.createElement('option');
                                option.value = ciclo.nombre;
                                // Convertir el número a romano
                                const match = ciclo.nombre.match(/Ciclo (\d+)/);
                                if (match) {
                                    const numero = parseInt(match[1]);
                                    option.textContent = `Ciclo ${NumberToRoman.convert(numero)}`;
                                } else {
                                    option.textContent = ciclo.nombre;
                                }
                                cicloSelector.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error al cargar ciclos:', error);
                        });
                }
            });

            // Función para cargar configuración de ciclo anterior
            btnCargarCiclo.addEventListener('click', async function() {
                const nombreCiclo = selectCicloPrevio.value;
                if (!nombreCiclo) {
                    alert('Por favor, seleccione un ciclo anterior');
                    return;
                }

                try {
                    const response = await fetch(`/ciclos/configuracion/${encodeURIComponent(nombreCiclo)}`);
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
                                cantidad.setAttribute('data-cantidad-original', detalle.cantidad_por_doctor);
                            }
                        });

                        // Actualizar porcentaje hospitalario
                        document.getElementById('porcentaje_hospitalario').value = data.porcentaje_hospitalario;

                        // Actualizar nombre del ciclo
                        document.getElementById('nombre_ciclo').value = nombreCiclo;

                        // Actualizar objetivo y días hábiles
                        document.getElementById('objetivo').value = data.objetivo;
                        document.getElementById('dias_habiles').value = data.dias_habiles;

                        // Actualizar cantidades según objetivo y días
                        actualizarMetaYFactor();

                        // Mostrar mensaje de éxito
                        alert('Configuración cargada exitosamente');
                    } else {
                        throw new Error(data.message || 'Error al cargar la configuración');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al cargar la configuración del ciclo: ' + error.message);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
