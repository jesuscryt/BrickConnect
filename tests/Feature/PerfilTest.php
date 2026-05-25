<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_de_usuario_es_visible(): void
    {
        $visitante = User::factory()->create();
        $objetivo  = User::factory()->create();

        $this->actingAs($visitante)
            ->get("/perfil/{$objetivo->id}")
            ->assertOk();
    }

    public function test_usuario_puede_ver_su_propio_perfil(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get("/perfil/{$user->id}")
            ->assertOk();
    }

    public function test_usuario_puede_editar_su_perfil(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/perfil', [
            'name'      => 'Carlos Martínez',
            'profesion' => 'Arquitecto',
            'empresa'   => 'Constructora SA',
            'ubicacion' => 'Madrid',
            'sobre_mi'  => 'Profesional con 10 años de experiencia.',
            'telefono'  => '+34 600 123 456',
        ])->assertRedirect(route('perfil.show', $user->id));

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'name'     => 'Carlos Martínez',
            'profesion' => 'Arquitecto',
        ]);
    }

    public function test_perfil_requiere_nombre(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/perfil', [
            'name' => '',
        ])->assertSessionHasErrors('name');
    }

    public function test_perfil_nombre_con_caracteres_invalidos_es_rechazado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/perfil', [
            'name' => 'Juan<script>',
        ])->assertSessionHasErrors('name');
    }

    public function test_usuario_puede_subir_avatar(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();

        $this->actingAs($user)->put('/perfil', [
            'name'   => $user->name,
            'avatar' => UploadedFile::fake()->create('avatar.jpg', 50, 'image/jpeg'),
        ])->assertRedirect(route('perfil.show', $user->id));

        $this->assertNotNull($user->fresh()->avatar);
    }

    public function test_formulario_de_edicion_solo_accesible_para_el_propio_usuario(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/perfil/editar')->assertOk();
    }
}
