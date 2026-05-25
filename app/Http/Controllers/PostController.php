<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador de publicaciones (feed).
 * Gestiona el muro de publicaciones tipo LinkedIn.
 */
class PostController extends Controller
{
    /** Mostrar el feed con todas las publicaciones */
    public function index()
    {
        // Eager loading completo para evitar N+1
        $posts = Post::with(['user', 'comentarios.user'])->latest()->paginate(10);
        $ofertasRecientes = Oferta::where('activa', true)->latest()->take(5)->get();
        $numContactos = Auth::user()->contactos()->count();

        return view('feed', compact('posts', 'ofertasRecientes', 'numContactos'));
    }

    /** Mostrar una publicación individual (enlace compartido) */
    public function show(Post $post)
    {
        $post->load(['user', 'comentarios.user']);
        $ofertasRecientes = Oferta::where('activa', true)->latest()->take(5)->get();
        $numContactos = Auth::user()->contactos()->count();

        return view('posts.show', compact('post', 'ofertasRecientes', 'numContactos'));
    }

    /** Guardar una nueva publicación */
    public function store(Request $request)
    {
        $request->validate([
            'contenido' => 'required|string|max:2000',
            'imagen'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'contenido.required' => 'Escribe algo para publicar.',
            'contenido.max'      => 'El texto no puede superar los 2000 caracteres.',
            'imagen.image'       => 'El archivo debe ser una imagen.',
            'imagen.max'         => 'La imagen no puede pesar más de 2MB.',
        ]);

        $datos = [
            'user_id'   => Auth::id(),
            'contenido' => $request->contenido,
        ];

        // Si se adjunta una imagen, guardarla en R2/S3
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('posts', 's3');
        }

        Post::create($datos);

        return redirect()->route('feed');
    }

    /** Eliminar una publicación propia */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        // Eliminar imagen si existe
        if ($post->imagen) {
            Storage::disk('s3')->delete($post->imagen);
        }

        $post->delete();

        return redirect()->route('feed');
    }
}
