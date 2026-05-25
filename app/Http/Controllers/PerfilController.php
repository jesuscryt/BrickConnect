<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conexion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador de perfil de usuario.
 * Ver y editar el perfil profesional.
 */
class PerfilController extends Controller
{
    /** Ver el perfil de un usuario */
    public function show(User $user)
    {
        $posts = $user->posts()->with('comentarios.user')->latest()->paginate(5);

        // Estado de conexión con el visitante
        $estasConectado   = false;
        $solicitudEnviada = false;
        $conexionRecibida = null;
        $conexionAceptada = null;

        if (Auth::check() && $user->id !== Auth::id()) {
            $authUser = Auth::user();

            // Conexión aceptada en cualquier dirección (yo → ellos o ellos → yo)
            $conexionAceptada = $authUser->conexionesEnviadas()
                    ->where('receptor_id', $user->id)->where('estado', 'aceptada')->first()
                ?? $authUser->conexionesRecibidas()
                    ->where('emisor_id', $user->id)->where('estado', 'aceptada')->first();

            $estasConectado = $conexionAceptada !== null;

            // Solicitud pendiente enviada por el usuario autenticado
            $solicitudEnviada = Conexion::where('emisor_id', Auth::id())
                ->where('receptor_id', $user->id)
                ->where('estado', 'pendiente')
                ->exists();

            // Solicitud pendiente que el dueño del perfil nos envió a nosotros
            $conexionRecibida = Conexion::where('emisor_id', $user->id)
                ->where('receptor_id', Auth::id())
                ->where('estado', 'pendiente')
                ->first();
        }

        return view('perfil.show', compact(
            'user', 'posts', 'estasConectado', 'solicitudEnviada', 'conexionRecibida', 'conexionAceptada'
        ));
    }

    /** Mostrar formulario de edición del perfil propio */
    public function edit()
    {
        $user = Auth::user();
        return view('perfil.edit', compact('user'));
    }

    /** Actualizar el perfil propio */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-]+$/u'],
            'profesion' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\.]+$/u'],
            'empresa'   => ['nullable', 'string', 'max:150', 'regex:/^[a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s\-\.,&\']+$/u'],
            'ubicacion' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\,\/\.]+$/u'],
            'sobre_mi'  => 'nullable|string|max:2000',
            'telefono'  => ['nullable', 'regex:/^[\d\s\+\-\(\)]{7,20}$/'],
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required'   => 'El nombre es obligatorio.',
            'name.max'        => 'El nombre no puede superar los 100 caracteres.',
            'name.regex'      => 'El nombre solo puede contener letras, espacios y guiones.',
            'profesion.max'   => 'La profesión no puede superar los 100 caracteres.',
            'profesion.regex' => 'La profesión solo puede contener letras, espacios, guiones y puntos.',
            'empresa.max'     => 'El nombre de empresa no puede superar los 150 caracteres.',
            'empresa.regex'   => 'La empresa contiene caracteres no permitidos.',
            'ubicacion.max'   => 'La ubicación no puede superar los 100 caracteres.',
            'ubicacion.regex' => 'La ubicación solo puede contener letras, espacios, guiones, comas y barras.',
            'sobre_mi.max'    => 'El campo "Sobre mí" no puede superar los 2000 caracteres.',
            'telefono.regex'  => 'El teléfono solo puede contener números, espacios, +, - y paréntesis (7-20 caracteres).',
            'avatar.image'    => 'El archivo debe ser una imagen.',
            'avatar.mimes'    => 'Solo se permiten imágenes JPEG o PNG.',
            'avatar.max'      => 'La imagen no puede superar 2 MB.',
        ]);

        $datos = $request->only(['name', 'profesion', 'empresa', 'ubicacion', 'sobre_mi', 'telefono']);

        // Si se sube un nuevo avatar, guardar y eliminar el anterior
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('s3')->delete($user->avatar);
            }
            $datos['avatar'] = $request->file('avatar')->store('avatars', 's3');
        }

        $user->update($datos);

        return redirect()->route('perfil.show', $user->id);
    }

}
