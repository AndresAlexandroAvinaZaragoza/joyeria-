<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [];

        for ($i = 1; $i <= 2000; $i++) {

            $numero = str_pad($i, 4, '0', STR_PAD_LEFT);

            $roles[] = [
                'nombre' => "ROL_PRUEBA_$numero",
                'descripcion' => "Rol ficticio numero $i generado para pruebas del sistema de joyeria.",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Evitar duplicados
        $existentes = DB::table('rol')
            ->whereIn('nombre', array_column($roles, 'nombre'))
            ->pluck('nombre')
            ->all();

        $roles = array_filter(
            $roles,
            fn ($r) => ! in_array($r['nombre'], $existentes)
        );

        $creados = 0;

        foreach (array_chunk($roles, 500) as $bloque) {
            DB::table('rol')->insert($bloque);
            $creados += count($bloque);
        }

        $this->command->info(
            "Roles nuevos creados: $creados"
        );
    }
}
