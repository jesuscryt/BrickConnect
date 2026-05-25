<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario de prueba
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Cuenta de administrador
        User::factory()->create([
            'name'     => 'Administrador',
            'email'    => 'admin@construlink.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Admin1234'),
            'is_admin' => true,
        ]);
    }
}
