<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo de usuario.
 * Representa a profesionales del sector construcción.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profesion',
        'empresa',
        'ubicacion',
        'sobre_mi',
        'telefono',
        'avatar',
        'is_admin',
        'banned_at',
        'ban_expires_at',
        'ban_reason',
    ];

    /**
     * Campos ocultos en serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
            'banned_at'         => 'datetime',
            'ban_expires_at'    => 'datetime',
        ];
    }

    /** Comprueba si el usuario es administrador */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /** Comprueba si el usuario está baneado (soporta baneos temporales y permanentes) */
    public function isBanned(): bool
    {
        if ($this->banned_at === null) {
            return false;
        }

        // Baneo permanente
        if ($this->ban_expires_at === null) {
            return true;
        }

        // Baneo temporal: comprobar si sigue vigente
        return $this->ban_expires_at->isFuture();
    }

    // ── Relaciones ──

    /** Publicaciones del usuario */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /** Ofertas de empleo publicadas */
    public function ofertas()
    {
        return $this->hasMany(Oferta::class);
    }

    /** Conexiones enviadas */
    public function conexionesEnviadas()
    {
        return $this->hasMany(Conexion::class, 'emisor_id');
    }

    /** Conexiones recibidas */
    public function conexionesRecibidas()
    {
        return $this->hasMany(Conexion::class, 'receptor_id');
    }

    /**
     * Obtener todos los contactos aceptados del usuario.
     * Devuelve un Builder (no una Relation): siempre llamar con paréntesis ->contactos()->...
     * No puede usarse como $user->contactos sin paréntesis ni con with('contactos').
     */
    public function contactos()
    {
        $id = $this->id;

        return User::where(function ($q) use ($id) {
            $q->whereIn('id', function ($sub) use ($id) {
                $sub->select('receptor_id')
                    ->from('conexiones')
                    ->where('emisor_id', $id)
                    ->where('estado', 'aceptada');
            })->orWhereIn('id', function ($sub) use ($id) {
                $sub->select('emisor_id')
                    ->from('conexiones')
                    ->where('receptor_id', $id)
                    ->where('estado', 'aceptada');
            });
        });
    }

    /** Notificaciones recibidas */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    /** Notificaciones no leídas */
    public function notificacionesNoLeidas()
    {
        return $this->notificaciones()->where('leida', false);
    }

    /**
     * Sobreescribir la notificación de reset con la versión en español.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /** URL pública del avatar almacenado en R2/S3 */
    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::disk('s3')->url($this->avatar) : null;
    }
}
