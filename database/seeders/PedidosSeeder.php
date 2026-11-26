<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PedidosSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = DB::table('users')
            ->where('rol_id', 4)
            ->where('status', 1)
            ->where('email', 'like', 'cliente%@example.test')
            ->pluck('id')
            ->toArray();

        if (empty($clientes)) {
            $this->command->error('No se encontraron clientes de prueba.');

            return;
        }

        $estados = [
            'pendiente',
            'pendiente',
            'confirmado',
            'confirmado',
            'preparando',
            'preparando',
            'enviado',
            'en_transito',
            'entregado',
            'entregado',
            'cancelado',
        ];

        $creados = 0;
        $omitidos = 0;

        for ($i = 1; $i <= 1500; $i++) {

            $numero = 'PED-PRUEBA-'.str_pad($i, 6, '0', STR_PAD_LEFT);

            if (DB::table('pedidos')
                ->where('numero_pedido', $numero)
                ->exists()) {
                $omitidos++;

                continue;
            }

            $estado = $estados[array_rand($estados)];

            // Fechas coherentes con el estado del pedido.
            $dias = match ($estado) {
                'entregado' => random_int(10, 365),
                'enviado', 'en_transito' => random_int(4, 60),
                'preparando' => random_int(1, 7),
                'cancelado' => random_int(1, 90),
                default => random_int(0, 30),
            };

            $fecha = Carbon::now()
                ->subDays($dias)
                ->subMinutes(random_int(0, 1439));

            $costosEnvio = [0, 79, 99, 129, 149];
            $costoEnvio = $costosEnvio[array_rand($costosEnvio)];

            DB::table('pedidos')->insert([
                'numero_pedido' => $numero,
                'subtotal' => 0,
                'costo_envio' => $costoEnvio,
                'total' => 0,
                'estado' => $estado,
                'user_id' => $clientes[array_rand($clientes)],
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            $creados++;
        }

        $this->command->info("Pedidos creados: {$creados}");
        $this->command->info("Pedidos omitidos: {$omitidos}");
    }
}
