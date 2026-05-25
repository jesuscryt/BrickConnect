<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo de publicación.
 * Los usuarios comparten contenido en el feed.
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contenido',
        'imagen',
    ];

    /** Autor del post */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Comentarios del post */
    public function comentarios()
    {
        return $this->hasMany(Comentario::class)->latest();
    }

    /** URL pública de la imagen almacenada en R2/S3 */
    public function imagenUrl(): ?string
    {
        return $this->imagen ? Storage::disk('s3')->url($this->imagen) : null;
    }
}
