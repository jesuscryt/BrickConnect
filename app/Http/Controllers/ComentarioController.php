<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Notificacion;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * Controlador de comentarios.
 * Crear y eliminar comentarios en publicaciones del feed.
 */
class ComentarioController extends Controller
{
    /** Crear un nuevo comentario en un post */
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'contenido' => 'required|string|max:1000',
        ]);

        $comentario = $post->comentarios()->create([
            'user_id'   => auth()->id(),
            'contenido' => $request->contenido,
        ]);

        // Notificar al dueño del post (nunca notificarse a uno mismo)
        if ($post->user_id !== auth()->id()) {
            Notificacion::create([
                'usuario_id'       => $post->user_id,
                'emisor_id'        => auth()->id(),
                'tipo'             => 'comentario',
                'notificable_type' => Comentario::class,
                'notificable_id'   => $comentario->id,
            ]);
        }

        return back();
    }

    /** Eliminar un comentario (solo el autor del comentario, vía Policy) */
    public function destroy(Comentario $comentario)
    {
        $this->authorize('delete', $comentario);

        $comentario->delete();

        return back();
    }
}
