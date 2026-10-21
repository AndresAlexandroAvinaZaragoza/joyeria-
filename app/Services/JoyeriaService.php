<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JoyeriaService
{
    public function calculateCartSubtotal(Carrito $cart): float
    {
        return (float) $cart->items()->with('producto')->get()->sum(
            fn (CarritoItem $item): float => (float) $item->producto->precio_venta * $item->cantidad
        );
    }

    public function productMargin(Producto $product): float
    {
        if ((float) $product->precio_venta === 0.0) {
            return 0.0;
        }

        return round((((float) $product->precio_venta - (float) $product->precio_costo) / (float) $product->precio_venta) * 100, 2);
    }

    public function availableStock(Producto $product): int
    {
        return (int) ($product->inventario?->stock_actual ?? 0);
    }

    public function customerTotal(int $userId): float
    {
        return (float) Pedido::where('user_id', $userId)->where('estado', '<>', 'cancelado')->sum('total');
    }

    public function generateOrderNumber(): string
    {
        return 'LS-'.now()->format('ymdHis').'-'.Str::upper(Str::random(4));
    }

    public function addCartItem(Carrito $cart, Producto $product, int $quantity): void
    {
        $stock = $this->availableStock($product);
        $current = (int) $cart->items()->where('producto_id', $product->getKey())->value('cantidad');

        if ($quantity <= 0 || $current + $quantity > $stock) {
            throw new \DomainException('La cantidad solicitada supera el stock disponible.');
        }

        $item = $cart->items()->firstOrNew(['producto_id' => $product->getKey()]);
        $item->cantidad = ($item->cantidad ?? 0) + $quantity;
        $item->save();
    }

    public function adjustInventory(Producto $product, int $userId, string $type, int $quantity): void
    {
        DB::transaction(function () use ($product, $userId, $type, $quantity): void {
            $inventory = $product->inventario()->lockForUpdate()->firstOrFail();
            $newStock = match ($type) {
                'ENTRADA' => $inventory->stock_actual + $quantity,
                'SALIDA' => $inventory->stock_actual - $quantity,
                'AJUSTE' => $quantity,
                default => throw new \DomainException('Tipo de movimiento inválido.'),
            };

            if ($newStock < 0) {
                throw new \DomainException('No hay stock suficiente para realizar la salida.');
            }

            $inventory->update(['stock_actual' => $newStock]);
            MovimientoInventario::create([
                'tipo_movimiento' => $type,
                'cantidad' => $quantity,
                'created_at' => now(),
                'producto_id' => $product->getKey(),
                'user_id' => $userId,
            ]);
        });
    }

    public function cancelOrder(Pedido $order, int $userId): void
    {
        DB::transaction(function () use ($order, $userId): void {
            $order->refresh();
            $order->load('detalles');

            if (in_array($order->estado, ['enviado', 'entregado', 'cancelado'], true)) {
                throw new \DomainException('No es posible cancelar este pedido.');
            }

            foreach ($order->detalles as $detail) {
                $inventory = $detail->producto()->firstOrFail()->inventario()->lockForUpdate()->firstOrFail();
                $inventory->increment('stock_actual', $detail->cantidad);
                MovimientoInventario::create([
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $detail->cantidad,
                    'created_at' => now(),
                    'producto_id' => $detail->producto_id,
                    'user_id' => $userId,
                ]);
            }

            $order->update(['estado' => 'cancelado']);
        });
    }
}
