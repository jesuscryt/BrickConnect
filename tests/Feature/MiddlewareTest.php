<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // CheckBanned
    // ─────────────────────────────────────────────────────────────

    public function test_usuario_baneado_permanente_es_expulsado(): void
    {
        $user = User::factory()->create([
            'banned_at'     => now(),
            'ban_expires_at' => null,
            'ban_reason'    => 'Conducta inapropiada',
        ]);

        $response = $this->actingAs($user)->get('/feed');

        // Redirige al login
        $response->assertRedirect(route('login'));

        // El error contiene el motivo del ban
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Conducta inapropiada',
            session('errors')->first('email')
        );

        // La sesión ya no tiene usuario autenticado
        $this->assertGuest();
    }

    public function test_usuario_baneado_temporal_activo_es_expulsado(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => now()->addDays(7),
            'ban_reason'     => null,
        ]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_usuario_baneado_temporal_expirado_puede_acceder(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now()->subDays(10),
            'ban_expires_at' => now()->subDays(3), // ban ya caducó
        ]);

        $response = $this->actingAs($user)->get('/feed');

        // El ban ha expirado: debe poder acceder al feed
        $response->assertOk();
        $this->assertAuthenticated();
    }

    public function test_usuario_no_baneado_puede_acceder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/feed');

        $response->assertOk();
        $this->assertAuthenticated();
    }

    public function test_mensaje_ban_incluye_fecha_expiracion(): void
    {
        $expira = now()->addDays(5);

        $user = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => $expira,
            'ban_reason'     => null,
        ]);

        $this->actingAs($user)->get('/feed');

        $error = session('errors')->first('email');
        $this->assertStringContainsString($expira->format('d/m/Y'), $error);
    }

    // ─────────────────────────────────────────────────────────────
    // IsAdmin
    // ─────────────────────────────────────────────────────────────

    public function test_no_admin_recibe_403_en_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_puede_acceder_al_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_usuario_no_autenticado_redirige_al_login_en_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }
}
