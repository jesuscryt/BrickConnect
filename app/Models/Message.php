<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de mensajes privados entre usuarios.
 * Solo se pueden enviar mensajes entre contactos aceptados.
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /** Usuario que envió el mensaje */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Usuario que recibe el mensaje */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
