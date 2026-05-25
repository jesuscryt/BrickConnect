<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isBanned()) {
            $mensaje = 'Tu cuenta ha sido suspendida.';

            if ($user->ban_reason) {
                $mensaje .= ' Motivo: ' . $user->ban_reason . '.';
            }

            if ($user->ban_expires_at) {
                $mensaje .= ' Podrás volver a acceder el ' . $user->ban_expires_at->format('d/m/Y H:i') . '.';
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => $mensaje]);
        }

        return $next($request);
    }
}
