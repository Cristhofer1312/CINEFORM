<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tablas sin dependencias
        Schema::create('taller.modalidad', function (Blueprint $table) {
            $table->id('id_modalidad');
            $table->string('nombre_modalidad');
            $table->string('abreviatura', 1);
            $table->text('descripcion');
            $table->string('status');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('taller.tipo_evaluaciones', function (Blueprint $table) {
            $table->id('id_tipo_evaluacion');
            $table->string('nombre');
            $table->string('descripcion');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('taller.estados_curso', function (Blueprint $table) {
            $table->id('id_estado');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('taller.aspectos', function (Blueprint $table) {
            $table->id('id_aspecto');
            $table->string('nombre');
            $table->string('abreviatura')->nullable();
            $table->string('status')->default('Activo');
            $table->timestamps();
        });

        Schema::create('taller.actividades_formativas', function (Blueprint $table) {
            $table->id('id_actividad_formativa');
            $table->string('nombre');
            $table->string('abreviatura', 2);
            $table->string('status')->default('Activo');
            $table->timestamps();
            $table->unique('abreviatura');
        });

        Schema::create('taller.modalidades_especiales', function (Blueprint $table) {
            $table->id('id_modalidad_especial');
            $table->string('nombre');
            $table->string('abreviatura', 2);
            $table->string('status')->default('Activo');
            $table->timestamps();
            $table->unique('abreviatura');
        });

        // 2. taller.cursos → (5 FKs internas + 1 FK a comun.personas)
        Schema::create('taller.cursos', function (Blueprint $table) {
            $table->id('id_curso');
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->unsignedBigInteger('id_modalidad')->nullable();
            $table->unsignedBigInteger('id_actividad_formativa')->nullable();
            $table->unsignedBigInteger('id_aspecto')->nullable();
            $table->unsignedBigInteger('id_modalidad_especial')->nullable();
            $table->unsignedBigInteger('id_persona');
            $table->text('descripcion')->nullable();
            $table->string('nivel')->nullable();
            $table->tinyInteger('trimestre')->nullable();
            $table->integer('correlativo')->nullable();
            $table->smallInteger('anio')->nullable();
            $table->integer('duracion')->nullable();
            $table->integer('horas')->nullable();
            $table->string('cantidad_cupos')->nullable();
            $table->string('fecha_inicio')->nullable();
            $table->string('fecha_fin')->nullable();
            $table->string('telegram')->nullable();
            $table->boolean('es_nacional')->default(false);
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            $table->foreign('id_modalidad')->references('id_modalidad')->on('taller.modalidad')->onDelete('set null');
            $table->foreign('id_actividad_formativa')->references('id_actividad_formativa')->on('taller.actividades_formativas')->onDelete('set null');
            $table->foreign('id_aspecto')->references('id_aspecto')->on('taller.aspectos')->onDelete('set null');
            $table->foreign('id_modalidad_especial')->references('id_modalidad_especial')->on('taller.modalidades_especiales')->onDelete('set null');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
        });

        // 3. taller.contenido_cursos → taller.cursos, taller.tipo_evaluaciones
        Schema::create('taller.contenido_cursos', function (Blueprint $table) {
            $table->id('id_contenido_curso');
            $table->unsignedBigInteger('id_curso');
            $table->string('titulo');
            $table->text('descripcion_breve');
            $table->text('descripcion');
            $table->string('url_contenido')->nullable();
            $table->integer('orden')->nullable();
            $table->boolean('es_evaluacion')->default(false);
            $table->unsignedBigInteger('id_tipo_evaluacion')->nullable();
            $table->decimal('ponderacion', 5, 2)->nullable();
            $table->date('fecha_contenido')->nullable();
            $table->unsignedBigInteger('creado_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_tipo_evaluacion')->references('id_tipo_evaluacion')->on('taller.tipo_evaluaciones')->onDelete('set null');
        });

        // 4. taller.inscripciones → taller.cursos, comun.personas
        Schema::create('taller.inscripciones', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_curso')->nullable();
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->date('fecha_inscripcion');
            $table->string('estado', 20)->default('activa');
            $table->integer('rechazada_por')->nullable();
            $table->timestamp('fecha_rechazo')->nullable();
            $table->text('motivo_estado')->nullable();
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
        });

        // 5. taller.curso_estado → taller.cursos, taller.estados_curso
        Schema::create('taller.curso_estado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_estado');
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_estado')->references('id_estado')->on('taller.estados_curso')->onDelete('cascade');
        });

        // 6. taller.calificaciones → taller.cursos, taller.contenido_cursos, comun.personas
        Schema::create('taller.calificaciones', function (Blueprint $table) {
            $table->id('id_calificacion');
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_contenido_curso');
            $table->unsignedBigInteger('id_persona');
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('calificado_por')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_contenido_curso')->references('id_contenido_curso')->on('taller.contenido_cursos');
            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('cascade');
        });

        // 7. taller.observaciones_curso → taller.cursos
        Schema::create('taller.observaciones_curso', function (Blueprint $table) {
            $table->id('id_observacion');
            $table->unsignedBigInteger('id_curso');
            $table->text('observacion');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
        });

        // 8. taller.curso_localidades → taller.cursos, comun.estados
        Schema::create('taller.curso_localidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_estado');

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_estado')->references('id')->on('comun.estados')->onDelete('cascade');
            $table->unique(['id_curso', 'id_estado']);
        });

        // 9. taller.curso_requisitos → taller.cursos
        Schema::create('taller.curso_requisitos', function (Blueprint $table) {
            $table->id('id_requisito');
            $table->unsignedBigInteger('id_curso');
            $table->string('tipo', 50);
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
        });

        // 10. taller.inscripcion_respuestas → taller.inscripciones, taller.curso_requisitos
        Schema::create('taller.inscripcion_respuestas', function (Blueprint $table) {
            $table->id('id_respuesta');
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_requisito');
            $table->text('respuesta_texto')->nullable();
            $table->string('ruta_archivo')->nullable();
            $table->timestamps();

            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('taller.inscripciones')->onDelete('cascade');
            $table->foreign('id_requisito')->references('id_requisito')->on('taller.curso_requisitos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $tables = [
            'taller.inscripcion_respuestas',
            'taller.curso_requisitos',
            'taller.curso_localidades',
            'taller.observaciones_curso',
            'taller.calificaciones',
            'taller.curso_estado',
            'taller.inscripciones',
            'taller.contenido_cursos',
            'taller.cursos',
            'taller.modalidades_especiales',
            'taller.actividades_formativas',
            'taller.aspectos',
            'taller.estados_curso',
            'taller.tipo_evaluaciones',
            'taller.modalidad',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
