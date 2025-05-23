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
                            <div class="flex items-end gap-2">
                                <x-primary-button id="search-button" class="w-full justify-center">
                                    {{ __('Buscar') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </div>

                    <div id="export-buttons" class="mb-4 hidden">
                        <button id="export-pdf" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a1 1 0 00-.707 1.707L7 4.414v3.758a1 1 0 01-.293.707l-4 4C.817 14.769 2.156 18 4.828 18h10.343c2.672 0 4.012-3.231 2.122-5.121l-4-4A1 1 0 0113 8.172V4.414l.707-.707A1 1 0 0013 2H7zm2 6.172V4h2v4.172a3 3 0 00.879 2.12l1.027 1.028a4 4 0 00-2.171.102l-.47.156a4 4 0 01-2.53 0l-.563-.187a1.993 1.993 0 00-.114-.035l1.063-1.063A3 3 0 009 8.172z"/>
                            </svg>
                            {{ __('Exportar PDF') }}
                        </button>
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
        let currentData = null;

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
                currentData = data;

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Mostrar botón de exportación
                document.getElementById('export-buttons').classList.remove('hidden');

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

        // Manejador de eventos para exportación PDF
        document.getElementById('export-pdf').addEventListener('click', async () => {
            if (!currentData) return;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            try {
                const response = await fetch(`/reports/cycles/export-pdf?start_date=${startDate}&end_date=${endDate}`);
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte-ciclos-${startDate}-${endDate}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
            } catch (error) {
                console.error('Error:', error);
                alert('Error al exportar PDF');
            }
        });
    </script>
    @endpush
</x-app-layout>
