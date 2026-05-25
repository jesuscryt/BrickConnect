<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de comentario en una publicación.
 */
class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'contenido',
    ];

    /** Post al que pertenece el comentario */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /** Autor del comentario */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
