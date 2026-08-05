<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Tabla principal de cursos con todas las columnas de nomenclatura PROCINEC.
     * Código: LAB-{actividad}{trimestre}{correlativo}{aspecto}{modo}{publico}-{año}
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taller.cursos', function (Blueprint $table) {
            $table->id('id_curso');
            $table->string('codigo')->nullable()->comment('Código PROCINEC generado: LAB-TA220FTPAD-2026');
            $table->string('nombre');
            $table->unsignedBigInteger('id_modalidad')->nullable();
            $table->unsignedBigInteger('id_actividad_formativa')->nullable()->comment('Tipo de actividad formativa para la nomenclatura');
            $table->unsignedBigInteger('id_aspecto')->nullable()->comment('Aspecto cinematográfico');
            $table->unsignedBigInteger('id_modalidad_especial')->nullable()->comment('Público objetivo: Niño, Adolescente, Adulto');
            $table->unsignedBigInteger('id_estado')->nullable()->comment('Región del curso');
            $table->unsignedBigInteger('id_persona')->comment('Facilitador del curso');
            $table->text('descripcion')->nullable();
            $table->string('nivel')->nullable()->comment('Básico, Medio, Avanzado');
            $table->tinyInteger('trimestre')->nullable()->comment('Trimestre del año: 1=Ene-Mar, 2=Abr-Jun, 3=Jul-Sep, 4=Oct-Dic');
            $table->integer('correlativo')->nullable()->comment('Número correlativo de la actividad educativa');
            $table->smallInteger('anio')->nullable()->comment('Año en que se realiza el curso');
            $table->integer('duracion')->nullable();
            $table->integer('horas')->nullable();
            $table->string('cantidad_cupos')->nullable();
            $table->string('fecha_inicio')->nullable();
            $table->string('fecha_fin')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
            $table->string('motivo_rechazo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.cursos');
    }
};