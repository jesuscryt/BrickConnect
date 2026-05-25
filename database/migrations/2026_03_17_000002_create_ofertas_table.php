<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para la tabla de ofertas de empleo.
 * Las empresas/usuarios pueden publicar vacantes del sector construcción.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quién publica la oferta
            $table->string('titulo');                                          // Título del puesto
            $table->string('empresa');                                         // Nombre de la empresa
            $table->string('ubicacion');                                       // Ubicación del trabajo
            $table->text('descripcion');                                       // Descripción completa
            $table->string('tipo_contrato')->default('Tiempo completo');       // Tipo de contrato
            $table->decimal('salario', 10, 2)->nullable();                     // Salario (opcional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
