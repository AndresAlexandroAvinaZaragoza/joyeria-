<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

class PedidoHistorialSeeder extends Seeder
{
    public function run(): void
    {
        $pedidos = DB::table('pedidos')
            ->where('numero_pedido', 'like', 'PED-PRUEBA-%')
            ->orderBy('id_pedidos')
            ->get();

        $secuencias = [
            'pendiente' => [
                'pendiente',
            ],

            'confirmado' => [
                'pendiente',
                'confirmado',
            ],

            'preparando' => [
                'pendiente',
                'confirmado',
                'preparando',
            ],

            'enviado' => [
                'pendiente',
                'confirmado',
                'preparando',
                'enviado',
            ],

            'en_transito' => [
                'pendiente',
                'confirmado',
                'preparando',
                'enviado',
                'en_transito',
            ],

            'entregado' => [
                'pendiente',
                'confirmado',
                'preparando',
                'enviado',
                'en_transito',
                'entregado',
            ],

            'cancelado' => [
                'pendiente',
                'cancelado',
            ],
        ];

        $motivos = [
            'pendiente' => 'Pedido registrado y pendiente de atención.',
            'confirmado' => 'El pedido fue confirmado.',
            'preparando' => 'El pedido se encuentra en preparación.',
            'enviado' => 'El pedido fue enviado por paquetería.',
            'en_transito' => 'El pedido se encuentra en tránsito.',
            'entregado' => 'El pedido fue entregado al cliente.',
            'cancelado' => 'El pedido fue cancelado.',
        ];

        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($pedidos as $pedido) {

            if (! isset($secuencias[$pedido->estado])) {
                $this->command->warn(
                    "Estado desconocido en pedido {$pedido->numero_pedido}: {$pedido->estado}"
                );

                continue;
            }

            $fechaBase = Carbon::parse($pedido->created_at);

            foreach ($secuencias[$pedido->estado] as $indice => $estado) {

                $existe = DB::table('pedido_historial')
                    ->where('pedido_id', $pedido->id_pedidos)
                    ->where('estado', $estado)
                    ->exists();

                if ($existe) {
                    $omitidos++;

                    continue;
                }

                try {
                    $fecha = $fechaBase->copy()
                        ->addHours($indice * 2);

                    DB::table('pedido_historial')->insert([
                        'pedido_id' => $pedido->id_pedidos,
                        'estado' => $estado,
                        'user_id' => null,
                        'motivo' => $motivos[$estado],
                        'created_at' => $fecha,
                        'updated_at' => $fecha,
                    ]);

                    $creados++;

                } catch (Throwable $e) {
                    $errores++;

                    $this->command->error(
                        "Error en pedido {$pedido->numero_pedido}, estado {$estado}: {$e->getMessage()}"
                    );
                }
            }
        }

        $this->command->info('========== RESUMEN ==========');
        $this->command->info("Pedidos revisados: {$pedidos->count()}");
        $this->command->info("Historiales creados: {$creados}");
        $this->command->info("Historiales omitidos: {$omitidos}");
        $this->command->info("Errores: {$errores}");
    }
}
