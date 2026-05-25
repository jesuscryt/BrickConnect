<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de autenticación.
 * Gestiona registro, login y logout.
 */
class AuthController extends Controller
{
    // ── Vistas ──

    /** Mostrar formulario de login */
    public function showLogin()
    {
        return view('auth.login');
    }

    /** Mostrar formulario de registro */
    public function showRegistro()
    {
        return view('auth.registro');
    }

    // ── Acciones ──

    /** Procesar el registro de un nuevo usuario */
    public function registro(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'name'     => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-]+$/u'],
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'regex:/[a-zA-Z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'name.max'           => 'El nombre no puede superar los 50 caracteres.',
            'name.regex'         => 'El nombre solo puede contener letras, espacios y guiones.',
            'email.required'     => 'El email es obligatorio.',
            'email.email'        => 'Introduce un email válido.',
            'email.unique'       => 'Este email ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'     => 'La contraseña debe contener al menos una letra y un número.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear el usuario en la base de datos
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Iniciar sesión automáticamente
        Auth::login($user);

        return redirect()->route('feed');
    }

    /** Procesar el inicio de sesión */
    public function login(Request $request)
    {
        // Validar credenciales
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'Introduce un email válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Intentar autenticación
        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales, $request->filled('recordar'))) {
            // Verificar si el usuario está baneado
            if (Auth::user()->isBanned()) {
                $bannedUser = Auth::user();

                // Construir mensaje detallado igual que CheckBanned
                $mensaje = 'Tu cuenta ha sido suspendida';
                if ($bannedUser->ban_expires_at) {
                    $mensaje .= ' hasta el ' . $bannedUser->ban_expires_at->format('d/m/Y');
                } else {
                    $mensaje .= ' permanentemente';
                }
                if ($bannedUser->ban_reason) {
                    $mensaje .= '. Motivo: ' . $bannedUser->ban_reason;
                }
                $mensaje .= '.';

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => $mensaje])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->route('feed');
        }

        // Si falla, volver con error
        return back()->withErrors([
            'email'    => 'El correo o la contraseña no son correctos.',
            'password' => 'El correo o la contraseña no son correctos.',
        ])->onlyInput('email');
    }

    /** Cerrar sesión */
    public function logout(Request $request)
    {
        // Destruir la sesión activa e invalidar el token CSRF para evitar reutilización
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }
}
