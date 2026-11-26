<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompraDetallesSeeder extends Seeder
{
    public function run(): void
    {
        $compras = DB::table('compras')
            ->where('numero_compra', 'like', 'COMP-PRUEBA-%')
            ->select('id_compra')
            ->get();

        $productos = DB::table('productos')
            ->join('inventario', 'productos.id_productos', '=', 'inventario.producto_id')
            ->where('productos.sku', 'like', 'JOY-PRUEBA-%')
            ->select(
                'productos.id_productos',
                'productos.precio_costo'
            )
            ->get();

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos de prueba con inventario.');

            return;
        }

        $insertados = 0;
        $omitidas = 0;

        foreach ($compras as $compra) {

            $tieneDetalles = DB::table('compra_detalles')
                ->where('compra_id', $compra->id_compra)
                ->exists();

            if ($tieneDetalles) {
                $omitidas++;

                continue;
            }

            $seleccionados = $productos
                ->shuffle()
                ->take(rand(3, 8));

            DB::transaction(function () use (
                $compra,
                $seleccionados,
                &$insertados
            ) {
                foreach ($seleccionados as $producto) {

                    $cantidad = rand(5, 20);

                    $costoOriginal = (float) $producto->precio_costo;

                    // Variación ficticia de hasta 10% en el costo.
                    $factor = rand(9000, 11000) / 10000;

                    $costoUnitario = round(
                        $costoOriginal * $factor,
                        2
                    );

                    DB::table('compra_detalles')->insert([
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costoUnitario,
                        'compra_id' => $compra->id_compra,
                        'producto_id' => $producto->id_productos,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // El trigger actualiza el inventario y el costo.
                    $insertados++;
                }
            });
        }

        $this->command->info("Detalles insertados: {$insertados}");
        $this->command->info("Compras omitidas: {$omitidas}");
    }
}
