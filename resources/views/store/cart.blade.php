<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Carrito | Lesa Joyería</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="store-page">
        @include('layouts.navigation')
        <main class="cart-page"><p class="eyebrow">Tu seleccion Lesa Joyería</p><h1>Carrito de compras</h1>
            @if (session('cart_status'))<div class="store-notice">{{ session('cart_status') }}</div>@endif
            @if ($items->isEmpty())
                <div class="empty-cart"><h2>Tu carrito esta esperando una pieza especial.</h2><a href="{{ route('home') }}" class="primary-button">Explorar joyas</a></div>
            @else
                <form
                    method="POST"
                    action="{{ route('cart.update') }}"
                    x-data="{
                        quantities: @js($items->mapWithKeys(fn ($item) => [$item['producto']->getKey() => $item['quantity']])->all()),
                        prices: @js($items->mapWithKeys(fn ($item) => [$item['producto']->getKey() => (float) $item['producto']->precio_venta])->all()),
                        subtotal() {
                            return Object.keys(this.quantities).reduce((total, id) => total + (Number(this.prices[id]) * Number(this.quantities[id] || 0)), 0);
                        },
                        money(value) {
                            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
                        }
                    }"
                >
                    @csrf
                    @method('PATCH')
                    <div class="cart-layout"><div class="cart-items">
                    @foreach ($items as $item)
                        @php($image = $item['producto']->imagenes->first()?->url_img)
                        <article class="cart-item">
                            <div class="cart-item__image">@if ($image)<img src="{{ str_starts_with($image, 'http') ? $image : Storage::disk('public')->url($image) }}" alt="{{ $item['producto']->nombre }}">@else<span>Lesa</span>@endif</div>
                            <div class="cart-item__info">
                                <p class="product-category">{{ $item['producto']->categoria?->nombre ?? 'Alta joyeria' }}</p>
                                <h2>{{ $item['producto']->nombre }}</h2>
                                <p class="cart-item__size">Talla: {{ $item['producto']->talla_medida ?: 'No especificada' }}</p>
                                <p class="cart-item__stock">Disponibles ahora: {{ $item['stock'] }}</p>
                                <strong>${{ number_format($item['producto']->precio_venta, 2) }} c/u</strong>
                                <p class="cart-item__line-total">Subtotal: <strong x-text="money(prices['{{ $item['producto']->getKey() }}'] * Number(quantities['{{ $item['producto']->getKey() }}'] || 0))"></strong></p>
                            </div>
                            <label class="quantity-label">
                                Cantidad
                                <input
                                    type="number"
                                    name="quantities[{{ $item['producto']->getKey() }}]"
                                    x-model.number="quantities['{{ $item['producto']->getKey() }}']"
                                    min="1"
                                    max="{{ min(99, $item['stock']) }}"
                                    required
                                    @input="
                                        const maximum = {{ min(99, $item['stock']) }};
                                        const entered = Number($event.target.value);
                                        quantities['{{ $item['producto']->getKey() }}'] = Math.min(Math.max(Number.isFinite(entered) && entered > 0 ? entered : 1, 1), maximum);
                                        $event.target.value = quantities['{{ $item['producto']->getKey() }}'];
                                    "
                                >
                            </label>
                            <div class="cart-item__actions">
                                <strong x-text="money(prices['{{ $item['producto']->getKey() }}'] * Number(quantities['{{ $item['producto']->getKey() }}'] || 0))"></strong>
                                <button type="submit" formaction="{{ route('cart.remove', $item['producto']) }}" formmethod="POST" name="_method" value="DELETE" class="remove-button">Quitar</button>
                            </div>
                        </article>
                    @endforeach
                    <button type="submit" class="secondary-button">Actualizar carrito</button>
                </div><aside class="cart-summary"><p class="eyebrow">Resumen</p><h2>Custodia de tu seleccion</h2><div><span>Subtotal</span><strong x-text="money(subtotal())"></strong></div>@auth<a href="{{ route('payments.create') }}" class="primary-button">Proceder al pago</a>@else<a href="{{ route('login') }}" class="primary-button">Inicia sesión para pagar</a>@endauth<small>Pago ficticio seguro para completar tu pedido.</small></aside></div></form>
            @endif
        </main>
        <footer class="store-footer"><div class="footer-brand"><strong>Lesa Joyería</strong><p>Alta joyeria creada con oficio y atencion al detalle.</p></div><div><span class="footer-title">Asistencia</span><p>Consulta de certificados<br>Cita privada</p></div><div><span class="footer-title">La Gazette Privee</span><p>Proximamente: novedades y piezas de temporada.</p></div></footer>
    </body>
</html>
