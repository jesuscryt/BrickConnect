<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Lista de conversaciones del usuario autenticado.
     * Muestra todos los contactos aceptados, con o sin mensajes previos.
     */
    public function index()
    {
        $userId = Auth::id();
        $user   = Auth::user();

        // Obtener todos los contactos aceptados
        $contactos = $user->contactos()->get()->keyBy('id');

        // Obtener todos los mensajes del usuario agrupados por interlocutor
        $mensajesPorUsuario = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with('sender', 'receiver')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            });

        // Construir lista unificada: contactos con y sin mensajes
        $conversaciones = collect();

        foreach ($contactos as $contactoId => $contacto) {
            if ($mensajesPorUsuario->has($contactoId)) {
                $msgs   = $mensajesPorUsuario[$contactoId];
                $ultimo = $msgs->sortByDesc('created_at')->first();
                $noLeidos = $msgs->filter(fn($m) => $m->receiver_id === $userId && $m->read_at === null)->count();

                $conversaciones->push([
                    'interlocutor'    => $contacto,
                    'ultimo_mensaje'  => $ultimo,
                    'no_leidos'       => $noLeidos,
                    'tiene_mensajes'  => true,
                ]);
            } else {
                $conversaciones->push([
                    'interlocutor'    => $contacto,
                    'ultimo_mensaje'  => null,
                    'no_leidos'       => 0,
                    'tiene_mensajes'  => false,
                ]);
            }
        }

        // Primero los que tienen mensajes recientes, luego los que no
        $conversaciones = $conversaciones->sortByDesc(function ($c) {
            return $c['tiene_mensajes'] ? $c['ultimo_mensaje']->created_at->timestamp : -1;
        });

        return view('chat.index', compact('conversaciones'));
    }

    /**
     * Conversación con un usuario concreto.
     * Acepta ?since={id} vía AJAX para devolver solo mensajes nuevos (polling).
     */
    public function show(Request $request, User $user)
    {
        $authId = Auth::id();

        // Prevenir chatear consigo mismo
        if ($user->id === $authId) {
            return redirect()->route('chat.index');
        }

        // Verificar que sean contactos aceptados
        $esContacto = Auth::user()->contactos()->where('users.id', $user->id)->exists();
        if (!$esContacto) {
            abort(403, 'Solo puedes enviar mensajes a tus contactos.');
        }

        // Marcar como leídos los mensajes recibidos de ese usuario
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // ── Modo polling AJAX: devolver solo mensajes nuevos desde ?since=id ──
        if ($request->ajax() && $request->filled('since')) {
            $nuevos = Message::where(function ($q) use ($authId, $user) {
                // Agrupar el OR en una clausula para que el AND posterior se aplique a ambas ramas
                $q->where(function ($inner) use ($authId, $user) {
                    $inner->where('sender_id', $authId)->where('receiver_id', $user->id);
                })->orWhere(function ($inner) use ($authId, $user) {
                    $inner->where('sender_id', $user->id)->where('receiver_id', $authId);
                });
            })
            ->where('id', '>', (int) $request->since)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'                  => $m->id,
                'sender_id'           => $m->sender_id,
                'body'                => $m->body,
                'read_at'             => $m->read_at,
                'created_at_time'     => $m->created_at->format('H:i'),
                'created_at_formatted'=> $m->created_at->tiempoRelativo(),
            ]);

            return response()->json(['mensajes' => $nuevos]);
        }

        // ── Vista normal ──
        $mensajes = Message::where(function ($q) use ($authId, $user) {
            $q->where('sender_id', $authId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($authId, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $authId);
        })->orderBy('created_at')->get();

        return view('chat.show', compact('user', 'mensajes'));
    }

    /**
     * Enviar un mensaje.
     */
    public function send(Request $request, User $user)
    {
        // Prevenir mensajes a uno mismo
        if ($user->id === Auth::id()) {
            return redirect()->route('chat.index');
        }

        // Solo entre contactos aceptados
        $esContacto = Auth::user()->contactos()->where('users.id', $user->id)->exists();
        if (!$esContacto) {
            abort(403, 'Solo puedes enviar mensajes a tus contactos.');
        }

        $request->validate(['body' => 'required|string|max:2000']);

        // Persistir el mensaje en la BD
        $mensaje = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $user->id,
            'body'        => $request->body,
        ]);

        // Respuesta AJAX: devolver datos del mensaje para actualizar la UI sin recargar
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'mensaje' => [
                    'id'               => $mensaje->id,
                    'sender_id'        => $mensaje->sender_id,
                    'body'             => $mensaje->body,
                    'read_at'          => $mensaje->read_at,
                    'created_at_time'  => $mensaje->created_at->format('H:i'),
                    'created_at_formatted' => $mensaje->created_at->tiempoRelativo(),
                ],
            ]);
        }

        return redirect()->route('chat.show', $user->id);
    }
}
