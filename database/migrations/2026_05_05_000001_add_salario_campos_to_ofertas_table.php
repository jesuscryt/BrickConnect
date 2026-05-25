<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade columnas de periodo y tipo (neto/bruto) al salario de las ofertas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->string('salario_periodo')->nullable()->after('salario'); // mensual, anual, por hora, etc.
            $table->string('salario_tipo')->nullable()->after('salario_periodo');   // bruto, neto
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn(['salario_periodo', 'salario_tipo']);
        });
    }
};
