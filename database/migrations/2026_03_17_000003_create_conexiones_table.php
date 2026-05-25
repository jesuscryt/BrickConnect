<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para la tabla de conexiones entre usuarios.
 * Funciona como el sistema de contactos de LinkedIn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conexiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('users')->onDelete('cascade');   // Quién envía la solicitud
            $table->foreignId('receptor_id')->constrained('users')->onDelete('cascade'); // Quién la recibe
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada'])->default('pendiente');
            $table->timestamps();

            // Evitar solicitudes duplicadas
            $table->unique(['emisor_id', 'receptor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conexiones');
    }
};
