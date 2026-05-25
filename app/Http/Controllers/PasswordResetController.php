<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Controlador de recuperación de contraseña.
 * Usa el Password Broker nativo de Laravel.
 */
class PasswordResetController extends Controller
{
    /** Mostrar formulario para solicitar el enlace de recuperación */
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    /** Enviar el enlace de recuperación por email */
    public function sendLink(Request $request)
    {
        $request->validate(
            ['email' => 'required|email'],
            ['email.required' => 'El email es obligatorio.', 'email.email' => 'Introduce un email válido.']
        );

        // Enviamos siempre el mismo mensaje para evitar enumeración de usuarios (anti-enumeration)
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Si el email existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.');
    }

    /** Mostrar formulario para introducir la nueva contraseña */
    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Procesar el cambio de contraseña */
    public function update(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'email.required'     => 'El email es obligatorio.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
