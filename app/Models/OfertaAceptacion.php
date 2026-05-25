<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para registrar las aceptaciones de usuarios en ofertas.
 * Permite que múltiples usuarios acepten la misma oferta.
 */
class OfertaAceptacion extends Model
{
    use HasFactory;

    protected $table = 'oferta_aceptaciones';

    protected $fillable = [
        'oferta_id',
        'user_id',
        'aceptada_en',
    ];

    protected $casts = [
        'aceptada_en' => 'datetime',
    ];

    /** Oferta asociada */
    public function oferta()
    {
        return $this->belongsTo(Oferta::class);
    }

    /** Usuario que aceptó */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
