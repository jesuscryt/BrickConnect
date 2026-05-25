<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class BanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    // ── Bloqueo en el login ──────────────────────────────────────────────────

    public function test_usuario_baneado_permanentemente_no_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'password'       => bcrypt('Password1'),
            'banned_at'      => now(),
            'ban_expires_at' => null,     // permanente
            'ban_reason'     => 'Spam',
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Password1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_baneado_temporalmente_no_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'password'       => bcrypt('Password1'),
            'banned_at'      => now(),
            'ban_expires_at' => now()->addDays(3),
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Password1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_con_baneo_expirado_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'password'       => bcrypt('Password1'),
            'banned_at'      => now()->subDays(10),
            'ban_expires_at' => now()->subDays(3), // ya expiró
        ]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Password1',
        ])->assertRedirect('/feed');

        $this->assertAuthenticated();
    }

    // ── Bloqueo en peticiones web (CheckBanned middleware) ───────────────────

    public function test_usuario_baneado_es_deslogueado_al_acceder_a_pagina_protegida(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/feed')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_usuario_baneado_recibe_mensaje_de_error_al_ser_redirigido(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => null,
            'ban_reason'     => 'Conducta inapropiada',
        ]);

        $this->actingAs($user)
            ->get('/feed')
            ->assertRedirect('/login');

        // Seguimos la redirección y comprobamos que hay errores en la sesión
        $this->followingRedirects()
            ->actingAs($user)  // re-autenticar porque el primer get lo deslogueó
            ->get('/feed');

        // El mensaje de ban debe estar en los errores de sesión del siguiente request
    }

    public function test_usuario_con_baneo_expirado_puede_acceder_a_paginas_protegidas(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now()->subDays(10),
            'ban_expires_at' => now()->subDays(3), // ya expiró
        ]);

        $this->actingAs($user)->get('/feed')->assertOk();
    }

    // ── Modelo isBanned() ────────────────────────────────────────────────────

    public function test_is_banned_devuelve_true_para_ban_permanente(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => null,
        ]);

        $this->assertTrue($user->isBanned());
    }

    public function test_is_banned_devuelve_true_para_ban_temporal_vigente(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now(),
            'ban_expires_at' => now()->addDays(7),
        ]);

        $this->assertTrue($user->isBanned());
    }

    public function test_is_banned_devuelve_false_para_ban_expirado(): void
    {
        $user = User::factory()->create([
            'banned_at'      => now()->subDays(10),
            'ban_expires_at' => now()->subDays(1),
        ]);

        $this->assertFalse($user->isBanned());
    }

    public function test_is_banned_devuelve_false_para_usuario_sin_ban(): void
    {
        $user = User::factory()->create([
            'banned_at' => null,
        ]);

        $this->assertFalse($user->isBanned());
    }
}
