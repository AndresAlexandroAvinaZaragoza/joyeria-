<nav x-data="{ open: false }" class="navbar-joyeria">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    <div class="navbar-container">

        {{-- =========================
             LOGO + NAVEGACIÓN
        ========================== --}}
        <div class="navbar-left">

            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" class="brand">
                <div class="brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.7">
                        <path d="M6 3h12l4 6-10 12L2 9l4-6z"/>
                        <path d="M2 9h20"/>
                        <path d="M6 3l3 6 3-6 3 6 3-6"/>
                        <path d="M9 9l3 12 3-12"/>
                    </svg>
                </div>

                <div class="brand-text">
                    <span class="brand-name">Joyería</span>
                    <span class="brand-subtitle">Administración</span>
                </div>
            </a>


            {{-- Links escritorio --}}
            <div class="desktop-menu">

                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                    </svg>

                    Dashboard
                </a>


                <a href="{{ route('usuarios.index') }}"
                   class="nav-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>

                    Usuarios
                </a>


                <a href="{{ route('inventario.index') }}"
                   class="nav-item {{ request()->routeIs('inventario.*') ? 'active' : '' }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>

                    Inventario
                </a>


                <a href="{{ route('productos.index') }}"
                   class="nav-item {{ request()->routeIs('productos.*') ? 'active' : '' }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path d="M20 7h-4V5a4 4 0 0 0-8 0v2H4a2 2 0 0 0-2 2l1 11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2l1-11a2 2 0 0 0-2-2z"/>
                        <path d="M8 7V5a4 4 0 0 1 8 0v2"/>
                    </svg>

                    Productos
                </a>

            </div>
        </div>


        {{-- =========================
             USUARIO
        ========================== --}}
        <div class="desktop-user">

            <x-dropdown align="right" width="48">

                <x-slot name="trigger">

                    <button class="user-button">

                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>

                        <div class="user-info">
                            <span class="user-name">
                                {{ Auth::user()->name }}
                            </span>

                            <span class="user-role">
                                Administrador
                            </span>
                        </div>

                        <svg class="arrow"
                             xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 20 20"
                             fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                  clip-rule="evenodd"/>
                        </svg>

                    </button>

                </x-slot>


                <x-slot name="content">

                    <div class="dropdown-header">
                        <strong>{{ Auth::user()->name }}</strong>
                        <span>{{ Auth::user()->email }}</span>
                    </div>

                    <x-dropdown-link :href="route('profile.edit')">
                        Mi perfil
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link
                            :href="route('logout')"
                            onclick="event.preventDefault();
                            this.closest('form').submit();">

                            Cerrar sesión

                        </x-dropdown-link>
                    </form>

                </x-slot>

            </x-dropdown>

        </div>


        {{-- =========================
             BOTÓN MÓVIL
        ========================== --}}
        <div class="mobile-button">

            <button @click="open = !open">

                <svg x-show="!open"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>

                <svg x-show="open"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>

            </button>

        </div>

    </div>


    {{-- =========================
         MENÚ MÓVIL
    ========================== --}}
    <div x-show="open"
         x-transition
         class="mobile-menu">

        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('usuarios.index') }}"
           class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            Usuarios
        </a>

        <a href="{{ route('inventario.index') }}"
           class="{{ request()->routeIs('inventario.*') ? 'active' : '' }}">
            Inventario
        </a>

        <a href="{{ route('productos.index') }}"
           class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">
            Productos
        </a>

        <div class="mobile-user">

            <strong>{{ Auth::user()->name }}</strong>
            <span>{{ Auth::user()->email }}</span>

        </div>

        <a href="{{ route('profile.edit') }}">
            Mi perfil
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="logout-mobile">
                Cerrar sesión
            </button>
        </form>

    </div>

</nav>