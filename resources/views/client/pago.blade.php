<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Pago | Lesa Joyería</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="store-page">
    @include('layouts.navigation')
    <main class="cart-page"><p class="eyebrow">Finaliza tu selección</p><h1>Pago y entrega</h1>
        @if ($errors->any())<div class="store-notice">{{ $errors->first() }}</div>@endif
        @if ($direcciones->isEmpty())
            <div class="empty-cart"><h2>Agrega una dirección desde tu perfil para continuar.</h2><a href="{{ route('profile.edit') }}" class="primary-button">Ir a mi perfil</a></div>
        @else
            <div class="cart-layout">
                <section class="cart-items">
                    <section class="cart-summary"><p class="eyebrow">Entrega</p><h2>¿Dónde recibes tu pedido?</h2>
                        @foreach ($direcciones as $direccion)<label class="checkout-address"><input type="radio" name="direccion_id" value="{{ $direccion->getKey() }}" @checked($loop->first) required><span>{{ $direccion->calle_numero }}, {{ $direccion->colonia }}<small>{{ $direccion->ciudad }}, {{ $direccion->estado }}, C.P. {{ $direccion->cp }}</small></span></label>@endforeach
                    </section>
                    <section class="cart-summary"><p class="eyebrow">PayPal Sandbox</p><h2>Paga con PayPal</h2><p>Usa una cuenta de prueba de PayPal Developer para completar el pedido.</p><div id="paypal-button-container"></div><p id="paypal-error" class="store-notice" hidden></p></section>
                </section>
                <aside class="cart-summary"><p class="eyebrow">Resumen</p><h2>{{ $items->sum('cantidad') }} pieza(s)</h2><div><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></div><div><span>Envío</span><strong>Gratis</strong></div><div><span>Total</span><strong>${{ number_format($subtotal, 2) }}</strong></div></aside>
            </div>
        @endif
    </main>
</body>
@if ($paypalClientId)
    <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($paypalClientId) }}&currency={{ urlencode($paypalCurrency) }}"></script>
    <script>
        const paypalError = document.getElementById('paypal-error');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const addressInput = () => document.querySelector('input[name="direccion_id"]:checked')?.value;

        const showPaypalError = (message) => {
            paypalError.textContent = message;
            paypalError.hidden = false;
        };

        paypal.Buttons({
            createOrder: async () => {
                const direccionId = addressInput();
                if (!direccionId) {
                    showPaypalError('Selecciona una dirección de entrega.');
                    throw new Error('Dirección requerida.');
                }

                const response = await fetch('{{ route('payments.paypal.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ direccion_id: direccionId }),
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'No se pudo iniciar el pago.');
                }
                return result.id;
            },
            onApprove: async (data) => {
                const response = await fetch('{{ route('payments.paypal.capture') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ order_id: data.orderID }),
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'No se pudo confirmar el pago.');
                }
                window.location.href = result.redirect;
            },
            onError: (error) => showPaypalError(error.message || 'PayPal canceló el pago.'),
        }).render('#paypal-button-container');
    </script>
@else
    <script>document.getElementById('paypal-error').textContent = 'PayPal Sandbox aún no está configurado.'; document.getElementById('paypal-error').hidden = false;</script>
@endif
</html>