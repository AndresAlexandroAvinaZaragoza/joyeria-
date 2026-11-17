<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');

        // Contraseña para todos los clientes de prueba
        $password = Hash::make('Cliente123!');

        $clientes = [];

        for ($i = 1; $i <= 2000; $i++) {

            // Generar correo único
            do {
                $email = 'cliente'.$faker->unique()->numberBetween(100000, 99999999)
                    .'@example.test';
            } while (DB::table('users')->where('email', $email)->exists());

            $clientes[] = [
                'name' => $faker->firstName(),
                'apellidos' => $faker->lastName().' '.$faker->lastName(),
                'email' => $email,
                'telefono' => '33'.$faker->numerify('########'),
                'email_verified_at' => null,
                'password' => $password,
                'remember_token' => null,
                'status' => 1,
                'rol_id' => 4,
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ];

            // Insertar por bloques de 500
            if (count($clientes) === 500) {
                DB::table('users')->insert($clientes);
                $clientes = [];
            }
        }

        // Insertar los registros restantes
        if (! empty($clientes)) {
            DB::table('users')->insert($clientes);
        }

        $this->command->info('Se generaron 2,000 clientes correctamente.');
    }
}
