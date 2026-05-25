<?php

namespace Tests\Feature;

use App\Models\Conexion;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /** Crea dos usuarios con conexión aceptada entre ellos. */
    private function usuariosConectados(): array
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Conexion::factory()->create([
            'emisor_id'   => $user1->id,
            'receptor_id' => $user2->id,
            'estado'      => 'aceptada',
        ]);

        return [$user1, $user2];
    }

    public function test_indice_chat_accesible_para_usuario_autenticado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/chat')->assertOk();
    }

    public function test_usuario_puede_enviar_mensaje_a_contacto(): void
    {
        [$emisor, $receptor] = $this->usuariosConectados();

        $this->actingAs($emisor)->post("/chat/{$receptor->id}", [
            'body' => 'Hola, ¿cómo estás?',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'sender_id'   => $emisor->id,
            'receiver_id' => $receptor->id,
            'body'        => 'Hola, ¿cómo estás?',
        ]);
    }

    public function test_usuario_no_puede_enviar_mensaje_a_no_contacto(): void
    {
        $emisor   = User::factory()->create();
        $receptor = User::factory()->create();

        $this->actingAs($emisor)->post("/chat/{$receptor->id}", [
            'body' => 'Mensaje no permitido',
        ])->assertForbidden();
    }

    public function test_usuario_no_puede_enviarse_mensaje_a_si_mismo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post("/chat/{$user->id}", [
            'body' => 'Mensaje a mí mismo',
        ])->assertRedirect(route('chat.index'));

        $this->assertDatabaseMissing('messages', [
            'sender_id'   => $user->id,
            'receiver_id' => $user->id,
        ]);
    }

    public function test_mensaje_requiere_body(): void
    {
        [$emisor, $receptor] = $this->usuariosConectados();

        $this->actingAs($emisor)->post("/chat/{$receptor->id}", [
            'body' => '',
        ])->assertSessionHasErrors('body');
    }

    public function test_show_chat_marca_mensajes_recibidos_como_leidos(): void
    {
        [$user1, $user2] = $this->usuariosConectados();

        // user1 envía 3 mensajes no leídos a user2
        Message::factory()->count(3)->create([
            'sender_id'   => $user1->id,
            'receiver_id' => $user2->id,
            'read_at'     => null,
        ]);

        // user2 abre el chat → debe marcarlos como leídos
        $this->actingAs($user2)->get("/chat/{$user1->id}")->assertOk();

        // No debe quedar ningún mensaje sin leer de user1 hacia user2
        $sinLeer = Message::where('sender_id', $user1->id)
            ->where('receiver_id', $user2->id)
            ->whereNull('read_at')
            ->count();

        $this->assertSame(0, $sinLeer);
    }

    public function test_usuario_no_puede_ver_chat_con_no_contacto(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1)->get("/chat/{$user2->id}")->assertForbidden();
    }

    public function test_show_chat_consigo_mismo_redirige_al_indice(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get("/chat/{$user->id}")
            ->assertRedirect(route('chat.index'));
    }

    public function test_polling_ajax_devuelve_solo_mensajes_nuevos(): void
    {
        [$user1, $user2] = $this->usuariosConectados();

        $mensajeViejo = Message::factory()->create([
            'sender_id'   => $user2->id,
            'receiver_id' => $user1->id,
        ]);
        $mensajeNuevo = Message::factory()->create([
            'sender_id'   => $user2->id,
            'receiver_id' => $user1->id,
        ]);

        $response = $this->actingAs($user1)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson("/chat/{$user2->id}?since={$mensajeViejo->id}");

        $response->assertOk();

        $ids = collect($response->json('mensajes'))->pluck('id')->toArray();
        $this->assertContains($mensajeNuevo->id, $ids);
        $this->assertNotContains($mensajeViejo->id, $ids);
    }

    public function test_envio_ajax_devuelve_datos_del_mensaje(): void
    {
        [$emisor, $receptor] = $this->usuariosConectados();

        $response = $this->actingAs($emisor)
            ->postJson("/chat/{$receptor->id}", ['body' => 'Mensaje vía AJAX']);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'mensaje' => ['id', 'sender_id', 'body', 'read_at', 'created_at_time'],
            ]);

        $this->assertDatabaseHas('messages', [
            'sender_id'   => $emisor->id,
            'receiver_id' => $receptor->id,
            'body'        => 'Mensaje vía AJAX',
        ]);
    }
}
