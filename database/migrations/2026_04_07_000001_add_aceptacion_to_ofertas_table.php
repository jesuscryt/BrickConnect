<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para agregar campos de aceptación a la tabla de ofertas.
 * Permite registrar quién aceptó la oferta y cuándo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->foreignId('aceptada_por')->nullable()->constrained('users')->onDelete('set null'); // Usuario que aceptó la oferta
            $table->timestamp('aceptada_en')->nullable(); // Fecha de aceptación
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropForeign(['aceptada_por']);
            $table->dropColumn(['aceptada_por', 'aceptada_en']);
        });
    }
};

