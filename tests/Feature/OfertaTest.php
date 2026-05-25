<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfertaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_crear_oferta(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/ofertas', [
            'titulo'        => 'Albañil para obra',
            'empresa'       => 'Constructora SA',
            'ubicacion'     => 'Madrid',
            'descripcion'   => 'Se busca albañil con experiencia.',
            'tipo_contrato' => 'Tiempo completo',
            'salario'       => 30000,
        ]);

        $response->assertRedirect('/ofertas');
        $this->assertDatabaseHas('ofertas', ['titulo' => 'Albañil para obra', 'user_id' => $user->id]);
    }

    public function test_usuario_puede_aceptar_oferta_ajena(): void
    {
        $creador   = User::factory()->create();
        $candidato = User::factory()->create();
        $oferta    = Oferta::factory()->create(['user_id' => $creador->id, 'activa' => true]);

        $response = $this->actingAs($candidato)
            ->postJson("/ofertas/{$oferta->id}/toggle-accept");

        $response->assertOk();
        $this->assertDatabaseHas('oferta_aceptaciones', [
            'oferta_id' => $oferta->id,
            'user_id'   => $candidato->id,
        ]);
    }

    public function test_creador_no_puede_aceptar_su_propia_oferta(): void
    {
        $user   = User::factory()->create();
        $oferta = Oferta::factory()->create(['user_id' => $user->id, 'activa' => true]);

        $response = $this->actingAs($user)
            ->postJson("/ofertas/{$oferta->id}/toggle-accept");

        $response->assertForbidden();
    }

    public function test_no_se_puede_aceptar_oferta_inactiva(): void
    {
        $creador   = User::factory()->create();
        $candidato = User::factory()->create();
        $oferta    = Oferta::factory()->create(['user_id' => $creador->id, 'activa' => false]);

        $response = $this->actingAs($candidato)
            ->postJson("/ofertas/{$oferta->id}/toggle-accept");

        $response->assertForbidden();
    }

    public function test_solo_el_creador_puede_eliminar_la_oferta(): void
    {
        $creador = User::factory()->create();
        $otro    = User::factory()->create();
        $oferta  = Oferta::factory()->create(['user_id' => $creador->id]);

        $this->actingAs($otro)->delete("/ofertas/{$oferta->id}")->assertForbidden();
        $this->assertDatabaseHas('ofertas', ['id' => $oferta->id]);
    }

    public function test_creador_puede_desactivar_oferta(): void
    {
        $user   = User::factory()->create();
        $oferta = Oferta::factory()->create(['user_id' => $user->id, 'activa' => true]);

        $this->actingAs($user)->post("/ofertas/{$oferta->id}/desactivar")->assertRedirect();
        $this->assertDatabaseHas('ofertas', ['id' => $oferta->id, 'activa' => false]);
    }
}
