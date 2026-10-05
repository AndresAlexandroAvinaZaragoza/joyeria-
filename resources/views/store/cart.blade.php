<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Carrito | AURA</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="store-page">
        @include('layouts.navigation')
        <main class="cart-page"><p class="eyebrow">Tu seleccion AURA</p><h1>Carrito de compras</h1>
            @if (session('cart_status'))<div class="store-notice">{{ session('cart_status') }}</div>@endif
            @if ($items->isEmpty())
                <div class="empty-cart"><h2>Tu carrito esta esperando una pieza especial.</h2><a href="{{ route('home') }}" class="primary-button">Explorar joyas</a></div>
            @else
                <form method="POST" action="{{ route('cart.update') }}">@csrf @method('PATCH')<div class="cart-layout"><div class="cart-items">
                    @foreach ($items as $item)
                        @php($image = $item['producto']->imagenes->first()?->url_img)
                        <article class="cart-item"><div class="cart-item__image">@if ($image)<img src="{{ str_starts_with($image, 'http') ? $image : Storage::disk('public')->url($image) }}" alt="{{ $item['producto']->nombre }}">@else<span>AURA</span>@endif</div><div class="cart-item__info"><p class="product-category">{{ $item['producto']->categoria?->nombre ?? 'Alta joyeria' }}</p><h2>{{ $item['producto']->nombre }}</h2><strong>${{ number_format($item['producto']->precio_venta, 2) }}</strong></div><label class="quantity-label">Cantidad<input type="number" name="quantities[{{ $item['producto']->getKey() }}]" value="{{ $item['quantity'] }}" min="1" max="99"></label><div class="cart-item__actions"><strong>${{ number_format($item['subtotal'], 2) }}</strong><button type="submit" formaction="{{ route('cart.remove', $item['producto']) }}" formmethod="POST" name="_method" value="DELETE" class="remove-button">Quitar</button></div></article>
                    @endforeach
                    <button type="submit" class="secondary-button">Actualizar carrito</button>
                </div><aside class="cart-summary"><p class="eyebrow">Resumen</p><h2>Custodia de tu seleccion</h2><div><span>Subtotal</span><strong>${{ number_format($total, 2) }}</strong></div><button type="button" class="primary-button" disabled>Proceder al pago</button><small>El proceso de pago estara disponible proximamente.</small></aside></div></form>
            @endif
        </main>
        <footer class="store-footer"><div class="footer-brand"><strong>AURA</strong><p>Alta joyeria creada con oficio y atencion al detalle.</p></div><div><span class="footer-title">Asistencia</span><p>Consulta de certificados<br>Cita privada</p></div><div><span class="footer-title">La Gazette Privee</span><p>Proximamente: novedades y piezas de temporada.</p></div></footer>
    </body>
</html>
