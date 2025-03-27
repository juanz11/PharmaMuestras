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
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-DO', {
                style: 'currency',
                currency: 'DOP'
            }).format(amount);
        }

        document.getElementById('search-button').addEventListener('click', async () => {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Por favor seleccione ambas fechas');
                return;
            }

            try {
                const response = await fetch(`/reports/cycles?start_date=${startDate}&end_date=${endDate}`);
                const data = await response.json();

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Crear la tabla
                let html = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ciclo
                                </th>`;
                
                // Agregar encabezados de productos con sus precios
                data.productos.forEach(producto => {
                    html += `
                                <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ${producto.name}<br>
                                    <span class="text-gray-400">(${formatCurrency(producto.valor)})</span>
                                </th>`;
                });

                html += `
                                <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valor Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">`;

                // Agregar filas de ciclos
                data.ciclos.forEach((ciclo, index) => {
                    html += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                                <td class="px-4 py-2 text-sm">
                                    ${ciclo.nombre}<br>
                                    <span class="text-xs text-gray-500">
                                        ${ciclo.fecha_inicio} - ${ciclo.fecha_fin}
                                    </span>
                                </td>`;
                    
                    data.productos.forEach(producto => {
                        const cantidad = ciclo.cantidades[producto.id] || 0;
                        html += `
                                <td class="px-4 py-2 text-sm text-center">
                                    ${cantidad}
                                </td>`;
                    });

                    html += `
                                <td class="px-4 py-2 text-sm text-right font-medium">
                                    ${formatCurrency(ciclo.costo_total)}
                                </td>
                            </tr>`;
                });

                // Agregar fila de totales
                html += `
                            <tr class="bg-gray-100 font-bold">
                                <td class="px-4 py-2 text-sm">
                                    Total
                                </td>`;
                
                data.productos.forEach(producto => {
                    const total = data.totales.cantidades[producto.id] || 0;
                    html += `
                                <td class="px-4 py-2 text-sm text-center">
                                    ${total}
                                </td>`;
                });

                html += `
                                <td class="px-4 py-2 text-sm text-right">
                                    ${formatCurrency(data.totales.costo_total)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>`;

                document.getElementById('results-container').innerHTML = html;
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar los datos');
            }
        });
    </script>
    @endpush
</x-app-layout>
