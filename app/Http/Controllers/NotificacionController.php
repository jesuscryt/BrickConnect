<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Notificacion;
use App\Models\OfertaAceptacion;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /**
     * Mostrar todas las notificaciones del usuario
     */
    public function index()
    {
        $user = Auth::user();
        // Carga profunda para evitar N+1: emisor y notificable con sus relaciones anidadas
        $notificaciones = $user->notificaciones()
            ->with(['emisor', 'notificable' => function (MorphTo $morphTo) {
                $morphTo->constrain([
                    Comentario::class       => fn($q) => $q->with('post'),
                    OfertaAceptacion::class => fn($q) => $q->with('oferta'),
                ]);
            }])
            ->latest()
            ->paginate(15);

        $contarNoLeidas = $user->notificacionesNoLeidas()->count();

        return view('notificaciones.index', compact('notificaciones', 'contarNoLeidas'));
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarLeida(Notificacion $notificacion)
    {
        // Solo el propietario de la notificación puede marcarla como leída
        if ($notificacion->usuario_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $notificacion->update(['leida' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    /**
     * Marcar todas las notificaciones del usuario como leídas
     */
    public function marcarTodasLeidas()
    {
        // Actualización en batch: una sola query para todas las no leídas
        Auth::user()->notificacionesNoLeidas()->update(['leida' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
