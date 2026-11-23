<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprasSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = DB::table('users')
            ->whereIn('rol_id', [1, 2])
            ->where('status', 1)
            ->pluck('id');

        if ($usuarios->isEmpty()) {
            $this->command->error('No hay administradores o empleados activos.');
            return;
        }

        $insertadas = 0;
        $omitidas = 0;

        DB::transaction(function () use ($usuarios, &$insertadas, &$omitidas) {

            for ($i = 1; $i <= 1500; $i++) {

                $numeroCompra = 'COMP-PRUEBA-' . str_pad(
                    $i,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                $existe = DB::table('compras')
                    ->where('numero_compra', $numeroCompra)
                    ->exists();

                if ($existe) {
                    $omitidas++;
                    continue;
                }

                $fecha = now()->subDays(rand(1, 365));

                DB::table('compras')->insert([
                    'numero_compra' => $numeroCompra,
                    'user_id' => $usuarios->random(),
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]);

                $insertadas++;
            }
        });

        $this->command->info("Compras insertadas: {$insertadas}");
        $this->command->info("Compras omitidas: {$omitidas}");
    }
}
