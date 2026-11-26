<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DireccionClientesSeeder extends Seeder
{
    public function run(): void
    {
        $colonias = [
            'Centro',
            'San Miguel',
            'Las Flores',
            'Los Pinos',
            'La Esperanza',
            'Jardines del Valle',
            'Lomas del Sol',
            'El Rosario',
            'Santa Cecilia',
            'La Providencia',
        ];

        $calles = [
            'Benito Juárez',
            'Miguel Hidalgo',
            'Morelos',
            'Francisco I. Madero',
            'Reforma',
            'Independencia',
            'Allende',
            'Emiliano Zapata',
            '5 de Mayo',
            'Constitución',
        ];

        $ciudades = [
            ['ciudad' => 'Arandas', 'estado' => 'Jalisco', 'cp' => '47180'],
            ['ciudad' => 'Guadalajara', 'estado' => 'Jalisco', 'cp' => '44100'],
            ['ciudad' => 'Tepatitlán', 'estado' => 'Jalisco', 'cp' => '47600'],
            ['ciudad' => 'León', 'estado' => 'Guanajuato', 'cp' => '37000'],
            ['ciudad' => 'Aguascalientes', 'estado' => 'Aguascalientes', 'cp' => '20000'],
        ];

        $referencias = [
            'Casa de fachada blanca',
            'Frente al parque',
            'A un costado de una tienda',
            'Casa con portón negro',
            'Entre dos avenidas principales',
            'Cerca de la escuela',
            'Casa de dos plantas',
            'Frente a una farmacia',
        ];

        $clientes = DB::table('users')
            ->where('rol_id', 4)
            ->where('email', 'like', 'cliente%@example.test')
            ->select('id')
            ->get();

        $insertadas = 0;
        $omitidas = 0;

        foreach ($clientes as $cliente) {

            $existe = DB::table('direccion_clientes')
                ->where('user_id', $cliente->id)
                ->exists();

            if ($existe) {
                $omitidas++;

                continue;
            }

            $ubicacion = $ciudades[array_rand($ciudades)];

            DB::table('direccion_clientes')->insert([
                'calle_numero' => $calles[array_rand($calles)]
                    .' #'.rand(10, 999),

                'colonia' => $colonias[array_rand($colonias)],

                'ciudad' => $ubicacion['ciudad'],

                'estado' => $ubicacion['estado'],

                'cp' => $ubicacion['cp'],

                'referencias' => $referencias[array_rand($referencias)],

                'predeterminada' => 1,

                'user_id' => $cliente->id,

                'created_at' => now()->subDays(rand(1, 180)),

                'updated_at' => now(),
            ]);

            $insertadas++;
        }

        $this->command->info("Direcciones insertadas: {$insertadas}");
        $this->command->info("Clientes omitidos por tener dirección: {$omitidas}");
    }
}
