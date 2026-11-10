<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Pago;
use App\Models\Pedido;
use App\Services\PayPalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class PagosController extends Controller
{
    public function __construct(private readonly PayPalService $paypal) {}

    public function create(Request $request): View|RedirectResponse
    {
        $cart = $request->user()->carrito()->with('items.producto')->first();
        $items = $cart?->items ?? collect();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('cart_status', 'Agrega una pieza antes de continuar al pago.');
        }

        return view('client.pago', [
            'items' => $items,
            'subtotal' => $items->sum(fn ($item) => $item->producto->precio_venta * $item->cantidad),
            'direcciones' => $request->user()->direcciones()->latest()->get(),
            'paypalClientId' => config('services.paypal.client_id'),
            'paypalCurrency' => config('services.paypal.currency'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'direccion_id' => ['required', 'integer', 'exists:direccion_clientes,id_direccion_clientes'],
            'metodo_pago' => ['required', 'in:tarjeta_ficticia,transferencia_ficticia'],
        ]);

        $pedido = $this->createPendingOrder($request, (int) $data['direccion_id'], $data['metodo_pago']);
        $pedido->pago()->update([
            'transaccion_id' => 'FICT-'.Str::upper(Str::random(12)),
            'estado' => 'completado',
            'fecha_pago' => now(),
        ]);
        $this->completeOrder($pedido);

        return redirect()->route('orders.index')->with('status', "Pedido {$pedido->numero_pedido} creado correctamente.");
    }

    public function createPayPalOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'direccion_id' => ['required', 'integer', 'exists:direccion_clientes,id_direccion_clientes'],
        ]);

        try {
            $cart = $request->user()->carrito()->with('items.producto')->first();
            if (! $cart || $cart->items->isEmpty()) {
                return response()->json(['message' => 'Tu carrito está vacío.'], 422);
            }

            $paypalOrder = $this->paypal->createOrder(
                number_format((float) $cart->items->sum(fn ($item) => $item->producto->precio_venta * $item->cantidad), 2, '.', ''),
                config('services.paypal.currency'),
            );
            $paypalOrderId = $paypalOrder['id'] ?? null;

            if (! is_string($paypalOrderId) || $paypalOrderId === '') {
                throw new RuntimeException('PayPal no devolvió un identificador de orden.');
            }

            $pedido = $this->createPendingOrder($request, (int) $data['direccion_id'], 'paypal');
            $pedido->pago()->update(['paypal_order_id' => $paypalOrderId]);

            return response()->json(['id' => $paypalOrderId]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible iniciar el pago con PayPal.',
            ], 502);
        }
    }

    public function capturePayPalOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'string', 'max:255'],
        ]);

        $pago = Pago::query()
            ->where('paypal_order_id', $data['order_id'])
            ->whereHas('pedido', fn ($query) => $query->where('user_id', $request->user()->getKey()))
            ->with('pedido')
            ->firstOrFail();

        if ($pago->estado === 'completado') {
            return response()->json(['redirect' => route('orders.index')]);
        }

        try {
            $capture = $this->paypal->captureOrder($data['order_id']);
            $captureStatus = data_get($capture, 'status');
            $captureId = data_get($capture, 'purchase_units.0.payments.captures.0.id');
            $capturedAmount = data_get($capture, 'purchase_units.0.payments.captures.0.amount.value');
            $capturedCurrency = data_get($capture, 'purchase_units.0.payments.captures.0.amount.currency_code');

            if (
                $captureStatus !== 'COMPLETED'
                || ! is_string($captureId)
                || $captureId === ''
                || ! is_string($capturedAmount)
                || number_format((float) $capturedAmount, 2, '.', '') !== number_format((float) $pago->monto, 2, '.', '')
                || $capturedCurrency !== config('services.paypal.currency')
            ) {
                $pago->update(['estado' => 'fallido']);

                return response()->json(['message' => 'PayPal no confirmó el pago.'], 422);
            }

            DB::transaction(function () use ($pago, $captureId): void {
                $lockedPago = Pago::query()->lockForUpdate()->findOrFail($pago->getKey());
                if ($lockedPago->estado === 'completado') {
                    return;
                }

                $lockedPago->update([
                    'paypal_capture_id' => $captureId,
                    'transaccion_id' => $captureId,
                    'estado' => 'completado',
                    'fecha_pago' => now(),
                ]);
                $this->completeOrder($lockedPago->pedido()->lockForUpdate()->firstOrFail());
            });

            return response()->json(['redirect' => route('orders.index')]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible confirmar el pago con PayPal.',
            ], 502);
        }
    }

    private function createPendingOrder(Request $request, int $addressId, string $paymentMethod): Pedido
    {
        $address = $request->user()->direcciones()->findOrFail($addressId);
        $cart = $request->user()->carrito()->with('items.producto')->first();

        if (! $cart || $cart->items->isEmpty()) {
            abort(422, 'Tu carrito está vacío.');
        }

        return DB::transaction(function () use ($cart, $address, $paymentMethod, $request): Pedido {
            $items = $cart->items;
            $subtotal = 0;

            foreach ($items as $item) {
                $product = $item->producto()->with('inventario')->lockForUpdate()->firstOrFail();
                if (! $product->status || ! $product->inventario || $product->inventario->stock_actual < $item->cantidad) {
                    abort(422, "La pieza {$product->nombre} ya no tiene existencias suficientes.");
                }
                $subtotal += $product->precio_venta * $item->cantidad;
            }

            $pedido = Pedido::create([
                'numero_pedido' => 'LS-'.now()->format('ymdHis').'-'.Str::upper(Str::random(4)),
                'subtotal' => $subtotal,
                'costo_envio' => 0,
                'total' => $subtotal,
                'estado' => 'pendiente',
                'user_id' => $request->user()->getKey(),
            ]);

            foreach ($items as $item) {
                $product = $item->producto;
                $pedido->detalles()->create([
                    'precio_unitario' => $product->precio_venta,
                    'cantidad' => $item->cantidad,
                    'importe' => $product->precio_venta * $item->cantidad,
                    'producto_id' => $product->getKey(),
                ]);
            }

            Pago::create([
                'metodo_pago' => $paymentMethod,
                'monto' => $pedido->total,
                'estado' => 'pendiente',
                'pedido_id' => $pedido->getKey(),
            ]);

            $pedido->envio()->create([
                'direccion_destino' => $this->formatAddress($address),
                'estado' => 'pendiente',
            ]);

            $cart->items()->delete();

            return $pedido;
        });
    }

    private function completeOrder(Pedido $pedido): void
    {
        if ($pedido->estado === 'confirmado') {
            return;
        }

        foreach ($pedido->detalles as $detail) {
            MovimientoInventario::create([
                'tipo_movimiento' => 'SALIDA',
                'cantidad' => $detail->cantidad,
                'created_at' => now(),
                'producto_id' => $product->getKey(),
                'user_id' => $pedido->user_id,
            ]);
        }

        $pedido->update(['estado' => 'confirmado']);
    }

    private function formatAddress(object $address): string
    {
        return "{$address->calle_numero}, {$address->colonia}, {$address->ciudad}, {$address->estado}, C.P. {$address->cp}"
            .($address->referencias ? " ({$address->referencias})" : '');
    }
}
