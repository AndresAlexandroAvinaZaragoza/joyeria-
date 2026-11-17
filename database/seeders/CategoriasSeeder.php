<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Anillos', 'Aretes', 'Pulseras', 'Collares',
            'Cadenas', 'Dijes', 'Relojes', 'Tobilleras',
            'Broqueles', 'Arracadas',
        ];

        $materiales = [
            'Oro amarillo', 'Oro blanco', 'Plata',
            'Acero inoxidable', 'Platino', 'Titanio',
            'Oro rosa', 'Cobre', 'Perlas', 'Diamantes',
        ];

        $colecciones = [
            'Clasica', 'Moderna', 'Elegante', 'Vintage',
            'Minimalista', 'Premium', 'Juvenil', 'Casual',
            'Formal', 'Romantica', 'Geometrica', 'Floral',
            'Artesanal', 'Exclusiva', 'Contemporanea',
            'Tradicional', 'Lujosa', 'Bohemia',
            'Primavera', 'Especial',
        ];

        $categorias = [];

        foreach ($tipos as $tipo) {
            foreach ($materiales as $material) {
                foreach ($colecciones as $coleccion) {

                    $nombre = "$tipo de $material - $coleccion";

                    $categorias[] = [
                        'nombre' => $nombre.' (Prueba)',
                        'slug' => Str::slug(
                            "prueba-$tipo-$material-$coleccion"
                        ),
                        'descripcion' => "Categoria de prueba de $tipo elaborada en $material, coleccion $coleccion.",
                        'status' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Evitar duplicados si se ejecuta nuevamente
        $existentes = DB::table('categorias')
            ->whereIn('slug', array_column($categorias, 'slug'))
            ->pluck('slug')
            ->all();

        $categorias = array_filter(
            $categorias,
            fn ($c) => ! in_array($c['slug'], $existentes)
        );

        $creados = 0;

        foreach (array_chunk($categorias, 500) as $bloque) {
            DB::table('categorias')->insert($bloque);
            $creados += count($bloque);
        }

        $this->command->info(
            "Categorias nuevas creadas: $creados"
        );
    }
}
