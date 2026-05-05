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
        Schema::create('comun.personas_educacion', function (Blueprint $table) {
            $table->id('id_persona_educacion'); // "id_persona_educacion" PRIMARY KEY
            $table->unsignedBigInteger('id_persona')->nullable(); // "id_persona" int
            $table->unsignedBigInteger('id_nivel_educativo')->nullable(); // "id_nivel_educativo" int
            $table->unsignedBigInteger('id_carrera')->nullable(); // "id_carrera" int
            $table->string('instituacion_educativa')->nullable(); // "instituacion_educativa" varchar
            $table->date('fecha_inicio')->nullable(); // "fecha_inicio" date
            $table->date('fecha_fin')->nullable(); // "fecha_fin" date
            $table->string('estado')->nullable(); // "estado" varchar
            
            // Columnas de auditoría
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            // Llaves foráneas (se agregarán en la migración de llaves foráneas)
            // $table->foreign('id_persona')->references('id_persona')->on('comun_personas');
            // $table->foreign('id_nivel_educativo')->references('id_nivel_educativo')->on('comun_niveles_educativos');
            // $table->foreign('id_carrera')->references('id_carrera')->on('comun_carreras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comun.personas_educacion');
    }
};