<x-app-layout>
    @vite(['resources/css/productos.css'])
    <div
        class="productos-page"
        x-data="{
            roleModalOpen: {{ $errors->any() && old('form') === 'role' ? 'true' : 'false' }},
            categoryModalOpen: {{ $errors->any() && old('form') === 'category' ? 'true' : 'false' }},
            editRoleModalOpen: false,
            editCategoryModalOpen: false,
            editRoleAction: '',
            editCategoryAction: '',
            editRoleName: '',
            editRoleDescription: '',
            editCategoryName: '',
            editCategorySlug: '',
            editCategoryDescription: '',
            editCategoryStatus: false
        }"
        @keydown.escape.window="
            roleModalOpen = false;
            categoryModalOpen = false;
            editRoleModalOpen = false;
            editCategoryModalOpen = false
        "
    >
        {{-- ========================================================= --}}
        {{-- NOTIFICACIONES --}}
        {{-- ========================================================= --}}
        @if (session('status') || session('error'))
            <div
                x-data="{ visible: true }"
                x-init="setTimeout(() => visible = false, 4000)"
                x-show="visible"
                x-transition.opacity
                class="fixed right-6 top-24 z-[60] flex max-w-sm items-center gap-3 border border-green-200 bg-white px-5 py-4 text-sm {{ session('error') ? 'text-red-800' : 'text-green-800' }} shadow-lg"
                role="status"
            >
                <span>
                    {{ session('status') ?? session('error') }}
                </span>
                <button
                    type="button"
                    class="ml-auto text-lg leading-none"
                    aria-label="Cerrar notificación"
                    @click="visible = false"
                >
                    &times;
                </button>
            </div>
        @endif
        <div class="productos-container">
            {{-- ===================================================== --}}
            {{-- ENCABEZADO --}}
            {{-- ===================================================== --}}
            <section class="productos-header">
                <div>
                    <span class="productos-label">
                        ADMINISTRACIÓN
                    </span>
                    <h1>
                        Configuración de <span>Catálogo</span>
                    </h1>
                    <p>
                        Administra los roles del equipo y las categorías de tus productos.
                    </p>
                </div>
                <div class="productos-actions">
                    <button
                        type="button"
                        class="productos-button"
                        @click="roleModalOpen = true"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.7"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 5v14m-7-7h14"
                            />
                        </svg>
                        NUEVO ROL
                    </button>
                    <button
                        type="button"
                        class="productos-button"
                        @click="categoryModalOpen = true"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.7"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 5v14m-7-7h14"
                            />
                        </svg>
                        NUEVA CATEGORÍA
                    </button>
                </div>
            </section>
            {{-- ===================================================== --}}
            {{-- ROLES --}}
            {{-- ===================================================== --}}
            <section
                class="configuracion-seccion"
                style="margin-bottom: 28px;"
            >
                {{-- BUSCADOR DE ROLES --}}
                <form
                    method="GET"
                    action="{{ route('config.index') }}"
                    class="productos-filtros"
                    style="width: 100%; max-width: 760px; margin: 0 0 28px; border: 0; border-bottom: 1px solid #efede9; grid-template-columns: minmax(240px, 1fr) auto;"
                >
                    <div class="productos-busqueda">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z"
                            />
                        </svg>
                        <input
                            type="search"
                            name="roles_search"
                            value="{{ request('roles_search') }}"
                            placeholder="Buscar roles..."
                            @input.debounce.400ms="$event.target.form.requestSubmit()"
                        >
                    </div>
                    <input
                        type="hidden"
                        name="categorias_search"
                        value="{{ request('categorias_search') }}"
                    >
                    <button
                        type="button"
                        class="productos-limpiar"
                        @click="window.location = '{{ route('config.index') }}'"
                    >
                        LIMPIAR
                    </button>
                </form>
                <div class="productos-table-card">
                <div class="productos-tabla-info">
                    <strong style="color: #222; margin-right: 8px;">
                        Roles
                    </strong>
                    Mostrando {{ $roles->firstItem() ?? 0 }}
                    a {{ $roles->lastItem() ?? 0 }}
                    de {{ $roles->total() }} roles
                </div>
                {{-- TABLA DE ROLES --}}
                <div class="productos-table-wrap">
                    <table
                        class="productos-table"
                        style="min-width: 680px;"
                    >
                        <thead>
                            <tr>
                                <th>ROL</th>
                                <th>DESCRIPCIÓN</th>
                                <th>USUARIOS</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $rol)
                                <tr>
                                    <td>
                                        <div class="producto-identidad">
                                            <div class="producto-thumb oro">
                                                ♙
                                            </div>
                                            <div>
                                                <strong>
                                                    {{ $rol->nombre }}
                                                </strong>
                                                <small>
                                                    ID #{{ $rol->id_rol }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $rol->descripcion ?: 'Sin descripción' }}
                                    </td>
                                    <td>
                                        {{ $rol->usuarios_count }}
                                    </td>
                                    <td>
                                        <div class="productos-acciones">
                                            <button
                                                type="button"
                                                title="Editar rol"
                                                aria-label="Editar rol"
                                                @click="
                                                    editRoleModalOpen = true;
                                                    editRoleAction = '{{ route('config.roles.update', $rol) }}';
                                                    editRoleName = @js($rol->nombre);
                                                    editRoleDescription = @js($rol->descripcion)
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.7"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M16.862 4.487 19.5 7.125M5.25 18.75l3.75-.75L19.5 7.5a1.875 1.875 0 0 0-2.652-2.652L6.348 15.348 5.25 18.75Z"
                                                    />
                                                </svg>
                                            </button>
                                            <form
                                                method="POST"
                                                action="{{ route('config.roles.destroy', $rol) }}"
                                                onsubmit="return confirm('¿Deseas eliminar este rol?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    title="Eliminar rol"
                                                    aria-label="Eliminar rol"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.7"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 7h12m-10 0v11a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V7m-7 0V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2m-5 3v7m3-7v7"
                                                        />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        No se encontraron roles.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- PAGINACIÓN DE ROLES --}}
                <div class="productos-paginacion">
                    @if ($roles->onFirstPage())
                        <button
                            type="button"
                            disabled
                        >
                            ANTERIOR
                        </button>
                    @else
                        <a href="{{ $roles->previousPageUrl() }}">
                            ANTERIOR
                        </a>
                    @endif
                    @foreach (
                        $roles->getUrlRange(
                            max(1, $roles->currentPage() - 2),
                            min($roles->lastPage(), $roles->currentPage() + 2)
                        ) as $page => $url
                    )
                        <a
                            href="{{ $url }}"
                            class="{{ $page == $roles->currentPage() ? 'pagina-activa' : '' }}"
                        >
                            {{ $page }}
                        </a>
                    @endforeach
                    @if ($roles->hasMorePages())
                        <a href="{{ $roles->nextPageUrl() }}">
                            SIGUIENTE
                        </a>
                    @else
                        <button
                            type="button"
                            disabled
                        >
                            SIGUIENTE
                        </button>
                    @endif
                </div>
                </div>
            </section>
            {{-- ===================================================== --}}
            {{-- CATEGORÍAS --}}
            {{-- ===================================================== --}}
            <section class="configuracion-seccion">
                {{-- BUSCADOR DE CATEGORÍAS --}}
                <form
                    method="GET"
                    action="{{ route('config.index') }}"
                    class="productos-filtros"
                    style="width: 100%; max-width: 760px; margin: 0 0 28px; border: 0; border-bottom: 1px solid #efede9; grid-template-columns: minmax(240px, 1fr) auto;"
                >
                    <div class="productos-busqueda">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z"
                            />
                        </svg>
                        <input
                            type="search"
                            name="categorias_search"
                            value="{{ request('categorias_search') }}"
                            placeholder="Buscar categorías..."
                            @input.debounce.400ms="$event.target.form.requestSubmit()"
                        >
                    </div>
                    <input
                        type="hidden"
                        name="roles_search"
                        value="{{ request('roles_search') }}"
                    >
                    <button
                        type="button"
                        class="productos-limpiar"
                        @click="window.location = '{{ route('config.index') }}'"
                    >
                        LIMPIAR
                    </button>
                </form>
                <div class="productos-table-card">
                <div class="productos-tabla-info">
                    <strong style="color: #222; margin-right: 8px;">
                        Categorías
                    </strong>
                    Mostrando {{ $categorias->firstItem() ?? 0 }}
                    a {{ $categorias->lastItem() ?? 0 }}
                    de {{ $categorias->total() }} categorías
                </div>
                {{-- TABLA DE CATEGORÍAS --}}
                <div class="productos-table-wrap">
                    <table
                        class="productos-table"
                        style="min-width: 820px;"
                    >
                        <thead>
                            <tr>
                                <th>CATEGORÍA</th>
                                <th>SLUG</th>
                                <th>DESCRIPCIÓN</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categorias as $categoria)
                                <tr>
                                    <td>
                                        <div class="producto-identidad">
                                            <div class="producto-thumb verde">
                                                ✦
                                            </div>
                                            <div>
                                                <strong>
                                                    {{ $categoria->nombre }}
                                                </strong>
                                                <small>
                                                    ID #{{ $categoria->id_categorias }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $categoria->slug }}
                                    </td>
                                    <td>
                                        {{ $categoria->descripcion ?: 'Sin descripción' }}
                                    </td>
                                    <td>
                                        <span
                                            class="productos-label"
                                            style="margin: 0; letter-spacing: 1px; color: {{ $categoria->status ? '#658264' : '#9a9a9a' }};"
                                        >
                                            {{ $categoria->status ? 'ACTIVA' : 'INACTIVA' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="productos-acciones">
                                            <button
                                                type="button"
                                                title="Editar categoría"
                                                aria-label="Editar categoría"
                                                @click="
                                                    editCategoryModalOpen = true;
                                                    editCategoryAction = '{{ route('config.categories.update', $categoria) }}';
                                                    editCategoryName = @js($categoria->nombre);
                                                    editCategorySlug = @js($categoria->slug);
                                                    editCategoryDescription = @js($categoria->descripcion);
                                                    editCategoryStatus = {{ $categoria->status ? 'true' : 'false' }}
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.7"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M16.862 4.487 19.5 7.125M5.25 18.75l3.75-.75L19.5 7.5a1.875 1.875 0 0 0-2.652-2.652L6.348 15.348 5.25 18.75Z"
                                                    />
                                                </svg>
                                            </button>
                                            <form
                                                method="POST"
                                                action="{{ route('config.categories.destroy', $categoria) }}"
                                                onsubmit="return confirm('¿Deseas eliminar esta categoría?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    title="Eliminar categoría"
                                                    aria-label="Eliminar categoría"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.7"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 7h12m-10 0v11a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V7m-7 0V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2m-5 3v7m3-7v7"
                                                        />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        No se encontraron categorías.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- PAGINACIÓN DE CATEGORÍAS --}}
                <div class="productos-paginacion">
                    @if ($categorias->onFirstPage())
                        <button
                            type="button"
                            disabled
                        >
                            ANTERIOR
                        </button>
                    @else
                        <a href="{{ $categorias->previousPageUrl() }}">
                            ANTERIOR
                        </a>
                    @endif
                    @foreach (
                        $categorias->getUrlRange(
                            max(1, $categorias->currentPage() - 2),
                            min($categorias->lastPage(), $categorias->currentPage() + 2)
                        ) as $page => $url
                    )
                        <a
                            href="{{ $url }}"
                            class="{{ $page == $categorias->currentPage() ? 'pagina-activa' : '' }}"
                        >
                            {{ $page }}
                        </a>
                    @endforeach
                    @if ($categorias->hasMorePages())
                        <a href="{{ $categorias->nextPageUrl() }}">
                            SIGUIENTE
                        </a>
                    @else
                        <button
                            type="button"
                            disabled
                        >
                            SIGUIENTE
                        </button>
                    @endif
                </div>
                </div>
            </section>
        </div>
        {{-- ========================================================= --}}
        {{-- MODAL: AGREGAR ROL --}}
        {{-- ========================================================= --}}
        <div
            x-cloak
            x-show="roleModalOpen"
            x-transition.opacity
            class="productos-modal-backdrop"
            @click.self="roleModalOpen = false"
        >
            <div class="productos-modal">
                <div class="productos-modal-header">
                    <div>
                        <span class="productos-label">
                            ADMINISTRACIÓN
                        </span>
                        <h2>Agregar rol</h2>
                    </div>
                    <button
                        type="button"
                        class="productos-modal-close"
                        @click="roleModalOpen = false"
                    >
                        &times;
                    </button>
                </div>
                <form
                    method="POST"
                    action="{{ route('config.roles.store') }}"
                    class="productos-form"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="form"
                        value="role"
                    >
                    <div class="productos-form-group full">
                        <label for="role-name">
                            Nombre del rol
                        </label>
                        <input
                            id="role-name"
                            name="nombre"
                            value="{{ old('form') === 'role' ? old('nombre') : '' }}"
                            required
                        >
                        @error('nombre')
                            <div class="productos-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="productos-form-group full">
                        <label for="role-description">
                            Descripción
                        </label>
                        <textarea
                            id="role-description"
                            name="descripcion"
                        >{{ old('form') === 'role' ? old('descripcion') : '' }}</textarea>
                    </div>
                    <div class="productos-form-actions">
                        <button
                            type="button"
                            class="productos-cancelar"
                            @click="roleModalOpen = false"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="productos-guardar"
                        >
                            GUARDAR ROL
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ========================================================= --}}
        {{-- MODAL: AGREGAR CATEGORÍA --}}
        {{-- ========================================================= --}}
        <div
            x-cloak
            x-show="categoryModalOpen"
            x-transition.opacity
            class="productos-modal-backdrop"
            @click.self="categoryModalOpen = false"
        >
            <div class="productos-modal">
                <div class="productos-modal-header">
                    <div>
                        <span class="productos-label">
                            ADMINISTRACIÓN
                        </span>
                        <h2>Agregar categoría</h2>
                    </div>
                    <button
                        type="button"
                        class="productos-modal-close"
                        @click="categoryModalOpen = false"
                    >
                        &times;
                    </button>
                </div>
                <form
                    method="POST"
                    action="{{ route('config.categories.store') }}"
                    class="productos-form"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="form"
                        value="category"
                    >
                    <div class="productos-form-group">
                        <label for="category-name">
                            Nombre
                        </label>
                        <input
                            id="category-name"
                            name="nombre"
                            value="{{ old('form') === 'category' ? old('nombre') : '' }}"
                            required
                        >
                        @error('nombre')
                            <div class="productos-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="productos-form-group">
                        <label for="category-slug">
                            Slug <small>(opcional)</small>
                        </label>
                        <input
                            id="category-slug"
                            name="slug"
                            value="{{ old('form') === 'category' ? old('slug') : '' }}"
                            placeholder="Se genera automáticamente"
                        >
                        @error('slug')
                            <div class="productos-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="productos-form-group full">
                        <label for="category-description">
                            Descripción
                        </label>
                        <textarea
                            id="category-description"
                            name="descripcion"
                        >{{ old('form') === 'category' ? old('descripcion') : '' }}</textarea>
                    </div>
                    <div class="productos-form-group">
                        <label>
                            <input
                                type="checkbox"
                                name="status"
                                value="1"
                                checked
                            >
                            Categoría activa
                        </label>
                    </div>
                    <div class="productos-form-actions">
                        <button
                            type="button"
                            class="productos-cancelar"
                            @click="categoryModalOpen = false"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="productos-guardar"
                        >
                            GUARDAR CATEGORÍA
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ========================================================= --}}
        {{-- MODAL: EDITAR ROL --}}
        {{-- ========================================================= --}}
        <div
            x-cloak
            x-show="editRoleModalOpen"
            x-transition.opacity
            class="productos-modal-backdrop"
            @click.self="editRoleModalOpen = false"
        >
            <div class="productos-modal">
                <div class="productos-modal-header">
                    <div>
                        <span class="productos-label">
                            ADMINISTRACIÓN
                        </span>
                        <h2>Editar rol</h2>
                    </div>
                    <button
                        type="button"
                        class="productos-modal-close"
                        @click="editRoleModalOpen = false"
                    >
                        &times;
                    </button>
                </div>
                <form
                    method="POST"
                    :action="editRoleAction"
                    class="productos-form"
                >
                    @csrf
                    @method('PUT')
                    <div class="productos-form-group full">
                        <label for="edit-role-name">
                            Nombre del rol
                        </label>
                        <input
                            id="edit-role-name"
                            name="nombre"
                            x-model="editRoleName"
                            required
                        >
                    </div>
                    <div class="productos-form-group full">
                        <label for="edit-role-description">
                            Descripción
                        </label>
                        <textarea
                            id="edit-role-description"
                            name="descripcion"
                            x-model="editRoleDescription"
                        ></textarea>
                    </div>
                    <div class="productos-form-actions">
                        <button
                            type="button"
                            class="productos-cancelar"
                            @click="editRoleModalOpen = false"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="productos-guardar"
                        >
                            GUARDAR CAMBIOS
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ========================================================= --}}
        {{-- MODAL: EDITAR CATEGORÍA --}}
        {{-- ========================================================= --}}
        <div
            x-cloak
            x-show="editCategoryModalOpen"
            x-transition.opacity
            class="productos-modal-backdrop"
            @click.self="editCategoryModalOpen = false"
        >
            <div class="productos-modal">
                <div class="productos-modal-header">
                    <div>
                        <span class="productos-label">
                            ADMINISTRACIÓN
                        </span>
                        <h2>Editar categoría</h2>
                    </div>
                    <button
                        type="button"
                        class="productos-modal-close"
                        @click="editCategoryModalOpen = false"
                    >
                        &times;
                    </button>
                </div>
                <form
                    method="POST"
                    :action="editCategoryAction"
                    class="productos-form"
                >
                    @csrf
                    @method('PUT')
                    <div class="productos-form-group">
                        <label for="edit-category-name">
                            Nombre
                        </label>
                        <input
                            id="edit-category-name"
                            name="nombre"
                            x-model="editCategoryName"
                            required
                        >
                    </div>
                    <div class="productos-form-group">
                        <label for="edit-category-slug">
                            Slug
                        </label>
                        <input
                            id="edit-category-slug"
                            name="slug"
                            x-model="editCategorySlug"
                        >
                    </div>
                    <div class="productos-form-group full">
                        <label for="edit-category-description">
                            Descripción
                        </label>
                        <textarea
                            id="edit-category-description"
                            name="descripcion"
                            x-model="editCategoryDescription"
                        ></textarea>
                    </div>
                    <div class="productos-form-group">
                        <label>
                            <input
                                type="checkbox"
                                name="status"
                                value="1"
                                x-model="editCategoryStatus"
                            >
                            Categoría activa
                        </label>
                    </div>
                    <div class="productos-form-actions">
                        <button
                            type="button"
                            class="productos-cancelar"
                            @click="editCategoryModalOpen = false"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="productos-guardar"
                        >
                            GUARDAR CAMBIOS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>