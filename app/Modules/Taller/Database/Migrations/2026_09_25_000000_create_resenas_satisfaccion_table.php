<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taller.resenas_satisfaccion', function (Blueprint $table) {
            $table->id('id_resena');
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_persona');
            $table->unsignedTinyInteger('contenido');
            $table->unsignedTinyInteger('pedagogico');
            $table->unsignedTinyInteger('material');
            $table->unsignedTinyInteger('organizacion');
            $table->timestamps();

            $table->unique(['id_inscripcion', 'id_curso'], 'resenas_satisfaccion_unica');
            $table->index('id_curso');

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('taller.inscripciones')->onDelete('cascade');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
        });

        DB::statement('ALTER TABLE taller.resenas_satisfaccion ADD CONSTRAINT resenas_satisfaccion_rango CHECK (contenido BETWEEN 1 AND 5 AND pedagogico BETWEEN 1 AND 5 AND material BETWEEN 1 AND 5 AND organizacion BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('taller.resenas_satisfaccion');
    }
};
