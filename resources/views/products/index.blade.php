<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inventario Disponible') }}
            </h2>
            <a href="{{ route('products.create') }}" style="background-color: #0d6efd !important;" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Crear Nuevo Producto
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-[95%] mx-auto px-4">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-12 px-4">
                @foreach ($products as $product)
                    <div style="width: 320px; height: 480px; margin: 0 auto;" class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300 flex flex-col">
                        <div class="relative h-[220px] overflow-hidden">
                            @if($product->image_path && Storage::disk('public')->exists($product->image_path))
                                <img src="{{ Storage::url($product->image_path) }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover object-center">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-[#0d6efd]">
                                    <img src="{{ asset('images/logo/logo-white-250.png') }}" 
                                         alt="Logo por defecto" 
                                         class="w-full h-full object-contain p-8">
                                </div>
                            @endif
                            <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gradient-to-t from-black/50 to-transparent">
                                <h3 class="text-xl font-bold text-white truncate">{{ $product->name }}</h3>
                            </div>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="flex-1">
                                <div class="space-y-3">
                                    <p class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="font-medium">Cantidad:</span> {{ $product->quantity }}
                                    </p>
                                    <p class="flex items-center text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">Valor:</span> ${{ number_format($product->valor, 2) }}
                                    </p>
                                    <p class="flex items-center text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="font-medium">Especialidad:</span> 
                                        <span class="ml-1 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                            {{ $product->medicalSpecialty ? $product->medicalSpecialty->name : 'Sin especialidad' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between gap-2">
                                <a href="{{ route('products.edit', $product) }}" 
                                   style="background-color: #0d6efd !important;"
                                   class="flex-1 text-white py-2 px-3 rounded-lg hover:opacity-90 transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center"
                                   title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span class="ml-2">Editar</span>
                                </a>
                                <button type="button"
                                        onclick="confirmDelete('{{ $product->id }}', '{{ $product->name }}')"
                                        class="flex-1 bg-red-600 text-white py-2 px-3 rounded-lg hover:opacity-90 transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center"
                                        title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span class="ml-2">Eliminar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 50;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar eliminación</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">¿Estás seguro de que deseas eliminar el producto <span id="productName" class="font-medium"></span>? Esta acción no se puede deshacer.</p>
                </div>
                <div class="flex justify-center gap-4 mt-4">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(productId, productName) {
            const modal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const productNameSpan = document.getElementById('productName');
            const cancelButton = document.getElementById('cancelDelete');

            modal.classList.remove('hidden');
            productNameSpan.textContent = productName;
            deleteForm.action = `/products/${productId}`;

            cancelButton.onclick = function() {
                modal.classList.add('hidden');
            }

            // Cerrar modal al hacer clic fuera
            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            }
        }
    </script>
</x-app-layout>
