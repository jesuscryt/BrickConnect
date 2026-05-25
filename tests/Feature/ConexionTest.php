<?php

namespace Tests\Feature;

use App\Models\Conexion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConexionTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_enviar_solicitud_de_conexion(): void
    {
        $emisor   = User::factory()->create();
        $receptor = User::factory()->create();

        $this->actingAs($emisor)->post("/conexion/{$receptor->id}")->assertRedirect();

        $this->assertDatabaseHas('conexiones', [
            'emisor_id'   => $emisor->id,
            'receptor_id' => $receptor->id,
            'estado'      => 'pendiente',
        ]);
    }

    public function test_usuario_no_puede_conectarse_consigo_mismo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post("/conexion/{$user->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('conexiones', ['emisor_id' => $user->id, 'receptor_id' => $user->id]);
    }

    public function test_receptor_puede_aceptar_solicitud(): void
    {
        $emisor   = User::factory()->create();
        $receptor = User::factory()->create();
        $conexion = Conexion::factory()->create([
            'emisor_id'   => $emisor->id,
            'receptor_id' => $receptor->id,
            'estado'      => 'pendiente',
        ]);

        $this->actingAs($receptor)->put("/conexion/{$conexion->id}/aceptar")->assertRedirect();

        $this->assertDatabaseHas('conexiones', ['id' => $conexion->id, 'estado' => 'aceptada']);

        // El emisor debe recibir una notificación de tipo 'solicitud_aceptada'
        $this->assertDatabaseHas('notificaciones', [
            'usuario_id' => $emisor->id,
            'emisor_id'  => $receptor->id,
            'tipo'       => 'solicitud_aceptada',
        ]);
    }

    public function test_solo_el_receptor_puede_aceptar_solicitud(): void
    {
        $emisor   = User::factory()->create();
        $receptor = User::factory()->create();
        $tercero  = User::factory()->create();
        $conexion = Conexion::factory()->create([
            'emisor_id'   => $emisor->id,
            'receptor_id' => $receptor->id,
        ]);

        $this->actingAs($tercero)->put("/conexion/{$conexion->id}/aceptar")->assertForbidden();
    }

    public function test_receptor_puede_rechazar_solicitud(): void
    {
        $emisor   = User::factory()->create();
        $receptor = User::factory()->create();
        $conexion = Conexion::factory()->create([
            'emisor_id'   => $emisor->id,
            'receptor_id' => $receptor->id,
        ]);

        $this->actingAs($receptor)->put("/conexion/{$conexion->id}/rechazar")->assertRedirect();

        $this->assertDatabaseHas('conexiones', ['id' => $conexion->id, 'estado' => 'rechazada']);
    }
}
