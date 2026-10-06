<nav x-data="{ open: false, cartOpen: false }" class="store-nav">
    @php
        $roleName = auth()->user()?->rol?->nombre ?? 'Cliente';
        $normalizedRole = strtolower($roleName);
        $isStaff = in_array($normalizedRole, ['admin', 'administrador', 'empleado'], true);
        $isAdmin = in_array($normalizedRole, ['admin', 'administrador'], true);
    @endphp
    <div class="store-nav__inner">
        <a href="{{ route('home') }}" class="store-brand" aria-label="Ir al inicio">
            <span><strong>Lesa Joyería</strong></span>
        </a>

        <div class="store-links">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') && ! request('categoria') ? 'is-active' : '' }}">Inicio</a>
            @if (isset($categorias))
                @foreach ($categorias as $categoria)
                    <a href="{{ route('home', ['categoria' => $categoria->slug]) }}" class="{{ request('categoria') === $categoria->slug ? 'is-active' : '' }}">{{ $categoria->nombre }}</a>
                @endforeach 
            @endif
            @if ($isStaff)
                <div class="store-admin-links">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Panel</a>
                    <a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') ? 'is-active' : '' }}">Productos</a>
                    <a href="{{ route('inventario.index') }}" class="{{ request()->routeIs('inventario.*') ? 'is-active' : '' }}">Inventario</a>
                    <a href="{{ route('imagenes.index') }}" class="{{ request()->routeIs('imagenes.*') ? 'is-active' : '' }}">Imagenes</a>
                    @if ($isAdmin)
                        <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'is-active' : '' }}">Usuarios</a>
                        <a href="{{ route('config.index') }}" class="{{ request()->routeIs('config.*') ? 'is-active' : '' }}">Configuracion</a>
                    @endif
                </div>
            @endif
        </div>

        <div class="store-actions">
            @if (request()->routeIs('home'))
                <button type="button" class="store-action store-action-button store-cart-button" aria-label="Abrir carrito" @click="cartOpen = true"><span class="store-icon">&#9825;</span><span>Carrito</span>@if (($cartCount ?? 0) > 0)<b class="store-badge">{{ $cartCount }}</b>@endif</button>
            @else
                <a href="{{ route('cart.index') }}" class="store-action store-cart-button" aria-label="Ver carrito"><span class="store-icon">&#9825;</span><span>Carrito</span>@if (($cartCount ?? 0) > 0)<b class="store-badge">{{ $cartCount }}</b>@endif</a>
            @endif
            @auth
                <x-dropdown align="right" width="48" contentClasses="py-1 bg-white">
                    <x-slot name="trigger">
                        <button type="button" class="store-user-trigger">
                            <span class="store-account__avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="store-account__text"><strong>{{ Auth::user()->name }}</strong><small>{{ $roleName }}</small></span>
                            <svg class="store-user-trigger__arrow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="store-dropdown-header"><strong>{{ Auth::user()->name }}</strong><span>{{ Auth::user()->email }}</span></div>
                        <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Salir</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @else
                <a href="{{ route('login') }}" class="store-login-link">Iniciar sesion</a>
                @if (Route::has('register'))<a href="{{ route('register') }}" class="store-register-link">Crear cuenta</a>@endif
            @endauth
        </div>

        <button type="button" class="store-menu-button" @click="open = !open" aria-label="Abrir menu"><span></span><span></span><span></span></button>
    </div>

    @if (request()->routeIs('home') || request()->routeIs('cart.index'))
        <div class="store-search-row">
            <form method="GET" action="{{ route('home') }}" class="store-search"><span aria-hidden="true">&#9906;</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar joyas, metal, corte o quilataje..."><button type="submit">Buscar</button></form>
        </div>
    @endif

    <div class="store-mobile-menu" x-show="open" x-transition>
        <a href="{{ route('home') }}">Inicio</a>
        @if (isset($categorias))
            @foreach ($categorias as $categoria)<a href="{{ route('home', ['categoria' => $categoria->slug]) }}">{{ $categoria->nombre }}</a>@endforeach
        @endif
        @if ($isStaff)
            <a href="{{ route('dashboard') }}">Panel</a>
            <a href="{{ route('productos.index') }}">Productos</a>
            <a href="{{ route('inventario.index') }}">Inventario</a>
            <a href="{{ route('imagenes.index') }}">Imagenes</a>
            @if ($isAdmin)
                <a href="{{ route('usuarios.index') }}">Usuarios</a>
                <a href="{{ route('config.index') }}">Configuracion</a>
            @endif
        @endif
        <a href="{{ route('cart.index') }}">Carrito ({{ $cartCount ?? 0 }})</a>
        @guest<a href="{{ route('login') }}">Iniciar sesion</a>@else<a href="{{ route('profile.edit') }}">Mi cuenta · {{ Auth::user()->rol?->nombre ?? 'Cliente' }}</a>@endguest
    </div>

    @if (request()->routeIs('home'))
        <div x-cloak x-show="cartOpen" x-transition.opacity class="cart-drawer-backdrop" @click="cartOpen = false">
            <aside class="cart-drawer" @click.stop role="dialog" aria-modal="true" aria-label="Carrito de compras">
                <div class="cart-drawer__header"><div><p class="eyebrow">Tu seleccion Lesa Joyería</p><h2>Carrito</h2></div><button type="button" class="cart-drawer__close" aria-label="Cerrar carrito" @click="cartOpen = false">&times;</button></div>
                @if (($cartItems ?? collect())->isEmpty())
                    <div class="cart-drawer__empty"><span class="store-icon">&#9825;</span><p>Tu carrito esta esperando una pieza especial.</p></div>
                @else
                    <div class="cart-drawer__items">
                        @foreach ($cartItems ?? [] as $item)
                            @php($drawerImage = $item['producto']->imagenes->first()?->url_img)
                            <article class="cart-drawer__item"><div class="cart-drawer__image">@if ($drawerImage)<img src="{{ str_starts_with($drawerImage, 'http') ? $drawerImage : Storage::disk('public')->url($drawerImage) }}" alt="{{ $item['producto']->nombre }}">@else<span>Lesa</span>@endif</div><div><p>{{ $item['producto']->nombre }}</p><small>Talla: {{ $item['producto']->talla_medida ?: 'No especificada' }}</small><small>{{ $item['quantity'] }} × ${{ number_format($item['producto']->precio_venta, 2) }}</small></div><form method="POST" action="{{ route('cart.remove', $item['producto']) }}">@csrf @method('DELETE')<button type="submit" aria-label="Quitar {{ $item['producto']->nombre }}">&times;</button></form></article>
                        @endforeach
                    </div>
                    <div class="cart-drawer__footer"><div><span>Total</span><strong>${{ number_format($cartItems->sum('subtotal'), 2) }}</strong></div><a href="{{ route('cart.index') }}" class="primary-button">Ver carrito completo</a></div>
                @endif
            </aside>
        </div>
    @endif
</nav>
