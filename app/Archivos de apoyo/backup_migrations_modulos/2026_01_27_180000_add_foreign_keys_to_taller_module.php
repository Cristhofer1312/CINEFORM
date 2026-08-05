<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Claves foráneas para taller_cursos
        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->foreign('id_modalidad')->references('id_modalidad')->on('taller.modalidad')->onDelete('set null');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
        });

        // 2. Claves foráneas para taller_contenido_cursos
        Schema::table('taller.contenido_cursos', function (Blueprint $table) {
            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_tipo_evaluacion')->references('id_tipo_evaluacion')->on('taller.tipo_evaluaciones')->onDelete('set null');
        });

        // 3. Claves foráneas para inscripciones
        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
        });

        // 4. Claves foráneas para taller_calificaciones
        Schema::table('taller.calificaciones', function (Blueprint $table) {
            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');

            // Nota: id_contenido_curso ya tiene su clave foránea en su propia migración
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->dropForeign(['id_modalidad']);
            $table->dropForeign(['id_persona']);
        });

        Schema::table('taller.contenido_cursos', function (Blueprint $table) {
            $table->dropForeign(['id_curso']);
            $table->dropForeign(['id_tipo_evaluacion']);
        });

        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->dropForeign(['id_curso']);
            $table->dropForeign(['id_persona']);
        });

        Schema::table('taller.calificaciones', function (Blueprint $table) {
            $table->dropForeign(['id_curso']);
            $table->dropForeign(['id_persona']);
        });
    }
};
