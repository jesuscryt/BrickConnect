<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de conexión entre usuarios.
 * Sistema de contactos tipo LinkedIn.
 */
class Conexion extends Model
{
    use HasFactory;

    protected $table = 'conexiones'; // Nombre de la tabla en español

    protected $fillable = [
        'emisor_id',
        'receptor_id',
        'estado',
    ];

    /** Usuario que envió la solicitud */
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    /** Usuario que recibe la solicitud */
    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }

    /**
     * Scope para buscar conexiones entre dos usuarios (en cualquier dirección).
     */
    public function scopeEntreUsuarios($query, int $userId1, int $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where(function ($inner) use ($userId1, $userId2) {
                $inner->where('emisor_id', $userId1)->where('receptor_id', $userId2);
            })->orWhere(function ($inner) use ($userId1, $userId2) {
                $inner->where('emisor_id', $userId2)->where('receptor_id', $userId1);
            });
        });
    }
}
