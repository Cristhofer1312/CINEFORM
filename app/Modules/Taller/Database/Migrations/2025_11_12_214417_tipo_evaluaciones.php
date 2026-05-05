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
        Schema::create('taller.tipo_evaluaciones', function (Blueprint $table) {
            $table->id('id_tipo_evaluacion');
            $table->string('nombre');
            $table->string('descripcion');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });
        DB::table('taller.tipo_evaluaciones')->insert([
            [
                'id_tipo_evaluacion' => 1,
                'nombre' => 'Examen',
                'descripcion' => 'Evaluacion escrita sobre un tema en especifico',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => 1,
                'actualizado_en' => now(),
            ],
            [
                'id_tipo_evaluacion' => 2,
                'nombre' => 'Exposicion',
                'descripcion' => 'Evaluacion realizando una exposicion sobre un tema en especifico',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => 1,
                'actualizado_en' => now(),
            ],
            [
                'id_tipo_evaluacion' => 3,
                'nombre' => 'Trabajo',
                'descripcion' => 'Evaluacion realizando un documento sobre un tema en especifico',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => 1,
                'actualizado_en' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.tipo_evaluaciones');
    }
};
