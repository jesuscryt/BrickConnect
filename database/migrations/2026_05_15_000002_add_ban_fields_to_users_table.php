<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade campos de duración y motivo de baneo a la tabla de usuarios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('ban_expires_at')->nullable()->after('banned_at'); // null = permanente
            $table->string('ban_reason')->nullable()->after('ban_expires_at');   // motivo opcional
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ban_expires_at', 'ban_reason']);
        });
    }
};
