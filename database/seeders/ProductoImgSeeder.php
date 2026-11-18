<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoImgSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = 'productos/imagen-generica.jpeg';

        if (!Storage::disk('public')->exists($ruta)) {
            $this->command->error(
                'No existe la imagen: storage/app/public/' . $ruta
            );
            return;
        }

        $productos = DB::table('productos')
            ->where('sku', 'like', 'JOY-PRUEBA-%')
            ->get(['id_productos']);

        $insertados = 0;

        foreach ($productos as $producto) {
            $existe = DB::table('producto_img')
                ->where('producto_id', $producto->id_productos)
                ->where('url_img', $ruta)
                ->exists();

            if ($existe) {
                continue;
            }

            DB::table('producto_img')->insert([
                'url_img' => $ruta,
                'es_principal' => 1,
                'orden' => 0,
                'created_at' => now(),
                'producto_id' => $producto->id_productos,
            ]);

            $insertados++;
        }

        $this->command->info(
            "Imágenes genéricas registradas: $insertados"
        );
    }
}