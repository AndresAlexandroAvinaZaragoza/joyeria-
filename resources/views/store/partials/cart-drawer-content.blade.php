@if ($cartItems->isEmpty())
    <div class="cart-drawer__empty"><span class="store-icon">&#9825;</span><p>Tu carrito esta esperando una pieza especial.</p></div>
@else
    <div class="cart-drawer__items">
        @foreach ($cartItems as $item)
            @php($drawerImage = $item['producto']->imagenes->first()?->url_img)
            <article class="cart-drawer__item"><div class="cart-drawer__image">@if ($drawerImage)<img src="{{ str_starts_with($drawerImage, 'http') ? $drawerImage : Storage::disk('public')->url($drawerImage) }}" alt="{{ $item['producto']->nombre }}">@else<span>Lesa</span>@endif</div><div class="cart-drawer__item-info"><p>{{ $item['producto']->nombre }}</p><small>Talla: {{ $item['producto']->talla_medida ?: 'No especificada' }}</small><small>Cantidad: {{ $item['quantity'] }}</small><small>Subtotal: ${{ number_format($item['subtotal'], 2) }}</small></div><form method="POST" action="{{ route('cart.remove', $item['producto']) }}">@csrf @method('DELETE')<button type="submit" aria-label="Quitar {{ $item['producto']->nombre }}">&times;</button></form></article>
        @endforeach
    </div>
    <div class="cart-drawer__footer"><div><span>Total</span><strong>${{ number_format($cartItems->sum('subtotal'), 2) }}</strong></div><a href="{{ route('cart.index') }}" class="primary-button">Ver carrito completo</a></div>
@endif
