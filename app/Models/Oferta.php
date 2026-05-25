<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de oferta de empleo.
 * Vacantes del sector construcción.
 */
class Oferta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'empresa',
        'ubicacion',
        'descripcion',
        'tipo_contrato',
        'salario',
        'salario_periodo',
        'salario_tipo',
        'horas_semanales',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    /** Usuario que publicó la oferta */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Aceptaciones de esta oferta */
    public function aceptaciones()
    {
        return $this->hasMany(OfertaAceptacion::class);
    }

    /**
     * Verificar si un usuario aceptó esta oferta.
     * Usa la colección cacheada si ya fue eager-loaded (evita query extra).
     */
    public function estaAceptadaPor(User $user): bool
    {
        return $this->aceptaciones->contains('user_id', $user->id);
    }

    /**
     * Contar el número de candidatos.
     * Usa la colección cacheada si ya fue eager-loaded (evita queries redundantes).
     */
    public function contarAceptaciones(): int
    {
        return $this->aceptaciones->count();
    }
}
