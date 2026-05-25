<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Nombre completo
            $table->string('email')->unique();               // Email único
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profesion')->nullable();         // Ej: Albañil, Arquitecto, Ingeniero
            $table->string('empresa')->nullable();           // Empresa actual
            $table->string('ubicacion')->nullable();         // Ciudad / Provincia
            $table->text('sobre_mi')->nullable();            // Descripción personal
            $table->string('telefono')->nullable();          // Teléfono de contacto
            $table->string('avatar')->nullable();            // Ruta de la foto de perfil
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
