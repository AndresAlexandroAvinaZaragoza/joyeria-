<x-app-layout>
    @vite(['resources/css/productos.css'])

    <div
        class="productos-page"
        x-data="{ modalOpen: {{ $errors->any() ? 'true' : 'false' }}, editModalOpen: false, editAction: '', editSku: '', editName: '', editDescription: '', editCategory: '', editMaterial: '', editWeight: '', editSize: '', editCost: '', editPrice: '' }"
        @keydown.escape.window="modalOpen = false; editModalOpen = false"
    >
        @if (session('status'))
            <div
                x-data="{ visible: true }"
                x-init="setTimeout(() => visible = false, 4000)"
                x-show="visible"
                x-transition.opacity
                class="fixed right-6 top-24 z-[60] flex max-w-sm items-center gap-3 border border-green-200 bg-white px-5 py-4 text-sm text-green-800 shadow-lg"
                role="status"
            >
                <span>{{ session('status') }}</span>
                <button type="button" class="ml-auto text-lg leading-none" aria-label="Cerrar notificación" @click="visible = false">&times;</button>
            </div>
        @endif

        <div class="productos-container">
            <section class="productos-header">
                <div>
                    <span class="productos-label">CATÁLOGO</span>
                    <h1>Gestión de <span>Productos</span></h1>
                    <p>Administra las piezas, materiales y precios de tu joyería.</p>
                </div>
                <div class="productos-actions">
                    <button type="button" class="productos-button" @click="modalOpen = true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                        </svg>
                        NUEVO PRODUCTO
                    </button>
                </div>
            </section>

            <form method="GET" action="{{ route('productos.index') }}" class="productos-filtros" x-data>
                <div class="productos-busqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z" />
                    </svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU, nombre o material..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <div class="productos-select">
                    <select name="categoria" @change="$event.target.form.requestSubmit()">
                        <option value="">TODAS LAS CATEGORÍAS</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id_categorias }}" @selected(request('categoria') === $categoria->id_categorias)>{{ $categoria->nombre }}</option>
                                @endforeach
                    </select>
                </div>
                <div class="productos-select">
                    <select name="material" @change="$event.target.form.requestSubmit()">
                        <option value="">TODOS LOS MATERIALES</option>
                        <option value="Oro Blanco 18k" @selected(request('material') === 'Oro Blanco 18k')>ORO BLANCO 18K</option>
                        <option value="Oro Amarillo 18k" @selected(request('material') === 'Oro Amarillo 18k')>ORO AMARILLO 18K</option>
                        <option value="Platino 950" @selected(request('material') === 'Platino 950')>PLATINO 950</option>
                    </select>
                </div>
                <button type="button" class="productos-limpiar" @click="window.location = '{{ route('productos.index') }}'">LIMPIAR</button>
            </form>

            <section class="productos-table-card">
                <div class="productos-tabla-info">
                    Mostrando {{ $productos->firstItem() ?? 0 }} a {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }} productos
                </div>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>PIEZA &amp; DESCRIPCIÓN</th>
                                <th>CATEGORÍA</th>
                                <th>MATERIAL</th>
                                <th>PESO (GR)</th>
                                <th>TALLA / MEDIDA</th>
                                <th>P. COMPRA</th>
                                <th>P. VENTA</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $producto)
                                <tr>
                                    <td>{{ $producto->sku }}</td>
                                    <td>
                                        <div class="producto-identidad">
                                            <div class="producto-thumb oro">✦</div>
                                            <div>
                                                <strong>{{ $producto->nombre }}</strong>
                                                <small>{{ $producto->descripcion }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="producto-categoria">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</td>
                                    <td>{{ $producto->material }}</td>
                                    <td>{{ $producto->peso_gr ? number_format($producto->peso_gr, 2) . ' g' : '—' }}</td>
                                    <td>{{ $producto->talla_medida ?? '—' }}</td>
                                    <td class="producto-precio">${{ number_format($producto->precio_costo, 2) }} MXN</td>
                                    <td class="producto-precio">${{ number_format($producto->precio_venta, 2) }} MXN</td>
                                    <td>
                                        <form method="POST" action="{{ route('productos.toggle-status', $producto) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="producto-switch {{ $producto->status ? 'activo' : 'inactivo' }}"
                                                title="{{ $producto->status ? 'Desactivar producto' : 'Activar producto' }}"
                                                aria-label="{{ $producto->status ? 'Desactivar producto' : 'Activar producto' }}"
                                            >
                                                <span></span>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="productos-acciones">
                                            <button
                                                type="button"
                                                title="Editar producto"
                                                @click="editModalOpen = true; editAction = '{{ route('productos.update', $producto) }}'; editSku = @js($producto->sku); editName = @js($producto->nombre); editDescription = @js($producto->descripcion); editCategory = @js((string) $producto->id_categoria); editMaterial = @js($producto->material); editWeight = @js($producto->peso_gr); editSize = @js($producto->talla_medida); editCost = @js($producto->precio_costo); editPrice = @js($producto->precio_venta)"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 19.5 7.125M5.25 18.75l3.75-.75L19.5 7.5a1.875 1.875 0 0 0-2.652-2.652L6.348 15.348 5.25 18.75Z"/>
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('productos.destroy', $producto) }}" onsubmit="return confirm('¿Deseas eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar producto" aria-label="Eliminar producto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0v11a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V7m-7 0V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2m-5 3v7m3-7v7"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="productos-paginacion">
                    @if ($productos->onFirstPage())
                        <button type="button" disabled>ANTERIOR</button>
                    @else
                        <a href="{{ $productos->previousPageUrl() }}">ANTERIOR</a>
                    @endif

                    @foreach ($productos->getUrlRange(max(1, $productos->currentPage() - 2), min($productos->lastPage(), $productos->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="{{ $page == $productos->currentPage() ? 'pagina-activa' : '' }}">{{ $page }}</a>
                    @endforeach

                    @if ($productos->hasMorePages())
                        <a href="{{ $productos->nextPageUrl() }}">SIGUIENTE</a>
                    @else
                        <button type="button" disabled>SIGUIENTE</button>
                    @endif
                </div>
            </section>
        </div>

        <div
            x-cloak
            x-show="modalOpen"
            x-transition.opacity
            class="productos-modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="nuevo-producto-titulo"
            @click.self="modalOpen = false"
        >
            <div class="productos-modal">
                <div class="productos-modal-header">
                    <div>
                        <span class="productos-label">CATÁLOGO</span>
                        <h2 id="nuevo-producto-titulo">Agregar producto</h2>
                    </div>
                    <button type="button" class="productos-modal-close" aria-label="Cerrar modal" @click="modalOpen = false">&times;</button>
                </div>

                <form method="POST" action="{{ route('productos.store') }}" class="productos-form" enctype="multipart/form-data">
                    @csrf
                    <div class="productos-form-group">
                        <label for="sku">SKU</label>
                        <input id="sku" name="sku" value="{{ old('sku') }}" required placeholder="Ej. JL-AN-014">
                        @error('sku') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="nombre">Nombre del producto</label>
                        <input id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                        @error('nombre') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group full">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion">{{ old('descripcion') }}</textarea>
                        @error('descripcion') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id_categorias }}" @selected(old('categoria') === $categoria->id_categorias)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="material">Material</label>
                        <input id="material" name="material" value="{{ old('material') }}" required placeholder="Ej. Oro Blanco 18k">
                        @error('material') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="peso_gr">Peso (gramos)</label>
                        <input id="peso_gr" name="peso_gr" type="number" step="0.01" min="0" value="{{ old('peso_gr') }}">
                        @error('peso_gr') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="talla">Talla / medida</label>
                        <input id="talla" name="talla" value="{{ old('talla') }}" placeholder="Ej. Talla 6.5">
                        @error('talla') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="precio_compra">Precio de compra</label>
                        <input id="precio_costo" name="precio_costo" type="number" step="0.01" min="0" value="{{ old('precio_costo') }}" required>
                        @error('precio_costo') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="precio_venta">Precio de venta</label>
                        <input id="precio_venta" name="precio_venta" type="number" step="0.01" min="0" value="{{ old('precio_venta') }}" required>
                        @error('precio_venta') <div class="productos-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="productos-form-actions">
                        <button type="button" class="productos-cancelar" @click="modalOpen = false">Cancelar</button>
                        <button type="submit" class="productos-guardar">GUARDAR PRODUCTO</button>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-cloak
            x-show="editModalOpen"
            x-transition.opacity
            class="productos-modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="editar-producto-titulo"
            @click.self="editModalOpen = false"
        >
            <div class="productos-modal">
                <div class="productos-modal-header">
                    <div>
                        <span class="productos-label">CATÁLOGO</span>
                        <h2 id="editar-producto-titulo">Editar producto</h2>
                    </div>
                    <button type="button" class="productos-modal-close" aria-label="Cerrar modal" @click="editModalOpen = false">&times;</button>
                </div>

                <form method="POST" :action="editAction" class="productos-form">
                    @csrf
                    @method('PUT')
                    <div class="productos-form-group">
                        <label for="edit-sku">SKU</label>
                        <input id="edit-sku" name="sku" x-model="editSku" required>
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-nombre">Nombre del producto</label>
                        <input id="edit-nombre" name="nombre" x-model="editName" required>
                    </div>
                    <div class="productos-form-group full">
                        <label for="edit-descripcion">Descripción</label>
                        <textarea id="edit-descripcion" name="descripcion" x-model="editDescription"></textarea>
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-categoria">Categoría</label>
                        <select id="edit-categoria" name="categoria" x-model="editCategory" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id_categorias }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-material">Material</label>
                        <input id="edit-material" name="material" x-model="editMaterial" required>
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-peso">Peso (gramos)</label>
                        <input id="edit-peso" name="peso_gr" type="number" step="0.01" min="0" x-model="editWeight">
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-talla">Talla / medida</label>
                        <input id="edit-talla" name="talla" x-model="editSize">
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-precio-costo">Precio de compra</label>
                        <input id="edit-precio-costo" name="precio_costo" type="number" step="0.01" min="0" x-model="editCost" required>
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-precio-venta">Precio de venta</label>
                        <input id="edit-precio-venta" name="precio_venta" type="number" step="0.01" min="0" x-model="editPrice" required>
                    </div>
                    <div class="productos-form-actions">
                        <button type="button" class="productos-cancelar" @click="editModalOpen = false">Cancelar</button>
                        <button type="submit" class="productos-guardar">GUARDAR CAMBIOS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>