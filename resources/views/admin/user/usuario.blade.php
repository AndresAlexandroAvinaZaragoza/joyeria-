<x-app-layout>

    {{-- CSS exclusivo de usuarios --}}
    @vite(['resources/css/usuarios.css'])

    <div class="usuarios-page">

        <div class="usuarios-container">

            {{-- ============================= --}}
            {{-- ENCABEZADO --}}
            {{-- ============================= --}}
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

                <button class="btn-nuevo-usuario">
                    <span>+</span>
                    NUEVO USUARIO
                </button>

            </section>


            {{-- ============================= --}}
            {{-- FILTROS --}}
            {{-- ============================= --}}
            <section class="usuarios-filtros">

                {{-- Buscador --}}
                <div class="buscador">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.8"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z"/>
                    </svg>

                    <input
                        type="text"
                        placeholder="Buscar por nombre, correo o rol..."
                    >

                </div>


                {{-- Rol --}}
                <div class="filtro-select">

                    <select>
                        <option>Rol: Todos los perfiles</option>
                        <option>Administrador</option>
                        <option>Cliente</option>
                    </select>

                </div>


                {{-- Estado --}}
                <div class="filtro-select">

                    <select>
                        <option>Estado: Todos</option>
                        <option>Activo</option>
                        <option>Inactivo</option>
                    </select>

                </div>


                {{-- Botón filtros --}}
                <button class="btn-filtros">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.8"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4 6h16M7 12h10M10 18h4"/>

                    </svg>

                </button>

            </section>


            {{-- ============================= --}}
            {{-- TABLA --}}
            {{-- ============================= --}}
            <section class="usuarios-card">

                <div class="tabla-info">

                    <div>
                        <span class="tabla-titulo">
                            USUARIOS
                        </span>

                        <span class="tabla-separador"></span>

                        <span class="tabla-registros">
                            Mostrando 6 de 6 registros
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

                            {{-- ===================== --}}
                            {{-- USUARIO 1 --}}
                            {{-- ===================== --}}
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar avatar-dorado">
                                            AA
                                        </div>

                                        <div>
                                            <strong>
                                                Andrés Aviña Zaragoza
                                            </strong>

                                            <span>
                                                andres.avina@joyeria.com
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge admin">
                                            ADMINISTRADOR
                                        </span>

                                        <small>
                                            Acceso completo
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
                                    <span class="estado activo">
                                        ACTIVO
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
                                        <button title="Editar usuario">
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


                                        {{-- Opciones --}}
                                        <button title="Más opciones">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="currentColor">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>


                            {{-- ===================== --}}
                            {{-- USUARIO 2 --}}
                            {{-- ===================== --}}
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar">
                                            CM
                                        </div>

                                        <div>
                                            <strong>
                                                Carlos Martínez
                                            </strong>

                                            <span>
                                                carlos@joyeria.com
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge">
                                            CLIENTE
                                        </span>

                                        <small>
                                            Usuario registrado
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <div class="conexion">

                                        <span>
                                            Hace 14 min
                                        </span>

                                        <small>
                                            Consulta de productos
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <span class="estado activo">
                                        ACTIVO
                                    </span>
                                </td>


                                <td>
                                    <div class="acciones">

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

                                        <button title="Editar usuario">
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

                                        <button title="Más opciones">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="currentColor">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>


                            {{-- ===================== --}}
                            {{-- USUARIO 3 --}}
                            {{-- ===================== --}}
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar">
                                            SR
                                        </div>

                                        <div>
                                            <strong>
                                                Sofía Ramírez
                                            </strong>

                                            <span>
                                                sofia@joyeria.com
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge">
                                            CLIENTE
                                        </span>

                                        <small>
                                            Usuario registrado
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <div class="conexion">

                                        <span>
                                            Hoy, 11:20 AM
                                        </span>

                                        <small>
                                            Inicio de sesión
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <span class="estado activo">
                                        ACTIVO
                                    </span>
                                </td>


                                <td>
                                    <div class="acciones">

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

                                        <button title="Editar usuario">
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

                                        <button title="Más opciones">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="currentColor">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>


                            {{-- ===================== --}}
                            {{-- USUARIO 4 --}}
                            {{-- ===================== --}}
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar avatar-claro">
                                            VR
                                        </div>

                                        <div>
                                            <strong>
                                                Valeria Ramos
                                            </strong>

                                            <span>
                                                valeria@joyeria.com
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge">
                                            CLIENTE
                                        </span>

                                        <small>
                                            Usuario registrado
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <div class="conexion">

                                        <span>
                                            Ayer, 21:15
                                        </span>

                                        <small>
                                            Catálogo de joyería
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <span class="estado activo">
                                        ACTIVO
                                    </span>
                                </td>


                                <td>
                                    <div class="acciones">

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

                                        <button title="Editar usuario">
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

                                        <button title="Más opciones">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="currentColor">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>


                            {{-- ===================== --}}
                            {{-- USUARIO 5 --}}
                            {{-- ===================== --}}
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar">
                                            ML
                                        </div>

                                        <div>
                                            <strong>
                                                Mauricio Leal
                                            </strong>

                                            <span>
                                                mauricio@joyeria.com
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge">
                                            CLIENTE
                                        </span>

                                        <small>
                                            Usuario registrado
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <div class="conexion">

                                        <span>
                                            Hoy, 09:05 AM
                                        </span>

                                        <small>
                                            Consulta de productos
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <span class="estado activo">
                                        ACTIVO
                                    </span>
                                </td>


                                <td>
                                    <div class="acciones">

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

                                        <button title="Editar usuario">
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

                                        <button title="Más opciones">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="currentColor">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>


                            {{-- ===================== --}}
                            {{-- USUARIO 6 --}}
                            {{-- ===================== --}}
                            <tr>

                                <td>
                                    <div class="usuario-identidad">

                                        <div class="avatar">
                                            EG
                                        </div>

                                        <div>
                                            <strong>
                                                Elena Garza
                                            </strong>

                                            <span>
                                                elena@joyeria.com
                                            </span>
                                        </div>

                                    </div>
                                </td>


                                <td>
                                    <div class="rol-info">

                                        <span class="rol-badge">
                                            CLIENTE
                                        </span>

                                        <small>
                                            Usuario registrado
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <div class="conexion">

                                        <span>
                                            Hace 42 min
                                        </span>

                                        <small>
                                            Consulta de catálogo
                                        </small>

                                    </div>
                                </td>


                                <td>
                                    <span class="estado inactivo">
                                        INACTIVO
                                    </span>
                                </td>


                                <td>
                                    <div class="acciones">

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

                                        <button title="Editar usuario">
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

                                        <button title="Más opciones">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="currentColor">
                                                <circle cx="12" cy="5" r="1.5"/>
                                                <circle cx="12" cy="12" r="1.5"/>
                                                <circle cx="12" cy="19" r="1.5"/>
                                            </svg>
                                        </button>

                                    </div>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                {{-- ============================= --}}
                {{-- PAGINACIÓN --}}
                {{-- ============================= --}}
                <div class="paginacion">

                    <button disabled>
                        ANTERIOR
                    </button>

                    <button class="pagina-activa">
                        1
                    </button>

                    <button>
                        2
                    </button>

                    <button>
                        3
                    </button>

                    <span>...</span>

                    <button>
                        10
                    </button>

                    <button>
                        SIGUIENTE
                    </button>

                </div>

            </section>

        </div>

    </div>

</x-app-layout>