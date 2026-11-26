<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovimientosInventarioSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios activos con rol de administrador o empleado.
        $usuarios = DB::table('users')
            ->where('status', 1)
            ->whereIn('rol_id', [1, 2])
            ->pluck('id')
            ->toArray();

        if (empty($usuarios)) {
            $this->command->error('No hay usuarios activos disponibles.');

            return;
        }

        // Productos generados por nuestro seeder.
        $productos = DB::table('productos')
            ->where('sku', 'like', 'JOY-PRUEBA-%')
            ->get(['id_productos']);

        $entradas = 0;
        $salidas = 0;
        $omitidos = 0;

        foreach ($productos as $indice => $producto) {

            DB::transaction(function () use (
                $producto,
                $indice,
                $usuarios,
                &$entradas,
                &$salidas,
                &$omitidos
            ) {
                // Evitar duplicar movimientos al ejecutar nuevamente.
                $yaTieneMovimientos = DB::table('movimientos_inventario')
                    ->where('producto_id', $producto->id_productos)
                    ->exists();

                if ($yaTieneMovimientos) {
                    $omitidos++;

                    return;
                }

                // Bloquear el inventario mientras se actualiza.
                $inventario = DB::table('inventario')
                    ->where('producto_id', $producto->id_productos)
                    ->lockForUpdate()
                    ->first();

                if (! $inventario) {
                    $omitidos++;

                    return;
                }

                $cantidadEntrada = rand(10, 30);
                $cantidadSalida = 0;

                // Aproximadamente un tercio tendrá una salida.
                if ($indice % 3 === 0) {
                    $cantidadSalida = rand(1, 5);
                }

                $usuario = $usuarios[array_rand($usuarios)];

                $fecha = now()->subDays(rand(1, 90))
                    ->subHours(rand(0, 23))
                    ->subMinutes(rand(0, 59));

                // Registrar entrada.
                DB::table('movimientos_inventario')->insert([
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $cantidadEntrada,
                    'producto_id' => $producto->id_productos,
                    'user_id' => $usuario,
                    'created_at' => $fecha,
                ]);

                $entradas++;

                // Registrar salida cuando corresponda.
                if ($cantidadSalida > 0) {
                    DB::table('movimientos_inventario')->insert([
                        'tipo_movimiento' => 'SALIDA',
                        'cantidad' => $cantidadSalida,
                        'producto_id' => $producto->id_productos,
                        'user_id' => $usuario,
                        'created_at' => $fecha->copy()->addMinutes(5),
                    ]);

                    $salidas++;
                }

                // Actualizar existencias con el saldo neto.
                $nuevoStock = $inventario->stock_actual
                    + $cantidadEntrada
                    - $cantidadSalida;

                DB::table('inventario')
                    ->where('producto_id', $producto->id_productos)
                    ->update([
                        'stock_actual' => $nuevoStock,
                        'updated_at' => now(),
                    ]);
            });
        }

        $this->command->info('Movimientos de inventario generados.');
        $this->command->info("Entradas: $entradas");
        $this->command->info("Salidas: $salidas");
        $this->command->info("Productos omitidos: $omitidos");
    }
}
