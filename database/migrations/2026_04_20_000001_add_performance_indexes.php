<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índices compuestos en messages para búsquedas de conversaciones y no leídos
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id'], 'messages_sender_receiver_index');
            $table->index(['receiver_id', 'read_at'], 'messages_receiver_read_index');
        });

        // Índice compuesto en notificaciones para filtrar por usuario y estado de lectura
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->index(['usuario_id', 'leida'], 'notificaciones_usuario_leida_index');
        });

        // Índices compuestos en conexiones para búsquedas por estado
        Schema::table('conexiones', function (Blueprint $table) {
            $table->index(['receptor_id', 'estado'], 'conexiones_receptor_estado_index');
            $table->index(['emisor_id', 'estado'], 'conexiones_emisor_estado_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_sender_receiver_index');
            $table->dropIndex('messages_receiver_read_index');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('notificaciones_usuario_leida_index');
        });

        Schema::table('conexiones', function (Blueprint $table) {
            $table->dropIndex('conexiones_receptor_estado_index');
            $table->dropIndex('conexiones_emisor_estado_index');
        });
    }
};
