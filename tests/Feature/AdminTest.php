<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** Crea y devuelve un usuario administrador. */
    private function crearAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    // ── Acceso al panel ──────────────────────────────────────────────────────

    public function test_usuario_normal_no_puede_acceder_al_panel_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_invitado_no_puede_acceder_al_panel_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_puede_acceder_al_panel(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_admin_puede_listar_usuarios(): void
    {
        $admin = $this->crearAdmin();
        User::factory()->count(3)->create();

        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
    }

    // ── Baneo y desbaneo ─────────────────────────────────────────────────────

    public function test_admin_puede_banear_usuario(): void
    {
        $admin = $this->crearAdmin();
        $user  = User::factory()->create();

        $this->actingAs($admin)->post("/admin/usuarios/{$user->id}/banear", [
            'dias'   => '7',
            'motivo' => 'Comportamiento inapropiado',
        ])->assertRedirect();

        $this->assertNotNull($user->fresh()->banned_at);
        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'ban_reason' => 'Comportamiento inapropiado',
        ]);
    }

    public function test_admin_no_puede_banear_a_otro_administrador(): void
    {
        $admin1 = $this->crearAdmin();
        $admin2 = $this->crearAdmin();

        $this->actingAs($admin1)->post("/admin/usuarios/{$admin2->id}/banear", [
            'dias' => '7',
        ])->assertRedirect();

        $this->assertNull($admin2->fresh()->banned_at);
    }

    public function test_admin_puede_desbanear_usuario(): void
    {
        $admin = $this->crearAdmin();
        $user  = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => now()->addDays(7),
            'ban_reason'     => 'Spam',
        ]);

        $this->actingAs($admin)->post("/admin/usuarios/{$user->id}/desbanear")->assertRedirect();

        $fresh = $user->fresh();
        $this->assertNull($fresh->banned_at);
        $this->assertNull($fresh->ban_expires_at);
        $this->assertNull($fresh->ban_reason);
    }

    // ── Eliminación de usuarios ──────────────────────────────────────────────

    public function test_admin_puede_eliminar_usuario(): void
    {
        Storage::fake('s3');
        $admin = $this->crearAdmin();
        $user  = User::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/usuarios/{$user->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->delete("/admin/usuarios/{$admin->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_no_puede_eliminar_a_otro_administrador(): void
    {
        $admin1 = $this->crearAdmin();
        $admin2 = $this->crearAdmin();

        $this->actingAs($admin1)
            ->delete("/admin/usuarios/{$admin2->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin2->id]);
    }

    // ── Gestión de publicaciones ─────────────────────────────────────────────

    public function test_admin_puede_listar_publicaciones(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->get('/admin/publicaciones')->assertOk();
    }

    public function test_admin_puede_eliminar_cualquier_publicacion(): void
    {
        Storage::fake('s3');
        $admin  = $this->crearAdmin();
        $author = User::factory()->create();
        $post   = Post::factory()->create(['user_id' => $author->id]);

        $this->actingAs($admin)
            ->delete("/admin/publicaciones/{$post->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    // ── Gestión de ofertas ───────────────────────────────────────────────────

    public function test_admin_puede_listar_ofertas(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->get('/admin/ofertas')->assertOk();
    }

    public function test_admin_puede_eliminar_cualquier_oferta(): void
    {
        $admin  = $this->crearAdmin();
        $author = User::factory()->create();
        $oferta = Oferta::factory()->create(['user_id' => $author->id]);

        $this->actingAs($admin)
            ->delete("/admin/ofertas/{$oferta->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
    }
}
