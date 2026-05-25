<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConexionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'emisor_id'   => User::factory(),
            'receptor_id' => User::factory(),
            'estado'      => 'pendiente',
        ];
    }

    public function aceptada(): static
    {
        return $this->state(['estado' => 'aceptada']);
    }
}
