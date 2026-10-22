<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Mis pedidos | Lesa Joyería</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="store-page">
    @include('layouts.navigation')
    <main class="cart-page"><p class="eyebrow">Tu historial</p><h1>Mis pedidos</h1>
        @if (session('status'))<div class="store-notice">{{ session('status') }}</div>@endif
        @forelse ($pedidos as $pedido)
            <article class="order-card order-card--details">
                <div class="order-card__summary">
                    <div>
                        <p class="eyebrow">{{ $pedido->numero_pedido }}</p>
                        <h2>{{ $pedido->created_at->format('d/m/Y') }}</h2>
                        <p>{{ $pedido->detalles->sum('cantidad') }} pieza(s) · {{ ucfirst($pedido->pago?->estado ?? 'pendiente') }}</p>
                    </div>
                    <div>
                        <strong>${{ number_format($pedido->total, 2) }}</strong>
                        <span>Envío: {{ ucfirst(str_replace('_', ' ', $pedido->envio?->estado ?? 'pendiente')) }}</span>
                    </div>
                </div>
                <div class="order-card__items">
                    @foreach ($pedido->detalles as $detalle)
                        @php
                            $imagenPrincipal = $detalle->producto?->imagenes->first()?->url_img;
                            $imagenUrl = $imagenPrincipal
                                ? (str_starts_with($imagenPrincipal, 'http') ? $imagenPrincipal : Storage::disk('public')->url($imagenPrincipal))
                                : null;
                        @endphp
                        <div class="order-item">
                            <div class="order-item__image">
                                @if ($imagenUrl)
                                    <img src="{{ $imagenUrl }}" alt="{{ $detalle->producto?->nombre ?? 'Producto del pedido' }}">
                                @else
                                    <span>Lesa</span>
                                @endif
                            </div>
                            <div class="order-item__content">
                                <strong>{{ $detalle->producto?->nombre ?? 'Producto no disponible' }}</strong>
                                <span>{{ $detalle->cantidad }} pieza(s) · ${{ number_format($detalle->precio_unitario, 2) }} c/u</span>
                            </div>
                            <strong class="order-item__total">${{ number_format($detalle->importe, 2) }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="empty-cart"><h2>Todavía no tienes pedidos.</h2><a href="{{ route('home') }}" class="primary-button">Explorar joyas</a></div>
        @endforelse
    </main>
</body>
</html>