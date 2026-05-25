<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración para cambiar la estructura de aceptaciones.
 * Permite que múltiples usuarios acepten la misma oferta.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Agregar campo 'activa' a la tabla ofertas si no existe
        if (!Schema::hasColumn('ofertas', 'activa')) {
            Schema::table('ofertas', function (Blueprint $table) {
                $table->boolean('activa')->default(true);
            });
        }

        // Crear tabla de aceptaciones (si no existe)
        if (!Schema::hasTable('oferta_aceptaciones')) {
            Schema::create('oferta_aceptaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('oferta_id')->constrained('ofertas')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('aceptada_en');
                $table->unique(['oferta_id', 'user_id']); // Un usuario solo puede aceptar una vez
                $table->timestamps();
            });
        }

        // Borrar las columnas antiguas de aceptación si existen
        if (Schema::hasColumn('ofertas', 'aceptada_por')) {
            Schema::table('ofertas', function (Blueprint $table) {
                // Primero borrar la clave foránea
                $table->dropForeign(['aceptada_por']);
                // Luego borrar la columna
                $table->dropColumn(['aceptada_por', 'aceptada_en']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_aceptaciones');
        
        Schema::table('ofertas', function (Blueprint $table) {
            if (Schema::hasColumn('ofertas', 'activa')) {
                $table->dropColumn('activa');
            }
        });
    }
};

