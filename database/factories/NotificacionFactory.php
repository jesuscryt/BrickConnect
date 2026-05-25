<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Conexion;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'usuario_id'       => User::factory(),
            'emisor_id'        => User::factory(),
            'tipo'             => 'solicitud_amistad',
            'notificable_type' => Conexion::class,
            'notificable_id'   => Conexion::factory(), // lazy: se crea solo cuando la notificación se persiste
            'leida'            => false,
        ];
    }
}
