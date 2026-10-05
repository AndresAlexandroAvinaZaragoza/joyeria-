<x-app-layout>
    @vite(['resources/css/productos.css'])

    <div
        class="productos-page"
        x-data="{ addModalOpen: {{ $errors->any() ? 'true' : 'false' }}, manageModalOpen: false, activeProduct: null, editModalOpen: false, editAction: '', editOrder: 0, editPrincipal: false, editProductName: '', addProductId: @js(old('producto_id', '')) }"
        @keydown.escape.window="addModalOpen = false; manageModalOpen = false; editModalOpen = false"
    >
        @if (session('status'))
            <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 4000)" x-show="visible" x-transition.opacity class="fixed right-6 top-24 z-[60] flex max-w-sm items-center gap-3 border border-green-200 bg-white px-5 py-4 text-sm text-green-800 shadow-lg" role="status">
                <span>{{ session('status') }}</span>
                <button type="button" class="ml-auto text-lg leading-none" aria-label="Cerrar notificación" @click="visible = false">&times;</button>
            </div>
        @endif

        <div class="productos-container">
            <section class="productos-header">
                <div>
                    <span class="productos-label">CATÁLOGO</span>
                    <h1>Imágenes de <span>Productos</span></h1>
                    <p>Administra las fotografías que se mostrarán en la tienda en línea.</p>
                </div>
                <button type="button" class="productos-button" @click="addProductId = ''; addModalOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" /></svg>
                    NUEVA IMAGEN
                </button>
            </section>

            <form method="GET" action="{{ route('imagenes.index') }}" class="productos-filtros">
                <div class="productos-busqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z" /></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU o nombre..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <button type="button" class="productos-limpiar" @click="window.location = '{{ route('imagenes.index') }}'">LIMPIAR</button>
            </form>

            <section class="productos-table-card">
                <div class="productos-tabla-info">Mostrando {{ $productosConImagenes->firstItem() ?? 0 }} a {{ $productosConImagenes->lastItem() ?? 0 }} de {{ $productosConImagenes->total() }} productos</div>
                <div class="productos-table-wrap">
                    <table class="productos-table imagenes-productos-table" style="min-width: 820px">
                        <thead><tr><th>PRODUCTO</th><th>IMÁGENES</th><th>PRINCIPAL</th><th>ACCIONES</th></tr></thead>
                        <tbody>
                            @forelse ($productosConImagenes as $producto)
                                <tr>
                                    <td>
                                        <div class="producto-identidad">
                                            <div>
                                                <strong>{{ $producto->nombre }}</strong>
                                                <small>SKU: {{ $producto->sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="imagenes-resumen">
                                            @forelse ($producto->imagenes->take(5) as $imagen)
                                                <img src="{{ Storage::disk('public')->url($imagen->url_img) }}" alt="Imagen de {{ $producto->nombre }}">
                                            @empty
                                                <span class="imagenes-sin-fotos">Sin imágenes</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td><span class="imagenes-contador">{{ $producto->imagenes->count() }} {{ $producto->imagenes->count() === 1 ? 'imagen' : 'imágenes' }}</span></td>
                                    <td>
                                        <button type="button" class="imagenes-administrar" @click="activeProduct = {{ $producto->id_productos }}; manageModalOpen = true">ADMINISTRAR IMÁGENES</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="inventario-vacio">No hay productos activos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="productos-paginacion">
                    @if ($productosConImagenes->onFirstPage()) <button type="button" disabled>ANTERIOR</button> @else <a href="{{ $productosConImagenes->previousPageUrl() }}">ANTERIOR</a> @endif
                    @foreach ($productosConImagenes->getUrlRange(max(1, $productosConImagenes->currentPage() - 2), min($productosConImagenes->lastPage(), $productosConImagenes->currentPage() + 2)) as $page => $url)<a href="{{ $url }}" class="{{ $page == $productosConImagenes->currentPage() ? 'pagina-activa' : '' }}">{{ $page }}</a>@endforeach
                    @if ($productosConImagenes->hasMorePages()) <a href="{{ $productosConImagenes->nextPageUrl() }}">SIGUIENTE</a> @else <button type="button" disabled>SIGUIENTE</button> @endif
                </div>
            </section>
        </div>

        <div x-cloak x-show="manageModalOpen" x-transition.opacity class="productos-modal-backdrop" role="dialog" aria-modal="true" @click.self="manageModalOpen = false">
            @foreach ($productosConImagenes as $producto)
                <div x-show="activeProduct === {{ $producto->id_productos }}" class="productos-modal imagenes-manage-modal">
                    <div class="productos-modal-header">
                        <div><span class="productos-label">CATÁLOGO</span><h2>Imágenes de {{ $producto->nombre }}</h2><p>SKU: {{ $producto->sku }}</p></div>
                        <button type="button" class="productos-modal-close" aria-label="Cerrar modal" @click="manageModalOpen = false">&times;</button>
                    </div>
                    <div class="imagenes-galeria">
                        @forelse ($producto->imagenes as $imagen)
                            <article class="imagen-card">
                                <img src="{{ Storage::disk('public')->url($imagen->url_img) }}" alt="Imagen de {{ $producto->nombre }}">
                                @if ($imagen->es_principal)<span class="imagen-card-principal">PRINCIPAL</span>@endif
                                <span class="imagen-card-orden">Orden {{ $imagen->orden }}</span>
                                <div class="imagen-card-actions">
                                    <button type="button" title="Editar imagen" @click="manageModalOpen = false; editModalOpen = true; editAction = '{{ route('imagenes.update', $imagen) }}'; editOrder = {{ $imagen->orden }}; editPrincipal = {{ $imagen->es_principal ? 'true' : 'false' }}; editProductName = @js($producto->nombre)">EDITAR</button>
                                    <form method="POST" action="{{ route('imagenes.destroy', $imagen) }}" onsubmit="return confirm('¿Deseas eliminar esta imagen?');">
                                        @csrf @method('DELETE')
                                        <button type="submit">ELIMINAR</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p class="imagenes-vacias">Este producto aún no tiene imágenes.</p>
                        @endforelse
                    </div>
                    <div class="imagenes-manage-footer">
                        <button type="button" class="productos-guardar" @click="addProductId = '{{ $producto->id_productos }}'; manageModalOpen = false; addModalOpen = true">+ AGREGAR IMAGEN</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div x-cloak x-show="addModalOpen" x-transition.opacity class="productos-modal-backdrop" role="dialog" aria-modal="true" @click.self="addModalOpen = false">
            <div class="productos-modal">
                <div class="productos-modal-header"><div><span class="productos-label">CATÁLOGO</span><h2>Agregar imagen</h2></div><button type="button" class="productos-modal-close" aria-label="Cerrar modal" @click="addModalOpen = false">&times;</button></div>
                <form method="POST" action="{{ route('imagenes.store') }}" class="productos-form" enctype="multipart/form-data">
                    @csrf
                    <div class="productos-form-group full"><label for="producto_id">Producto</label><select id="producto_id" name="producto_id" x-model="addProductId" required><option value="">Selecciona un producto</option>@foreach ($productos as $producto)<option value="{{ $producto->id_productos }}">{{ $producto->sku }} - {{ $producto->nombre }}</option>@endforeach</select>@error('producto_id')<div class="productos-error">{{ $message }}</div>@enderror</div>
                    <div class="productos-form-group full"><label for="imagen">Archivo de imagen</label><input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" required>@error('imagen')<div class="productos-error">{{ $message }}</div>@enderror</div>
                    <div class="productos-form-group"><label for="orden">Orden de visualización</label><input id="orden" name="orden" type="number" min="0" value="{{ old('orden', 0) }}" required>@error('orden')<div class="productos-error">{{ $message }}</div>@enderror</div>
                    <div class="productos-form-group">
                        <label class="imagen-principal-control" for="es_principal">
                            <input id="es_principal" name="es_principal" type="checkbox" value="1" @checked(old('es_principal'))>
                            <span class="imagen-principal-switch" aria-hidden="true"><span></span></span>
                            <span class="imagen-principal-copy"><strong>Imagen principal</strong><small>Se mostrará primero en la tienda</small></span>
                        </label>
                    </div>
                    <div class="productos-form-actions"><button type="button" class="productos-cancelar" @click="addModalOpen = false">Cancelar</button><button type="submit" class="productos-guardar">GUARDAR IMAGEN</button></div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="editModalOpen" x-transition.opacity class="productos-modal-backdrop" role="dialog" aria-modal="true" @click.self="editModalOpen = false">
            <div class="productos-modal">
                <div class="productos-modal-header"><div><span class="productos-label">CATÁLOGO</span><h2>Editar imagen</h2><p x-text="editProductName"></p></div><button type="button" class="productos-modal-close" aria-label="Cerrar modal" @click="editModalOpen = false">&times;</button></div>
                <form method="POST" :action="editAction" class="productos-form" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="productos-form-group full"><label for="edit-imagen">Reemplazar archivo (opcional)</label><input id="edit-imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp"></div>
                    <div class="productos-form-group"><label for="edit-orden">Orden de visualización</label><input id="edit-orden" name="orden" type="number" min="0" x-model="editOrder" required></div>
                    <div class="productos-form-group">
                        <label class="imagen-principal-control" for="edit-principal">
                            <input id="edit-principal" name="es_principal" type="checkbox" value="1" x-model="editPrincipal">
                            <span class="imagen-principal-switch" aria-hidden="true"><span></span></span>
                            <span class="imagen-principal-copy"><strong>Imagen principal</strong><small>Se mostrará primero en la tienda</small></span>
                        </label>
                    </div>
                    <div class="productos-form-actions"><button type="button" class="productos-cancelar" @click="editModalOpen = false">Cancelar</button><button type="submit" class="productos-guardar">GUARDAR CAMBIOS</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>