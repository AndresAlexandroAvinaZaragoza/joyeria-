<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pago | Lesa Joyería</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="store-page">
    @include('layouts.navigation')
    <main class="cart-page"><p class="eyebrow">Finaliza tu selección</p><h1>Pago y entrega</h1>
        @if ($errors->any())<div class="store-notice">{{ $errors->first() }}</div>@endif
        @if ($direcciones->isEmpty())
            <div class="empty-cart"><h2>Agrega una dirección desde tu perfil para continuar.</h2><a href="{{ route('profile.edit') }}" class="primary-button">Ir a mi perfil</a></div>
        @else
            <div class="cart-layout">
                <form method="POST" action="{{ route('payments.store') }}" class="cart-items">@csrf
                    <section class="cart-summary"><p class="eyebrow">Entrega</p><h2>¿Dónde recibes tu pedido?</h2>
                        @foreach ($direcciones as $direccion)<label class="checkout-address"><input type="radio" name="direccion_id" value="{{ $direccion->getKey() }}" @checked($loop->first) required><span>{{ $direccion->calle_numero }}, {{ $direccion->colonia }}<small>{{ $direccion->ciudad }}, {{ $direccion->estado }}, C.P. {{ $direccion->cp }}</small></span></label>@endforeach
                    </section>
                    <section class="cart-summary"><p class="eyebrow">Pago ficticio</p><h2>Selecciona un método</h2><label class="checkout-address"><input type="radio" name="metodo_pago" value="tarjeta_ficticia" checked><span>Tarjeta de prueba<small>No se realizará ningún cargo real.</small></span></label><label class="checkout-address"><input type="radio" name="metodo_pago" value="transferencia_ficticia"><span>Transferencia de prueba<small>Se marcará como completada automáticamente.</small></span></label><button type="submit" class="primary-button">Confirmar pedido</button></section>
                </form>
                <aside class="cart-summary"><p class="eyebrow">Resumen</p><h2>{{ $items->sum('cantidad') }} pieza(s)</h2><div><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></div><div><span>Envío</span><strong>Gratis</strong></div><div><span>Total</span><strong>${{ number_format($subtotal, 2) }}</strong></div></aside>
            </div>
        @endif
    </main>
</body>
</html>