<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Crea la tabla taller.observaciones_curso para almacenar el historial
     * de rechazos/observaciones de gerencia sobre un curso, y elimina las
     * columnas obsoletas `motivo` de curso_estado y `motivo_rechazo` de cursos.
     */
    public function up(): void
    {
        // 1. Crear tabla de observaciones
        Schema::create('taller.observaciones_curso', function (Blueprint $table) {
            $table->id('id_observacion');
            $table->unsignedBigInteger('id_curso');
            $table->text('observacion');
            $table->unsignedBigInteger('creado_por')->nullable()->comment('ID del usuario que registró la observación');
            $table->timestamps();

            $table->foreign('id_curso')
                ->references('id_curso')
                ->on('taller.cursos')
                ->onDelete('cascade');
        });

        // 2. Eliminar columna motivo de curso_estado
        Schema::table('taller.curso_estado', function (Blueprint $table) {
            $table->dropColumn('motivo');
        });

        // 3. Eliminar columna motivo_rechazo de cursos
        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->dropColumn('motivo_rechazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taller.observaciones_curso');

        Schema::table('taller.curso_estado', function (Blueprint $table) {
            $table->text('motivo')->nullable();
        });

        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->string('motivo_rechazo')->nullable();
        });
    }
};
