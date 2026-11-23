<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnviosSeeder extends Seeder
{
    public function run(): void
    {
        $direcciones = DB::table('direccion_clientes')
            ->where('predeterminada', 1)
            ->get()
            ->keyBy('user_id');

        $pedidos = DB::table('pedidos')
            ->where('numero_pedido', 'like', 'PED-PRUEBA-%')
            ->orderBy('id_pedidos')
            ->get();

        $paqueterias = [
            'DHL',
            'FedEx',
            'Estafeta',
            'Redpack',
            'Paquetexpress',
        ];

        $creados = 0;
        $omitidos = 0;

        foreach ($pedidos as $pedido) {

            // Evitar duplicar envíos.
            $existe = DB::table('envios')
                ->where('pedido_id', $pedido->id_pedidos)
                ->exists();

            if ($existe) {
                $omitidos++;
                continue;
            }

            $direccion = $direcciones->get($pedido->user_id);

            if (!$direccion) {
                $this->command->warn(
                    "Pedido {$pedido->numero_pedido} sin dirección predeterminada."
                );
                $omitidos++;
                continue;
            }

            $destino = implode(', ', array_filter([
                $direccion->calle_numero,
                $direccion->colonia,
                $direccion->ciudad,
                $direccion->estado,
                $direccion->cp,
                $direccion->referencias,
            ]));

            $fechaPedido = Carbon::parse($pedido->created_at);

            $estadoEnvio = match ($pedido->estado) {
                'pendiente', 'confirmado' => 'pendiente',
                'preparando' => 'preparando',
                'enviado' => 'enviado',
                'en_transito' => 'en_transito',
                'entregado' => 'entregado',
                'cancelado' => 'cancelado',
                default => 'pendiente',
            };

            $fechaEnvio = null;
            $fechaEntrega = null;
            $paqueteria = null;
            $numeroGuia = null;

            // Los envíos que ya salieron tienen paquetería y guía.
            if (in_array($estadoEnvio, [
                'enviado',
                'en_transito',
                'entregado',
            ])) {
                $paqueteria = $paqueterias[array_rand($paqueterias)];

                $numeroGuia = 'GUIA-' .
                    str_pad($pedido->id_pedidos, 8, '0', STR_PAD_LEFT);

                $fechaEnvio = $fechaPedido->copy()->addDay();
            }

            if ($estadoEnvio === 'entregado') {
                $fechaEntrega = $fechaEnvio->copy()
                    ->addDays(random_int(2, 6));
            }

            DB::table('envios')->insert([
                'direccion_destino' => $destino,
                'paqueteria' => $paqueteria,
                'numero_guia' => $numeroGuia,
                'estado' => $estadoEnvio,
                'fecha_envio' => $fechaEnvio,
                'fecha_entrega' => $fechaEntrega,
                'created_at' => $fechaPedido,
                'pedido_id' => $pedido->id_pedidos,
            ]);

            $creados++;
        }

        $this->command->info("Envíos creados: {$creados}");
        $this->command->info("Envíos omitidos: {$omitidos}");
    }
}
