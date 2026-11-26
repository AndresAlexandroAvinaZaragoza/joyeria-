<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarritoItemsSeeder extends Seeder
{
    public function run(): void
    {
        $productos = DB::table('productos')
            ->where('sku', 'like', 'JOY-PRUEBA-%')
            ->where('status', 1)
            ->pluck('id_productos')
            ->shuffle()
            ->values();

        if ($productos->isEmpty()) {
            $this->command->error('No se encontraron productos de prueba activos.');

            return;
        }

        $carritos = DB::table('carrito')
            ->join('users', 'carrito.user_id', '=', 'users.id')
            ->where('users.rol_id', 4)
            ->where('users.email', 'like', 'cliente%@example.test')
            ->select('carrito.id_carrito')
            ->get();

        $insertados = 0;
        $omitidos = 0;

        foreach ($carritos as $carrito) {
            $productosExistentes = DB::table('carrito_items')
                ->where('carrito_id', $carrito->id_carrito)
                ->pluck('producto_id')
                ->all();

            $disponibles = $productos
                ->diff($productosExistentes)
                ->values();

            if ($disponibles->isEmpty()) {
                $omitidos++;

                continue;
            }

            $cantidad = min(rand(2, 5), $disponibles->count());

            $seleccionados = $disponibles
                ->shuffle()
                ->take($cantidad);

            foreach ($seleccionados as $productoId) {
                DB::table('carrito_items')->insert([
                    'cantidad' => rand(1, 3),
                    'producto_id' => $productoId,
                    'carrito_id' => $carrito->id_carrito,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertados++;
            }
        }

        $this->command->info("Productos agregados a carritos: {$insertados}");
        $this->command->info("Carritos omitidos: {$omitidos}");
    }
}
