<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_usuario_puede_registrarse(): void
    {
        $response = $this->post('/registro', [
            'name'                  => 'Juan García',
            'email'                 => 'juan@test.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertRedirect('/feed');
        $this->assertDatabaseHas('users', ['email' => 'juan@test.com']);
    }

    public function test_registro_requiere_email_unico(): void
    {
        User::factory()->create(['email' => 'existente@test.com']);

        $response = $this->post('/registro', [
            'name'                  => 'Juan',
            'email'                 => 'existente@test.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_usuario_puede_hacer_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Password1')]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertRedirect('/feed');
        $this->assertAuthenticated();
    }

    public function test_login_falla_con_credenciales_incorrectas(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correcto')]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'incorrecto',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_rutas_protegidas_redirigen_al_login(): void
    {
        $this->get('/feed')->assertRedirect('/login');
        $this->get('/ofertas')->assertRedirect('/login');
        $this->get('/red')->assertRedirect('/login');
        $this->get('/chat')->assertRedirect('/login');
    }
}
