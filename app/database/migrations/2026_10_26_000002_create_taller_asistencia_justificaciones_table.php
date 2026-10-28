<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * D3 (26/10): para certificar se exige el 100 % de asistencia. El facilitador
     * puede justificar cada falta con un motivo, y la falta justificada cuenta
     * como cumplida. Una fila por clase e inscripción.
     */
    public function up(): void
    {
        Schema::create('taller.asistencia_justificaciones', function (Blueprint $table) {
            $table->id('id_justificacion');
            $table->unsignedBigInteger('id_contenido_curso');
            $table->unsignedBigInteger('id_inscripcion');
            $table->text('motivo');
            $table->unsignedBigInteger('justificada_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id_contenido_curso', 'id_inscripcion'], 'uq_justificacion_actividad_inscripcion');

            $table->foreign('id_contenido_curso')
                ->references('id_contenido_curso')
                ->on('taller.contenido_cursos')
                ->onDelete('cascade');
            $table->foreign('id_inscripcion')
                ->references('id_inscripcion')
                ->on('taller.inscripciones')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taller.asistencia_justificaciones');
    }
};
