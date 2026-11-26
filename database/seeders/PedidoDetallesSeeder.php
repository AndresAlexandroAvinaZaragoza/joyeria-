<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class PedidoDetallesSeeder extends Seeder
{
    public function run(): void
    {
        $pedidos = DB::table('pedidos')
            ->where('numero_pedido', 'like', 'PED-PRUEBA-%')
            ->orderBy('id_pedidos')
            ->get();

        $creados = 0;
        $omitidos = 0;
        $errores = 0;
        $detallesGenerados = 0;

        foreach ($pedidos as $pedido) {

            // No generar detalles para pedidos cancelados.
            if ($pedido->estado === 'cancelado') {
                $omitidos++;

                continue;
            }

            // Evitar duplicar detalles.
            $tieneDetalles = DB::table('pedido_detalles')
                ->where('pedido_id', $pedido->id_pedidos)
                ->exists();

            if ($tieneDetalles) {
                $omitidos++;

                continue;
            }

            try {

                DB::transaction(function () use (
                    $pedido,
                    &$detallesGenerados
                ) {

                    // Obtener productos activos con inventario disponible.
                    $productos = DB::table('productos')
                        ->join(
                            'inventario',
                            'productos.id_productos',
                            '=',
                            'inventario.producto_id'
                        )
                        ->where('productos.sku', 'like', 'JOY-PRUEBA-%')
                        ->where('productos.status', 1)
                        ->where('productos.precio_venta', '>', 0)
                        ->where('inventario.stock_actual', '>', 0)
                        ->select([
                            'productos.id_productos',
                            'productos.precio_venta',
                            'inventario.stock_actual',
                        ])
                        ->inRandomOrder()
                        ->limit(4)
                        ->lockForUpdate()
                        ->get()
                        ->all();

                    if (count($productos) < 2) {
                        throw new \RuntimeException(
                            'No hay suficientes productos con stock.'
                        );
                    }

                    // Seleccionar entre 2 y 4 productos diferentes.
                    shuffle($productos);

                    $cantidadProductos = random_int(
                        2,
                        count($productos)
                    );

                    $productos = array_slice(
                        $productos,
                        0,
                        $cantidadProductos
                    );

                    $subtotal = 0;

                    $fecha = Carbon::parse($pedido->created_at);

                    foreach ($productos as $producto) {

                        $stockDisponible = (int) $producto->stock_actual;

                        // Entre 1 y 3 unidades, sin superar el stock.
                        $cantidad = random_int(
                            1,
                            min(3, $stockDisponible)
                        );

                        $precio = (float) $producto->precio_venta;

                        $importe = round(
                            $precio * $cantidad,
                            2
                        );

                        DB::table('pedido_detalles')->insert([
                            'precio_unitario' => $precio,
                            'cantidad' => $cantidad,
                            'importe' => $importe,
                            'pedido_id' => $pedido->id_pedidos,
                            'producto_id' => $producto->id_productos,
                            'created_at' => $fecha,
                            'updated_at' => $fecha,
                        ]);

                        // El trigger descuenta el inventario.
                        $subtotal += $importe;

                        $detallesGenerados++;
                    }

                    $subtotal = round($subtotal, 2);
                    $costoEnvio = (float) $pedido->costo_envio;
                    $total = round($subtotal + $costoEnvio, 2);

                    // Actualizar importes del pedido.
                    DB::table('pedidos')
                        ->where('id_pedidos', $pedido->id_pedidos)
                        ->update([
                            'subtotal' => $subtotal,
                            'total' => $total,
                        ]);
                });

                $creados++;

            } catch (Throwable $e) {

                $errores++;

                $this->command->error(
                    "Error en pedido {$pedido->numero_pedido}: {$e->getMessage()}"
                );
            }
        }

        $this->command->info("Pedidos procesados: {$creados}");
        $this->command->info("Detalles generados: {$detallesGenerados}");
        $this->command->info("Pedidos omitidos: {$omitidos}");
        $this->command->info("Pedidos con errores: {$errores}");
    }
}
