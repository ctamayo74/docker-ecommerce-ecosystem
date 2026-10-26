<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder principal de la aplicación. Ejecuta:
     *  - Un usuario/cliente de prueba con credenciales conocidas.
     *  - El catálogo de productos de ejemplo (ProductSeeder).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'Cliente de Prueba',
                'password' => bcrypt('password123'),
                'phone' => '+503 7000-0000',
                'address' => 'Soyapango, San Salvador, El Salvador',
            ]
        );

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
