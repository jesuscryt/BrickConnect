<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de notificaciones.
 * Registra notificaciones para solicitudes de amistad y comentarios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // A quién va dirigida
            $table->foreignId('emisor_id')->constrained('users')->onDelete('cascade'); // Quién la envía
            $table->string('tipo'); // 'solicitud_amistad', 'comentario', etc.
            $table->morphs('notificable'); // Polimórfica para enlazar a diferentes modelos
            $table->boolean('leida')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
