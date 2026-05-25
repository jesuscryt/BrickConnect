<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sincroniza la secuencia de PostgreSQL de la tabla 'ofertas'
 * con el valor máximo de ID existente, evitando
 * UniqueConstraintViolationException al insertar nuevas filas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Solo ejecutar en PostgreSQL; SQLite no tiene secuencias ni pg_get_serial_sequence
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('ofertas', 'id'),
                COALESCE((SELECT MAX(id) FROM ofertas), 0) + 1,
                false
            )
        ");
    }

    public function down(): void
    {
        // No hay forma segura de revertir esto sin perder datos
    }
};
