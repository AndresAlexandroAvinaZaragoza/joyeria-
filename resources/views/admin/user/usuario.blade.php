<x-app-layout>

    {{-- CSS exclusivo de usuarios --}}
    @vite(['resources/css/usuarios.css'])

    <div class="usuarios-page" x-data="{ modalOpen: {{ $errors->any() ? 'true' : 'false' }}, editModalOpen: false, editAction: '', editName: '', editEmail: '', editRole: '' }" @keydown.escape.window="modalOpen = false; editModalOpen = false">
        @if (session('status'))
            <div
                x-data="{ visible: true }"
                x-init="setTimeout(() => visible = false, 4000)"
                x-show="visible"
                x-transition.opacity
                role="status"
                class="fixed right-6 top-24 z-[60] flex max-w-sm items-center gap-3 border border-green-200 bg-white px-5 py-4 text-sm text-green-800 shadow-lg"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5 shrink-0 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                </svg>
                <span>{{ session('status') }}</span>
                <button type="button" class="ml-auto text-lg leading-none text-green-600 hover:text-green-900" aria-label="Cerrar notificación" @click="visible = false">
                    &times;
                </button>
            </div>
        @endif

        <div class="usuarios-container">
            {{-- ENCABEZADO --}}
            <section class="usuarios-header">
                <div>
                    <span class="usuarios-label">
                        USUARIOS
                    </span>
                    <h1>
                        Gestión de Usuarios <span>&</span> Roles
                    </h1>
                    <p>
                        Administra el acceso al sistema, asigna roles y controla
                        los permisos de cada usuario.
                    </p>
                    <p>
                        Mantén un entorno seguro para tu joyería.
                    </p>
                </div>
                <button type="button" class="btn-nuevo-usuario" @click="modalOpen = true">
                    <span>+</span>
                    NUEVO USUARIO
                </button>
            </section>
            {{-- FILTROS --}}
            <form method="GET" action="{{ route('usuarios.index') }}" class="usuarios-filtros" x-data>
                <div class="buscador">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, correo o rol..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <div class="filtro-select">
                    <select name="rol_id" @change="$event.target.form.requestSubmit()">
                        <option value="">Rol: Todos los perfiles</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" @selected(request('rol_id') == $rol->id_rol)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </form>


            {{-- TABLA --}}
            <section class="usuarios-card">
                <div class="tabla-info">
                    <div>
                        <span class="tabla-titulo">
                            USUARIOS
                        </span>
                        <span class="tabla-separador"></span>
                        <span class="tabla-registros">
                            Mostrando {{ $usuarios->firstItem() ?? 0 }} a {{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} registros
                        </span>
                    </div>
                </div>


                <div class="tabla-responsive">
                    <table class="usuarios-tabla">
                        <thead>
                            <tr>
                                <th>USUARIO</th>
                                <th>ROL</th>
                                <th>ÚLTIMA CONEXIÓN</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($usuarios as $usuario)
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar avatar-dorado">
                                            AA
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $usuario->name }}
                                            </strong>

                                            <span>
                                                {{ $usuario->email }}
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge admin">
                                            {{ $usuario->rol->nombre }}
                                        </span>

                                        <small>
                                            {{ $usuario->rol->descripcion }}
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <div class="conexion">

                                        <span class="conexion-online">
                                            <i></i>
                                            En línea
                                        </span>

                                        <small>
                                            Panel administrativo
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <span class="estado {{ $usuario->status == 1 ? 'activo' : 'inactivo' }}">
                                        {{ $usuario->status == 1 ? 'ACTIVO' : 'INACTIVO' }}
                                    </span>
                                </td>


                                <td>
                                    <div class="acciones">

                                        {{-- Ver --}}
                                        <button title="Ver usuario">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.7"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                                <circle cx="12" cy="12" r="2.5"/>
                                            </svg>
                                        </button>


                                        {{-- Editar --}}
                                        <button
                                            type="button"
                                            title="Editar usuario"
                                            @click="editModalOpen = true; editAction = '{{ route('usuarios.update', $usuario) }}'; editName = @js($usuario->name); editEmail = @js($usuario->email); editRole = '{{ $usuario->rol_id }}'"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.7"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M16.862 4.487 19.5 7.125M5.25 18.75l3.75-.75L19.5 7.5a1.875 1.875 0 0 0-2.652-2.652L6.348 15.348 5.25 18.75Z"/>
                                            </svg>
                                        </button>


                                        {{-- Eliminar --}}
                                        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" onsubmit="return confirm('¿Deseas eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar usuario" aria-label="Eliminar usuario">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.7"
                                                 stroke="currentColor">
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


                {{-- ============================= --}}
                {{-- PAGINACIÓN --}}
                {{-- =============================  --}}
                <div class="paginacion">
                    @if ($usuarios->onFirstPage())
                        <button type="button" disabled>ANTERIOR</button>
                    @else
                        <a href="{{ $usuarios->previousPageUrl() }}">ANTERIOR</a>
                    @endif

                    @foreach ($usuarios->getUrlRange(max(1, $usuarios->currentPage() - 2), min($usuarios->lastPage(), $usuarios->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="{{ $page == $usuarios->currentPage() ? 'pagina-activa' : '' }}">{{ $page }}</a>
                    @endforeach

                    @if ($usuarios->hasMorePages())
                        <a href="{{ $usuarios->nextPageUrl() }}">SIGUIENTE</a>
                    @else
                        <button type="button" disabled>SIGUIENTE</button>
                    @endif

                </div>

                {{-- Modal para agregar usuario --}}
            </section>

                <div
                    x-cloak
                    x-show="modalOpen"
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="nuevo-usuario-titulo"
                    @click.self="modalOpen = false"
                >
                    <div
                        class="w-full max-w-lg overflow-hidden bg-white shadow-2xl"
                    >
                        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b741d]">Usuarios</span>
                                <h2 id="nuevo-usuario-titulo" class="mt-1 font-serif text-2xl font-medium text-gray-900">Agregar usuario</h2>
                            </div>
                            <button type="button" class="text-2xl leading-none text-gray-400 transition hover:text-gray-900" aria-label="Cerrar modal" @click="modalOpen = false">
                                &times;
                            </button>
                        </div>

                        <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-4 px-6 py-6">
                            @csrf

                            <div>
                                <x-input-label for="modal-name" :value="__('Name')" />
                                <x-text-input id="modal-name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-email" :value="__('Email')" />
                                <x-text-input id="modal-email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-rol" value="Rol" />
                                <select id="modal-rol" name="rol_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecciona un rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id_rol }}" @selected(old('rol_id') == $rol->id_rol)>
                                            {{ $rol->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('rol_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-password" :value="__('Password')" />
                                <x-text-input id="modal-password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-password-confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="modal-password-confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                                <button type="button" class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100" @click="modalOpen = false">Cancelar</button>
                                <x-primary-button>{{ __('Register') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>




                {{-- Modal para editar usuario --}}
                <div
                    x-cloak
                    x-show="editModalOpen"
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="nuevo-usuario-titulo"
                    @click.self="editModalOpen = false"
                >
                    <div
                        class="w-full max-w-lg overflow-hidden bg-white shadow-2xl"
                    >
                        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#9b741d]">Usuarios</span>
                                <h2 id="nuevo-usuario-titulo" class="mt-1 font-serif text-2xl font-medium text-gray-900">Editar usuario</h2>
                            </div>
                            <button type="button" class="text-2xl leading-none text-gray-400 transition hover:text-gray-900" aria-label="Cerrar modal" @click="editModalOpen = false">
                                &times;
                            </button>
                        </div>

                        <form method="POST" :action="editAction" class="space-y-4 px-6 py-6">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="modal-name" :value="__('Name')" />
                                <x-text-input id="edit-modal-name" class="mt-1 block w-full" type="text" name="name" x-model="editName" required autocomplete="name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-email" :value="__('Email')" />
                                <x-text-input id="edit-modal-email" class="mt-1 block w-full" type="email" name="email" x-model="editEmail" required autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-rol" value="Rol" />
                                <select id="edit-modal-rol" name="rol_id" x-model="editRole" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecciona un rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id_rol }}">
                                            {{ $rol->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('rol_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-password" :value="__('Password')" />
                                <x-text-input id="edit-modal-password" class="mt-1 block w-full" type="password" name="password" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-password-confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="edit-modal-password-confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
                                <button type="button" class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100" @click="editModalOpen = false">Cancelar</button>
                                <x-primary-button>{{ __('Update') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>

    </div>

</x-app-layout>