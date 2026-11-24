<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class PagosSeeder extends Seeder
{
    public function run(): void
    {
        $pedidos = DB::table('pedidos')
            ->where('numero_pedido', 'like', 'PED-PRUEBA-%')
            ->orderBy('id_pedidos')
            ->get();

        $metodos = [
            'tarjeta_ficticia',
            'transferencia_ficticia',
            'paypal',
        ];

        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($pedidos as $pedido) {
            $existe = DB::table('pagos')
                ->where('pedido_id', $pedido->id_pedidos)
                ->exists();

            if ($existe) {
                $omitidos++;
                continue;
            }

            try {
                $metodo = $metodos[array_rand($metodos)];

                if ($pedido->estado === 'cancelado') {
                    $estadoPago = 'fallido';
                } elseif ($pedido->estado === 'pendiente') {
                    $estadoPago = 'pendiente';
                } else {
                    $estadoPago = 'completado';
                }

                $fechaPedido = Carbon::parse($pedido->created_at);

                $fechaPago = $estadoPago === 'completado'
                    ? $fechaPedido->copy()->addHours(random_int(1, 24))
                    : null;

                DB::table('pagos')->insert([
                    'metodo_pago' => $metodo,
                    'transaccion_id' => null,
                    'paypal_order_id' => null,
                    'paypal_capture_id' => null,
                    'monto' => $pedido->total,
                    'estado' => $estadoPago,
                    'comprobante_url' => null,
                    'fecha_pago' => $fechaPago,
                    'created_at' => $fechaPedido,
                    'pedido_id' => $pedido->id_pedidos,
                ]);

                $creados++;
            } catch (Throwable $e) {
                $errores++;

                $this->command->error(
                    "Error en pedido {$pedido->numero_pedido}: {$e->getMessage()}"
                );
            }
        }

        $this->command->info("Pagos creados: {$creados}");
        $this->command->info("Pagos omitidos: {$omitidos}");
        $this->command->info("Errores: {$errores}");
    }
}
