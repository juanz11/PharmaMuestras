<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes de Ciclos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Fecha Inicio')" />
                                <x-text-input id="start_date" type="date" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('Fecha Fin')" />
                                <x-text-input id="end_date" type="date" class="mt-1 block w-full" />
                            </div>
                            <div class="flex items-end">
                                <x-primary-button id="search-button" class="w-full justify-center">
                                    {{ __('Buscar') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </div>

                    <div id="results-container">
                        <!-- Los resultados se insertarán aquí -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchButton = document.getElementById('search-button');
            const resultsContainer = document.getElementById('results-container');

            function formatCurrency(amount) {
                return new Intl.NumberFormat('es-DO', {
                    style: 'currency',
                    currency: 'DOP'
                }).format(amount);
            }

            function createCycleCard(cycle) {
                const card = document.createElement('div');
                card.className = 'mb-6 bg-white rounded-lg shadow';

                // Encabezado del ciclo
                const header = document.createElement('div');
                header.className = 'px-6 py-4 border-b border-gray-200';
                header.innerHTML = `
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold">${cycle.nombre}</h3>
                        <span class="px-3 py-1 text-sm rounded-full ${
                            cycle.estado === 'completado' ? 'bg-green-100 text-green-800' :
                            cycle.estado === 'en_progreso' ? 'bg-blue-100 text-blue-800' :
                            'bg-gray-100 text-gray-800'
                        }">${cycle.estado}</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        <span>${cycle.fecha_inicio} - ${cycle.fecha_fin}</span>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-600">Total Entregado:</span>
                            <span class="ml-2 font-semibold">${cycle.total_entregado}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">Costo Total:</span>
                            <span class="ml-2 font-semibold">${formatCurrency(cycle.costo_total)}</span>
                        </div>
                    </div>
                `;

                // Tabla de productos
                const productsTable = document.createElement('div');
                productsTable.className = 'px-6 py-4';
                productsTable.innerHTML = `
                    <h4 class="text-sm font-semibold mb-3">Productos Entregados</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Costo Unit.</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Costo Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${cycle.productos.map(producto => `
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm">${producto.name}</td>
                                        <td class="px-4 py-2 text-sm text-right">${producto.cantidad_entregada}</td>
                                        <td class="px-4 py-2 text-sm text-right">${formatCurrency(producto.costo_unitario)}</td>
                                        <td class="px-4 py-2 text-sm text-right">${formatCurrency(producto.costo_total)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;

                card.appendChild(header);
                card.appendChild(productsTable);
                return card;
            }

            searchButton.addEventListener('click', async function() {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                if (!startDate || !endDate) {
                    alert('Por favor seleccione ambas fechas');
                    return;
                }

                try {
                    console.log('Sending request with dates:', startDate, endDate);
                    const response = await fetch(`/reports/cycles?start_date=${startDate}&end_date=${endDate}`);
                    console.log('Response status:', response.status);
                    
                    const data = await response.json();
                    console.log('Received data:', data);

                    if (!response.ok) {
                        throw new Error(data.error || 'Error al obtener los datos');
                    }

                    resultsContainer.innerHTML = '';

                    if (data.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="text-center text-gray-500 py-4">
                                No se encontraron resultados para el rango de fechas seleccionado
                            </div>
                        `;
                        return;
                    }

                    data.forEach(cycle => {
                        resultsContainer.appendChild(createCycleCard(cycle));
                    });

                } catch (error) {
                    console.error('Error:', error);
                    resultsContainer.innerHTML = `
                        <div class="text-center text-red-500 py-4">
                            Error: ${error.message}
                        </div>
                    `;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
