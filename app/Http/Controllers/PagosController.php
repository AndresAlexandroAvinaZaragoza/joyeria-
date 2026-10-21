<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PagosController extends Controller
{
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'direccion_id' => ['required', 'integer', 'exists:direccion_clientes,id_direccion_clientes'],
            'metodo_pago' => ['required', 'in:tarjeta_ficticia,transferencia_ficticia'],
        ]);

        $direccion = $request->user()->direcciones()->findOrFail($data['direccion_id']);
        $cart = $request->user()->carrito()->with('items.producto')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('cart_status', 'Tu carrito está vacío.');
        }

        $pedido = DB::transaction(function () use ($cart, $direccion, $data, $request): Pedido {
            $items = $cart->items;
            $subtotal = 0;

            foreach ($items as $item) {
                $producto = $item->producto()->with('inventario')->lockForUpdate()->firstOrFail();
                if (! $producto->status || ! $producto->inventario || $producto->inventario->stock_actual < $item->cantidad) {
                    abort(422, "La pieza {$producto->nombre} ya no tiene existencias suficientes.");
                }
                $subtotal += $producto->precio_venta * $item->cantidad;
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
                $producto = $item->producto()->with('inventario')->lockForUpdate()->firstOrFail();
                $importe = $producto->precio_venta * $item->cantidad;
                $pedido->detalles()->create([
                    'precio_unitario' => $producto->precio_venta,
                    'cantidad' => $item->cantidad,
                    'importe' => $importe,
                    'producto_id' => $producto->getKey(),
                ]);
            }

            Pago::create([
                'metodo_pago' => $data['metodo_pago'],
                'transaccion_id' => 'FICT-'.Str::upper(Str::random(12)),
                'monto' => $pedido->total,
                'estado' => 'completado',
                'fecha_pago' => now(),
                'pedido_id' => $pedido->getKey(),
            ]);

            $pedido->envio()->create([
                'direccion_destino' => $this->formatAddress($direccion),
                'estado' => 'pendiente',
            ]);

            $cart->items()->delete();

            return $pedido;
        });

        return redirect()->route('orders.index')->with('status', "Pedido {$pedido->numero_pedido} creado correctamente.");
    }

    private function formatAddress(object $direccion): string
    {
        return "{$direccion->calle_numero}, {$direccion->colonia}, {$direccion->ciudad}, {$direccion->estado}, C.P. {$direccion->cp}"
            .($direccion->referencias ? " ({$direccion->referencias})" : '');
    }
}
