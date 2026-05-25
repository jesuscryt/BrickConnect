<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // La ruta POST /forgot-password tiene throttle:5,1
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    // ── Formulario de solicitud ──────────────────────────────────────────────

    public function test_formulario_solicitud_enlace_es_accesible(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_solicitud_enlace_con_email_existente_devuelve_mensaje_generico(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');
    }

    public function test_solicitud_enlace_con_email_inexistente_devuelve_el_mismo_mensaje(): void
    {
        // Anti-enumeration: el mensaje de sesión debe ser el mismo aunque el email no exista
        $this->post('/forgot-password', ['email' => 'noexiste@example.com'])
            ->assertSessionHas('status');
    }

    public function test_solicitud_enlace_requiere_email_valido(): void
    {
        $this->post('/forgot-password', ['email' => 'no-es-un-email'])
            ->assertSessionHasErrors('email');
    }

    // ── Formulario de nueva contraseña ───────────────────────────────────────

    public function test_formulario_reset_es_accesible_con_token(): void
    {
        $this->get('/reset-password/token-cualquiera?email=test@example.com')
            ->assertOk();
    }

    // ── Proceso de cambio de contraseña ─────────────────────────────────────

    public function test_puede_restablecer_contrasena_con_token_valido(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NuevaContrasena1',
            'password_confirmation' => 'NuevaContrasena1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NuevaContrasena1', $user->fresh()->password));
    }

    public function test_reset_con_token_invalido_devuelve_error(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token'                 => 'token-invalido-xxxxx',
            'email'                 => $user->email,
            'password'              => 'NuevaContrasena1',
            'password_confirmation' => 'NuevaContrasena1',
        ])->assertSessionHasErrors('email');

        // La contraseña no debe haber cambiado
        $this->assertFalse(Hash::check('NuevaContrasena1', $user->fresh()->password));
    }

    public function test_reset_requiere_confirmacion_de_contrasena(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'NuevaContrasena1',
            'password_confirmation' => 'ContrasenaDiferente1',
        ])->assertSessionHasErrors('password');
    }

    public function test_nueva_contrasena_debe_tener_letras_y_numeros(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'soloLetras',
            'password_confirmation' => 'soloLetras',
        ])->assertSessionHasErrors('password');
    }
}
