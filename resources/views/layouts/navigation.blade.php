<nav x-data="{ open: false, cartOpen: false }" class="store-nav">
    <div class="store-nav__inner">
        <a href="{{ route('home') }}" class="store-brand" aria-label="Ir al inicio">
            <span class="store-brand__mark">A</span>
            <span><strong>AURA</strong><small>Alta joyeria</small></span>
        </a>

        <div class="store-links">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') && ! request('categoria') ? 'is-active' : '' }}">Inicio</a>
            @if (isset($categorias))
                @foreach ($categorias as $categoria)
                    <a href="{{ route('home', ['categoria' => $categoria->slug]) }}" class="{{ request('categoria') === $categoria->slug ? 'is-active' : '' }}">{{ $categoria->nombre }}</a>
                @endforeach
            @endif
        </div>

        <div class="store-actions">
            @if (request()->routeIs('home'))
                <button type="button" class="store-action store-action-button" aria-label="Abrir carrito" @click="cartOpen = true"><span class="store-icon">&#9825;</span><span>Carrito</span>@if (($cartCount ?? 0) > 0)<b class="store-badge">{{ $cartCount }}</b>@endif</button>
            @else
                <a href="{{ route('cart.index') }}" class="store-action" aria-label="Ver carrito"><span class="store-icon">&#9825;</span><span>Carrito</span>@if (($cartCount ?? 0) > 0)<b class="store-badge">{{ $cartCount }}</b>@endif</a>
            @endif
            @auth
                @php($roleName = Auth::user()->rol?->nombre ?? 'Cliente')
                <div class="store-account"><span class="store-account__avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span><span class="store-account__text"><strong>{{ Auth::user()->name }}</strong><small>{{ $roleName }}</small></span><a href="{{ route('profile.edit') }}" class="store-account__link">Mi cuenta</a></div>
                @if (in_array(strtolower($roleName), ['admin', 'administrador', 'empleado'], true))<a href="{{ route('dashboard') }}" class="store-admin-link">Panel</a>@endif
                <form method="POST" action="{{ route('logout') }}" class="store-logout-form">@csrf<button type="submit" class="store-login-link">Salir</button></form>
            @else
                <a href="{{ route('login') }}" class="store-login-link">Iniciar sesion</a>
                @if (Route::has('register'))<a href="{{ route('register') }}" class="store-register-link">Crear cuenta</a>@endif
            @endauth
        </div>

        <button type="button" class="store-menu-button" @click="open = !open" aria-label="Abrir menu"><span></span><span></span><span></span></button>
    </div>

    <div class="store-search-row">
        <form method="GET" action="{{ route('home') }}" class="store-search"><span aria-hidden="true">&#9906;</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar joyas, metal, corte o quilataje..."><button type="submit">Buscar</button></form>
    </div>

    <div class="store-mobile-menu" x-show="open" x-transition>
        <a href="{{ route('home') }}">Inicio</a>
        @if (isset($categorias))
            @foreach ($categorias as $categoria)<a href="{{ route('home', ['categoria' => $categoria->slug]) }}">{{ $categoria->nombre }}</a>@endforeach
        @endif
        <a href="{{ route('cart.index') }}">Carrito ({{ $cartCount ?? 0 }})</a>
        @guest<a href="{{ route('login') }}">Iniciar sesion</a>@else<a href="{{ route('profile.edit') }}">Mi cuenta · {{ Auth::user()->rol?->nombre ?? 'Cliente' }}</a>@endguest
    </div>

    @if (request()->routeIs('home'))
        <div x-cloak x-show="cartOpen" x-transition.opacity class="cart-drawer-backdrop" @click="cartOpen = false">
            <aside class="cart-drawer" @click.stop role="dialog" aria-modal="true" aria-label="Carrito de compras">
                <div class="cart-drawer__header"><div><p class="eyebrow">Tu seleccion AURA</p><h2>Carrito</h2></div><button type="button" class="cart-drawer__close" aria-label="Cerrar carrito" @click="cartOpen = false">&times;</button></div>
                @if (($cartItems ?? collect())->isEmpty())
                    <div class="cart-drawer__empty"><span class="store-icon">&#9825;</span><p>Tu carrito esta esperando una pieza especial.</p></div>
                @else
                    <div class="cart-drawer__items">
                        @foreach ($cartItems ?? [] as $item)
                            @php($drawerImage = $item['producto']->imagenes->first()?->url_img)
                            <article class="cart-drawer__item"><div class="cart-drawer__image">@if ($drawerImage)<img src="{{ str_starts_with($drawerImage, 'http') ? $drawerImage : Storage::disk('public')->url($drawerImage) }}" alt="{{ $item['producto']->nombre }}">@else<span>AURA</span>@endif</div><div><p>{{ $item['producto']->nombre }}</p><small>{{ $item['quantity'] }} × ${{ number_format($item['producto']->precio_venta, 2) }}</small></div><form method="POST" action="{{ route('cart.remove', $item['producto']) }}">@csrf @method('DELETE')<button type="submit" aria-label="Quitar {{ $item['producto']->nombre }}">&times;</button></form></article>
                        @endforeach
                    </div>
                    <div class="cart-drawer__footer"><div><span>Total</span><strong>${{ number_format($cartItems->sum('subtotal'), 2) }}</strong></div><a href="{{ route('cart.index') }}" class="primary-button">Ver carrito completo</a></div>
                @endif
            </aside>
        </div>
    @endif
</nav>
