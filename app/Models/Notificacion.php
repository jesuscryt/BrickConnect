<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de notificaciones.
 * Soporta los tipos: solicitud_amistad, solicitud_aceptada, comentario, aceptacion_oferta.
 */
class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'emisor_id',
        'tipo',
        'notificable_type',
        'notificable_id',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    /** Usuario que recibe la notificación */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /** Usuario que envió la notificación */
    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    /** Modelo relacionado polimórficamente (Conexion, Comentario, OfertaAceptacion) */
    public function notificable()
    {
        return $this->morphTo();
    }
}
