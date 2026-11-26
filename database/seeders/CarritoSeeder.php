<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarritoSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = DB::table('users')
            ->where('rol_id', 4)
            ->where('email', 'like', 'cliente%@example.test')
            ->select('id')
            ->get();

        $insertados = 0;
        $omitidos = 0;

        foreach ($clientes as $cliente) {

            $existe = DB::table('carrito')
                ->where('user_id', $cliente->id)
                ->exists();

            if ($existe) {
                $omitidos++;

                continue;
            }

            DB::table('carrito')->insert([
                'session_token' => null,
                'user_id' => $cliente->id,
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ]);

            $insertados++;
        }

        $this->command->info("Carritos insertados: {$insertados}");
        $this->command->info("Clientes omitidos por tener carrito: {$omitidos}");
    }
}
