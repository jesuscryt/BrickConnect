<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfertaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'titulo'           => fake()->jobTitle(),
            'empresa'          => fake()->company(),
            'ubicacion'        => fake()->city(),
            'descripcion'      => fake()->paragraphs(2, true),
            'tipo_contrato'    => fake()->randomElement(['Tiempo completo', 'Media jornada', 'Temporal', 'Por obra']),
            'salario'          => fake()->optional()->randomFloat(2, 1000, 60000),
            'salario_periodo'  => fake()->optional()->randomElement(['mensual', 'anual', 'por hora']),
            'salario_tipo'     => fake()->optional()->randomElement(['bruto', 'neto']),
            'horas_semanales'  => fake()->optional()->numberBetween(20, 40),
            'activa'           => true,
        ];
    }
}
