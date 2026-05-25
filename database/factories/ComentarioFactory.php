<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComentarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id'   => Post::factory(),
            'user_id'   => User::factory(),
            'contenido' => fake()->sentence(),
        ];
    }
}
