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
        Schema::create('taller.contenido_cursos', function (Blueprint $table) {
            $table->id('id_contenido_curso');
            $table->unsignedBigInteger('id_curso');
            $table->string('titulo');
            $table->text('descripcion_breve');
            $table->text('descripcion');
            $table->string('url_contenido')->nullable();
            $table->boolean('es_evaluacion')->default(false);
            $table->unsignedBigInteger('id_tipo_evaluacion')->nullable();
            $table->decimal('ponderacion', 5, 2)->nullable();
            $table->date('fecha_contenido')->nullable();
            $table->unsignedBigInteger('creado_por');
            $table->timestamp('creado_en')->useCurrent();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.contenido_cursos');
    }
};
