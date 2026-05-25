<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /** Panel de control: estadísticas generales */
    public function index()
    {
        // Contadores para las tarjetas del dashboard
        $totalUsuarios  = User::count();
        $totalBaneados  = User::whereNotNull('banned_at')->count();
        $totalPosts     = Post::count();
        $totalOfertas   = Oferta::count();

        $ultimosUsuarios = User::latest()->take(5)->get();

        return view('admin.index', compact(
            'totalUsuarios', 'totalBaneados', 'totalPosts', 'totalOfertas', 'ultimosUsuarios'
        ));
    }

    /** Listar todos los usuarios */
    public function usuarios(Request $request)
    {
        $query = User::latest();

        // Búsqueda por nombre o email (case-insensitive)
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('name', 'ilike', "%{$busqueda}%")
                  ->orWhere('email', 'ilike', "%{$busqueda}%");
            });
        }

        // Filtro por estado: activo o baneado
        if ($request->filled('estado')) {
            if ($request->estado === 'baneado') {
                // Baneados actualmente: tienen banned_at y el ban no ha expirado (permanente o futuro)
                $query->whereNotNull('banned_at')
                      ->where(function ($q) {
                          $q->whereNull('ban_expires_at')
                            ->orWhere('ban_expires_at', '>=', now());
                      });
            } elseif ($request->estado === 'activo') {
                // Activos: nunca baneados O ban ya expirado
                $query->where(function ($q) {
                    $q->whereNull('banned_at')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('ban_expires_at')
                             ->where('ban_expires_at', '<', now());
                      });
                });
            }
        }

        $usuarios = $query->paginate(20)->withQueryString();

        return view('admin.usuarios', compact('usuarios'));
    }

    /** Banear un usuario con duración y motivo opcionales */
    public function banear(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'No se puede banear a un administrador.');
        }

        $request->validate([
            'dias'   => 'required|in:1,3,7,15,30,90,permanente',
            'motivo' => 'nullable|string|max:500',
        ]);

        // Calcular fecha de expiración; null indica ban permanente
        $expira = $request->dias === 'permanente'
            ? null
            : now()->addDays((int) $request->dias);

        $user->update([
            'banned_at'      => now(),
            'ban_expires_at' => $expira,
            'ban_reason'     => $request->motivo ?: null,
        ]);

        $duracion = $request->dias === 'permanente' ? 'permanentemente' : "por {$request->dias} día(s)";

        return back()->with('success', "Usuario \"{$user->name}\" baneado {$duracion}.");
    }

    /** Desbanear un usuario */
    public function desbanear(User $user)
    {
        // Limpiar todos los campos de ban para restaurar el acceso
        $user->update([
            'banned_at'      => null,
            'ban_expires_at' => null,
            'ban_reason'     => null,
        ]);

        return back()->with('success', "Usuario \"{$user->name}\" desbaneado correctamente.");
    }

    /** Eliminar permanentemente un usuario */
    public function eliminarUsuario(User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'No se puede eliminar a un administrador.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        // Recoger rutas de imágenes de posts antes de borrar
        $imagenesPost = $user->posts()->whereNotNull('imagen')->pluck('imagen')->toArray();

        // Eliminar avatar si existe
        if ($user->avatar) {
            Storage::disk('s3')->delete($user->avatar);
        }

        $nombre = $user->name;
        $user->delete();

        // Eliminar imágenes de posts del storage
        foreach ($imagenesPost as $img) {
            Storage::disk('s3')->delete($img);
        }

        return back()->with('success', "Usuario \"{$nombre}\" eliminado permanentemente.");
    }

    /** Listar todas las publicaciones */
    public function publicaciones(Request $request)
    {
        $query = Post::with('user')->latest();

        // Búsqueda en el contenido del post (case-insensitive)
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where('contenido', 'ilike', "%{$busqueda}%");
        }

        $posts = $query->paginate(20)->withQueryString();

        return view('admin.publicaciones', compact('posts'));
    }

    /** Eliminar cualquier publicación */
    public function eliminarPublicacion(Post $post)
    {
        // Borrar imagen de S3 antes de eliminar el registro
        if ($post->imagen) {
            Storage::disk('s3')->delete($post->imagen);
        }

        $post->delete();

        return back()->with('success', 'Publicación eliminada correctamente.');
    }

    /** Listar todas las ofertas */
    public function ofertas(Request $request)
    {
        $query = Oferta::with('user')->latest();

        // Búsqueda por título o empresa (case-insensitive)
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('titulo', 'ilike', "%{$busqueda}%")
                  ->orWhere('empresa', 'ilike', "%{$busqueda}%");
            });
        }

        $ofertas = $query->paginate(20)->withQueryString();

        return view('admin.ofertas', compact('ofertas'));
    }

    /** Eliminar cualquier oferta */
    public function eliminarOferta(Oferta $oferta)
    {
        $oferta->delete();

        return back()->with('success', 'Oferta eliminada correctamente.');
    }
}
