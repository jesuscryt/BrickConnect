<?php

namespace App\Http\Controllers;

use App\Models\Conexion;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de conexiones (red de contactos).
 * Enviar, aceptar, rechazar solicitudes y ver red.
 */
class ConexionController extends Controller
{
    /** Ver mi red de contactos */
    public function index()
    {
        $user = Auth::user();

        // Contactos aceptados con su conexión (para poder eliminarla)
        $contactos = $user->contactos()->get();

        // Mapa contacto_id => conexion para mostrar el botón "Desconectar"
        $conexionesAceptadas = $user->conexionesEnviadas()
            ->where('estado', 'aceptada')
            ->get()
            ->keyBy('receptor_id')
            ->merge(
                $user->conexionesRecibidas()
                    ->where('estado', 'aceptada')
                    ->get()
                    ->keyBy('emisor_id')
            );

        // Solicitudes pendientes recibidas
        $pendientes = Conexion::where('receptor_id', $user->id)
            ->where('estado', 'pendiente')
            ->with('emisor')
            ->get();

        return view('red.index', compact('contactos', 'pendientes', 'conexionesAceptadas'));
    }

    /** Enviar solicitud de conexión */
    public function enviar(User $user)
    {
        $emisor = Auth::user();

        // No conectarse a uno mismo
        if ($emisor->id === $user->id) {
            $response = ['success' => false, 'error' => 'No puedes conectarte contigo mismo.'];
            return request()->expectsJson() ? response()->json($response) : back()->with('error', $response['error']);
        }

        // Verificar si ya hay una conexión PENDIENTE o ACEPTADA (no rechazada)
        $conexionExistente = Conexion::entreUsuarios($emisor->id, $user->id)
            ->whereIn('estado', ['pendiente', 'aceptada'])
            ->first();

        if ($conexionExistente) {
            $response = ['success' => false, 'error' => 'Ya existe una solicitud de conexión.'];
            return request()->expectsJson() ? response()->json($response) : back()->with('error', $response['error']);
        }

        // Si hay una conexión rechazada, eliminarla para permitir nueva solicitud
        Conexion::entreUsuarios($emisor->id, $user->id)
            ->where('estado', 'rechazada')
            ->delete();

        $conexion = Conexion::create([
            'emisor_id'   => $emisor->id,
            'receptor_id' => $user->id,
            'estado'      => 'pendiente',
        ]);

        // Crear notificación para el receptor
        Notificacion::create([
            'usuario_id' => $user->id,
            'emisor_id'  => $emisor->id,
            'tipo'       => 'solicitud_amistad',
            'notificable_type' => Conexion::class,
            'notificable_id'   => $conexion->id,
        ]);

        $response = ['success' => true, 'message' => 'Solicitud de conexión enviada.'];
        return request()->expectsJson() ? response()->json($response) : back()->with('success', $response['message']);
    }

    /** Aceptar una solicitud de conexión */
    public function aceptar(Conexion $conexion)
    {
        // Solo el receptor puede aceptar
        if ($conexion->receptor_id !== Auth::id()) {
            abort(403);
        }

        $conexion->update(['estado' => 'aceptada']);

        // Marcar la notificación original (enviada al receptor al recibir la solicitud) como leída
        Notificacion::where('notificable_id', $conexion->id)
                    ->where('notificable_type', Conexion::class)
                    ->update(['leida' => true]);

        // Notificar al emisor que su solicitud fue aceptada
        Notificacion::create([
            'usuario_id'       => $conexion->emisor_id,
            'emisor_id'        => Auth::id(),
            'tipo'             => 'solicitud_aceptada',
            'notificable_type' => Conexion::class,
            'notificable_id'   => $conexion->id,
        ]);

        // Si es AJAX, devolver JSON
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Conexión aceptada.']);
        }

        return back();
    }

    /** Rechazar una solicitud de conexión */
    public function rechazar(Conexion $conexion)
    {
        if ($conexion->receptor_id !== Auth::id()) {
            abort(403);
        }

        $conexion->update(['estado' => 'rechazada']);

        // Marcar notificación como leída
        Notificacion::where('notificable_id', $conexion->id)
                    ->where('notificable_type', Conexion::class)
                    ->update(['leida' => true]);

        // Si es AJAX, devolver JSON
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Solicitud rechazada.']);
        }

        return back()->with('success', 'Solicitud rechazada.');
    }

    /** Eliminar una conexión existente (desconectar) */
    public function eliminar(Conexion $conexion)
    {
        $userId = Auth::id();

        // Solo pueden eliminar los participantes de la conexión
        if ($conexion->emisor_id !== $userId && $conexion->receptor_id !== $userId) {
            abort(403);
        }

        $conexion->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Conexión eliminada.']);
        }

        return back();
    }
}
