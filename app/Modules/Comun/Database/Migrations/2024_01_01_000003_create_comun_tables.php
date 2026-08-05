<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. comun.especializaciones
        Schema::create('comun.especializaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('descripcion');
            $table->string('status');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        // 2. comun.personas → 6 FKs (security.* + comun.estados/municipios/parroquias)
        Schema::create('comun.personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->foreignId('user_id')->constrained('security.users')->onDelete('cascade');
            $table->foreignId('tipo_dni')->constrained('security.document_types');
            $table->string('dni')->nullable();
            $table->string('pasaporte')->nullable();
            $table->string('rif')->nullable();
            $table->string('reg_nac_cine')->nullable();
            $table->foreignId('genero')->constrained('security.genders');
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('telefono')->nullable();
            $table->string('telefono_opcional')->nullable();
            $table->foreignId('id_pais')->constrained('security.countries');
            $table->foreignId('id_estado')->references('id')->on('comun.estados');
            $table->foreignId('id_municipio')->references('id')->on('comun.municipios');
            $table->foreignId('id_parroquia')->references('id')->on('comun.parroquias');
            $table->string('direccion')->nullable();
            $table->integer('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        // 3. comun.personas_educacion → comun.personas, comun.niveles_educacion, comun.carreras
        Schema::create('comun.personas_educacion', function (Blueprint $table) {
            $table->id('id_persona_educacion');
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->unsignedBigInteger('id_nivel_educativo')->nullable();
            $table->unsignedBigInteger('id_carrera')->nullable();
            $table->string('institucion_educativa')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('set null');
            $table->foreign('id_nivel_educativo')->references('id_nivel_educacion')->on('comun.niveles_educacion')->onDelete('set null');
            $table->foreign('id_carrera')->references('id_carrera')->on('comun.carreras')->onDelete('set null');
        });

        // 4. comun.personas_especializacion → comun.personas, comun.especializaciones
        Schema::create('comun.personas_especializacion', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->integer('id_especializacion')->nullable();
            $table->integer('anos_experiencia')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            $table->foreign('id_persona')->references('id_persona')->on('comun.personas')->onDelete('set null');
            $table->foreign('id_especializacion')->references('id')->on('comun.especializaciones')->onDelete('set null');
        });
    }

    public function down(): void
    {
        $tables = [
            'comun.personas_especializacion',
            'comun.personas_educacion',
            'comun.personas',
            'comun.especializaciones',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
