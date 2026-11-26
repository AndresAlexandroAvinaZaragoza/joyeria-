<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = DB::table('categorias')
            ->where('status', 1)
            ->pluck('id_categorias')
            ->toArray();

        if (empty($categorias)) {
            $this->command->error('No existen categorías activas.');

            return;
        }

        $tipos = [
            'Anillo',
            'Pulsera',
            'Dije',
            'Collar',
            'Aretes',
            'Cadena',
            'Esclava',
            'Broquel',
            'Arracadas',
            'Medalla',
        ];

        $materiales = [
            'Oro amarillo',
            'Oro blanco',
            'Plata 925',
            'Acero inoxidable',
            'Oro rosa',
            'Plata oxidada',
            'Oro laminado',
            'Titanio',
            'Platino',
            'Cobre',
        ];

        $colecciones = [
            'Clásica',
            'Moderna',
            'Elegante',
            'Vintage',
            'Minimalista',
            'Premium',
            'Juvenil',
            'Romántica',
            'Geométrica',
            'Floral',
        ];

        $tallas = [
            '12', '14', '16', '18', '20',
            '22', '24', '26', '28', '30',
        ];

        $existentes = DB::table('productos')
            ->pluck('sku')
            ->flip()
            ->all();

        $registros = [];
        $total = 2000;
        $omitidos = 0;

        for ($i = 1; $i <= $total; $i++) {

            $sku = 'JOY-PRUEBA-'.str_pad($i, 6, '0', STR_PAD_LEFT);

            // Evitar duplicar productos al ejecutar nuevamente el seeder.
            if (isset($existentes[$sku])) {
                $omitidos++;

                continue;
            }

            $tipo = $tipos[($i - 1) % count($tipos)];
            $material = $materiales[($i - 1) % count($materiales)];
            $coleccion = $colecciones[($i - 1) % count($colecciones)];

            $costo = rand(150, 5000);
            $precioVenta = round($costo * (1 + (rand(30, 100) / 100)), 2);

            $registros[] = [
                'sku' => $sku,
                'nombre' => "$tipo de $material $coleccion Modelo $i",
                'descripcion' => "Pieza de joyería de la colección $coleccion, fabricada en $material.",
                'material' => $material,
                'peso_gr' => round(rand(1, 150) / 10, 2),
                'talla_medida' => $tallas[($i - 1) % count($tallas)],
                'precio_costo' => $costo,
                'precio_venta' => $precioVenta,
                'status' => 1,
                'id_categoria' => $categorias[($i - 1) % count($categorias)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar en bloques para evitar consultas demasiado grandes.
        foreach (array_chunk($registros, 500) as $bloque) {
            DB::table('productos')->insert($bloque);
        }

        $this->command->info('Seeder de productos ejecutado.');
        $this->command->info('Productos nuevos: '.count($registros));
        $this->command->info('Productos omitidos por SKU existente: '.$omitidos);
    }
}
