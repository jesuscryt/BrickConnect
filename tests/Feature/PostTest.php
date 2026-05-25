<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_crear_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'contenido' => 'Mi primera publicación de prueba.',
        ]);

        $response->assertRedirect('/feed');
        $this->assertDatabaseHas('posts', [
            'user_id'   => $user->id,
            'contenido' => 'Mi primera publicación de prueba.',
        ]);
    }

    public function test_post_requiere_contenido(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', ['contenido' => '']);

        $response->assertSessionHasErrors('contenido');
    }

    public function test_usuario_puede_eliminar_su_propio_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/posts/{$post->id}");

        $response->assertRedirect('/feed');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_usuario_no_puede_eliminar_post_ajeno(): void
    {
        $propietario = User::factory()->create();
        $otro        = User::factory()->create();
        $post        = Post::factory()->create(['user_id' => $propietario->id]);

        $this->actingAs($otro)->delete("/posts/{$post->id}")->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_usuario_puede_comentar_un_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comentarios", [
            'contenido' => 'Gran publicación!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comentarios', [
            'post_id'   => $post->id,
            'user_id'   => $user->id,
            'contenido' => 'Gran publicación!',
        ]);
    }

    public function test_usuario_no_puede_eliminar_comentario_ajeno(): void
    {
        $propietario = User::factory()->create();
        $otro        = User::factory()->create();
        $post        = Post::factory()->create();
        $comentario  = Comentario::factory()->create(['user_id' => $propietario->id, 'post_id' => $post->id]);

        $this->actingAs($otro)->delete("/comentarios/{$comentario->id}")->assertForbidden();
    }

    public function test_post_con_imagen_se_guarda_correctamente(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'contenido' => 'Post con imagen.',
            'imagen'    => UploadedFile::fake()->create('obra.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect('/feed');
        $post = Post::where('user_id', $user->id)->first();
        $this->assertNotNull($post->imagen);
    }
}
