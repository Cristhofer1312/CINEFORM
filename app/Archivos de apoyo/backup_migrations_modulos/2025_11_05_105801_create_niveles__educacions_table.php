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
        Schema::create('comun.niveles_educacion', function (Blueprint $table) {
            $table->increments('id_nivel_educacion');
            $table->string('nivel', 255);
            $table->string('descripcion', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::table('comun.niveles_educacion')->insert([
            ['id_nivel_educacion' => 1, 'nivel' => 'Educación Inicial', 'descripcion' => 'Educación preescolar y jardín'],
            ['id_nivel_educacion' => 2, 'nivel' => 'Educación Primaria', 'descripcion' => 'Ciclo básico de educación formal'],
            ['id_nivel_educacion' => 3, 'nivel' => 'Educación Secundaria', 'descripcion' => 'Educación media obligatoria'],
            ['id_nivel_educacion' => 4, 'nivel' => 'Educación Técnica', 'descripcion' => 'Formación técnica o profesional'],
            ['id_nivel_educacion' => 5, 'nivel' => 'Educación Universitaria', 'descripcion' => 'Programas de pregrado universitario'],
            ['id_nivel_educacion' => 6, 'nivel' => 'Postgrado', 'descripcion' => 'Especialización, maestría, doctorado'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comun.niveles_educacion');
    }
};
