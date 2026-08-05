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
        // 1. Requisitos para facilitador (globales)
        Schema::create('taller.requisitos_facilitador', function (Blueprint $table) {
            $table->id('id_requisito_facilitador');
            $table->string('tipo', 50); // 'pregunta', 'recurso', 'documento'
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('creado_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('creado_por')->references('id')->on('security.users')->onDelete('cascade');
        });

        // 2. Postulaciones a facilitador
        Schema::create('taller.postulaciones_facilitador', function (Blueprint $table) {
            $table->id('id_postulacion');
            $table->unsignedBigInteger('id_persona');
            $table->string('estado', 20)->default('pendiente'); // 'pendiente', 'aprobada', 'rechazada'
            $table->text('motivo_rechazo')->nullable();
            $table->unsignedBigInteger('revisada_por')->nullable();
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
            $table->foreign('revisada_por')->references('id')->on('security.users')->onDelete('set null');

            // Índice para búsquedas por persona
            $table->index('id_persona', 'idx_postulaciones_persona');
        });

        // Índice parcial único para PostgreSQL (una persona solo puede tener una postulación pendiente)
        \Illuminate\Support\Facades\DB::statement("CREATE UNIQUE INDEX uniq_postulacion_pendiente ON taller.postulaciones_facilitador (id_persona) WHERE estado = 'pendiente'");

        // 3. Respuestas de postulación
        Schema::create('taller.postulacion_respuestas', function (Blueprint $table) {
            $table->id('id_respuesta');
            $table->unsignedBigInteger('id_postulacion');
            $table->unsignedBigInteger('id_requisito_facilitador');
            $table->text('respuesta_texto')->nullable();
            $table->string('ruta_archivo')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_postulacion')->references('id_postulacion')->on('taller.postulaciones_facilitador')->onDelete('cascade');
            $table->foreign('id_requisito_facilitador')->references('id_requisito_facilitador')->on('taller.requisitos_facilitador')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taller.postulacion_respuestas');
        Schema::dropIfExists('taller.postulaciones_facilitador');
        Schema::dropIfExists('taller.requisitos_facilitador');
    }
};
