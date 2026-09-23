<x-app-layout>
    @vite(['resources/css/productos.css', 'resources/css/inventario.css'])

    <div class="productos-page inventario-page" x-data="{ addModalOpen: {{ $errors->any() ? 'true' : 'false' }}, editModalOpen: false, editAction: '', editStock: 0, editMinimum: 0, editProductName: '' }" @keydown.escape.window="addModalOpen = false; editModalOpen = false">
        @if (session('status'))
            <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 4000)" x-show="visible" x-transition.opacity class="fixed right-6 top-24 z-[60] flex max-w-sm items-center gap-3 border border-green-200 bg-white px-5 py-4 text-sm text-green-800 shadow-lg" role="status">
                <span>{{ session('status') }}</span>
                <button type="button" class="ml-auto text-lg leading-none" aria-label="Cerrar notificación" @click="visible = false">&times;</button>
            </div>
        @endif

        <div class="productos-container">
            <section class="productos-header">
                <div>
                    <span class="productos-label">INVENTARIO</span>
                    <h1>Control de <span>Stock</span></h1>
                    <p>Administra las existencias disponibles de cada pieza.</p>
                </div>
                <button type="button" class="productos-button" @click="addModalOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" /></svg>
                    AGREGAR STOCK
                </button>
            </section>

            <form method="GET" action="{{ route('inventario.index') }}" class="productos-filtros" x-data>
                <div class="productos-busqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z" /></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU o nombre..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <div class="productos-select">
                    <select name="categoria" @change="$event.target.form.requestSubmit()">
                        <option value="">TODAS LAS CATEGORÍAS</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id_categorias }}" @selected(request('categoria') == $categoria->id_categorias)>{{ strtoupper($categoria->nombre) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="productos-limpiar" @click="window.location = '{{ route('inventario.index') }}'">LIMPIAR</button>
            </form>

            <section class="productos-table-card">
                <div class="productos-tabla-info">Mostrando {{ $inventarios->firstItem() ?? 0 }} a {{ $inventarios->lastItem() ?? 0 }} de {{ $inventarios->total() }} productos</div>
                <div class="productos-table-wrap">
                    <table class="productos-table inventario-table">
                        <thead><tr><th>SKU</th><th>NOMBRE</th><th>CATEGORÍA</th><th>STOCK</th><th>PRECIO VENTA</th><th>ACCIONES</th></tr></thead>
                        <tbody>
                            @forelse ($inventarios as $inventario)
                                <tr>
                                    <td>{{ $inventario->producto->sku }}</td>
                                    <td><strong>{{ $inventario->producto->nombre }}</strong></td>
                                    <td class="producto-categoria">{{ $inventario->producto->categoria?->nombre ?? 'Sin categoría' }}</td>
                                    <td><span class="stock-badge {{ $inventario->stock_actual <= $inventario->stock_minimo ? 'stock-bajo' : '' }}">{{ $inventario->stock_actual }}</span></td>
                                    <td class="producto-precio">${{ number_format($inventario->producto->precio_venta, 2) }} MXN</td>
                                    <td><div class="productos-acciones"><button type="button" title="Editar stock" @click="editModalOpen = true; editAction = '{{ route('inventario.update', $inventario) }}'; editStock = {{ $inventario->stock_actual }}; editMinimum = {{ $inventario->stock_minimo }}; editProductName = @js($inventario->producto->nombre)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 19.5 7.125M5.25 18.75l3.75-.75L19.5 7.5a1.875 1.875 0 0 0-2.652-2.652L6.348 15.348 5.25 18.75Z"/></svg></button></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="inventario-vacio">No hay productos en inventario.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="productos-paginacion">
                    @if ($inventarios->onFirstPage()) <button type="button" disabled>ANTERIOR</button> @else <a href="{{ $inventarios->previousPageUrl() }}">ANTERIOR</a> @endif
                    @foreach ($inventarios->getUrlRange(max(1, $inventarios->currentPage() - 2), min($inventarios->lastPage(), $inventarios->currentPage() + 2)) as $page => $url)<a href="{{ $url }}" class="{{ $page == $inventarios->currentPage() ? 'pagina-activa' : '' }}">{{ $page }}</a>@endforeach
                    @if ($inventarios->hasMorePages()) <a href="{{ $inventarios->nextPageUrl() }}">SIGUIENTE</a> @else <button type="button" disabled>SIGUIENTE</button> @endif
                </div>
            </section>
        </div>

        <div x-cloak x-show="addModalOpen" x-transition.opacity class="productos-modal-backdrop" role="dialog" aria-modal="true" @click.self="addModalOpen = false">
            <div class="productos-modal inventario-modal">
                <div class="productos-modal-header"><div><span class="productos-label">INVENTARIO</span><h2>Agregar stock</h2></div><button type="button" class="productos-modal-close" @click="addModalOpen = false">&times;</button></div>
                <form method="POST" action="{{ route('inventario.store') }}" class="productos-form">@csrf
                    <div class="productos-form-group full"><label for="producto_id">Producto</label><select id="producto_id" name="producto_id" required><option value="">Selecciona un producto</option>@foreach ($productos as $producto)<option value="{{ $producto->id_productos }}" @selected(old('producto_id') == $producto->id_productos)>{{ $producto->sku }} - {{ $producto->nombre }}</option>@endforeach</select>@error('producto_id')<div class="productos-error">{{ $message }}</div>@enderror</div>
                    <div class="productos-form-group"><label for="cantidad">Cantidad a agregar</label><input id="cantidad" name="cantidad" type="number" min="1" required value="{{ old('cantidad') }}">@error('cantidad')<div class="productos-error">{{ $message }}</div>@enderror</div>
                    <div class="productos-form-group"><label for="stock_minimo">Stock mínimo</label><input id="stock_minimo" name="stock_minimo" type="number" min="0" value="{{ old('stock_minimo', 0) }}"></div>
                    <div class="productos-form-actions"><button type="button" class="productos-cancelar" @click="addModalOpen = false">Cancelar</button><button type="submit" class="productos-guardar">AGREGAR STOCK</button></div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="editModalOpen" x-transition.opacity class="productos-modal-backdrop" role="dialog" aria-modal="true" @click.self="editModalOpen = false">
            <div class="productos-modal inventario-modal">
                <div class="productos-modal-header"><div><span class="productos-label">INVENTARIO</span><h2>Editar stock</h2><p x-text="editProductName"></p></div><button type="button" class="productos-modal-close" @click="editModalOpen = false">&times;</button></div>
                <form method="POST" :action="editAction" class="productos-form">@csrf @method('PUT')
                    <div class="productos-form-group"><label for="edit-stock">Stock actual</label><input id="edit-stock" name="stock_actual" type="number" min="0" x-model="editStock" required></div>
                    <div class="productos-form-group"><label for="edit-minimum">Stock mínimo</label><input id="edit-minimum" name="stock_minimo" type="number" min="0" x-model="editMinimum"></div>
                    <div class="productos-form-actions"><button type="button" class="productos-cancelar" @click="editModalOpen = false">Cancelar</button><button type="submit" class="productos-guardar">GUARDAR CAMBIOS</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>