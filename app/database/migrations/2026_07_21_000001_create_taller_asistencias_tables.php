<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla 1: Tokens temporales para links/QR de asistencia
        Schema::create('taller.asistencia_tokens', function (Blueprint $table) {
            $table->id('id_token');
            $table->unsignedBigInteger('id_contenido_curso');
            $table->string('token', 64)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_expiracion')->nullable();
            $table->unsignedBigInteger('creado_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_contenido_curso')
                  ->references('id_contenido_curso')
                  ->on('taller.contenido_cursos')
                  ->onDelete('cascade');
        });

        // Tabla 2: Registros de asistencia individual por actividad
        Schema::create('taller.asistencias', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->unsignedBigInteger('id_contenido_curso');
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_inscripcion');
            $table->timestamp('fecha_hora_marcado');
            $table->boolean('activa')->default(true);
            $table->unsignedBigInteger('anulada_por')->nullable();
            $table->timestamp('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->string('ip_marcado', 45)->nullable();
            $table->string('metodo_marcado', 10)->default('link');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            // Un estudiante solo marca una vez por actividad
            $table->unique(['id_contenido_curso', 'id_persona'], 'uq_asistencia_actividad_persona');

            $table->foreign('id_contenido_curso')
                  ->references('id_contenido_curso')
                  ->on('taller.contenido_cursos')
                  ->onDelete('cascade');
            $table->foreign('id_persona')
                  ->references('id_persona')
                  ->on('comun.personas')
                  ->onDelete('cascade');
            $table->foreign('id_inscripcion')
                  ->references('id_inscripcion')
                  ->on('taller.inscripciones')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taller.asistencias');
        Schema::dropIfExists('taller.asistencia_tokens');
    }
};
