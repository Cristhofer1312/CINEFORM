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
        Schema::create('taller.calificaciones', function (Blueprint $table) {
            $table->id('id_calificacion');

            // Relación con el Curso (para facilitar búsquedas)
            $table->unsignedBigInteger('id_curso');

            // Relación con el Contenido (que es la Evaluación en sí)
            $table->unsignedBigInteger('id_contenido_curso');

            // Relación con el Estudiante (Persona)
            $table->unsignedBigInteger('id_persona');

            // La nota
            $table->decimal('calificacion', 5, 2)->nullable(); // Ej: 95.50

            // Feedback
            $table->text('observacion')->nullable();

            // Auditoría
            $table->unsignedBigInteger('calificado_por')->nullable(); // ID del usuario facilitador
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_contenido_curso')->references('id_contenido_curso')->on('taller.contenido_cursos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.calificaciones');
    }
};
