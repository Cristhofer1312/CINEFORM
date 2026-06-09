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
        Schema::create('taller.inscripcion_respuestas', function (Blueprint $table) {
            $table->id('id_respuesta');
            $table->unsignedInteger('id_inscripcion');
            $table->unsignedBigInteger('id_requisito');
            $table->text('respuesta_texto')->nullable();
            $table->string('ruta_archivo')->nullable();
            $table->timestamps();

            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('taller.inscripciones')->onDelete('cascade');
            $table->foreign('id_requisito')->references('id_requisito')->on('taller.curso_requisitos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taller.inscripcion_respuestas');
    }
};
