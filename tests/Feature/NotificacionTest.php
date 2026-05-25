<?php

namespace Tests\Feature;

use App\Models\Conexion;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionTest extends TestCase
{
    use RefreshDatabase;

    /** Crea una notificación con su Conexion de soporte. */
    private function crearNotificacion(User $receptor, User $emisor, bool $leida = false): Notificacion
    {
        $conexion = Conexion::factory()->create([
            'emisor_id'   => $emisor->id,
            'receptor_id' => $receptor->id,
        ]);

        return Notificacion::factory()->create([
            'usuario_id'       => $receptor->id,
            'emisor_id'        => $emisor->id,
            'notificable_type' => Conexion::class,
            'notificable_id'   => $conexion->id,
            'leida'            => $leida,
        ]);
    }

    public function test_usuario_puede_ver_sus_notificaciones(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/notificaciones')->assertOk();
    }

    public function test_usuario_puede_marcar_notificacion_como_leida(): void
    {
        $user   = User::factory()->create();
        $emisor = User::factory()->create();

        $notificacion = $this->crearNotificacion($user, $emisor);

        $this->actingAs($user)
            ->postJson("/notificaciones/{$notificacion->id}/leer")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notificaciones', [
            'id'    => $notificacion->id,
            'leida' => true,
        ]);
    }

    public function test_usuario_no_puede_marcar_notificacion_ajena_como_leida(): void
    {
        $propietario = User::factory()->create();
        $otro        = User::factory()->create();

        $notificacion = $this->crearNotificacion($propietario, $otro);

        $this->actingAs($otro)
            ->postJson("/notificaciones/{$notificacion->id}/leer")
            ->assertForbidden();

        $this->assertDatabaseHas('notificaciones', [
            'id'    => $notificacion->id,
            'leida' => false,
        ]);
    }

    public function test_usuario_puede_marcar_todas_las_notificaciones_como_leidas(): void
    {
        $user   = User::factory()->create();
        $emisor = User::factory()->create();

        $conexion = Conexion::factory()->create([
            'emisor_id'   => $emisor->id,
            'receptor_id' => $user->id,
        ]);

        Notificacion::factory()->count(3)->create([
            'usuario_id'       => $user->id,
            'emisor_id'        => $emisor->id,
            'notificable_type' => Conexion::class,
            'notificable_id'   => $conexion->id,
            'leida'            => false,
        ]);

        $this->actingAs($user)
            ->postJson('/notificaciones/marcar-todas')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(0, $user->notificacionesNoLeidas()->count());
    }

    public function test_notificaciones_de_otro_usuario_no_se_marcan_al_marcar_todas(): void
    {
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $emisor = User::factory()->create();

        $notifUser1 = $this->crearNotificacion($user1, $emisor);
        $notifUser2 = $this->crearNotificacion($user2, $emisor);

        $this->actingAs($user1)->postJson('/notificaciones/marcar-todas')->assertOk();

        // La notificación de user2 sigue sin leer
        $this->assertDatabaseHas('notificaciones', [
            'id'    => $notifUser2->id,
            'leida' => false,
        ]);
    }
}
